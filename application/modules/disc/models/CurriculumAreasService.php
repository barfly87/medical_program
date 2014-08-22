<?php
class CurriculumAreasService {

    public function getListOfCurriculumAreas($discipline_id){
        $curriculumAreas =  new CurriculumAreas();
        $result = array();
        if((int)$discipline_id > 1) {
            $rows = $curriculumAreas->listOfCurriculumAreas((int)$discipline_id);
            if($rows != false && count($rows) > 0) {
                $count = 1;
                foreach($rows as $val) {
                    $attach = $this->getLearningObjectivesAttachedToCurriculumArea($val['auto_id'], (int)$discipline_id);
                    $val['no'] = $count++;
                    $val['lo_attached'] = $attach;
                    $result[] = $val;
                }
                return $result;
            }
        }
        return false;
    }
    
    private function getLearningObjectivesAttachedToCurriculumArea($curriculumarea, $discipline_id){
        if(strlen($curriculumarea) < 1 || $discipline_id < 2) {
            return false;
        }
        $rows = $this->getLearningObjectives($curriculumarea, $discipline_id);
        if($rows !== false) {
            return true;
        }
        return false;
    }

    public function getLearningObjectives($curriculumarea, $discipline_id){
        $learningobjectives = new LearningObjectives();
        try{
            $select = $learningobjectives->select()
                                                ->where(
                                                            "     discipline1 = ".$discipline_id
                                                            ." OR discipline2 = ".$discipline_id
                                                            ." OR discipline3 = ".$discipline_id
                                                        )
                                                ->where(
                                                            "     curriculumarea1 = '".$curriculumarea."'"
                                                            ." OR curriculumarea2 = '".$curriculumarea."'"
                                                            ." OR curriculumarea3 = '".$curriculumarea."'"
                                                        );
            $rows = $learningobjectives->fetchAll($select)->toArray();
            if(count($rows) > 0) {
                return true;
            } else {                    
                return false;
            } 
        } catch (Exception $e) {
            return false;
        }              
        return false;
    }
        
    public function save($auto_id, $disc_id, $curriculumarea){
        $curriculumAreas =  new CurriculumAreas();
        if(!empty($auto_id) && !empty($disc_id) && $disc_id > 1 && !empty($curriculumarea)) {
            return $curriculumAreas->save($auto_id, $disc_id, $curriculumarea);
        }
        return false;
    }
    
    public function add($disc_id, $curriculumarea){
        $curriculumAreas =  new CurriculumAreas();
        if(!empty($disc_id) && (int)$disc_id > 1 && !empty($curriculumarea)) {
            return $curriculumAreas->add((int)$disc_id, $curriculumarea);
        }
        return false;
    }
    
    public function delete($auto_id){
        $curriculumAreas =  new CurriculumAreas();
        if(!empty($auto_id) && (int)$auto_id > 0) {
            return $curriculumAreas->deleteCurriculumArea((int)$auto_id);
        }
        return false;
        
    }
    
    public function archive($auto_id) {
        if(!empty($auto_id) && (int)$auto_id > 0) {
            $curriculumAreas = new CurriculumAreas();
            return $curriculumAreas->archiveCurriculumArea($auto_id);
        }
        return false;
    }
    
    public function getAllCurriculumAreas(){
        $curriculumAreas = new CurriculumAreas();
        $rows = $curriculumAreas->getAllRowsWithStatusCurrent();
        if($rows != false) {
            $result = array();
            foreach($rows as $row) {
                $result[$row['discipline_id']][$row['auto_id']] = $row['curriculumarea'];  
            }
            return $result;
        }
        return false;
    }
    
}