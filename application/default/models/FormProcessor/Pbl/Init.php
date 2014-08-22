<?php
class FormProcessor_Pbl_Init extends FormProcessor {
    
    private $titleCurrentPbl    = array('- Current PBL');
    private $titleStage1Pbl     = array('- Current Stage 1 PBL');
    private $pblRef             = null;
    private $pblDetails         = array();
    
    protected $pageTitle        = null;
    protected $request          = null;
    protected $taTitle          = '';
    protected $req              = array();
    protected $configWeekview   = array();
    protected $reqConfig        = array();
    
    public $error               = false;
    
    public function process(Zend_Controller_Request_Abstract $request)  {
        $this->configWeekview = Compass::getConfig('weekview');
        $this->req['configWeekview'] = $this->configWeekview;
        $this->request = $request;
        if(UserAcl::isStudent()) {
            $this->processStudent($this->request);
        } else {
            $this->processStaff($this->request);    
        }
        //'Pbl' class creates pbl info based on pbl ref 
        $pbl = new Pbl($this->pblRef);
        if($pbl->hasError() == true) {
            $this->error = true;
            return;
        }
        //Store pbl details
        $this->pblDetails = $pbl->getPblDetails();
        
        //If pbl ref was given 
        $this->setTitleIfNull();
    }

    private function processStudent(Zend_Controller_Request_Abstract $request) {
        $ref = $request->getParam(PblConst::$ref, UserAcl::currentPbl());
        if($ref == UserAcl::currentPbl()) {
            $this->pageTitle = $this->titleCurrentPbl;
        }
        $this->setPblRef($ref);
    }
    
    private function processStaff(Zend_Controller_Request_Abstract $request) {
        $ref = $request->getParam(PblConst::$ref, UserAcl::stage1Pbl());
        if($ref == UserAcl::stage1Pbl()) {
            $this->pageTitle = $this->titleStage1Pbl;
        }
        $this->setPblRef($ref); 
    }
    
    private function setTitleIfNull() {
        if(is_null($this->pageTitle) && !is_null($this->pblRef) && !empty($this->pblDetails) && !is_null($this->pblDetails['pblName'])) {
            $this->pageTitle = array($this->pblRef . ' - ' . $this->pblDetails['pblName']);
        }
    }
    
    private function setPblRef($ref) {
        $this->pblRef = $ref;
    }
    
    public function getPageTitle() {
        return $this->pageTitle;
    }
    
    public function getPblDetails() {
        return $this->pblDetails;
    }
    
    public function getReq() {
        return $this->req;
    }

    protected function processResource() {
        if(isset($this->req['type']) && isset($this->req['typeId'])) {
            $type = $this->req['type'];
            $typeId = $this->req['typeId'];
            $resourceId = (int)$this->request->getParam('resourceid', 0);
            $resourceTypeId = (int)$this->request->getParam('resourcetypeid', 0);
            
            $pblBlockResource = new PblBlockResource();
            if($resourceId > 0) {
                $this->req['resourceId'] = $resourceId;
                $mediabankResourceService = new MediabankResourceService();
                
                $where = 'pbr.auto_id = ' .$resourceId;
                $rows = $pblBlockResource->getAllResources($type, $typeId, $where);
                if(!empty($rows) && isset($rows[0]['resource_id'])) {
                    $mid = $rows[0]['resource_id'];
                    $this->req['resourceIdTitle'] = $mediabankResourceService->getTitleForMid($mid);
                }
                
            } else if($resourceTypeId > 0){
                $this->req['resourceTypeId'] = $resourceTypeId;
                $where = 'pbr.resource_type_id = '.$resourceTypeId;
                $rows = $pblBlockResource->getAllResources($type, $typeId, $where);
                if(!empty($rows) && isset($rows[0]['resource_type_name'])) {
                    $this->req['resourceTypeName'] = $rows[0]['resource_type_name'];
                }
            }
            if(!empty($rows)) {
                foreach($rows as $key => $row) {
                    if(MediabankResourceService::isUserAllowed($row['allow']) != true) {
                        unset($rows[$key]);
                    }
                }
                $this->req['resources'] = $rows;
            }
        }
    }
    
    protected function setRequestConfig() {
        if(! empty($this->configWeekview)) {
            if(isset($this->req['type'])) {
                $items = $this->configWeekview['item'];
                if(!empty($items)) {
                    switch($this->req['type']) {
                        case 'ta':
                            $this->reqConfig = $this->getRequestConfigTaActivityType($items);
                        break;
                        case 'lo':
                            $this->reqConfig = $this->getRequestConfigLo($items);
                        break;
                        case 'pbl':
                            $this->reqConfig = $this->getRequestConfigPblCategory($items);
                        break;
                    }
                } else {
                    Compass::error('Pbl Item Config is not set. Check logs');
                }
            }
        } else {
            Compass::error('Pbl config key "weekview" is not set in the config.ini file', __DIR__.'/'.__CLASS__, __LINE__);
        }
    }
    
    private function getRequestConfigTaActivityType($items) {
        if(isset($this->req['activityTypeId'])) {
            foreach($items as $item) {
                if($item['type'] == 'ta' && $item['activitytypeid'] == $this->req['activityTypeId']) {
                    return $item;
                }
            }
            Compass::error('Pbl config key "weekview.item.*.activitytypeid" is not set in the config.ini file. (* can be the config name for this teaching activity id)', __DIR__.'/'.__CLASS__, __LINE__);
        } else {
            Compass::error('When GET param [type="ta"] is received we are also expecting a GET param "activitytypeid" to be present which does not seem to be the case.', __DIR__.'/'.__CLASS__, __LINE__);
        }
        return array();
    }
    
    private function getRequestConfigLo($items) {
        if(isset($this->configWeekview['item'])) {
            foreach($items as $item) {
                if($item['type'] == 'lo') {
                    return $item;
                }
            }
        } 
        Compass::error('Pbl config key "weekview.item.learningobjective" is not set in the config.ini file', __DIR__.'/'.__CLASS__, __LINE__);
        return array();
    }
    
    private function getRequestConfigPblCategory($items) {
        if(isset($this->req['category'])) {
            foreach($items as $item) {
                if($item['type'] == 'pbl' && $item['category'] == $this->req['category']) {
                    return $item;
                }
            }
            Compass::error('Pbl config key "weekview.item.*.category" is not set in the config.ini file. (* e.g pblresources or manageresources )', __DIR__.'/'.__CLASS__, __LINE__);
        } else {
            Compass::error('When GET param [type="pbl"] is received we are also expecting a GET param "category" to be present which does not seem to be the case.', __DIR__.'/'.__CLASS__, __LINE__);
        }
        return array();
    }

    
    protected function setTaTitle($taId) {
        try {
            $this->req['taTitle'] = PblBlockConst::getTaTitle($taId);
        } catch(Exception $ex) {
            $this->req['taTitle'] = $this->taTitle;                        
        }
    }
    
}
?>