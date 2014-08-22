<?php
require_once dirname(__FILE__) . '/../TestConfiguration.php';

class controllers_IndexControllerTest extends PHPUnit_Framework_TestCase {
    /**
     * Instance of the front controller
     *
     * @var Zend_Controller_Front
     */
    public $front;
    
    public function setUp() {
        $this->front = Zend_Controller_Front::getInstance();
        
        // clear out any settings from prior runs
        $this->front->resetInstance();
        Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer')->setNeverRender(false);
        
        // setup for this run
        // path to modules and hence controllers
        $this->front->setControllerDirectory(TestConfiguration::$compassRoot.'/application/controllers'); 

        $this->front->returnResponse(true); // don't echo the response
        $this->front->setParam('noErrorHandler', false);      
    }

    public function testIndexAction() { 
        // set up request
        $request = new Zend_Controller_Request_Http('http://localhost/index/index');
        
        // do not render view - we are testing the controller function
        Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer')->setNeverRender();

        // dispatch the index page
        $response = $this->front->dispatch($request);

        // test that there are no exceptions
        $this->assertFalse($response->isException());

        // test that the correct data has been assigned to the view
        $view = Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer')->view;

        $this->assertSame(1, $view->objectives->count());
        $this->assertContains('TBC access for immigrants', $view->objectives->current()->lo);
        $this->assertSame('Welcome to Compass!', $view->title);
    }    
}
