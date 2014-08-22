<?php 
abstract class TaResourceAbstract {
    
    abstract function getResources();
    protected $taId                             = null;
    protected $resources                        = array();
    protected $mediabankResourceService         = null;
    protected $resourceTypeContentId            = null;
    protected $resourceTypeReferencesId         = null;
    protected $resourceTypeLectureRecordingsId  = null;
    protected $resourceTypePrologueId           = null;
    protected $resourceTypeStaffOnly            = null;
    protected $resourceTypeTutorGuide           = null;    
        
    protected function startProcess($taId) {
        $taId = (int)$taId;
        if($taId > 0 ) {
            $this->taId = $taId;
        } else {
            Zend_Registry::get('logger')->warn('Constructor could not be initialized for class ' . __CLASS__ . PHP_EOL . 'Incorrect Ta ID Given : ' . $taId);
            exit;
        }
        $this->resourceTypeContentId            = ResourceTypeConstants::$CONTENT_ID;
        $this->resourceTypeReferencesId         = ResourceTypeConstants::$REFERENCES_ID;
        $this->resourceTypeLectureRecordingsId  = ResourceTypeConstants::$RECORDINGS_ID;
        $this->resourceTypePrologueId           = ResourceTypeConstants::$PROLOGUE_ID;
        $this->resourceTypeStaffOnly            = ResourceTypeConstants::$STAFF_ONLY_ID;
        
        $this->mediabankResourceService         = new MediabankResourceService(); 
        $this->_setResources();
    }
    
    private function _setResources() {
        try {
            $taResourceService = new TaResourceService();
            $resources = $taResourceService->getResourcesGroupByResourceType($this->taId);        
            if(count($resources) > 0 ) {
                $this->resources = $resources;
            }
        } catch (Exception $ex) {
            $error = $ex->getMessage().PHP_EOL.$ex->getTraceAsString().PHP_EOL;
            Zend_Registry::get('logger')->warn(PHP_EOL."CLASS\t: ".__CLASS__.PHP_EOL."METHOD\t: ".__METHOD__.PHP_EOL."Error\t: ".$error.PHP_EOL);
        }    
    }
    
    protected function getContentDefault() {
        try {        
            if(isset($this->resources[$this->resourceTypeContentId]) && !empty($this->resources[$this->resourceTypeContentId])) {
                return $this->resources[$this->resourceTypeContentId];
            }
            return array();
        } catch (Exception $ex) {
            $error = $ex->getMessage().PHP_EOL.$ex->getTraceAsString().PHP_EOL;
            Zend_Registry::get('logger')->warn(PHP_EOL."CLASS\t: ".__CLASS__.PHP_EOL."METHOD\t: ".__METHOD__.PHP_EOL."Error\t: ".$error.PHP_EOL);
            return array();
        }
    }
    
    protected function getReferencesDefault() {
        try {        
            if(isset($this->resources[$this->resourceTypeReferencesId]) && !empty($this->resources[$this->resourceTypeReferencesId])) {
                return $this->resources[$this->resourceTypeReferencesId];
            }
            return array();
        } catch (Exception $ex) {
            $error = $ex->getMessage().PHP_EOL.$ex->getTraceAsString().PHP_EOL;
            Zend_Registry::get('logger')->warn(PHP_EOL."CLASS\t: ".__CLASS__.PHP_EOL."METHOD\t: ".__METHOD__.PHP_EOL."Error\t: ".$error.PHP_EOL);
            return array();
        }
    }
    
    protected function getLectureRecordingsDefault() {
        try {        
            $lectureRecordings = new LectureRecordings();
            if(isset($this->resources[$this->resourceTypeLectureRecordingsId]) && !empty($this->resources[$this->resourceTypeLectureRecordingsId])) {
                $return = $lectureRecordings->processResources($this->resources[$this->resourceTypeLectureRecordingsId]);
                return $return;
            } else {
                return $lectureRecordings->processResources(array());
            }
        } catch (Exception $ex) {
            $error = $ex->getMessage().PHP_EOL.$ex->getTraceAsString().PHP_EOL;
            Zend_Registry::get('logger')->warn(PHP_EOL."CLASS\t: ".__CLASS__.PHP_EOL."METHOD\t: ".__METHOD__.PHP_EOL."Error\t: ".$error.PHP_EOL);
            return array();
        }
    }
    
