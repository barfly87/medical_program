<?php  
 
class HelpController extends Zend_Controller_Action {

    public function init() {
        $readActions = array('search');
        $this->_helper->_acl->allow('student', $readActions);
    }
    
    public function searchAction() {
    	PageTitle::setTitle($this->view, $this->_request);
        $this->_helper->layout()->setLayout('popup');   
        $helpService = new HelpService();
        $mappings = $helpService->getMappingForLuceneFieldsToColumnNames();
        $this->view->mappings = $mappings;     
    }
} 