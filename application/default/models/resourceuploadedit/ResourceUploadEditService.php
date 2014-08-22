<?php
class ResourceUploadEditService {
    
    private $_request = null;
    private $_page = null;
    
    public function __construct ($request) {
        $this->_request = $request;
        $this->_setPageObject();
        
    }
    
    private function _setPageObject() {
        switch($this->_request->type) {
            case 'pbl':
                $this->_page = new ResourceUploadEditPbl($this->_request);
                break;
            case 'ta':
                $this->_page = new ResourceUploadEditTa($this->_request);
                break;
            case 'lo':
                $this->_page = new ResourceUploadEditLo($this->_request);
                break;
            case 'block':
                $this->_page = new ResourceUploadEditBlock($this->_request);       
                break;
        }
    }
    
    public function getViewData() {
        $page = new stdClass;
        $page->actionName               = $this->getActionName();
        $page->typeName                 = $this->_page->getTypeName();
        $page->returnUrl                = $this->_page->getReturnUrl();
        $page->returnUrlTitle           = $this->_page->getReturnUrlTitle();
        $page->actionAllowedByMediabank = $this->_page->isActionAllowedByMediabank();
        $page->metadata                 = $this->_page->getMetadataForEditAction();
        return $page;
    }
    
    private function getActionName() {
        switch($this->_request->action) {
            case 'edit':
                return 'Edit';
                break;
            case 'upload':
                return 'Upload';
                break;
        }
    }
}
?>