<?php 
class ResourceManageService {
    private $request = null;
    private $error = false;
    private $pageObj = null;
    
    public function getPageObject() {
        return $this->pageObj;
    }
    
    public function processRequest(Zend_Controller_Request_Abstract $request){
        $this->_setRequest($request);
        $page   = $request->getParam('page',    '');
        $ref    = $request->getParam('ref',     '');
        if(empty($page) || empty($ref)) {
            $this->error = true;
            return;
        }
        $this->_setPageObject($page, $ref);
        if(is_null($this->pageObj) || $this->pageObj->hasRequestError() === true) {
            $this->error = true;
            return;
        }
    }
    
    public function hasError() {
        return $this->error;
    }
    
    public function getResults() {
        return $this->pageObj->getResults();
    }
    
    public function getManageResourcesData() {
        return $this->pageObj->getManageResourcesData();
    }
    
    public function getPageType() {
        return $this->pageObj->getPageType();
    }
    
    public function getPageTypeId() {
        return $this->pageObj->getPageTypeId();
    }
    
    public function getRequest() {
        return $this->request;
    }

    private function _setRequest(Zend_Controller_Request_Abstract $request) {
        $this->request = $request;
    }
    
    private function _setPageObject($page,$ref) {
        $request = new stdClass;
        $request->ref = $ref;
        switch($page) {
            case 'ta':
                $this->pageObj = new ResourceManageTa($request);
                break;
            case 'lo':
                $this->pageObj = new ResourceManageLo($request);
                break;
            case 'pbl':
                $this->pageObj = new ResourceManagePbl($request);
                break;
            case 'block':
                $this->pageObj = new ResourceManageBlock($request);
                break;
        }
    }
    
}//END OF CLASS

?>