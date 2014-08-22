<?php

class MediabankResourceType extends Zend_Db_Table_Abstract {
    
    protected $_name = 'lk_resourcetype';
    private $autoIdAllowPair = array();
    
    public function getRows($where = null, $columns = null, $orderBy = null, $limit = null, $pair = null) {
        try {
            $select = $this->select();
            if(!is_null ($columns)) {
                $select->from($this, $columns);
            } else {
                $select->from($this);
            }
            if(!is_null($where) && is_array($where)) {
                foreach($where as $columnName => $columnVal) {
                    $select->where($columnName .' = ?', $columnVal);
                }
            }
            if(!is_null($orderBy)) {
                $select->order($orderBy);
            }
            if(!is_null($limit) && (int)$limit > 0) {
                $select->limit((int)$limit);
            }
            $rows = $this->fetchAll($select);
            if($rows->count() > 0) {
                $rows = $rows->toArray();
                if($pair === true && !empty($columns) && count($columns) == 2 ) {
                    $return = array();
                    foreach($rows as $row) {
                        $return[$row[$columns[0]]] = $row[$columns[1]];
                    }
                    return $return;
                }
                return $rows;
            }
            return array();
        } catch (Exception $ex) {
        	Zend_Registry::get('logger')->warn(PHP_EOL.$ex->getMessage().PHP_EOL.$ex->getTraceAsString().PHP_EOL.PHP_EOL);
            return array();
        }
    }
    
    public function fetchAutoidResourceTypePair($where = null) {
        $columns = array('auto_id', 'resource_type');
        return $this->getRows($where, $columns, 'resource_type ASC', null, true);
    }
    
    public function fetchAutoIdAllowPair() {
        if(empty($this->autoIdAllowPair)) {
            $columns = array('auto_id', 'allow');
            $this->autoIdAllowPair = $this->getRows(null, $columns, 'auto_id ASC', null, true);
        }
        return $this->autoIdAllowPair;  
    }
    
    public function getResourceTypeAutoIdsForUser($user) {
        $rows = $this->getRows(array('allow' => $user), array('auto_id'), array('auto_id'));
        $autoIds = array();
        if(!empty($rows)) {
            foreach($rows as $row) {
                if(isset($row['auto_id'])) {
                    $autoIds[] = $row['auto_id'];
                }
            }           
        }
        return $autoIds;
    }
}