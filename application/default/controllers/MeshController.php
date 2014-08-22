<?php  
 
class MeshController extends Zend_Controller_Action {
    public function init() {
        $readActions = array('index', 'root', 'heading', 'crawler');
        $this->_helper->_acl->allow('student', $readActions);
        
        $ajaxContext = $this->_helper->getHelper('AjaxContext');
        $ajaxContext->addActionContext('root', 'html');
        $ajaxContext->addActionContext('heading', 'html');
        $ajaxContext->addActionContext('crawler', 'html');
        $ajaxContext->initContext();
    }

    /** Auto generate keywords base on the input text, and only display those above the cut off value in config file */
    public function crawlerAction() {	
		$this->view->result = MeshService::autoGenerateKeywords($_POST['lotext']);
    }
    
    /** Display a popup window showing the MeSH tree, so user can pick MeSH terms */
    public function indexAction() {
    	PageTitle::setTitle($this->view, $this->_request);
    	$this->_helper->layout()->setLayout('basic'); 

        $descriptorFinder = new Descriptors();
        $this->view->mesh_count = $descriptorFinder->getNumberOfDescriptors();
    }
    
    /** Expand the root node of MeSH tree based on the passed-in categeory parameter */
    public function rootAction() {
    	$find = $this->_getParam('find');
    	
    	// validate input
		if (!preg_match('/^[A-Z]$/', $find, $matches)) {
			die("Invalid form input.");
		}

		$this->view->letter = $find;
		
		$descriptorFinder = new Descriptors();
        $this->view->results = $descriptorFinder->getChildrenOfRootCategory($find);
    }
    
    /** Similar to rootAction, but expand other part of the MeSH tree based on tree number and uid */
    public function headingAction() {
    	$find = $this->_getParam('find');
    	$uid = $this->_getParam('uid');
    	
    	// validate input, must start with capital letter
    	if (!preg_match("/^[A-Z]/", $find, $matches)) {
    		die("Invalid form input.");
    	}
    	
    	// validate input, must start with 'D' and followed by 6 digits
    	if (!preg_match("/^D[0-9]{6}$/", $uid, $matches2)) {
    		die("Invalid id.");
    	}

		// Get all children of current node
		$descriptorFinder = new Descriptors();
        $children = $descriptorFinder->getDescriptorsByParent($find, 'treenumbers');

        $this->view->results = $children;
        $this->view->find = $find;
        $this->view->find_name = $descriptorFinder->getHeadingTextFromUid($uid);;
        $this->view->showLinks = $descriptorFinder->getLinkStatus($children, $find);
    }
} 