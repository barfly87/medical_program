<?php
class LinkDoctypeTrigger extends LinkDoctypeAbstract {
    private $pblSessionId = 4;
    private $result = array();
    private $insertQuery = '';
    private $resourceTypeIdVideo = 7;
    private $resourceTypeIdText = 8;
    
    
    public function __construct() {
        $this->doctype = 'Trigger';
    }
    
    public function process() {
        $results = $this->processDocType($this->doctype);
        $this->processTriggers($results);
        return $this->result;
    }
 
    private function processTriggers($results) {
        $return = array();
        $taIds = array();
        if(!empty($results)) {
            foreach($results as $row) {
                $notes = $this->filterNotes(trim($row['notes']));
                if(!empty($notes) && isset($notes['pbl'])) {
                    $taRows = $this->getTeachingActivityId($notes);
                    if($taRows !== false) {
                        $notes['mid'] = $row['mid'];
                        foreach($taRows as $taId) {
                            $taIds[$taId][] = $notes;   
                        } 
                    } else {
                        $this->result['pbl']['notFound'][] = "Could not find any teaching activity attached to this row for <pre>" .print_r($row,true).print_r($notes,true). "</pre>";
                    }
                } else {
                    //$this->result['pbl']['notFound'][] = "Notes empty ({$row['notes']})";
                }
            }
        }
        $this->processTaIds($taIds);
        $this->insertQueries($this->insertQuery);
        return $return;
    }
    
    private function insertQueries($queries) {
        $db = Zend_Registry::get("db");
        $db->beginTransaction();
        try {
            $db->getConnection()->exec($queries);
            $db->commit();
            return true;
        } catch (Exception $ex) {
            $db->rollBack();
            $this->result['error_string'] = "Database error";
            Zend_Registry::get('logger')->warn("\n".$ex->getMessage()."\n".$ex->getTraceAsString()."\n\n");
            return false;
        }
    }
    

    private function processTaIds($taIds) {
        $return = array();
       
        foreach($taIds as $taId => $rows) {
            foreach($rows as $row) {
                switch($row['filetype']) {
                    case 'text':
                        $return[$taId]['text'][$row['filetypesequence']] = $row; 
                    break;
                    case 'video':
                        $return[$taId]['video'][$row['filetypesequence']] = $row; 
                    break;
                }
            }
            $this->createInsertQueries($return[$taId], $taId);
        }
    }
    
    private function createInsertQueries($rows,$taId ) {
        $resourceExist = $this->resourceExist($taId);
        if($resourceExist === false) {
            $insertTextTemplate = "INSERT INTO lk_resource VALUES(DEFAULT, 'ta', %d, '".ResourceConstants::$TYPE_MEDIABANK."', '%s', %d, {$this->resourceTypeIdText})";
            $insertVideoTemplate = "INSERT INTO lk_resource VALUES(DEFAULT, 'ta', %d, '".ResourceConstants::$TYPE_MEDIABANK."', '%s', %d, {$this->resourceTypeIdVideo})";
            $orderBy = 1;
            $keys = array(1,2,3);
            foreach($keys as $key) {
                if(isset($rows['video']) && isset($rows['video'][$key])) {
                     $row = $rows['video'][$key];
                     $recordExist = $this->recordExist($taId, $row['mid']);
                     if($recordExist === false) {
                        $this->insertQuery .= sprintf($insertVideoTemplate, $taId, $row['mid'],$orderBy).';'.PHP_EOL;
                        $this->result['pbl']['found'][] = sprintf($insertVideoTemplate, $taId, $row['mid'],$orderBy).';<br />';
                        $orderBy++;
                     } else {
                         //$this->result['pbl']['notFound'][] = "Record already exist for $taId and {$row['mid']}";
                     }
                }
                if(isset($rows['text']) && isset($rows['text'][$key])) {
                    $row = $rows['text'][$key];
                    $recordExist = $this->recordExist($taId, $row['mid']);
                    if($recordExist === false) {
                        $this->insertQuery .= sprintf($insertTextTemplate, $taId, $row['mid'],$orderBy).';'.PHP_EOL;
                        $this->result['pbl']['found'][] = sprintf($insertTextTemplate, $taId, $row['mid'],$orderBy).';<br />';
                        $orderBy++;
                    } else {
                        //$this->result['pbl']['notFound'][] = "Record already exist for $taId and {$row['mid']}";
                    }
                }
            }                 
        } else {
            $this->result['pbl']['notFound'][] = "Resource already exist <pre>".print_r($resourceExist, true).print_r($this->getTa($taId), true)."</pre>";
        }
    }
    
