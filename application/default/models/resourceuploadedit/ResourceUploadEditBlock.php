<?php
class ResourceUploadEditBlock extends ResourceUploadEditAbstract {
    private $blockId = null;
    private $blocks = null;
    
    public function __construct($request) {
        parent::__construct();
        $this->request = $request;
        $this->blockId = $request->typeId;
        $this->blocks = new Blocks();
    }
    
    public function getTypeName() {
        return 'Block';
    }
    
    public function getReturnUrl() {
        if($this->blockId <= 11) {
            return Compass::baseUrl().'/block/managestage1or2resources/ref/'.$this->blockId;
        } else {
            return Compass::baseUrl().'/block/manageresources/ref/'.$this->blockId;
        }
        
    }
    
    public function getReturnUrlTitle() {
        return $this->blocks->getBlockName($this->blockId);
    }
    
    public function isActionAllowedByMediabank() {
        return parent::_isActionAllowedByMediabank();
    }
}
?>