<?php
class ResourceLooseService {
    
    public static $ACTION_upload = 'upload';
    
    private $_mediabankResourceService = null;
    private $_subaction = '';
    
    public function __construct() {
        $this->_mediabankResourceService = new MediabankResourceService();
    }
    
    public function getViewData(Zend_Controller_Request_Abstract $request) {
        $referer = $request->getParam(MediabankResourceConstants::$FORM_looseResourceReferer, '');
        if( empty($referer) && isset($_SERVER['HTTP_REFERER']) && !empty($_SERVER['HTTP_REFERER']) ) {
            $referer = $_SERVER['HTTP_REFERER'];
        }
        $viewData = new stdClass;
        $viewData->referer = $referer;
        return array(
            'viewData' => $viewData
        );
    }
    
    public function isSubactionAllowedByMediabank($subaction) {
        $return = false;
        if(!empty($subaction)) {
            switch($subaction) {
                case self::$ACTION_upload:
                    return $this->_isUploadActionAllowedByMediabank();
                break;
            }
        }
        $this->logNotAllowedActionMediabankError($subaction);
        return $return;
    }
    
    private function _isUploadActionAllowedByMediabank(){
        $allowAdd = $this->_mediabankResourceService->allowAdd();
        if($allowAdd == false) {
            $this->logNotAllowedActionMediabankError(self::$ACTION_upload);
            return false;
        }
        return true;
    }
    
    private function logNotAllowedActionMediabankError($subaction) {
        $error = sprintf("'%s' user is not allowed by Mediabank to perform '%s' action'".PHP_EOL,
                    UserAcl::getUid(), $subaction
        );            
        Zend_Registry::get('logger')->warn($error);
    }
    
    
}
?>