    private function resourceExist($taId) {
        $ta = new MediabankResource();
        $where = "type ='ta' and type_id = $taId and resource_type='".ResourceConstants::$TYPE_MEDIABANK."' and resource_id like '%|compassresources|%' and resource_type_id != 8" ;
        $rows = $ta->fetchAll($where);
        if($rows->count() > 0) {
            return $rows->toArray();
        } 
        return false;
    }
    
    private function getTa($taId) {
        $ta = new TeachingActivities();
        $where = "taid = $taId" ;
        $rows = $ta->fetchAll($where);
        if($rows->count() > 0) {
            return $rows->toArray();
        } 
        return array();
    }
   
    private function recordExist($typeId, $mid) {
        $ta = new MediabankResource();
        $where = "type ='ta' and type_id = $typeId and resource_type='".ResourceConstants::$TYPE_MEDIABANK."' and resource_id = '$mid'" ;
        $row = $ta->fetchAll($where);
        if($row->count() > 0) {
            return true;
        } 
        return false;
    }
    
    private function getTeachingActivityId($notes){
        if(     isset($notes['pbl']) && !empty($notes['pbl']) 
            &&  isset($notes['phasecode']) && !empty($notes['phasecode'])) {
            $pbl = new Pbl($notes['pbl']);
            $pblDetails = $pbl->getPblDetails();
            return $this->findTeachingActivityIdFor($notes['phasecode'], $pblDetails['pblBlock'], $pblDetails['pblBlockWeek']);
        } else {
            //$this->result['pbl']['notFound'][] = 'you do not have pbl and phasecode what are you doing here';
        }
    }
    
    private function findTeachingActivityIdFor($phaseCode, $pblBlock, $pblBlockWeek) {
        $teachingActivity = new TeachingActivities();
        $where = sprintf('type = %d and block = %d and block_week = %d and sequence_num = %d', $this->pblSessionId, $pblBlock + 1, $pblBlockWeek + 1, $phaseCode + 1);
        $row = $teachingActivity->fetchAll($where);
        $return = array();
        if($row->count() == 1 ) {
            if(isset($row[0]) && !empty($row[0]->auto_id)) {
                $this->result['pbl']['found'][] = "FOUND TA {$row[0]->auto_id} for $this->pblSessionId, Block  $pblBlock, BlockWeek $pblBlockWeek, PhaseCode $phaseCode";
                $return[0] =  $row[0]->auto_id;
            }
        } else if ($row->count() > 1) {//Normally it should be one. But if versioning as happen to ta there might be more than one
            foreach($row as $val) {
                $return[] = $val->auto_id;
            }
            $this->result['pbl']['found'][] = "More than 1 ta found ".implode(',',$return)." for Sequence No $this->pblSessionId, Block  $pblBlock, BlockWeek $pblBlockWeek, PhaseCode $phaseCode";
        } else {
            $this->result['pbl']['notFound'][] = "Could not find ta for Sequence No $this->pblSessionId, Block  $pblBlock, BlockWeek $pblBlockWeek, PhaseCode $phaseCode $where";
            $return = false;
        }             
        return $return;
    }
    
    private function filterNotes($notesString) {
        $return = array();
        if(!empty($notesString)) {
            $notesExploded = explode('|', $notesString);
            foreach($notesExploded as $part) {
                $part = trim($part);
                $parts = explode(':', $part);
                if(! empty($parts) && count($parts) == 2) {
                    $return[$parts[0]] = $parts[1];
                }
            }
        }
        return $return;
    }
}
?>