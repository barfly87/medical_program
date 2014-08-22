<?php  
 
class QueryController extends Zend_Controller_Action {

    public function init() {
        $readActions = array('index');
        $this->_helper->_acl->allow('student', $readActions);
    }

    public function indexAction() { 
    	PageTitle::setTitle($this->view, $this->_request);
        $this->view->title = 'Frequently asked queries';
    } 
} 