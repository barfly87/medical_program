<?php
class MediabankLearningTopicConnector extends MediabankAbstractConnector{

    private $db = null;
    private $searchQueryFormat = null;
    private $query = '+collectionID:"compassresources" +native.cmsdocumentid:"%d"';
    private $result = array('error'=>true ,'error_string' => '');
    private $loginfo = '';
    
    public function __construct() {
        try {
            $this->mediabankResourceService = new MediabankResourceService();
            $this->db = Zend_Registry::get("db");
        } catch (Exception $ex) {
            Zend_Registry::get('logger')->warn("\n".$ex->getMessage()."\n".$ex->getTraceAsString()."\n\n");
        }
    }

    public function link() {
        $this->startLinking();   
        return $this->result;
    }
    
    private function startLinking() {
        $rows = $this->getAllResourcesForCompass("resource_id like '%cmsdocs-%'");    
        if(!empty($rows)) {
            $queries = '';
            if(is_null($this->mediabankResource)) {
                $this->mediabankResource = new MediabankResource();
            }
            //Debugging $rows = array(0 => array('resource_id'=>'http://smp.sydney.edu.au/mediabank/|cmsdocs-2010|18974', 'type_id' => '462'));
            foreach($rows as $row) {
                $learningtopicdata = $this->processCompassMid($row['resource_id']);
                if(!empty($learningtopicdata)) {
                    $queries .= $this->createInsertQuery($learningtopicdata, $row['type_id']);
                }
            }
            
            if(!empty($queries)) {
                $insert = $this->insertQueries($queries);
                if($insert === true) {
                    $this->result['error'] = false;
                }
            }     
        } else {
            $this->result['error_string'] = "Could not find any mids in compass which are like '%cmsdocs-%'";
        }
    }
    
    private function insertQueries($queries) {
        $this->db->beginTransaction();
        try {
            $this->db->getConnection()->exec($queries);
            $this->db->commit();
            return true;
        } catch (Exception $ex) {
            $this->db->rollBack();
            $this->result['error_string'] = "Database error";
            Zend_Registry::get('logger')->warn("\n".$ex->getMessage()."\n".$ex->getTraceAsString()."\n\n");
            return false;
        }
    }
        
    private function createInsertQuery($data, $taid) {
        $query = '';
        $queryFormat = "INSERT INTO lk_resource(auto_id, type, type_id, resource_type, resource_id, order_by,resource_type_id) VALUES(DEFAULT, 'ta',%d,'".ResourceConstants::$TYPE_MEDIABANK."','%s',1,%s);\n";
        
        if(!empty($data)) {
            foreach($data as $learningtopic) {
                $resourceType = null;
                if($learningtopic['cmsresourcetype'] == 'Learning Topic') {
                    $resourceType = 8;
                } else if ($learningtopic['cmsresourcetype'] == 'Learning Topic Reference') {
                    $resourceType = 9;
                }
                if($resourceType != null) {
                    $row = $this->mediabankResource->fetchAll("resource_id = '{$learningtopic['mid']}' and type_id = $taid");
                    if(! $row->count() > 0) {
                        $query .= sprintf($queryFormat, $taid, $learningtopic['mid'], $resourceType);
                    }
                }
            }
        }
        return $query;
    }
    
    private function processCompassMid($compassmid) {
        $documentId = $this->getDocumentId($compassmid);
        if(!empty($documentId)) {
            $query = sprintf($this->query,$documentId);
            $searchResult = $this->searchForDocumentIdInMediabank($query);
            if($searchResult !== false) {
                return $this->getMidsFromSearchResults($searchResult);
            } else {
                $this->loginfo .= "Could not find any object for this query :".$query."\n";
                return false;
            }
        } else {
            $this->loginfo .= "Could not find document id in mid :".$compassmid."\n";
            return false;    
        }
    }
    
    private function getMidsFromSearchResults($searchResult) {
        $data = array();
        if(is_array($searchResult) && !empty($searchResult)) {
            foreach($searchResult as $obj) {
                if(isset($obj->attributes) && isset($obj->attributes['mid'])) {
                    $mid = $obj->attributes['mid'];
                    $metadata = $this->getMetadataForMid($mid);
                    $data[] = array('mid'=> $mid, 'cmsresourcetype' => $metadata['cmsresourcetype']);
                }
            }
        }
        if(empty($data)) {
            $this->loginfo .= "Could not get mid from search result ".print_r($searchResult, true)."\n";
            return false;
        }
        return $data;
    }
    
    private function getDocumentId($mid) {
        $explode = explode('|',$mid);
        if(isset($explode[2]) && (int)$explode[2] > 0) {
            return (int)$explode[2];
        }
        return '';
    }
    
    private function searchForDocumentIdInMediabank($query) {
        return $this->mediabankResourceService->search($query);
    }
    
    public function __destruct() {
        Zend_Registry::get('logger')->info($this->loginfo);
    }
        
 }
?>