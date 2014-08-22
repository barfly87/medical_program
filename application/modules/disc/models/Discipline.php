<?php

class Discipline extends Zend_Db_Table_Abstract {
    protected $_name = 'lk_discipline';
    protected $_primary = 'auto_id';
    protected $_dependentTables = array('LearningObjectives');

    public function listOfDisciplines($page = 'default', $type = null) {        
        $rows = false;
        switch ($page) {
            
            case 'default':
                $rows = $this->getRows();
            break;
            case 'list':
                $rows = $this->getRowsForListPage();
            break;
            case 'mydetails':
                $rows = $this->getRowsForMyDetailsPage($type);
            break;                       
        }
        return $rows;
    }
    
    public function getRows(){
        try{   
            $select = $this->select()->from($this, array('auto_id','synonym','name','parent_id'))->order('auto_id');
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
   
    public function getRowsForListPage() {
        try{        
            $select = $this->select()->from($this)->order('auto_id');
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

    public function getRowsForMyDetailsPage($type) {
        try{
            $select = $this->select();
            if($type != null) {
                $select->where($type.' = 1');        
            }
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

    public function getRowForId($id) {
        try{
            $select = $this->select()
                                    ->where('auto_id = '.(int)$id)
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
    
    public function checkIfRowExist($name = 0, $parent_id = 0){
        try{
            $select = $this->select()
                            ->where("name = '".$name."'")
                            ->where('parent_id = '.(int)$parent_id)
                            ->limit('1'); 
            return parent::fetchRow($select);
        } catch (Exception $e) {
            return false;
        }          
    }
    
    public function getListOfDisciplines($type = null){
        try{
            $select = $this->select()
                    ->where($type." = 1 ")->order('auto_id');
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
    
    public function getName($id = 0){
        try{
            if($id == 0) {
                return false;
            }
            $select = $this->select()
                                    ->from($this, array('name'))
                                    ->where('auto_id = '.(int)$id)
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
    
    public function getParent($id = 0){
        try{
            if($id == 0) {
                return false;
            }
            $select = $this->select()
                                    ->from($this, array('name','parent_id'))
                                    ->where('auto_id = '.(int)$id)
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

    public function getAllIdsAndNames() {
        try{
            $select = $this->select()->from($this, array('auto_id','name'))->order('auto_id');
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
    
}