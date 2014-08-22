<?php  

class PblController extends Zend_Controller_Action {
    
    public function init() {
        $student = array('index','top','display');
        $this->_helper->_acl->allow('student',$student);
    }
    
    public function indexAction() {
        $params = $this->_request->getParams();
        $url = '/pbl/display/type/lo';
        if(isset($params['ref'])) {
            $url .= '/ref/'.$params['ref'];
        }
        $this->_redirect($url);
    }
    
    public function displayAction() {
        $request = $this->getRequest();
        //Get the class for form to process and which page to render
        $fp = new FormProcessor_Pbl_Display();
        $fp->process($request);
        
        //If any error found throw error
        if($fp->error) {
            $this->throwError();
        }
        
        //Set page title that would be shown by the browser in the title bar for bookmarking purposes
        PageTitle::setTitle($this->view, $request, $fp->getPageTitle());
        
        //Get the pbl details which would be common to all the pages from 'FormProcessor_Pbl_Init' class
        //They contain some information which is used by 'menu.phtml' file for displaying left hand side menu.
        $pblDetails = $fp->getPblDetails();
        $this->view->assign($pblDetails);
        
        //Get all the data regarding the request that is been made
        $req = $fp->getReq();
        
        $pblDisplay = new PblDisplay();
        $pblDisplay->setPblDetails($pblDetails);
        $pblDisplay->setReq($req);
        $this->view->assign($pblDisplay->getPageDetails());
        
        //manageresources.phtml file is used by both block and pbl interface
        $this->view->pblOrBlock = PblBlockConst::$pbl;
        $this->view->pblOrBlockRef = $pblDetails['pblRef'];
        $this->view->pblOrBlockId = $pblDetails['pblId'];
        $this->view->pblOrBlockName = $pblDetails['pblName'];
        
        $this->render('fragments/template');
    }
    
    private function throwError() {
        throw new Zend_Controller_Action_Exception("Page not found.", 404);
    }

    public function topAction() {
        PageTitle::setTitle($this->view, $this->getRequest());
        $pblService = new PblService();
        $this->view->releasedPbls =  $pblService->getReleasedPBL();
    }
}
