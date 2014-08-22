<?php
class MediabankMedvidConnector extends MediabankAbstractConnector{

    private $db = null;
    private $searchQueryFormat = null;
    private $result = array('single' => array(), 'multi' => array(), 'notfound' => array(),'error'=>true ,'error_string' => '');
    private $reindexTaIds = array();
    
    public function __construct() {
        try {
            $this->mediabankResourceService = new MediabankResourceService();
            $this->db = Zend_Registry::get("db");
            $this->searchQueryFormat = $this->getSearchQueryFormat();
        } catch (Exception $ex) {
            Zend_Registry::get('logger')->warn("\n".$ex->getMessage()."\n".$ex->getTraceAsString()."\n\n");
        }
    }

    public function link() {
        $medvidmids = $this->getAllMidsForCollection(MedvidConst::$repositoryId, MedvidConst::$collectionId);
        if(!empty($medvidmids)) {
            $currentYearMedvidmids = $this->getMidsForCurrentYear($medvidmids);
            if(!empty($currentYearMedvidmids)) {
                return $this->startLinking($currentYearMedvidmids);
            }
            $currentYear = date('Y',time());
            $this->result['error_string'] = 'Could not find any mids for current year : ' .$currentYear;
        } else {
            $this->result['error_string'] = 'Mediabank did not return any mids for Medvid collection.';
        }
        return $this->result;
    }

    private function startLinking($medvidmids) {
        $compassmids = $this->getAllMidsForCompass();
        if(!empty($compassmids)) {
            $notaddedmids = array_diff($medvidmids, $compassmids);
            if(!empty($notaddedmids)) {
                foreach($notaddedmids as $mid) {
                    $this->addMidInCompass($mid);
                }
                $queries = $this->createInsertQuery();
                $insertQueries = $this->insertQueries($queries);
                if($insertQueries === false) {
                    $this->result['error_string'] = 'Could not insert queries in the database.';
                } else {
                    $this->reindexTeachingActivities($this->reindexTaIds);
                    $this->result['error'] = false;
                }
            } else {
                $this->result['error_string'] = 'No difference between Medvid collection mids and compass mids found.';
            }
        } else {
            $this->result['error_string'] = "Could not find any mids from compass.";
        }
        return $this->result;
    }
    
    private function getMidsForCurrentYear($medvidmids) {
        $currentYearMedvidmids = array();
        $currentYear = date('Y',time());
        if(!empty($medvidmids)) {
            foreach($medvidmids as $mid) {
                $explode = explode('|',$mid);
                if(isset($explode[2]) && strpos($explode[2], $currentYear.'_') !== false) {
                    $currentYearMedvidmids[] = $mid;
                }
            }
        }
        return $currentYearMedvidmids;
    }
    
    private function addMidInCompass($mid) {
        $metadata = $this->getMetadataForMid($mid);
        if(!empty($metadata)) {
            $tas = $this->getTasLinkedToMetadata($metadata);
            if($tas === false) {
                $this->result['notfound'][] = array('mid' => $mid, 'metadata' => $metadata);
            } else if(is_array($tas)) {
                $this->result['multi'][] = array('mid' => $mid, 'tas' => $tas);
            } else {
                $this->result['single'][] = array('mid' => $mid, 'ta' => $tas);
            }
        }
    }    
    
    private function createInsertQuery() {
        $query = '';
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
    
    private function getTasLinkedToMetadata($metadata) {
        $return = array();
        if(is_array($metadata) && !empty($metadata)) {
            $block          = (int)$metadata['block'];
            $calendarYear   = (int)$metadata['calendar_year'];
            $taType         = ucwords($metadata['ta_type']);
            $weeknum        = (int)$metadata['week'];
            $seq            = (int)$metadata['sequence'];
            $query          = sprintf($this->searchQueryFormat, $block, $taType, $weeknum, $seq);
            $rows           = $this->db->query($query)->fetchAll();
            if(count($rows) > 0) {
                foreach($rows as $row) {
                    $return[] = $row['auto_id'];                        
                }
            }
        }
        if(!empty($return)) {
            if(count($return) == 1){
                return $return[0];
            }
            return $return;
        }
        return false;
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
        
 }
?>