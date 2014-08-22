<?php 
class ResourceUploadEditTa extends ResourceUploadEditAbstract {
    
    public function __construct($request) {
        parent::__construct();
        $this->request = $request;
    }
    
    public function getTypeName() {
        return 'Teaching Activity';
    }
    
    public function getReturnUrl() {
        return Compass::baseUrl().'/resource/manage/page/ta/ref/'.$this->request->typeId;
    }
    
    public function getReturnUrlTitle() {
        return $this->request->typeId;
    }
    
    public function isActionAllowedByMediabank() {
        return parent::_isActionAllowedByMediabank();
    }
    
}
?>
