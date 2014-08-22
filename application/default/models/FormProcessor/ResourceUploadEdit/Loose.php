<?php
class FormProcessor_ResourceUploadEdit_Loose extends FormProcessor_ResourceUploadEdit_Abstract {
    
    public function process(Zend_Controller_Request_Abstract $requestPost) {
        $this->initParent($requestPost);
        $this->processTitle();
        $this->processDescription();
        $this->processAuthor();
        $this->processCopyright();
        $this->processResource(true);
        return $this->looseUploadMediabankResource();
    }
    
}
?>