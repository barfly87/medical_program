<?php
class ResourceUploadEditRequestTemp extends ResourceUploadEditRequest {
    
    public function __construct(Zend_Controller_Request_Abstract $request) {
        $this->request = $request;
        $this->setTempResource();
        $this->processAction();
        $this->processType();
        $this->processTypeId();
        $this->processResourceId();
        $this->processMidForTemp();
        $this->processDivForTemp();
    }
    
}
?>