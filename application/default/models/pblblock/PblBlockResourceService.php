<?php
class PblBlockResourceService {
    
    private $type = null;
    private $typeid = null;
    
    public function __construct($type, $typeid) {
        if(!empty($type) && (int)$typeid > 0) {
            $this->type = $type;
            $this->typeid = $typeid;
        }
    }

/*    
    public function getSingleData() {
        $return = array();
        $rows = $this->getResources(1);
        if (count($rows) > 0) {
            foreach($rows as $row) {
                $return[$row['resource_type_id']]['mid'] = MediabankResourceConstants::encode($row['resource_id']);
            }
        }
        return $return;
    }
    
    public function getMultiData() {
        try {
            $return = array();
            $rows = $this->getResources(0);
            if (count($rows) > 0) {
                $mediabankResourceService = new MediabankResourceService();
                $title = 'Title Not Found';
                
                foreach($rows as $row) {
                    try {
                        $midTitle = trim($mediabankResourceService->getTitleForMid($row['resource_id']));
                        $title = (!empty($midTitle)) ? $midTitle : $title;
                    } catch (Exception $ex) {}
                    
                    $resourceTypeId =& $row['resource_type_id'];
                    $resourceId =& $row['auto_id'];
                    $return[$resourceTypeId][$resourceId]['mid'] = MediabankResourceConstants::encode($row['resource_id']);
                    $return[$resourceTypeId][$resourceId]['title'] = $title;
                }
            }
            return $return;
        } catch(Exception $ex) {
            Zend_Registry::get('logger')->debug("\n".$ex->getMessage()."\n".$ex->getTraceAsString()."\n\n");
            return array();            
        }
    }
*/    
    
    private function getResources($maxAllowed) {
        try {
            $return = array();
            if(is_null($this->type) || is_null($this->typeid)) {
                return $return;
            }            
            
            $db = Zend_Registry::get("db");
            $query = <<<QUERY
SELECT pbr.*, pbrt.max_allowed, pbrt.parent_id FROM lk_resource as pbr 
    JOIN lk_resourcetype as pbrt 
    ON pbr.resource_type_id = pbrt.auto_id 
WHERE pbr.type_id = %d and pbrt.max_allowed = %d and pbr.type = '%s'           
QUERY;
            $query = sprintf($query, $this->typeid, $maxAllowed, $this->type);
            $stmt = $db->query($query);
            $rows = $stmt->fetchAll();
            if (count($rows) > 0) {
                return $rows;
            }
            return $return;
        } catch (Exception $ex) {
            Zend_Registry::get('logger')->debug("\n".$ex->getMessage()."\n".$ex->getTraceAsString()."\n\n");
            return array();
        }
    }
    

    public function getResource($maxAllowed, $resourceType, $resourceTypeId) {
        switch($maxAllowed) {
            case 1:
                return $this->getResourceSingle($resourceType, $resourceTypeId);
            break;
            case 0:
                return $this->getResourceMulti($resourceType, $resourceTypeId);
            break;                                    
        }
        return array();
    }
    
    private function getResourceSingle($resourceType, $resourceTypeId) {
        try {
            $return = array();
            $pblBlockResource = new PblBlockResource();
            $query = sprintf("type_id=%d and type='%s' and resource_type_id=%d", $this->typeid, $this->type, $resourceTypeId);
            $row = $pblBlockResource->fetchRow($query);
            if($row === false) {
                return $return;
            }
            $row = $row->toArray();
            $row['mid']= MediabankResourceConstants::encode($row['resource_id']);
            $row['title'] = $resourceType;
            return $row;
        } catch(Exception $ex) {
            Zend_Registry::get('logger')->debug("\n".$ex->getMessage()."\n".$ex->getTraceAsString()."\n\n");
            return array();            
        }
    }
    
    private function getResourceMulti($resourceType, $resourceTypeId) {
        try {
            $return = array();
            #check if this resource type id exists
            $pblBlockResourceType = new PblBlockResourceType();
            $row = $pblBlockResourceType->fetchRow('auto_id = '.$resourceTypeId);
            if($row !== false) {
                $row = $row->toArray();
                
                //Get childrens and resources attached to each children for this parent_id 
                $childrens = $this->getChildrensOfParent($resourceTypeId);
                
                //Only childrens with resources would be returned back.
                if(!empty($childrens)) {
                    $parentRow['childrens'] = $childrens;
                    $return['parents'][0] = $parentRow;
                }
            }
            return $return;
        } catch(Exception $ex) {
            Zend_Registry::get('logger')->debug("\n".$ex->getMessage()."\n".$ex->getTraceAsString()."\n\n");
            return array();            
        }
    }
    
    private function getChildrensOfParent($parentId) {
        try {
            $return = array();
            $pblBlockResourceType = new PblBlockResourceType();
            
            //Get all the childrens of this parent id
            $rows = $pblBlockResourceType->fetchAll("parent_id = ".$parentId);
    
            if($rows !== false) {
                foreach($rows->toArray() as $row) {
                    //Check if the current user is allowed to view this children 
                    $allowed = MediabankResourceService::isUserAllowed($row['allow']);
                    if($allowed) {
                        //Get resources attached to this child
                        $resources = $this->getResourcesForTypeId($row['auto_id']);
                        
                        //Push this child to the return array if it has got some resources attached to it
                        if(!empty($resources)) {
                            $row['resources'] = $resources;
                            $return[] = $row;
                        }
                    }     
                }
            }
            return $return;
        } catch(Exception $ex) {
            Zend_Registry::get('logger')->debug("\n".$ex->getMessage()."\n".$ex->getTraceAsString()."\n\n");
            return array();            
        }
    }
    
    private function getResourcesForTypeId($autoId) {
        try {
            $mediabankResourceService = new MediabankResourceService();
            $return = array();
            $pblBlockResource = new PblBlockResource();
            //Get all resources attached to the resource type id given
            $query = sprintf("type_id=%d and type='%s' and resource_type_id=%d", $this->typeid, $this->type, $autoId);
            $rows = $pblBlockResource->fetchAll($query);
            if($rows !== false) {
                $rows = $rows->toArray();
                foreach($rows as $row) {
                    try {
                        $title = trim($mediabankResourceService->getTitleForMid($row['resource_id']));
                        $title = (!empty($title)) ? $title : 'Title Not Found';
                    } catch (Exception $ex) {}
                    $row['title'] = $title;
                    $row['mid'] = MediabankResourceConstants::encode($row['resource_id']);
                    $return[] = $row; 
                }
            }
            return $return;
        } catch(Exception $ex) {
            Zend_Registry::get('logger')->debug("\n".$ex->getMessage()."\n".$ex->getTraceAsString()."\n\n");
            return array();            
        }
    }    
    
}
?>