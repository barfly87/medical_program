<?php

class Strengths extends Compass_Db_Table_LookupTable {
    protected $_name = 'lk_strength';
    protected $_dependentTables = array('LinkageLoTas');
    
    public function getIdForStrength($strength) {
    	$select = $this->select();
    	$select->where('name=?', $strength);
    	$row = $this->fetchRow($select);
    	if (!$row) {
    		throw new Exception("Could not find strength: $strength");
    	}
    	return $row['auto_id'];
    }
}