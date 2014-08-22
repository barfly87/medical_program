<?php

class Blocks extends Compass_Db_Table_LookupTable {
    protected $_name = 'lk_block';
    protected $_dependentTables = array('TeachingActivities', 'StageBlockSeqs');
    
    /** Get block id based on block name */
    public function getBlockId($name) {
    	$select = $this->select()->where("name = ?", $name);
    	$row = $this->fetchRow($select);
    	if (!$row) {
    		throw new Exception(Zend_Registry::get('Zend_Translate')->_('Block')." $name does not exist");
    	}
    	return $row['auto_id'];
    }
    
    /** Get block name based on block id */
    public function getBlockName($id) {
    	$row = $this->find($id)->current();
    	if (!$row) {
    		throw new Exception(Zend_Registry::get('Zend_Translate')->_('Block')." id $id does not exist");
    	}
    	return $row['name'];
    }
}