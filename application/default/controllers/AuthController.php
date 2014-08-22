<?php
 
class AuthController extends Zend_Controller_Action {

    protected $_redirectUrl = '/index';

   /**
    * Set up acl info
    */
    public function init() {
        $this->_helper->_acl->allow(null);
        
        $actions = array('changedomain');
        $this->_helper->_acl->allow('staff', $actions);
    }

   /**
    * Redirect user to appropriate page if he gets to this page by accident
    */
    public function indexAction() {
        $auth = Zend_Auth::getInstance();
        if ($auth->hasIdentity())          //already logged in, redirect to index page
            $this->_redirect('/index');
        else                               //otherwise redirect to login page
            $this->_redirect('/auth/login');
    }

    /**
     * Display login form as well as any error msg from previous incorrect login
     */
    public function loginAction() {
    	PageTitle::setTitle($this->view, $this->_request);
        $this->view->title = 'Log in';
        $flashMessenger = $this->_helper->FlashMessenger;
        $flashMessenger->setNamespace('actionErrors');
        $this->view->actionErrors = $flashMessenger->getMessages();
        $this->view->userTypes = $this->_getLoginUserTypes();
    }
    
    private function _getLoginUserTypes() {
        $userTypes = Compass::getConfig('login.user_types');
        $formUserTypes = null;
        if(!empty($userTypes) && is_array($userTypes)) {
            foreach($userTypes as $userType) {
                $explode = explode('##', $userType);
                $formUserTypes[$explode[0]] = $explode[1];
            }
        }
        return $formUserTypes;
    }

    /**
     * Log out the user and redirect to login page
     */
    public function logoutAction() {
		// tries to log out using Zend
        $auth = Zend_Auth::getInstance();
        $auth->clearIdentity();

		// nukes all app auth session vars
		if (isset($_SESSION['COMPASS_SHARED']))  { unset($_SESSION['COMPASS_SHARED']); }
		if (isset($_SESSION['COMPASS_AUTH']))    { unset($_SESSION['COMPASS_AUTH']); }
		if (isset($_SESSION['EVENTSDB_AUTH']))   { unset($_SESSION['EVENTSDB_AUTH']); }

		// redirects to login
		setcookie ("SMP_PERMAUTH", "", time() - 360000, '/');
        $this->_redirect('/auth/login');
    }
    /**
     * Consent form for Daniel Burn's social research study
     */
    public function consentAction() {
    	PageTitle::setTitle($this->view, $this->_request);
    	
    	if(isset($_SESSION['COMPASS_SHARED']['social_study_consent']))
    		unset($_SESSION['COMPASS_SHARED']['social_study_consent']);
        $this->view->title = 'Partipant Consent';
		$this->view->redirecturl='/index';
    }
    
    /**
     * Display a message if user doesn't have the privilege to perform an action
     */
    public function privilegesAction() {      
    }

