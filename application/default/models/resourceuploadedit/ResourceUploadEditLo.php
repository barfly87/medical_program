<?php
class ResourceUploadEditLo extends ResourceUploadEditAbstract {
    
    public function __construct($request) {
        parent::__construct();
        $this->request = $request;
    }
    
    public function getTypeName() {
        return 'Learning Objective';
    }
    
    public function getReturnUrl() {
        return Compass::baseUrl().'/resource/manage/page/lo/ref/'.$this->request->typeId;
    }
    
    public function getReturnUrlTitle() {
        return $this->request->typeId;
    }
    
    public function isActionAllowedByMediabank() {
        return parent::_isActionAllowedByMediabank();
    }
    
    
}
?>