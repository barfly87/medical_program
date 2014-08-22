<?php
class FormProcessor_Block_Init extends FormProcessor {
    public $error = false;
    protected $block = null;
    protected $request = null;
    protected $requestParams = array();
    protected $formsProcessed = array();
    protected $resourceRequested = false;
    protected $taTitle = '';
    protected $pageTitle = null;
    
    public function process(Zend_Controller_Request_Abstract $request)  {
        $this->request = $request;
        $blockId = (int)$request->getParam('ref',0);
        if($blockId > 0) {  
            $blockDbService = new BlockDbService();
            $block = $blockDbService->getBlockName($blockId);
            if($block == false) {
                $this->error = true;
                return;
            } else {
                $this->block['blockName'] = $block;
                $this->block['blockId'] = $blockId;
            }
        } else {
            $this->error = true;
            return;
        }
        $this->setPageTitle();
    }
   
    public function setPageTitle() {
        $this->pageTitle = array(' - '.$this->block['blockName']);
    }
    
    public function getPageTitle() {
        return $this->pageTitle;
    }
    
    public function getBlockDetails() {
        return $this->block;
    }
    
    public function getRequestParams() {
        return $this->requestParams;
    }
    
    public function getFormsProcessed() {
        return $this->formsProcessed;
    }
    
    protected function processResource() {
        $resourceId = (int)$this->request->getParam('resource',0);
        $mid = $this->request->getParam('mid','');
        if($resourceId > 0 && ! empty($mid)) {
            $this->requestParams['resourceId'] = $resourceId;
            $this->requestParams['mid'] = $mid;   
            try{
                $mediabankResourceService = new MediabankResourceService();
                $this->requestParams['resourceTitle'] = $mediabankResourceService->getTitleForMid($mid);
            } catch(Exception $ex) {
                $this->requestParams['resourceTitle'] = '';
            }
            $this->resourceRequested = true;
        }
    }
    
    protected function setTaTitle($taId) {
        try {
            $this->requestParams['taTitle'] = PblBlockConst::getTaTitle($taId);
        } catch(Exception $ex) {
            $this->requestParams['taTitle'] = $this->taTitle;                        
        }
    }
    
}
?>