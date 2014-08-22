<?php
class LinkDoctypePatientdatasheet extends LinkDoctypeAbstract {
    
    private $doctype = 'Patient Data Sheet';
    private $result = array();
    private $pblSessionId = 4;
    private $resourceTypeId = 7;
    private $insertQuery = '';
    
    public function process() {
        $results = $this->processDocType($this->doctype);
        $this->processPatientDataSheet($results);
        return $this->result;
    }
    
    private function processPatientDataSheet($results) {
        $return = array();
        if(!empty($results)) {
            foreach($results as $key => $row) {
                $notes = $this->filterNotes($row['notes']);
                if(!empty($notes)) {
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
                    $this->result['pbl']['notFound'][] = "Could not find any teaching activity attached to this row for <pre>" .print_r($row,true).print_r($notes,true). "</pre>";    
                }
            }
        }
        $this->processTaIds($taIds);
        $this->insertQueries($this->insertQuery);
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
               $this->createInsertQueries($row['mid'], $taId);
            }
        }
    }
    
    private function createInsertQueries($mid,$taId ) {
        $resourceExist = $this->resourceExist($mid,$taId);
        if($resourceExist === false) {
            $orderBy = $this->getOrderByFor($taId);
            $insertTemplate = "INSERT INTO lk_resource VALUES(DEFAULT, 'ta', %d, '".ResourceConstants::$TYPE_MEDIABANK."', '%s', %d, {$this->resourceTypeId})";
            $this->insertQuery .= sprintf($insertTemplate, $taId, $mid,$orderBy).';'.PHP_EOL;
                
        } else {
            $this->result['pbl']['notFound'][] = "Resource already exist <pre>".print_r($resourceExist, true).print_r($this->getTa($taId), true)."</pre>";
        }
    }
    
    private function getOrderByFor($taId) {
        $orderBy = 0;
        $ta = new MediabankResource();
        $where = "type ='ta' and type_id = $taId and resource_type='".ResourceConstants::$TYPE_MEDIABANK."' and resource_type_id = 7" ;
        $rows = $ta->fetchAll($where);
        if($rows->count() > 0) {
            $rows = $rows->toArray();
            foreach($rows as $row) {
                if($row['order_by'] > $orderBy) {
                    $orderBy = $row['order_by'];
                }
            }
        } 
        return ++$orderBy;
        
    }
    
    private function resourceExist($mid, $taId) {
        $ta = new MediabankResource();
        $where = "type ='ta' and type_id = $taId and resource_type='".ResourceConstants::$TYPE_MEDIABANK."' and resource_id = '$mid'" ;
        $rows = $ta->fetchAll($where);
        if($rows->count() > 0) {
            return $rows->toArray();
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
            $this->result['pbl']['notFound'][] = 'you do not have pbl and phasecode what are you doing here';
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
        } else if ($row->count() > 1) {//Normally it should be one. But if versioning as happen to TA there might be more than one
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
    
    
    private function filterNotes($notes) {
        $return =  array();
        $notesExploded = explode('|',$notes);
        if(count($notesExploded) == 2) {
            $part1 = explode(':', $notesExploded[0]);
            $part2 = explode(':', $notesExploded[1]);
            if(count($part1) == 2 && count($part2) == 2) {
                $return['pbl'] = $part1[1];
                $return['phasecode'] = $part2[1];
            }
        }
        return $return;
     }
 
}
?>