<?php

class SequenceNumbers extends Zend_Db_Table_Abstract {
    protected $_name = 'lk_sequence_num';
    protected $_dependentTables = array('TeachingActivities');
    
    public function getAllSequenceNumbers($order = 'seqnum ASC') {
        $rows = $this->fetchAll(NULL, $order);
        $result = array();
        foreach ($rows as $row) {
             $result[$row->auto_id] = $row->seqnum;
        }
        asort($result);
        return $result;
    }
}