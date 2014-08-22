<?php

class LOVerbs extends Compass_Db_Table_LookupTable {
    protected $_name = 'lk_loverb';
    
    public function getAllVerbsArraySorted() {
    	$db = Zend_Registry::get("db");
    	$select = $db->select()->from(array('v' => 'lk_loverb'), array('col1' => 'name', 'col2' => 'name'))->order('col1');
    	return $db->fetchPairs($select);
    }
    
    public function getAllVerbs() {
    	return array_values($this->getAllNames());
    }
}