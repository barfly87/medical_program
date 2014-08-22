<?php
class FormProcessor_Block_Get extends FormProcessor_Block_Init {
    
    public function process(Zend_Controller_Request_Abstract $request) {
        
        //This function process block related parameters and create block info
        parent::process($request);
        
        //This function processes request pertaining to this page
        $this->processGet();
        
    }
    
    private function processGet() {
        $resource = $this->sanitize($this->request->getParam('me',''));
        if(empty($resource)) {
           $this->error = true;
           return; 
        }
        
        $pblBlockResourceType = new PblBlockResourceType();
        //$resource would come from the url like '../get/me/casesummary' where $resource='casesummary'
        $row = $pblBlockResourceType->fetchRow(
                                        "url_name = '$resource'"
                                      );
        
        if($row === false) {
            $this->error = true;
            return;
        }
        
        $resourceId = (int)$this->sanitize($this->request->getParam('id',''));
        
        $this->requestParams['resourceUrlName'] = $row->url_name;
        $this->requestParams['resourceTypeId']  = $row->auto_id;
        $this->requestParams['resourceType']    = $row->resource_type;
        $this->requestParams['resourceId']      = $resourceId;
    }
    
    public function getPageTitle() {
        $return =  $this->pageTitle;
        $resourceTypeText = $this->requestParams['resourceType'];
        
        if($this->requestParams['resourceId'] != 0 ) {
            $resourceTitle = PblBlockConst::getResourceIdTitle($this->requestParams['resourceId']);
            if(!empty($resourceTitle)) {  
                $resourceText = $resourceTitle;
            }  
        }
        $pageTitle = array();
        (isset($resourceTypeText))  ? $pageTitle[] = $resourceTypeText  : '';
        (isset($resourceText))      ? $pageTitle[] = $resourceText      : '';
        
        $return[] = implode(' - ',$pageTitle); 
        return $return;
    }    
    
} 
?>