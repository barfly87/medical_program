<?php
class MediabankEcho360Connector extends MediabankAbstractConnector{

    private $db                 = null;
    private $searchQueryFormat  = null;
    private $query              = null;
    private $result             = array('single' => array(), 'multi' => array(), 'notfound' => array(),'error'=>true ,'error_string' => '');
    private $reindexTaIds       = array();
    private $collectionId       = null;
    
    public function __construct() {
        try {
            $this->collectionId = MediabankResourceConstants::$COLLECTION_echo360;
            $this->mediabankResourceService = new MediabankResourceService();
            $this->searchQueryFormat = $this->getSearchQueryFormat();
            $this->db = Zend_Registry::get("db");
            $this->query = '+collectionID:"'.$this->collectionId.'" +smps.calendar_year:"'.date('Y',time()).'"';
        } catch (Exception $ex) {
            Zend_Registry::get('logger')->warn("\n".$ex->getMessage()."\n".$ex->getTraceAsString()."\n\n");
        }
    }

    public function link() {
        $array = array();
        $this->startLinking();
        return $this->result;
    }
    
    private function startLinking() {
        //get existing compass mids
        $lkResourceRows = $this->getAllResourcesForCompass("resource_id like '%|".$this->collectionId."|%'");
        $compassmids = $this->getEcho360MidsFromRows($lkResourceRows);
        
        //get echo360 mids from mediabank
        $mediabankSearchResults = $this->mediabankResourceService->search($this->query);
        $mediabankmids = array();
        if($mediabankSearchResults !== false) {
            foreach($mediabankSearchResults as $result) {
                $mediabankmids[] = $result->attributes['mid'];
            }
        }
        if(empty($mediabankmids)){
            $error = 'Mediabank did not return any echo360 mids';
            Zend_Registry::get('logger')->warn(PHP_EOL."CLASS\t: ".__CLASS__.PHP_EOL."METHOD\t: ".__METHOD__.PHP_EOL."Error\t: ".$error.PHP_EOL);
            $this->result['error_string'] = $error;
            return;
        }
        sort($mediabankmids,SORT_STRING);
        Zend_Registry::get('logger')->warn("\nMediabank returned results for ".count($mediabankSearchResults)." echo360 search query.\n".print_r($mediabankmids, true));
        
        //create new mids in compass using difference between mediabank mids and compass mids
        $notaddedmids = array_diff($mediabankmids, $compassmids);
        if(empty($notaddedmids)) {
            $error = 'Nothing to add. No difference between Echo360 collection mids and compass mids found.';
            Zend_Registry::get('logger')->warn(PHP_EOL."CLASS\t: ".__CLASS__.PHP_EOL."METHOD\t: ".__METHOD__.PHP_EOL."Error\t: ".$error.PHP_EOL);
            $this->result['error'] = false;
            return;
        } else {
            Zend_Registry::get('logger')->warn(PHP_EOL.'Difference between compass mids and mediabank mids'.PHP_EOL.print_r($notaddedmids, true));
        }
        
        foreach($notaddedmids as $mid) {
            $this->addMidInCompass($mid);
        }
        //Create insert queries using $this->result['single'] and $this->result['multi'] rows
        $queries = $this->createInsertQuery();
        Zend_Registry::get('logger')->warn(PHP_EOL. "Notfound, Single or Multi".PHP_EOL.print_r($this->result,true));
        if(strlen(trim($queries)) > 0) {
            Zend_Registry::get('logger')->warn(PHP_EOL. "Trying to insert this queries" .PHP_EOL);
            $insertQueries = $this->insertQueries($queries);
            if($insertQueries === false) {
                $error = 'Could not insert queries in the database.';
                $this->result['error_string'] = $error;
                Zend_Registry::get('logger')->warn(PHP_EOL."CLASS\t: ".__CLASS__.PHP_EOL."METHOD\t: ".__METHOD__.PHP_EOL."Error\t: ".$error.PHP_EOL);
            } else {
                $this->reindexTeachingActivities($this->reindexTaIds);
                $this->result['error'] = false;
            }
        } else {
            Zend_Registry::get('logger')->warn(PHP_EOL. "No queries created." .PHP_EOL);
            $this->result['error'] = false;
        }
        
    }    
    
    private function getEcho360MidsFromRows($rows) {
        $mids = array();
        if(! empty($rows)) {
            foreach($rows as $row) {
                $mids[] = $row['resource_id'];
            }
        }
        return $mids;
    }
    
