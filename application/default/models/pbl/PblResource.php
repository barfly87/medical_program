<?php

class PblResource extends Zend_Db_Table_Abstract {
    protected $_name = 'lk_resource';
    
    public function getAllResources($pbl_auto_id, $where = null) {
        try {
            $db = Zend_Registry::get("db");
            $select = $db->select()
                            ->from(array('pr' => 'lk_resource'))
                            ->join(array('prt' => 'lk_resourcetype'),
                                   'pr.resource_type_id = prt.auto_id',
                                   array('pbl_resource_type','url_name','max_allowed','parent_id','allow'))
                            ->where('pr.pbl_auto_id = '. $pbl_auto_id);
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
