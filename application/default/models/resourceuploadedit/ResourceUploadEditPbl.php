<?php
class ResourceUploadEditPbl extends ResourceUploadEditAbstract {
    private $pblId = null;
    private $pbls = null;
    
    public function __construct($request) {
        parent::__construct();
        $this->request = $request;
        $this->pblId = $request->typeId;
        $this->pbls = new Pbls();
    }
    
    public function getTypeName() {
        return 'PBL';
    }
    
    public function getReturnUrl() {
        $ref = $this->pbls->getPblRef($this->pblId);
        return Compass::baseUrl().'/pbl/display/type/pbl/category/managepblresources/ref/'.$ref;
    }
    
    public function getReturnUrlTitle() {
        return $this->pbls->getPblName($this->pblId);
    }
    
    public function isActionAllowedByMediabank() {
        return parent::_isActionAllowedByMediabank();
    }
    
}
?>