<?php
class ResourceUploadEditRequestFactory {
    
    public function getRequestObject(Zend_Controller_Request_Abstract $request) {
        $action = $request->getParam('action',null);
        $typeId = $request->getParam('id', null);
        if($typeId == 'new') {
            return new ResourceUploadEditRequestTemp($request);
        }
        switch($action) {
            case 'upload':
                return new ResourceUploadEditRequestUpload($request);
            break;
            case 'edit':
                return new ResourceUploadEditRequestEdit($request);
            break;
        }
        $error = "Error: TypeId is not 'new' OR action is not 'edit' or 'upload' (Resource Controller)";
        Zend_Registry::get('logger')->warn($error.PHP_EOL);
        return false;
    }
    
}
?>