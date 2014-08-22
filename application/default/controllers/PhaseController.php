<?php  

class PhaseController extends Zend_Controller_Action {

	public function init() {
        $student = array('index');
        $this->_helper->_acl->allow('student',$student);       
    }
    
    public function indexAction() {
    	$req = $this->getRequest();
    	$phaseName = trim($req->getParam('name','II'));
    	
    	$sbs = new StageBlockSeqs();
    	$this->view->allModules =  $sbs->getModulesForPhase($phaseName);
    }
    
}