<?php

class CurriculumAreas extends Zend_Db_Table_Abstract {
    protected $_name = 'lk_curriculumareas';
    protected $_primary = 'auto_id';
    protected $_rowClass = 'CurriculumArea';
    protected $_referenceMap = array(
        'CurriculumAreasStatus' => array(
            'columns' => array('status'),
            'refTableClass' => 'CurriculumAreasStatus',
            'refColumns' => array('auto_id')
        )
    );
    
    /**
     * Only curriculum areas with 'Current' status would be returned.
     * @param $discipline_id
     * @return mixed
     */
    public function listOfCurriculumAreas($discipline_id){
       try{
            $select = $this->select()->where('discipline_id = '.(int)$discipline_id)->order('order_by');
            $rows = parent::fetchAll($select);
            if($rows == false) {
                return false;
            } else {           
                return $this->filterCurrentCurriculumAreas($rows);
            } 
        } catch (Exception $e) {
            return false;
        }              
    }

    public function save($auto_id, $disc_id, $curriculumarea){
       try{
            #$recordExist = $this->recordExist($auto_id, $disc_id);
            #if($recordExist === false) {
                #return false;
            #}
            $data = array('curriculumarea' => $curriculumarea);
            $where = "auto_id = ".$auto_id;
            #$where[] = "discipline_id = ".$disc_id;  
            return $this->update($data, $where);
        } catch (Exception $e) {
            return false;
        }              
    }

    public function recordExist($auto_id,$disc_id){
        try{
            $select = $this->select()
                                    ->where('auto_id = '.(int)$auto_id)
                                    ->where('discipline_id = '.(int)$disc_id)
                                    ->limit('1'); 
            $row = parent::fetchRow($select);
            if($row == false) {
                return false;
            } else {                    
                return $row->toArray();
            } 
        } catch (Exception $e) {
            return false;
        }              
        
    }    
    
    public function add($disc_id, $curriculumarea) {
       try{
            $curriculumExist = $this->curriculumAreaExist($disc_id, $curriculumarea);
            if($curriculumExist) {
                return false;
            }
            $order_by = $this->getNextOrderBy((int)$disc_id);
            $data = array(
                        'discipline_id' => $disc_id,
                        'curriculumarea' => $curriculumarea,
                        'order_by' => $order_by
            );
            return $this->insert($data);
        } catch (Exception $e) {
            return false;
        }              
    }
    
    private function curriculumAreaExist($disc_id,$curriculumarea) {
        try {
            $select = $this->select()
                                    ->where("discipline_id = ".(int)$disc_id)
                                    ->where("curriculumarea = '".$curriculumarea."'")
                                    ->limit('1'); 
            $row = parent::fetchRow($select);
            if($row == false) {
                return false;
            } else {                    
                return true;
            } 
        } catch(Exception $e) {
            return false;
        }
    }
    
    private function getNextOrderBy($disc_id) {
        try {
            $select = $this->select()->from($this,array("max(order_by)"))->where('discipline_id = '.(int)$disc_id);
            $row = parent::fetchRow($select);
            if($row === false) {
                return '1';//First entry for this discipline
            } else {
                $row = $row->toArray();
                return ++$row['max'];
            }
        } catch(Exception $e) {
            return 1;        
        }
    }
    
    public function deleteCurriculumArea($auto_id){
       try{
            $where = $this->getAdapter()->quoteInto('auto_id = ?', $auto_id);
            return $this->delete($where);
        } catch (Exception $e) {
            return false;
        }              
    }
    
    public function archiveCurriculumArea($auto_id) {
        try {
            $where = $this->getAdapter()->quoteInto('auto_id = ?', $auto_id);
            $row = $this->fetchRow($where);
            $curriculumAreaNewName = 'DELETED - ' . $row->curriculumarea;

            //Log message about this DELETE
            $message = sprintf("CURRICULUM AREA - '%s' WAS DELETED BY '%s' ON '%s'", $row->curriculumarea, UserAcl::getUid(), date('Y-m-d, h:i:s A',time()));
            Zend_Registry::get('logger')->warn(PHP_EOL."DELETE LOG\t: ".$message.PHP_EOL);
            return $this->update( array ('curriculumarea' => $curriculumAreaNewName, 'status' => CurriculumAreasStatus::ARCHIVED_ID), $where);
        } catch (Exception $ex) {
            $error = $ex->getMessage().PHP_EOL.$ex->getTraceAsString().PHP_EOL;
        	Zend_Registry::get('logger')->warn(PHP_EOL."CLASS\t: ".__CLASS__.PHP_EOL."METHOD\t: ".__METHOD__.PHP_EOL."Error\t: ".$error.PHP_EOL);
            return false;
        }
    }
    
    public function filterCurrentCurriculumAreas($rows) {
        if(is_object($rows)) {
            $curriculumAreas = array();
            foreach($rows as $row) {
                if($row->status_name == CurriculumAreasStatus::CURRENT) {
                    $curriculumArea = $row->toArray();
                    $curriculumArea['status_name'] = $row->status_name;
                    $curriculumAreas[] = $curriculumArea;
                }
            }
            return $curriculumAreas;
        } else {
            die('Expecting object array given.');
        }
    }
    
    public function getAllRows(){
       try{
            $select = $this->select()->order('order_by');
            $rows = parent::fetchAll($select);
            if($rows == false) {
                return false;
            } else {                    
                return $rows->toArray();
            } 
        } catch (Exception $e) {
            return false;
        }              
    }
    
    public function getAllRowsWithStatusCurrent(){
       try{
            $select = $this->select()->order('order_by');
            $rows = parent::fetchAll($select);
            if($rows == false) {
                return false;
            } else {     
                return $this->filterCurrentCurriculumAreas($rows); 
            } 
        } catch (Exception $e) {
            return false;
        }              
    }
    
    public function sort($disc_id,$autoIds) {
       try{
            $disc_id = (int)$disc_id;
            $order_by = 1;
            foreach($autoIds as $autoId) {
                $auto_id = (int)$autoId;
                if(!empty($autoId) && $autoId > 0 ) {
                    $data = array();
                    $where = array();
                    $recordExist = $this->recordExist($autoId,$disc_id);
                    if($recordExist !== false) {
                        $data = array('order_by' => $order_by);
                        $where[] = "auto_id = '".$autoId."'";
                        $where[] = "discipline_id = '".$disc_id."'";  
                        $update = $this->update($data, $where);
                        $order_by++;
                    }
                }
            } 
            return true;
        } catch (Exception $e) {
            return false;
        }              
    }
}
