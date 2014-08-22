<?php
class ResourceManagePbl extends ResourceManageAbstract {
    private $pblId = null;
    private $requestError = false;
    
    public function __construct($request) {
        //set pblId
        $pblId = (int)$request->ref;
        if($pblId > 0) {
            //check if ta id exist in th teaching activity table
            $pblIdExist = $this->_pblIdExist($pblId);
            if($pblIdExist === false) {
                $this->requestError = true;
                Zend_Registry::get('logger')->warn("Resource management page cannot be displayed because PBL ID '$pblId' does not exist");
            } else {
                $this->pblId = $pblId;                
            }
        } else {
            $this->requestError = true;
            Zend_Registry::get('logger')->warn("Resource management page cannot be displayed because PBL ID '$pblId' does not exist");
        }
        
        $this->setPage();
    }
    
    //check if pbl id exist in the teaching activity table
    private function _pblIdExist($pblId) {
        $pbls = new Pbls();
        $rows = $pbls->fetchAll('auto_id = ' . $pblId);
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
        $whereFmt = "type = '".ResourceManageConstants::$pagePbl."' and type_id = ".$this->pblId." and resource_type_id = %d";
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
        $this->page = ResourceManageConstants::$pagePbl;
    }

    public function getManageResourcesData() {
        $data = new stdClass;
        $data->display = false;
        $data->title = '';
        $data->url ='';
        return $data;
    }
    
    
    public function getPageType() {
        return 'pbl';
    }
    
    public function getPageTypeId() {
        return $this->pblId;      
    }
    
}
?>