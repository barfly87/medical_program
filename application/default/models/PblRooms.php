<?php

class PblRooms extends Compass_Db_Table_LookupTable {
    protected $_name = 'lk_pblroom';
    
    /** Get room based on group */
    public function getPblRoom($group) {
    	$select = $this->select()->where("groupname = ?", trim($group));
    	$row = $this->fetchRow($select);
    	if (!$row) {
    		return NULL;
    	}
    	return $row['room'];
    }
}