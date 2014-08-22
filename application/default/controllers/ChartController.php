<?php
class ChartController extends Zend_Controller_Action {

	/**
	 * Set up ACL info
	 */
    public function init() {
        $writeActions = array('pielo', 'barlodisc');
        $this->_helper->_acl->allow('admin', $writeActions);
    }

    public function pieloAction() {
    	PageTitle::setTitle($this->view, $this->_request);
    	$this->view->chart = ChartService::pieLoChart();
    }
    
    public function barlodiscAction() {
    	PageTitle::setTitle($this->view, $this->_request);
		$this->view->chart = ChartService::barLoDiscChart();
    }    
}
