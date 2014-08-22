<?php 
 
class ErrorController extends Zend_Controller_Action {

   /**
    * Set up acl info
    */
    public function init() {
        $this->_helper->_acl->allow(null);
    }

   /**
    * Set up error msgs to be displayed
    */
    public function errorAction() {
    	PageTitle::setTitle($this->view, $this->_request);
        $errors = $this->_getParam('error_handler'); 
 
        switch ($errors->type) { 
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER: 
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ACTION: 
                // 404 error -- controller or action not found 
                $this->getResponse()->setHttpResponseCode(404); 
                $this->view->message = 'Page not found'; 
                break; 
            default: 
                // application error 
                $this->getResponse()->setHttpResponseCode(500); 
                $this->view->message = $errors->exception->getMessage(); 
                break; 
        } 
 
        $this->view->env       = $this->getInvokeArg('env'); 
        $this->view->exception = $errors->exception; 
        $this->view->request   = $errors->request; 
    } 
}