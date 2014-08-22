<?php  
 
class Disc_DiscController extends Zend_Controller_Action { 
    
    public function init() {
        $webservicesActions = array('disciplineservice');
        $readActions = array('index', 'view', 'taform', 'menu1','list', 'mydetails','mydetailscomplete');
        $writeActions = array('add', 'addcomplete', 'edit', 'editcomplete','list', 'mydetails','mydetailscomplete');
        $this->_helper->_acl->allow('guest',$webservicesActions);
        $this->_helper->_acl->allow('student', $readActions);
        $this->_helper->_acl->allow('staff', $writeActions);
    }

    public function indexAction() {              
        $request = $this->getRequest();
        $this->view->title = DisciplineService::$indexTitle;

        //If user is student show 'My Details' Page or if user is admin show 'List' Page
        $disciplineService = new DisciplineService();
        $compassAdmin = $disciplineService->isUserCompassAdmin();
        $admin = $disciplineService->isUserAdmin();
        if($compassAdmin || $admin) {
            $this->_redirect(DisciplineService::$listLink);
        } else {
            $this->_redirect(DisciplineService::$myDetailsLink);
        }      
    }

    public function disciplineserviceAction(){
        ini_set('soap.wsdl_cache_enabled', '0');
        $request = $this->getRequest();
        $wsdl = $request->getParam('wsdl', null); 
        if(isset($wsdl)) {
            $autodiscover = new Zend_Soap_AutoDiscover();
            $autodiscover->setClass('DisciplineSoapWebService');
            $autodiscover->handle();
        } else {
            $wsdlLink = 'http://' . $_SERVER['HTTP_HOST'] . Compass::baseUrl() .'/disc/disc/disciplineservice?wsdl';
            $soap = new Zend_Soap_Server($wsdlLink);
            $soap->setClass('DisciplineSoapWebService');
            $soap->handle();
        }
        exit;
    }
       
    public function listAction() { 
        $request = $this->getRequest();  
        PageTitle::setTitle($this->view, $request);
        $this->view->title = DisciplineService::$listTitle;
        $this->view->editLink = Compass::baseUrl().DisciplineService::$editLink;
        
        $disciplineService = new DisciplineService();
        $this->view->rows = $disciplineService->getListOfDisciplinesForListPage();

        //Students are not allowed to see edit or add links        
        $this->view->admin = $disciplineService->isUserCompassAdmin();

    }

    public function addAction() {   
        
        //Check if user is admin if not they cannot edit     
        $disciplineService = new DisciplineService();
        $admin = $disciplineService->isUserCompassAdmin();
        if(!$admin) {
            $this->_redirect(DisciplineService::$myDetailsLink);
        }
        
        $this->view->title = DisciplineService::$addTitle;        
        $request = $this->getRequest();        
        PageTitle::setTitle($this->view, $request);  
        $fp = new FormProcessor_AddEditDiscipline();
        
        $disciplineService = new DisciplineService();
        $errors = $this->_request->getParam('error');        
        if(isset($errors) && strlen($errors) > 0) {
            $this->view->errors = $disciplineService->createErrors($errors);
        }

        if ($request->isPost()) {
            $result = $fp->process($request);
            //If any errors found redirect to 'Add' Page
            if( isset($result['error'] ) && $result['error'] === true ) {
                $this->_redirect(DisciplineService::$addLink.'/error/'.$result['error_msg']);
            }
            $session = new Zend_Session_Namespace('discAddComplete');
            $session->disc_id = $result;
            //Redirect to 'Add Complete' Page
            $this->_redirect(DisciplineService::$addCompleteLink);
        }      

        $this->view->types = DisciplineService::$types;
        $this->view->disciplines = $disciplineService->getListOfDisciplines();
        $this->view->fp = $fp;
    }

    public function addcompleteAction() {
    	PageTitle::setTitle($this->view, $this->_request);
        $this->view->title = DisciplineService::$addCompleteTitle;
        $this->view->listLink = Compass::baseUrl().DisciplineService::$listLink;
        $session = new Zend_Session_Namespace('discAddComplete');
        if (!$session->disc_id) {
            $this->_redirect(DisciplineService::$addLink);
            return;
        }
    }
    
    private function isUserCompassAdmin($disciplineService){
        $admin = $disciplineService->isUserCompassAdmin();
        if(!$admin) {
            $this->_redirect(DisciplineService::$listLink);
        }        
    }
    
