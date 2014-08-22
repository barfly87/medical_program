<?php

class Stages extends Zend_Db_Table_Abstract {
    protected $_name = 'lk_stage';
    protected $_dependentTables = array('TeachingActivities', 'StageBlockSeqs');
    
    /** Get all stage names */
    public function getAllStages($order = 'stage ASC') {
        $rows = $this->fetchAll(NULL, $order);
        $result = array();
        foreach ($rows as $row) {
             $result[$row->auto_id] = $row->stage;
        }
        asort($result);
        return $result;
    }
    
    /** Get stage name from a stage id */
    public function getStageName($id) {
    	$row = $this->find($id)->current();
    	if (!$row) {
    		throw new Exception(Zend_Registry::get('Zend_Translate')->_('Stage')." id $id does not exist");
    	}
    	return $row['stage'];
    }
    
    /** Get stage id based on stage name */
    public function getStageId($name) {
    	$select = $this->select()->where("stage = ?", $name);
    	$row = $this->fetchRow($select);
    	if (!$row) {
    		throw new Exception(Zend_Registry::get('Zend_Translate')->_('Stage'). " $name does not exist");
    	}
    	return $row['auto_id'];
    }
}