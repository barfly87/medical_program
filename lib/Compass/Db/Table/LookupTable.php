<?php

class Compass_Db_Table_LookupTable extends Zend_Db_Table_Abstract {
    protected $_name = '';
    protected $_primary = 'auto_id';
    protected $_dependentTables = NULL;

    public function getAllNames($order = 'name ASC') {
        $rows = $this->fetchAll(NULL, $order);
        $result = array();
        foreach ($rows as $ros) {
             $result[$ros->auto_id] = $ros->name;
        }
        return $result;
    }
}