<?php
class ResourceUploadEditRequestUpload extends ResourceUploadEditRequest  {
    
    public function __construct(Zend_Controller_Request_Abstract $request) {
        $this->request = $request;
        $this->processAction();
        $this->processType();
        $this->processTypeId();
        $this->processResourceId();
    }
    
}
?>