<?php 
class ResourceManageTa extends ResourceManageAbstract {
    private $taId = null;
    private $requestError = false;
    private $taIdObj = null;
    
    public function __construct($request) {
        //set taId
        $taId = (int)$request->ref;
        if($taId > 0) {
            //check if ta id exist in th teaching activity table
            $taIdExist = $this->_taIdExist($taId);
            if($taIdExist === false) {
                $this->requestError = true;
                Zend_Registry::get('logger')->warn("Resource management page cannot be displayed because TA ID '$taId' does not exist");
            } else {
                $this->taId = $taId;                
            } 
        } else {
            $this->requestError = true;
            Zend_Registry::get('logger')->warn("Resource management page cannot be displayed because TA ID '$taId' does not exist");
        }
        $this->setPage();
    }
    
    //check if ta id exist in th teaching activity table
    private function _taIdExist($taId) {
        $teachingActivities = new TeachingActivities();
        $rows = $teachingActivities->fetchAll('auto_id = ' . $taId);
        if($rows->count() > 0) {
            return true;
        }
        return false;
    }
    
    //Read the parent documentation
    public function hasRequestError() {
        return $this->requestError;
    }
    
    //Read the parent documentation
    public function getResults() {
        $resourceTypes = $this->getResourceTypesForCurrentPage();
        return $this->_processPage($resourceTypes);
    }
    
    //Loops through each of the resource types where column 'ta'= 1
    //Then for each resource type find resources attached to it and keep on adding in the result set
    private function _processPage($resourceTypes) {
        $return = array();
        $whereFmt = "type = '".ResourceManageConstants::$pageTa."' and type_id = ".$this->taId." and resource_type_id = %d";
        foreach($resourceTypes as $resourceType) {
            $resourceTypeId = $resourceType['auto_id'];
            $where = sprintf($whereFmt,$resourceTypeId);
            $resources = $this->getResources($where);
            $return[$resourceTypeId]['resourceTypeTableRow'] = $resourceType;
            $return[$resourceTypeId]['resources'] = $resources;
        }
        return $return;
    }
    
    private function getTaIdObject() {
        if(is_null($this->taIdObj)) {
            $teachingActivities = new TeachingActivities();
            $row = $teachingActivities->fetchRow('auto_id = '. $this->taId);
            if(!empty($row)) {
                $this->taIdObj = $row;
            }
        }
        return $this->taIdObj;
    }
    
    //Read the parent documentation
    protected function setPage() {
        $this->page = ResourceManageConstants::$pageTa;
    }

    public function getManageResourcesData() {
        $data = new stdClass;
        $data->display = true;
        $teachingActivityText[] = 'Teaching Activity';
        $teachingActivityText[] = $this->taId;
        if(null !== $this->getTaIdObject()) {
            $teachingActivityText[] = $this->getTaIdObject()->name;
        }
        $data->title = join(' - ', $teachingActivityText);
        $data->url = Compass::baseUrl().'/teachingactivity/view/id/'.$this->taId;
        
        return $data;
    }
    
    public function getPageType() {
        return 'ta';
    }
    
    public function getPageTypeId() {
        return $this->taId;      
    }
    
}
?>