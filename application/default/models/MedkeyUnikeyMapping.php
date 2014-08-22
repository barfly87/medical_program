<?php

class MedkeyUnikeyMapping extends Zend_Db_Table_Abstract {
    protected $_name = 'medkey';
    
    public function getUnikey($medkey) {
    	$select = $this->select()->where("id = ?", $medkey);
    	$row = $this->fetchRow($select);
    	if (!$row) {
    		return "med__$medkey";
    	}
    	return $row['unikey'] ? $row['unikey'] : "med__$medkey";
    }
}