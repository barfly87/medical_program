<?php

class Terms extends Zend_Db_Table_Abstract {
    protected $_name = 'lk_term';
    protected $_dependentTables = array('TeachingActivities');
    
    public function getAllTerms($order = 'auto_id ASC') {
    	$rows = $this->fetchAll(NULL, $order);
        $result = array();
        foreach ($rows as $row) {
             $result[$row->auto_id] = $row->term;
        }
        return $result;
    }
}