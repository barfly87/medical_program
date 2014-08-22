<?php

class LinkageHistory extends Zend_Db_Table_Row_Abstract {
    public function status() {
    	return $this->findParentRow("Status", "Status")->name;
    }
    
    public function new_status() {
    	return $this->findParentRow("Status", "NewStatus")->name;
    }	
	
	public function strength() {
		return $this->findParentRow("Strengths")->name;
	}

	function __get($key) {
		if (method_exists($this, $key)) {
			return $this->$key();
		}
	    //If we just need the integer value of a particular column in linkage table instead of name in lookup table, 
    	//we have to append 'ID' to the column name. This is just an easy fix instead of changing method names and use the new
    	//method names on almost all the pages.
    	if (substr($key, -2) == 'ID') {
    		return parent::__get(substr($key, 0, strlen($key) - 2));
    	}
		return parent::__get($key);
	}
}