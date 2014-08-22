<?php
class FormProcessor_ResourceUploadEdit_Temp extends FormProcessor_ResourceUploadEdit_Abstract {
    
    public function process(Zend_Controller_Request_Abstract $requestPost) {
        $this->initParent($requestPost);
        $this->processActionName();
        $this->processTempResource();
        $this->processResourceTypeIdPost();
        $this->processTitle();
        $this->processDescription();
        $this->processAuthor();
        $this->processCopyright();
        if($this->requestActionName == MediabankResourceConstants::$FORM_actionNameUpload) {
            $this->processResource(true);    
        } else if($this->requestActionName == MediabankResourceConstants::$FORM_actionNameEdit) {
            $this->processResource(false);
        }
        $this->tempMediabankResource();
    }
        
}
?>