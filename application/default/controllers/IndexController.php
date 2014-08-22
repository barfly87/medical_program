<?php  
 
class IndexController extends Zend_Controller_Action {

    public function init() {
        $readActions = array('index','monitor');
        $this->_helper->_acl->allow('guest', $readActions);
    }

    public function indexAction() {
    	PageTitle::setTitle($this->view, $this->_request);
        $this->view->title = 'Welcome to Compass!';
    } 
    
    public function monitorAction() {
        $quietParam = $this->_request->getParam('quiet', '');
        $quiet = ($quietParam == '' || $quietParam == 'true' || $quietParam == '1') ? true : false;
        $allOkParam = $this->_request->getParam('allOk','');
        $allOk = ($allOkParam == '' || $allOkParam == 'true' || $allOkParam == '1') ? true : false;
        $healthCheckService = new HealthCheckService();
        $healthCheckService->monitor($quiet, $allOk);
    }
} 