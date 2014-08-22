<?php
class CurriculumArea  extends Zend_Db_Table_Row_Abstract {
    
    public function __get($key) {
        if(method_exists($this, $key)){
            return $this->$key();
        }
        return parent::__get($key);
    }
    
    public function status_name() {
        return $this->findParentRow("CurriculumAreasStatus")->name;
    }
    
}