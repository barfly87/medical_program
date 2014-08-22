<?php
/**
 * @deprecated This Class should not be used. MediabankResourceType should be used instead.
 * @author kamal
 *
 */
class PblBlockResourceType extends Zend_Db_Table_Abstract {
    protected $_name = 'lk_resourcetype';

    public function getResourceTypesWithNoParent() {
        try {
            $rows = $this->fetchAll('parent_id = 0', 'auto_id ASC');
            $result = array();
            foreach ($rows as $row) {
                 $result[$row->auto_id] = trim($row->resource_type);
            }
            asort($result);
            return $result;
        } catch (Exception $ex) {
            Zend_Registry::get('logger')->debug("\n".$ex->getMessage()."\n".$ex->getTraceAsString()."\n\n");
            return array();
        }
    }
    
    public function isSingleOrMulti() {
        try {
            $rows = $this->fetchAll(NULL, 'auto_id ASC');
            $result = array();
            foreach ($rows as $row) {
                 $fileType = ($row->max_allowed == 1) ? 'single' : 'multi';
                 $result[$row->auto_id] = $fileType;
            }
            return $result;
        } catch (Exception $ex) {
            Zend_Registry::get('logger')->debug("\n".$ex->getMessage()."\n".$ex->getTraceAsString()."\n\n");
            return array();
        }
    }
    
    public function getMaxAllowed($auto_id) {
        try{
            $row = $this->fetchRow("auto_id = $auto_id");
            if(!is_null($row)) {
                return (isset($row->max_allowed) ? $row->max_allowed : false);
            }
            return false;
        } catch(Exception $ex) {
            Zend_Registry::get('logger')->debug("\n".$ex->getMessage()."\n".$ex->getTraceAsString()."\n\n");
            return false;
        }
    }
    
    public function getChildrensForParentId($parent_id) {
        try {
            $rows = $this->fetchAll('parent_id ='.$parent_id);
            if(! is_null($rows)) {
                return $rows->toArray();
            }
            return false;
        } catch (Exception $ex) {
            Zend_Registry::get('logger')->debug("\n".$ex->getMessage()."\n".$ex->getTraceAsString()."\n\n");
            return false;        
        }
    }
    
    public function getTableName() {
        return $this->_name;
    }
    
    public function getRow($auto_id) {
        try {
            $return = array();
            $row = $this->fetchRow("auto_id = $auto_id");
            if(!is_null($row)) {
                return $row->toArray();
            }
            return $return;
        } catch(Exception $ex) {
            Zend_Registry::get('logger')->debug("\n".$ex->getMessage()."\n".$ex->getTraceAsString()."\n\n");
            return array();
        }
    }
    
}