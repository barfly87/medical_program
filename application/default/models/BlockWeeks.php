<?php

class BlockWeeks extends Zend_Db_Table_Abstract {
    protected $_name = 'lk_blockweek';
    protected $_dependentTables = array('TeachingActivities', 'BlockPblSeqs');
    
    /** Get all the weeks available */
    public function getAllWeeks($order = 'weeknum ASC') {
		$result[$this->getEmptyWeekId()] = '';

        $rows = $this->fetchAll('weeknum IS NOT NULL AND weeknum < 12', $order);
        foreach ($rows as $row) {
             $result[$row->auto_id] = $row->weeknum;
        }
        return $result;
    }
    
    /** Get the empty week row id */
    public function getEmptyWeekId() {
    	$select = $this->select()->where("weeknum IS NULL");
    	$row = $this->fetchRow($select);
    	return $row['auto_id'];
    }
}