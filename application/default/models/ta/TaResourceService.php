<?php
class TaResourceService {
    
    public function getResourcesForTaId($taId = null) {
        try {        
            $return = array();
            if(is_null($taId)) {
                return $return;
            }
            $mediabankResource = new MediabankResource();
            return $mediabankResource->getResources($taId, 'ta');
        } catch (Exception $e) {
            Zend_Registry::get('logger')->warn($e->getMessage());
            return array();
        }    
    }
    
    public function getResourcesGroupByResourceType($taId) {
        try {        
            $return = array();
            $taId = (int)$taId;
            if($taId > 0) {
                $rows = $this->getResourcesForTaId($taId);
                if($rows !== false && count($rows) > 0) {
                    return $this->_groupByResourceType($rows);
                }
            }
            return $return;
        } catch (Exception $e) {
            Zend_Registry::get('logger')->warn($e->getMessage());
            return array();
        }    
    }
    
    private function _groupByResourceType($rows) {
        try {        
            $return = array();
            if(!empty($rows)) {
                foreach($rows as $row) {
                    $resourceTypeId = (int)$row['resource_type_id'];
                    if(empty($resourceTypeId)) {
                        $return['empty'][] = $row;
                    } else {
                        $return[$resourceTypeId][] = $row;
                    }
                }
            }
            return $return;
        } catch (Exception $e) {
            Zend_Registry::get('logger')->warn($e->getMessage());
            return array();
        }    
    }
    
}
?>