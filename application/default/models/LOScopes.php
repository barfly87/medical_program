<?php

class LOScopes extends Compass_Db_Table_LookupTable {
    protected $_name = 'lk_loscope';
    
    public function getAllScopesArray() {
    	$db = Zend_Registry::get("db");
    	$select = $db->select()->from(array('s' => 'lk_loscope'), array('col1' => 'name', 'col2' => 'name'))->order('col1');
    	return $db->fetchPairs($select);
    }
    
    public function getAllScopes() {
    	return array_values($this->getAllNames());
    }
    
}