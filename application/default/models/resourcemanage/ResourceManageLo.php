<?php
class ResourceManageLo extends ResourceManageAbstract {
    private $loId = null;
    private $requestError = false;
    private $loIdObj = null;
    
    public function __construct($request) {
        //set loId
        $loId = (int)$request->ref;
        if($loId > 0) {
            //check if ta id exist in th teaching activity table
            $loIdExist = $this->_loIdExist($loId);
            if($loIdExist === false) {
                $this->requestError = true;
                Zend_Registry::get('logger')->warn("Resource management page cannot be displayed because LO ID '$loId' does not exist");
            } else {
                $this->loId = $loId;                
            } 
        } else {
            $this->requestError = true;
            Zend_Registry::get('logger')->warn("Resource management page cannot be displayed because LO ID '$loId' does not exist");
        }
        $this->setPage();
    }
    
    //check if lo id exist in th teaching activity table
    private function _loIdExist($loId) {
        $learningObjectives = new LearningObjectives();
        $rows = $learningObjectives->fetchAll('auto_id = ' . $loId);
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
        $whereFmt = "type = '".ResourceManageConstants::$pageLo."' and type_id = ".$this->loId." and resource_type_id = %d";
        foreach($resourceTypes as $resourceType) {
            $resourceTypeId = $resourceType['auto_id'];
            $where = sprintf($whereFmt,$resourceTypeId);
            $resources = $this->getResources($where);
            $return[$resourceTypeId]['resourceTypeTableRow'] = $resourceType;
            $return[$resourceTypeId]['resources'] = $resources;
        }
        return $return;
    }
    
    //Read the parent documentation
    protected function setPage() {
        $this->page = ResourceManageConstants::$pageLo;
    }
    
    private function getLoIdObject() {
        if(is_null($this->loIdObj)) {
            $learningObjectives = new LearningObjectives();
            $row = $learningObjectives->fetchRow('auto_id = '. $this->loId);
            if(!empty($row)) {
                $this->loIdObj = $row;
            }
        }
        return $this->loIdObj;
    }
        
    public function getManageResourcesData() {
        $data = new stdClass;
        $data->display = true;
        $data->url = Compass::baseUrl().'/learningobjective/view/id/'.$this->loId;
        
        $learningObjectiveText[] = 'Learning Objective ';
        $learningObjectiveText[] = $this->loId;
        if(null !== $this->getLoIdObject()) {
            if(strlen(trim($this->getLoIdObject()->shorttitle)) > 0) {
                $learningObjectiveText[] = $this->getLoIdObject()->shorttitle;
            }
        }
        $data->title = join(' - ', $learningObjectiveText);
        
        return $data;
    }
    
    public function getPageType() {
        return 'lo';
    }
    
    public function getPageTypeId() {
        return $this->loId;      
    }
    
}
?>