<?php
class MediabankResourceHistory extends Zend_Db_Table_Abstract {
    protected $_name = 'lk_resource_history';
    protected $_primary = 'auto_id';
    private $actions = array('add','edit','delete');
    
    public function setHistory($lk_resource_id, $action) {
        try{
            $lk_resource_id = (int)$lk_resource_id;
            $userid = $this->getUserId();
            if($lk_resource_id == 0 || strlen($action) == 0 || !in_array($action, $this->actions) || empty($userid)) {
                return false;
            }
            $mediabankResource = new MediabankResource();
            $where = "auto_id = ".$lk_resource_id;
            $row = $mediabankResource->fetchRow($where);
            $title = '';
            if(! empty($row->resource_id)) {
                MediabankCacheMetadata::removeCache($row->resource_id);
                try {
                    $mediabankResourceService = new MediabankResourceService();
                    $title = $mediabankResourceService->getTitleForMid($row->resource_id);
                } catch (Exception $ex) {
                }
            }
            $timestamp = date('Y-m-d H:i:s');
            $data = array (
                'lk_resource_id'    => $lk_resource_id,
                'type'              => $row->type,
                'type_id'           => $row->type_id,
                'resource_type'     => $row->resource_type,
                'resource_id'       => $row->resource_id,
                'resource_title'    => $title,
                'uid'               => $userid,
                'timestamp'         => $timestamp,
                'action'            => $action
            );
            $result = $this->insert($data);
            if($result != false) {
                return true;
            }
            return false;
        } catch(Exception $ex) {
            Zend_Registry::get('logger')->warn($ex->getMessage());
            return false;
        }
        
    }
    
    public function getRowsForType($type, $type_id){
        try {
            $where = "type_id = '".$type_id."' and type = '".$type."' ";
            $rows = $this->fetchAll($where);
            if($rows != false) {
                return $rows->toArray();
            }
            return array();
        } catch(Exception $ex) {
            Zend_Registry::get('logger')->warn($ex->getMessage());
            return array();
        }
    }
    
    private function getUserId(){
        $auth = Zend_Auth::getInstance();  
        if ($auth->hasIdentity()) {  
            $user = $auth->getIdentity();
            if(strlen($user->user_id) > 0 ) {
                return $user->user_id;
            }
        }
        return '';  
    }
        
    public function setAddHistory($resrc) {
        try {
            if( count($resrc) > 0 ) {
                $mediabankResource = new MediabankResource();
                foreach($resrc as $resrcType => $resrcId) {
                    $rows = $mediabankResource->getResources($resrcId, $resrcType);
                    if($rows !== false) {
                        foreach($rows as $row) {
                            $this->setHistory($row['auto_id'], 'add');
                        }
                    }                                                                                                                
                }
            }
        } catch (Exception $ex) {
            Zend_Registry::get('logger')->warn($ex->getMessage());
        }    
    }
    
    public function getHistory($resourceAutoId) {
        try {
            if((int)$resourceAutoId > 0) {
                $rows = $this->fetchAll('lk_resource_id = '. (int)$resourceAutoId);
                if($rows->count() > 0) {
                    return $rows->toArray();
                }
            }
            return array();
        } catch (Exception $ex) {
            Zend_Registry::get('logger')->warn($ex->getMessage());
        }
    }
}
?>