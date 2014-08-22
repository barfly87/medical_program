<?php
class ResourceUploadEditRequest {
    private $action         = null;
    private $typeId         = null;
    private $type           = null;
    private $resourceId     = null;
    private $tempResource   = null;
    private $mid            = null;
    private $div            = null;
    
    protected $request      = null;
    
    private $error          = array();
    
    protected function processAction() {
        //zend framework action
        $action         = $this->request->getParam('action');
        if(! in_array($action, array('upload','edit'))) {
            $this->errors[] = "'action' should be either upload or edit.";   
        } else {
            $this->action = $action;            
        }
    }
    
    protected function processType() {
        $type = $this->request->getParam('type','');
        if(empty($type)) {
           $this->errors[] = "'type' request parameter is empty"; 
        } else if(! in_array($type, ResourceConstants::$TYPES_allowed)) {
            $this->errors[] = "'type' request parameter has value of '$type' instead of either ".print_r(ResourceConstants::$TYPES_allowed, true);   
        } else {
            $this->type = $type;            
        }
    }
    
    protected function processTypeId() {
        $typeId = $this->request->getParam('id','');
        if(empty($typeId)) {
            $this->errors[] =  "'id' request parameter is empty.";
        } else {
            if($typeId == 'new') {
                $this->typeId = 'new';    
            } else if((int)$typeId > 0){
                $this->typeId = (int)$typeId;
            } else {
                $this->errors[] =  "'id' request parameter is '$typeId' instead of 'new' or a value greater than '0'";
            }
        }
    }
    
    protected function processResourceId() {
        $resourceId = (int)$this->request->getParam('resourceid',0);
        if($resourceId > 0) {
            $this->resourceId = $resourceId;
        }
    }
    
    protected function processDivForTemp() {
        $div = (int)$this->request->getParam('div',0);
        if($div > 0) {
            $this->div = $div;    
        }
    }
    
    protected function processMidForTemp() {
        $mid = MediabankResourceConstants::sanitizeMid(trim($this->request->getParam('mid','')));
        if(!empty($mid)) {
            $this->mid = $mid;
        }
    }
    
    protected function processMidForEdit() {
        if($this->typeId != null && $this->type != null & $this->resourceId != null) {
            $mediabankResource = new MediabankResource();
            $mid = $mediabankResource->getMid($this->typeId, $this->type, $this->resourceId);
            if($mid === false) {
                $error = "Could not get mid from lk_resource table using type:'%s' and type_id:'%s' and resource_id= '%s'";
                $this->errors[] =  sprintf($error, $this->typeId, $this->type, $this->resourceId);
            } else {
                $this->mid = $mid;
            }
        } else {
            $this->errors[] =  'Please process typeId, type and resourceId before you call processMidForEdit()';
        }
    }
    
    protected function setTempResource() {
        $this->tempResource = true;
    }
    
    public function getRequestParams() {
        $req = new stdClass;
        if(empty($this->errors)) {
            $req->action            = $this->action;
            $req->typeId            = $this->typeId;
            $req->type              = $this->type;
            $req->resourceId        = $this->resourceId;
            $req->tempResource      = $this->tempResource;
            $req->mid               = $this->mid;
            $req->resourceTypeId    = $this->getResourceTypeId();
            $req->div               = $this->div;
        }
        return $req;
    }

    protected function getResourceTypeId() {
        return (int)$this->request->getParam('resourcetypeid',0);
    }
    
    public function hasError() {
        if(! empty($this->errors) ) {
            $error = "Error: ". implode(PHP_EOL, $this->errors);
            Zend_Registry::get('logger')->warn($error.PHP_EOL);
            return true;
        }
        return false;
    }
    
    public function logUnauthorizedError() {
        $error = "'%s' is not allowed to run '%s' action for type '%s' and typeId '%s'".PHP_EOL;
        $error = sprintf($error,UserAcl::getUid(),$this->action,$this->type,$this->typeId);
        Zend_Registry::get('logger')->warn($error);
    }
}

?>