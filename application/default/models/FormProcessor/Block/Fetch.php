<?php
class FormProcessor_Block_Fetch extends FormProcessor_Block_Init {
    
    private $taTypeId   = null;
    private $taTypeName = null;
    private $taId       = null;
    private $viewPage   = null;
    
    
    
    public function process(Zend_Controller_Request_Abstract $request) {
        
        //This function process block related parameters and create block info
        parent::process($request);
            
        //This function processes request parameters specific to this page
        $this->processFetch();

        //set ta title
        $this->setTaTitle($this->taId);
        
    }
    
    private function processFetch() {
        $taTypeFinder   = new ActivityTypes();
        $viewPage       = null;
        $taTypeId       = (int) $this->request->getParam('tatypeid',0);
        $taTypeName     = null;

        //Check if the Ta Type Id exist in the database and fetch Ta Type Name if found.
        if($taTypeId > 0) {
            try {
                $taTypeName = $taTypeFinder->getActivityName($taTypeId);
                if(empty($taTypeName)) {
                    $error = "Could not find Ta type id : ".$this->request->getParam('tatypeid');
                    Zend_Registry::get('logger')->warn(PHP_EOL."CLASS\t: ".__CLASS__.PHP_EOL."METHOD\t: ".__METHOD__.PHP_EOL."ERROR\t: ".$error.PHP_EOL);
                    $this->error = true;
                }
            } catch (Exception $ex) {
                $error = "Could not find Ta type id : ".$this->request->getParam('tatypeid');
                Zend_Registry::get('logger')->warn(PHP_EOL."CLASS\t: ".__CLASS__.PHP_EOL."METHOD\t: ".__METHOD__.PHP_EOL."ERROR\t: ".$error.PHP_EOL);
                $this->error = true;
            }
            $viewPage = 'list';
        }
        //Check if Ta Id exist in the database and its ta type id is the one we have received
        $taId = (int)$this->request->getParam('taid',0);
        if($taId > 0) {
            try {
                $teachingActivities = new TeachingActivities();
                $taRows = $teachingActivities->fetchAll("auto_id = $taId and type= $taTypeId" );
                if($taRows->count() > 0) {
                    $viewPage = 'ta';
                } else {
                    $error = "Could not find Ta id ".$this->request->getParam('taid')." for ta type id : ".$this->request->getParam('tatypeid');
                    Zend_Registry::get('logger')->warn(PHP_EOL."CLASS\t: ".__CLASS__.PHP_EOL."METHOD\t: ".__METHOD__.PHP_EOL."ERROR\t: ".$error.PHP_EOL);
                    $taId = 0;    
                }
            } catch (Exception $ex) {
                $error = "Could not find Ta id ".$this->request->getParam('taid')." for ta type id : ".$this->request->getParam('tatypeid');
                Zend_Registry::get('logger')->warn(PHP_EOL."CLASS\t: ".__CLASS__.PHP_EOL."METHOD\t: ".__METHOD__.PHP_EOL."ERROR\t: ".$error.PHP_EOL);
            }
        }
        //If there are no errors then set vars
        if($this->error !== true) {
            $this->taTypeId                     = $taTypeId;
            $this->taTypeName                   = $taTypeName;
            $this->taId                         = $taId;
            $this->viewPage                     = $viewPage;
        }
        $this->requestParams['taTypeId']    = $this->taTypeId;
        $this->requestParams['taTypeName']  = $this->taTypeName;
        $this->requestParams['taId']        = $this->taId;
        $this->requestParams['viewPage']    = $this->viewPage;
    }
    
    public function getPageTitle() {
        $pageTitle = $this->pageTitle[0];
        switch($this->requestParams['viewPage']) {
            case 'list':
                $this->requestParams['taTypeName'] = BlockConst::renameTaTypeForBlockMenu($this->requestParams['taTypeName']);
                $pageTitle .= ' - '.$this->requestParams['taTypeName'];
            break;
            case 'ta':
                $pageTitle .= ' - '.$this->requestParams['taTypeName'].' - '.$this->requestParams['taTitle'];
            break;
        }
        return $pageTitle;
    }    
    
} 
?>