<?php

class Years extends Zend_Db_Table_Abstract {
    protected $_name = 'lk_year';
    protected $_dependentTables = array('TeachingActivities');
    
    public function getAllYears($order = 'auto_id ASC') {
    	$rows = $this->fetchAll(NULL, $order);
        $result = array();
        foreach ($rows as $row) {
             $result[$row->auto_id] = $row->year;
        }
        return $result;
    }
    
	public function getYearId($year) {
		$select = $this->select();
		if (!empty($year)) {
    		$select->where("year = ?", $year);
		} else {
			$select->where("year is NULL");
		}
    	$row = $this->fetchRow($select);
    	if (!$row) {
    		throw new Exception("Year $year does not exist");
    	}
    	return $row['auto_id'];
    }
}