<?php
abstract class ResourceManageAbstract {
    
    //This function is used by the controller class to check whether
    //the extending class threw any error in regards to the request parameters received
    abstract protected function hasRequestError();
    
    //This function is used by the controller class to get the results back for displaying
    //resource types for this page and the resources attaching to each one of them
    //  13 =>                                       //resource_type_id                                          
    //      array (
    //          'resourceType' => 
    //              array (
    //                  'auto_id' => 13,
    //                  'resource_type' => 'Recordings',
    //                  'url_name' => '',
    //                  'allow' => 'student',
    //                  'pbl' => 0,
    //                  'block' => 0,
    //                  'ta' => 1,
    //                  'lo' => 0,
    //          ),
    //        'resources' => 
    //              array (
    //                  0 => 
    //                      array (
    //                          'auto_id' => 4546,
    //                          'type' => 'ta',
    //                          'type_id' => 1824,
    //                          'resource_type' => 'mediabank',
    //                          'resource_id' => 'http://smp.sydney.edu.au/mediabank/|lectopia|24082',
    //                          'order_by' => 1,
    //                          'resource_type_id' => 13,
    //                  ),
    //              ),
    //          ),
    abstract protected function getResults();
    
    //The extending class needs to set the page before any processing pertaining to this page
    //is done.
    abstract protected function setPage();
    
    abstract protected function getManageResourcesData();
    
    abstract protected function getPageType();
    
    //Table object for 'PblResource'
    protected $pblResource = null;
    
    //Table object for 'PblResourceType'
    protected $pblResourceType = null;
    
    //Mediabank Service Object
    protected $mediabankResourceService = null;
    
    //Default order by for the queries searched in 'PblResource' table
    private $defaultResourceOrderBy = 'order_by ASC';
    
    //Default order by for the queries searched in 'PblResourceType' table
    private $defaultResourceTypeOrderBy = 'resource_type ASC';
    
    protected $page = null;
    
    protected function getResourceTypesForCurrentPage($orderBy = null) {
        //Check if the page is within the the current list of existing pages. eg.. 'ta', 'lo', 'pbl', 'block' ..
        if(in_array($this->page, ResourceManageConstants::$pages)) {
            try {
                //We don't want to create more than one object for 'PblResourceType'
                if(is_null($this->pblResourceType)) {
                    $this->pblResourceType = new PblResourceType();
                }
                //This basically allows for custom order by otherwise default one would be used.
                if(is_null($orderBy)) {
                    $orderBy = $this->defaultResourceTypeOrderBy;
                }
                //We want to look for only those resource types (for this page) whose flag is set to '1'
                //'1' basically ones this page should allow the staff to add resources of this type.
                $where = $this->page .' = 1';
                $rows = $this->pblResourceType->fetchAll($where, $orderBy);
                if($rows->count() > 0) {
                    return $rows->toArray();
                }
                return array();
            } catch (Exception $ex) {
                Zend_Registry::get('logger')->warn("Error while looking for value 1 in column '{$this->page}' in table 'lk_resourcetype'");
                return array();
            }
        }  
        return array();          
    }
    
    protected function getResources($where, $orderBy = null) {
        $return = array();
        if(is_null($this->mediabankResourceService)) {
            $this->mediabankResourceService = new MediabankResourceService();
        }
        //Where should not be empty. It should be something like "type ='ta' and type_id= '' ...."
        if(!empty($where)) {
            //We don't want to create more than one object of 'PblResource'
            if(is_null($this->pblResource)) {
                $this->pblResource = new PblResource();    
            }
            //This basically allows for custom order by otherwise default one would be used.
            if(is_null($orderBy)) {
                $orderBy = $this->defaultResourceOrderBy;
            }
            $rows = $this->pblResource->fetchAll($where, $orderBy);
            if($rows->count() > 0) {
                $resources = $rows->toArray();
                $count = 1;
                foreach($resources as $resource) {
                    $title = $this->mediabankResourceService->getTitleForMid($resource['resource_id']);
                    $allowEdit = $this->mediabankResourceService->allowEdit($resource['resource_id']);
                    $history = $this->getHistory($resource['auto_id']);
                    $return[] = $resource + array('resource_title'=>trim($title),'allow_edit'=>$allowEdit,'history'=>$history);
                }
            }
            
        }
        return $return;
    }
    
    protected function getHistory($resourceAutoId) {
        $mediabankResourceHistory = new MediabankResourceHistory();
        return $mediabankResourceHistory->getHistory($resourceAutoId);
    }
    
}
?>