    /**
     * Log in the user, store user id and group info in the session for later use
     */
    public function identifyAction() {
        // Set redirect url to where user wants to go initially
        $this->_redirectUrl = $this->_request->getParam('redirecturl');

        // If user clicks the login link, set the redirect to index page,
        // otherwise it will result in an infinite loop.
        if ($this->_redirectUrl == '/auth/login')
           $this->_redirectUrl = '/index';


        if ($this->_request->isPost()) {
            // collect the data from the user
            $formData = $this->_getFormData();
        
            if (empty($formData['username']) || empty($formData['password'])) {
                $this->_flashMessage('Please provide a username and password.');
            } else {
                //This logic only applies to Malaya site
                $userTypes = $this->_getLoginUserTypes();
                if(!empty($userTypes) && !empty($formData['usertype']) && in_array($formData['usertype'], array_flip($userTypes))) {
                    $formData['username'] = $formData['usertype'].$formData['username'];
                }
                // do the authentication
                $authAdapter = $this->_getAuthAdapter($formData);
                $auth = Zend_Auth::getInstance();
                $result = $auth->authenticate($authAdapter);

                $config = Zend_Registry::get('config');
                $log_path = $config->ldap->log_path;
                if ($log_path) {
                    $messages = $result->getMessages();

                    $logger = new Zend_Log();
                    $logger->addWriter(new Zend_Log_Writer_Stream($log_path));
                    $filter = new Zend_Log_Filter_Priority(Zend_Log::DEBUG);
                    $logger->addFilter($filter);

                    foreach ($messages as $i => $message) {
                       if ($i-- > 1) { // $messages[2] and up are log messages
                          $message = str_replace("\n", "\n  ", $message);
                          $logger->log("Ldap: $i: $message", Zend_Log::DEBUG);
                       }
                    }
                }

                if (!$result->isValid()) {
                    $this->_flashMessage('Login failed');
                } else {
                	$identity = UserAcl::loadDomainInfo();
                	Zend_Registry::get('logger')->info(__METHOD__ .
    					": user login successful - {$identity->user_id} ({$identity->role}-{$identity->domain}) Discipline Roles ({$identity->disciplineRole}) Mail: {$identity->email}");
    				Zend_Registry::get('logger')->debug(__METHOD__. var_export($identity, 1));
                    $auth->getStorage()->write($identity);
					
					// share this in the shared session namespace so that other COMPASS related apps can log in
					$sharedSession = new Zend_Session_Namespace("COMPASS_SHARED");
					$sharedSession->user_id = $identity->user_id;
					
                    $this->_redirect($this->_redirectUrl);
                    return;
                }
            }
        }
        
        $this->_redirect('/auth/login');
    }
    /**
     * Log in the user, store user id and group info in the session for later use
     */
    public function setconsentAction() {
		$uid=UserAcl::getUid();
    	$this->_redirectUrl = $this->_request->getParam('redirecturl');
    	$consentparam = $this->_request->getParam('consent');
    	$consent = -1;
    	if($consentparam=='true')
    		$consent=1;
    	$consentchecker = new StudentSocialStudyConsent();
    	$data = array('uid'=>$uid,'consent'=>$consent);
		$consentchecker->insert($data);
        $this->_redirect($this->_redirectUrl);
        return;
    	
    }
    public function changedomainAction() {
    	$this->_helper->viewRenderer->setNoRender();
    	$this->_helper->layout()->disableLayout();
    	
    	$domain = $this->_getParam("domain");
    	Zend_Registry::get('logger')->info(__METHOD__. "- " . $domain);
    	$domainFinder = new Domains();
    	$domains = $domainFinder->getAllNames('auto_id');
    	if (in_array($domain, $domains)) {
    		$identity = Zend_Auth::getInstance()->getIdentity();
    		$identity->domain = $domain;
    		unset($identity->stages);
    		unset($identity->blocks);
    		foreach ($identity->all_domains[$domain] as $k => $v) {
    			$identity->$k = $v;
    		}
    		echo "Current domain changed to $domain.";
    	} else {
    		echo "Error occurred while changing domain";
    	}
    } 

    /**
     * Set up error message so that it will be displayed on the login page
     *
     */
    protected function _flashMessage($message) {
        $flashMessenger = $this->_helper->FlashMessenger;
        $flashMessenger->setNamespace('actionErrors');
        $flashMessenger->addMessage($message);
    }

    /**
     * Retrieve the login form data from _POST
     *
     * @return array
     */
    protected function _getFormData() {
        $data = array();
        $filterChain = new Zend_Filter();
        $filterChain->addFilter(new Zend_Filter_StripTags());
        $filterChain->addFilter(new Zend_Filter_StringTrim());
        
        $data['username'] = $filterChain->filter($this->_request->getPost('username'));
        $data['password'] = $filterChain->filter($this->_request->getPost('password'));
        $data['usertype'] = $filterChain->filter($this->_request->getPost('usertype'));
        return $data;
    }

    /**
     * Set up the auth adapater for interaction with the database
     *
     * @return Zend_Auth_Adapter_DbTable
     */
    protected function _getAuthAdapter($fmData) {            
        $config = Zend_Registry::get('config');
        $options = $config->ldap->toArray();
        unset($options['log_path']);
        return new Zend_Auth_Adapter_Ldap($options, $fmData['username'], $fmData['password']);
    }
} 