    protected function getLectureRecordingsLectopia() {
        try {
            $return = array();
            if(isset($this->resources[$this->resourceTypeLectureRecordingsId]) && !empty($this->resources[$this->resourceTypeLectureRecordingsId])) {
                $resources = $this->resources[$this->resourceTypeLectureRecordingsId];
                $collectionId = MediabankConstants::getMediabankBasePath().'|'.MediabankResourceConstants::$COLLECTION_lectopia;
                foreach ($resources as $resource) {
                	if(stristr($resource['resource_id'], $collectionId)) {
                	    $return[] = $resource;
                	}
                }
                return $return;
            }
            
        } catch (Exception $ex) {
        	$error = $ex->getMessage().PHP_EOL.$ex->getTraceAsString().PHP_EOL;
        	Zend_Registry::get('logger')->warn(PHP_EOL."CLASS\t: ".__CLASS__.PHP_EOL."METHOD\t: ".__METHOD__.PHP_EOL."Error\t: ".$error.PHP_EOL);
            return array();
        }
    }
    
    protected function getTeachingActivity() {
        try {
            if(! empty($this->taId)) {
                $teachingActivities = new TeachingActivities();
                return $teachingActivities->fetchRow('auto_id = '.$this->taId);
            }
            return array();
        } catch (Exception $ex) {
        	$error = $ex->getMessage().PHP_EOL.$ex->getTraceAsString().PHP_EOL;
        	Zend_Registry::get('logger')->warn(PHP_EOL."CLASS\t: ".__CLASS__.PHP_EOL."METHOD\t: ".__METHOD__.PHP_EOL."Error\t: ".$error.PHP_EOL);
            return array();
        }            
    }
    
    public static $iscounter = 1;
    /**
     * All the resources which are not shown in separate categories would go into 
     * General Resources. Therefore we need to exclude resource type such as 
     * content, references, recordings and prologue which appear separately on the view page.
     */
    protected function getGeneralResourcesDefault($user = null) {
        try {        
            $resourceTypeIds = ResourceTypeConstants::studentOtherResources();                                 

            //If User info is not given we need to grab it from session.
            //This would normally happen when you are in teaching activity view page
            //Used by 'TaResourceView.php' CLASS
            if(is_null($user)) {                                        
                if(UserAcl::isStaffOrAbove()) {
                    $resourceTypeIds = ResourceTypeConstants::staffOtherResources();
                }
            //User info would be given when storing ta_resources info differently for students 
            //and staff.
            //Used by 'TaResourceLucene.php' CLASS
            } else {
                if($user == ResourceTypeConstants::$ALLOW_STAFF) {
                    $resourceTypeIds = ResourceTypeConstants::staffOtherResources();
                }
            }
                                 
            $resourceService = new MediabankResourceService();
            return $resourceService->getResourcesForResourceTypeIds($this->taId,'ta', $resourceTypeIds);
            
        } catch (Exception $ex) {
            $error = $ex->getMessage().PHP_EOL.$ex->getTraceAsString().PHP_EOL;
            Zend_Registry::get('logger')->warn(PHP_EOL."CLASS\t: ".__CLASS__.PHP_EOL."METHOD\t: ".__METHOD__.PHP_EOL."Error\t: ".$error.PHP_EOL);
            return array();
        }
    }
    
    protected function getPrologueDefault() {
        try {        
            if(isset($this->resources[$this->resourceTypePrologueId]) && !empty($this->resources[$this->resourceTypePrologueId])) {
                return $this->resources[$this->resourceTypePrologueId];
            }
            return array();
        } catch (Exception $ex) {
            $error = $ex->getMessage().PHP_EOL.$ex->getTraceAsString().PHP_EOL;
            Zend_Registry::get('logger')->warn(PHP_EOL."CLASS\t: ".__CLASS__.PHP_EOL."METHOD\t: ".__METHOD__.PHP_EOL."Error\t: ".$error.PHP_EOL);
            return array();
        }
    }
    
    protected function getHtmlForResources($resources) {
        try {        
            $return = array();
            foreach($resources as $resource) {
                $return[] = $this->getHtmlForMid($resource['resource_id']);
            }
            return $return;
        } catch (Exception $ex) {
            $error = $ex->getMessage().PHP_EOL.$ex->getTraceAsString().PHP_EOL;
            Zend_Registry::get('logger')->warn(PHP_EOL."CLASS\t: ".__CLASS__.PHP_EOL."METHOD\t: ".__METHOD__.PHP_EOL."Error\t: ".$error.PHP_EOL);
            return array();
        }
    }
    
    protected function getHtmlForMid($mid) {
        try {        
            $metadata = $this->mediabankResourceService->getMetaData($mid);
            if(isset($metadata['html']) && isset($metadata['html']['val'])) { 
                return $metadata['html']['val'];
            }
            return '';
        } catch (Exception $ex) {
            $error = $ex->getMessage().PHP_EOL.$ex->getTraceAsString().PHP_EOL;
            Zend_Registry::get('logger')->warn(PHP_EOL."CLASS\t: ".__CLASS__.PHP_EOL."METHOD\t: ".__METHOD__.PHP_EOL."Error\t: ".$error.PHP_EOL);
            return '';
        }    
    }
    
}