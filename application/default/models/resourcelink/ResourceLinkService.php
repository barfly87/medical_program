<?php
class ResourceLinkService {
    
    private $_requestTypeId     = null;
    private $_requestType       = null;
    private $typeName           = null;
    private $typeReturnUrl      = null;
    private $typeDescription    = null;
    private $typeReturnUrlTitle = null;
    
    public function __construct ($requestTypeId, $requestType) {
        $this->_requestTypeId   = $requestTypeId;
        $this->_requestType     = $requestType;
        $this->_process();
        
    }
    
    private function _process() {
        if($this->_requestTypeId == 'new') {
            $this->_processTemp();            
        } else {
            switch($this->_requestType) {
                case 'pbl':
                    $this->_processPbl();
                    break;
                case 'ta':
                    $this->_processTa();
                    break;
                case 'lo':
                    $this->_processLo();
                    break;
                case 'block':
                    $this->_processBlock();       
                    break;
            }
        }
    }
    
    private function _processPbl() {
        $pbls = new Pbls();
        $ref = $pbls->getPblRef($this->_requestTypeId);

        $this->typeName             = 'PBL';
        $this->typeReturnUrl        = Compass::baseUrl().'/pbl/manageresources/ref/'.$ref;
        $this->typeReturnUrlTitle   = $pbls->getPblName($this->_requestTypeId);
        $this->typeDescription      = '';
    }

    private function _processTa() {
        $ta = new TeachingActivities();
        $row = $ta->fetchRow('auto_id='.$this->_requestTypeId);
        
        $this->typeName = 'Teaching Activity';
        $this->typeReturnUrl = Compass::baseUrl().'/resource/manage/page/ta/ref/'.$this->_requestTypeId;
        $this->typeReturnUrlTitle = $this->_requestTypeId;
        $this->typeDescription = $row->name;
    }
    
    private function _processLo() {
        $lo = new LearningObjectives();
        $row = $lo->fetchRow('auto_id='.$this->_requestTypeId);
        
        $this->typeName = 'Learning Objective';
        $this->typeReturnUrl = Compass::baseUrl().'/resource/manage/page/lo/ref/'.$this->_requestTypeId;
        $this->typeReturnUrlTitle = $this->_requestTypeId;
        $this->typeDescription = $row->lo;
    }
    
    private function _processBlock() {
        $blocks = new Blocks();
        
        $this->typeName = 'Block';
        $this->typeReturnUrl = Compass::baseUrl().'/block/manageresources/ref/'.$this->_requestTypeId;
        $this->typeReturnUrlTitle = $blocks->getBlockName($this->_requestTypeId);
        $this->typeDescription = '';
    }
    
    private function _processTemp() {
        $this->typeName = '';
        if($this->_requestType == 'ta') {
            $this->typeName = 'Teaching Activity';
        } else if ($this->_requestType == 'lo') {
            $this->typeName = 'Learning Objective';
        }
        
        $this->typeReturnUrl = '';
        $this->typeReturnUrlTitle = '';
        $this->typeDescription = '';
    }
    
    public function getViewData() {
        $data = new stdClass;
        $data->typeName             = $this->typeName;
        $data->typeReturnUrl        = $this->typeReturnUrl;
        $data->typeReturnUrlTitle   = $this->typeReturnUrlTitle;
        $data->typeDescription      = $this->typeDescription;
        $data->mediabankCollections = $this->getMediabankCollections();
        return $data;
    }
    
    public function getMediabankCollections() {
        $mediabankResourceService = new MediabankResourceService();
        $collectionIds = $mediabankResourceService->getListOfCollectionIds();
        array_unshift($collectionIds, 'any');
        sort($collectionIds);
        return $collectionIds;
    }

}
?>