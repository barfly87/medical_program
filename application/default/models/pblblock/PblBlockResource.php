<?php

class PblBlockResource extends Zend_Db_Table_Abstract {
    protected $_name = 'lk_resource';
    
    public function getAllResources($type, $typeid, $where = null) {
        try {
            $db = Zend_Registry::get("db");
            $select = $db->select()
                            ->from(array('pbr' => 'lk_resource'))
                            ->join(array('pbrt' => 'lk_resourcetype'),
                                   'pbr.resource_type_id = pbrt.auto_id',
                                   array('resource_type_name' => 'resource_type','url_name','allow'))
                            ->where("pbr.type_id = $typeid and pbr.type = '$type' ")
            				->order("order_by ASC");
            if(!empty($where)) {
                $select = $select->where($where);
            }
            $result = $db->fetchAll($select);
            return $result;
        } catch (Exception $ex) {
            Zend_Registry::get('logger')->debug("\n".$ex->getMessage()."\n".$ex->getTraceAsString()."\n\n");
            return array();
        }
    }
    
}