    private function addMidInCompass($mid) {
        $metadata = $this->getMetadataForMid($mid,'Sydney Medical Program Structure');
        if(! empty($metadata)) {
            $tas = $this->getTasLinkedToMetadata($metadata, $mid);
            if($tas === false) {
                $this->result['notfound'][] = array('mid' => $mid, 'metadata' => $metadata);
            } else if(is_array($tas)) {
                $this->result['multi'][] = array('mid' => $mid, 'tas' => $tas);
            } else {
                $this->result['single'][] = array('mid' => $mid, 'ta' => $tas);
            }
        } else {
            Zend_Registry::get('logger')->warn("Empty metadata returned for schema 'Sydney Medical Program Structure' for MID\t: " . $mid);
        }
    } 

    private function getTasLinkedToMetadata($metadata, $mid) {
        $return = array();
        if(!empty($metadata) && is_array($metadata)) {
            $calendarYear   = (int)$metadata['calendar_year'];
            $block          = (int)$metadata['block'];
            $taType         = ucwords($metadata['ta_type']);
            $weeknum        = (int)$metadata['week'];
            $seq            = (int)$metadata['sequence'];
            
            if($calendarYear > 0 && $block >= 0 && !empty($taType) && $weeknum > 0 && $seq > 0) {
                try {
                    $taType         = ($taType == 'Theme Session') ? 'Theme session' : $taType;
                    $query          = sprintf($this->searchQueryFormat, $block, $taType, $weeknum, $seq);
                    $rows           = $this->db->query($query)->fetchAll();
                    
                    if(count($rows) > 0) {
                        foreach($rows as $row) {
                            $return[] = $row['auto_id'];                        
                        }
                    } else {
                        Zend_Registry::get('logger')->warn("\nQuery to find teaching activities : ".$query."\n");
                        $msg = "Could not find any teaching activities attached for this mid : $mid".PHP_EOL;
                        $msg .="Calendar Year : $calendarYear, Block : $block, Ta Type : $taType, Weeknum : $weeknum, Sequence : $seq";
                        Zend_Registry::get('logger')->warn("\n".$msg."\n");
                    }
                    
                } catch (Exception $ex) {
                    Zend_Registry::get('logger')->warn("\nDatabase error for fetching tas for mid $mid".$ex->getMessage()."\n".$ex->getTraceAsString()."\n\n");
                    return false;
                }
            }                
        } else {
            $msg = "Incorrect or missing metadata for mid $mid".PHP_EOL."Calendar Year : $calendarYear, Block : $block, Ta Type : $taType, Weeknum : $weeknum, Sequence : $seq";
            Zend_Registry::get('logger')->warn("\n".$msg."\n");
        }
        
        if(!empty($return)) {
            if(count($return) == 1){
                return $return[0];
            }
            return $return;
        }
        return false;
    }    
    
    private function getSearchQueryFormat() {
        return <<<HEREDOCS
            SELECT ta.auto_id FROM teachingactivity as ta
            WHERE 
                ta.block        = (select block_id from stage_block_seq   where seq_no = %d)        and
                ta.type         = (select auto_id from lk_activitytype  where name ='%s')           and
                ta.block_week   = (select auto_id from lk_blockweek     where weeknum = %d)         and
                ta.sequence_num = (select auto_id from lk_sequence_num  where seqnum = %d);
HEREDOCS;
    } 

    private function createInsertQuery() {
        $query = '';
        $taIds = array();

        //Create INSERT Queries
        $queryFormat = "INSERT INTO lk_resource(auto_id, type, type_id, resource_type, resource_id, order_by,resource_type_id) VALUES(DEFAULT, 'ta',%d,'".ResourceConstants::$TYPE_MEDIABANK."','%s',1,13);\n";
        if(!empty($this->result['single'])) {
            $query .= "\n\n\n";
            foreach($this->result['single'] as $row) {
                $this->reindexTaIds[] = (int)$row['ta'];
                $query .= sprintf($queryFormat, $row['ta'], $row['mid']);
            }
            $query .= "\n\n\n";
        }
        
        if(!empty($this->result['multi'])) {
            $query .="\n\n\n";
            foreach($this->result['multi'] as $row) {
                foreach($row['tas'] as $ta) {
                    $this->reindexTaIds[] = (int)$ta;
                    $query .= sprintf($queryFormat, $ta, $row['mid']);
                }
                $query .="\n";
            }
        } 
        
        return $query;
    }    
    
    private function insertQueries($queries) {
        $this->db->beginTransaction();
        try {
            $this->db->getConnection()->exec($queries);
            $this->db->commit();
            return true;
        } catch (Exception $ex) {
            $this->db->rollBack();
            Zend_Registry::get('logger')->warn("\n".$ex->getMessage()."\n".$ex->getTraceAsString()."\n\n");
            return false;
        }
    }
        
 }
?>