    private function checkDisciplineExist($disciplineService, $request){
        //Get Id
        $id = (int)$request->getParam('id');
        if(empty($id) || $id <= 1){
            $this->_redirect(DisciplineService::$listLink);
        }
        //Get row for this id
        $row = $disciplineService->getRowForId($id);
        // If row does not exist for this id, redirect to 'List' Page
        if($row === false) {
            $this->_redirect(DisciplineService::$listLink);
        } 
        return array('id' => $id, 'row' => $row);
    }
    
    private function allowEdit($disciplineService, $row){
        //Check if user can edit this page
        $isAllowEdit = $disciplineService->allowEdit($row);
        if(!$isAllowEdit) {
            $this->_redirect(DisciplineService::$listLink);
        }         
    }

    public function editAction() {
        $request = $this->getRequest();
        PageTitle::setTitle($this->view, $request);       
        $disciplineService = new DisciplineService();
        
        //Check if user is compass admin. If not they cannot edit
        $this->isUserCompassAdmin($disciplineService);
        
        //Check if discipline exist 
        $result = $this->checkDisciplineExist($disciplineService, $request);
        $id = $result['id'];
        $row = $result['row'];
        
        //Check if user is allowed to edit this page
        $this->allowEdit($disciplineService, $row); 
             
        $this->view->title = DisciplineService::$editTitle;
        
        $errors = $this->_request->getParam('error');
        if(isset($errors) && strlen($errors) > 0) {
            $this->view->errors = $disciplineService->createErrors($errors);
        }

        $fp = new FormProcessor_AddEditDiscipline($id);
        if ($request->isPost()) {
            $result = $fp->process($request);

            //If any errors found redirect to 'Edit' Page of the 'id'
            if( isset($result['error'] ) && $result['error'] === true ) {
                $this->_redirect(DisciplineService::$editLink.'/id/'.$id.'/error/'.$result['error_msg']);
            }
            $session = new Zend_Session_Namespace('discEditComplete');
            $session->disc_id = $result;
            //Redirect to 'Edit Complete' Page
            $this->_redirect(DisciplineService::$editCompleteLink);
        } 
        $this->view->discipline = $row;
        $this->view->types = DisciplineService::$types;
        $this->view->disciplines = $disciplineService->getListOfDisciplinesForEditPage($id);       
    }

    public function editcompleteAction() {
    	PageTitle::setTitle($this->view, $this->_request);
        $this->view->title = DisciplineService::$editCompleteTitle;
        $this->view->listLink = Compass::baseUrl().DisciplineService::$listLink;
        $session = new Zend_Session_Namespace('discEditComplete');
        if (!$session->disc_id) {
            $this->_redirect(DisciplineService::$listLink);
            return;
        }
    }

    public function mydetailsAction() {
        $request = $this->getRequest();
        PageTitle::setTitle($this->view, $request);
        $this->view->title = DisciplineService::$mydetailsTitle;
        
        if ($request->isPost()) {
            $fp = new FormProcessor_MyDetails();
            $result = $fp->process($request);

            //If any errors found redirect to 'My Details' Page
            if($result === false) {
                $this->_redirect(DisciplineService::$myDetailsLink);
            }
            $session = new Zend_Session_Namespace('discMyDetailsComplete');
            $session->disc_id = $result;
            //Redirect to 'My Details Complete' Page
            $this->_redirect(DisciplineService::$myDetailsCompleteLink);
        }

        $disciplineService = new DisciplineService();    
        $this->view->username = $this->view->escape($disciplineService->getUsername());
        $this->view->disciplines = $disciplineService->getListOfDisciplinesForMyDetailsPage(); 
        $this->view->user_id = Zend_Auth::getInstance()->getIdentity()->user_id;  
        $this->view->user_disc = $disciplineService->getSelectedDisciplinesForUser($this->view->user_id);   
    }
   
    public function mydetailscompleteAction() {  
    	PageTitle::setTitle($this->view, $this->_request);      
        $this->view->title = DisciplineService::$mydetailsCompleteTitle;
        $this->view->listLink = Compass::baseUrl().DisciplineService::$listLink;
        $session = new Zend_Session_Namespace('discMyDetailsComplete');
        if (!$session->disc_id) {
            $this->_redirect(DisciplineService::$myDetailsLink);
        }        
    }
    
} 

