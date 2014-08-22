<?php
class PblBlockConst {
    
    public static $pbl = 'pbl';
    public static $block = 'block';    
    
    public static function createDynamicLinks($type, $pblOrBlockId, $excludeResourceTypeIds = false) {  
        try {
            if(is_null($pblOrBlockId)) {
                return $return;
            }
            $pblBlockResource = new PblBlockResource();
            $resources = $pblBlockResource->getAllResources($type, $pblOrBlockId);
            return self::processResources($resources, $excludeResourceTypeIds);
        } catch(Exception $ex) {
            Zend_Registry::get('logger')->debug("\n".$ex->getMessage()."\n".$ex->getTraceAsString()."\n\n");
            return array('error' => true);            
        }
    }
    
    public static function processResources($resources, $excludeResourceTypeIds = false) {
        $return = array();
        $alreadyAddedResourceTypeIds = array();
        foreach($resources as $resource) {
            if(in_array($resource['resource_type_id'],$alreadyAddedResourceTypeIds)) {
                continue;
            }
            if (!empty($excludeResourceTypeIds) && in_array($resource['resource_type_id'], $excludeResourceTypeIds)) {
            	continue;
            }
            $alreadyAddedResourceTypeIds[] = $resource['resource_type_id'];
            $allowed = MediabankResourceService::isUserAllowed($resource['allow']);
            if($allowed === true) {
                $return[] = $resource;
            }
        }
        return $return;
    }
    
    public static function getModuleControllerAction () {
        $zend = Compass::request();
        return array(
            'zendController'    => $zend['controller'],
            'zendAction'        => $zend['action'],
            'zendModule'        => $zend['module']
        );    
    }

    public static function prevNextTa($allTaIds,$currentTaId,$urlFormat) {
        $return = array('prev'=>'','next'=>'');
        if(!empty($allTaIds) && !empty($currentTaId)) {
            $count = 0;            
            foreach($allTaIds as $taId) {
                if($taId == $currentTaId) {
                    if(isset($allTaIds[$count - 1])) {
                        $return['prev'] = $allTaIds[$count - 1];
                    }
                    if(isset($allTaIds[$count + 1])) {
                        $return['next'] = $allTaIds[$count + 1];
                    }
                    break;
                }
                $count++;
            }
        }
        if(!empty($return['prev'])) {
            $return['prev'] = MediabankResourceConstants::encode(sprintf($urlFormat, $return['prev']));
        }
        if(!empty($return['next'])) {
            $return['next'] = MediabankResourceConstants::encode(sprintf($urlFormat, $return['next']));
        }
        return $return;
    }
    
    public static function getResourceIdTitle($resourceId) {
        try {
            $pblBlockResource = new PblBlockResource();
            $row = $pblBlockResource->fetchRow('auto_id = '. $resourceId);
            if($row !== false) {
                $mid = $row['resource_id'];
                $mediabankResourceService = new MediabankResourceService();
                return $mediabankResourceService->getTitleForMid($mid);
            }
            return '';
        } catch(Exception $ex) {
            Zend_Registry::get('logger')->debug("\n".$ex->getMessage()."\n".$ex->getTraceAsString()."\n\n");
            return '';            
        }
    }
    
    public static function getTaTitle($taId) {
        try {
            $taTitle = '';
            if((int)$taId > 0 ) {
                $tas = new TeachingActivities();
                $result = $tas->fetchRow('auto_id = '.(int)$taId);
                if($result != null) {
                    $taTitle = $result->name;
                }
            }
            return $taTitle;
        } catch(Exception $ex) {
            Zend_Registry::get('logger')->debug("\n".$ex->getMessage()."\n".$ex->getTraceAsString()."\n\n");
            return '';            
        }
    }    
    
    public static function urlMatch($url) {
        $pattern = '/^'.str_replace('/','\/',$url).'/';
        if(preg_match($pattern,$_SERVER['REQUEST_URI']) !== 0) {
            return true;
        }
        return false;
    }
    
}
?>