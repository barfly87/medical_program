<?php
abstract class ResourceUploadEditAbstract {
    
    protected abstract function getTypeName();
    protected abstract function getReturnUrl();
    protected abstract function getReturnUrlTitle();
    protected abstract function isActionAllowedByMediabank();

    protected $request = null;
    protected $mediabankResourceService = null;
    
    public function __construct() {
        $this->mediabankResourceService = new MediabankResourceService();
    }

    public function getMetadataForEditAction() {
        $metadata = array();
        if(!is_null($this->request)) {
            if($this->request->action == 'edit' && !empty($this->request->mid)) {
                return $this->mediabankResourceService->getMetaData($this->request->mid);
            }
        }
        return $metadata;
    }
    
    protected function _isActionAllowedByMediabank() {
        $return = false;
        if(!is_null($this->request)) {
            switch($this->request->action) {
                case 'upload':
                    return $this->_isUploadActionAllowedByMediabank();
                break;
                case 'edit':
                    return $this->_isEditActionAllowedByMediabank();
                break;
            }
        }
        return $return;
    }
    
    private function _isUploadActionAllowedByMediabank(){
        $allowAdd = $this->mediabankResourceService->allowAdd();
        if($allowAdd == false) {
            $this->logNotAllowedActionMediabankError();
            return false;
        }
        return true;
    }

    private function _isEditActionAllowedByMediabank(){
        $allowEdit = $this->mediabankResourceService->allowEdit($this->request->mid);
        if($allowEdit == false) {
            $this->logNotAllowedActionMediabankError();
            return false;
        }
        return true;
    }
    
    private function logNotAllowedActionMediabankError() {
        $error = sprintf("'%s' user is not allowed by Mediabank to perform '%s' action for type '%s' and id '%d'".PHP_EOL,
                    UserAcl::getUid(),
                    $this->request->action,
                    $this->request->type,
                    $this->request->typeId
        );            
        Zend_Registry::get('logger')->warn($error);
    }
    
}
?>