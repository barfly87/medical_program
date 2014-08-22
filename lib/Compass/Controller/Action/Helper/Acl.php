<?php


/**
 * ACL integration
 *
 * Compass_Controller_Action_Helper_Acl provides ACL support to a controller.
 */
class Compass_Controller_Action_Helper_Acl extends Zend_Controller_Action_Helper_Abstract {

    protected $_auth;
    protected $_acl;
    protected $_controllerName;

    /**
     *
     * Optionally set view object and options.
     *
     * @param  Zend_View_Interface $view
     * @param  array $options
     * @return void
     */
    public function __construct(Zend_View_Interface $view = null, array $options = array()) {
        $this->_auth = Zend_Auth::getInstance();
		$this->_auth->setStorage(new Zend_Auth_Storage_Session('COMPASS_AUTH'));
        $this->_acl = $options['acl'];
    }

    /**
     * Hook into action controller initialization
     *
     * @return void
     */
    public function init() {

        // add resource for this controller
        $controller = $this->getRequest()->getControllerName();
        $this->_controllerName = $controller;
        if(!$this->_acl->has($controller)) {
            $this->_acl->add(new Zend_Acl_Resource($controller));
        }
    }

    /**
     * Hook into action controller preDispatch() workflow
     *
     * @return void
     */
    public function preDispatch() {
        /* sets a default role */
		$role = 'guest';

		/* other pre-dispatch stuff */
        $request = $this->getRequest();
        $controller = $request->getControllerName();
        $action = $request->getActionName();
        $module = $request->getModuleName();
        $response = $this->getResponse();
        
		/* logs the person in if they already have an identity */
		$sharedSession = new Zend_Session_Namespace("COMPASS_SHARED");
        if ($this->_auth->hasIdentity()) {
            $user = $this->_auth->getIdentity();
            if (is_object($user)) {
                $role = $user->role;
            }
        } else if (isset($sharedSession->user_id)) {
            $identity = $this->_getIdentityForUserName($sharedSession->user_id);
	        $role = $identity->role;
        } else if (isset($_COOKIE["SMP_PERMAUTH"])) {
        	$uidplushash = str_rot13(base64_decode($_COOKIE["SMP_PERMAUTH"]));
        	$uidparts = explode('|',$uidplushash);
        	$uid = $uidparts[0];
        	$uidhash = md5($uid.'|TOPSECRET1138');
        	if($uidhash != $uidparts[1]) {
        		setcookie ("SMP_PERMAUTH", "", time() - 360000);
        		throw new Exception('Invalid user credentials. Please enter a username and password to log in');
        	}
        	$identity = $this->_getIdentityForUserName($uid);
        	$role = $identity->role;
        } else {
            $httpAuthenticationAllowed = UserAcl::httpAuthenticationAllowed($controller, $action);
            if($httpAuthenticationAllowed) {
                if (! UserAcl::isSetHttpAuth()) {
                    UserAcl::promptHttpAuth();
                } else {
                    $formData = UserAcl::getHttpAuthUsernamePassword();
                    $authLdapAdapter = UserAcl::getLdapAuthAdapter($formData);
                    $auth = Zend_Auth::getInstance();
                    $result = $auth->authenticate($authLdapAdapter);
                    if (!$result->isValid()) {
                        UserAcl::promptHttpAuth();
                    } else {
                        $identity = $this->_getIdentityForUserName($formData['username']);
                        $role = $identity->role;
                     }
                }
            }
        }

        // if config has studentresource && user has relevant groups && role > guest
        // then, check whether they have agreed or disagreed to use the social resource sharing tools
        if($role != 'guest' && strpos($_SERVER["REQUEST_URI"], "setconsent")===false) {
        	if(StudentResourceService::showSocialTools()) {
	        	$consent = StudentResourceService::checkSocialToolConsent();
	        	if($consent===null) {// if they haven't consented, send to consent form
		       		$request->setModuleName("default");
		            $request->setControllerName("auth");
		            $request->setActionName("consent");
		            //$request->setDispatched(false); 
	        	}
	        }
        }
        //Check ACLs
        $resource = $controller;
        $privilege = $action;
        
        if (!$this->_acl->has($resource)) {
            $resource = null;
        }
        //Zend_Registry::get('logger')->debug(__METHOD__ . ": $role, $resource, $privilege");
        if (!$this->_acl->isAllowed($role, $resource, $privilege)) {
            if (!$this->_auth->hasIdentity()) {
                $noPermsAction = $this->_acl->getNoAuthAction();
            } else {
                $noPermsAction = $this->_acl->getNoAclAction();
            }
            
            $request->setModuleName($noPermsAction['module']);
            $request->setControllerName($noPermsAction['controller']);
            $request->setActionName($noPermsAction['action']);
            $request->setDispatched(false);        
        }
    }

    /**
     * Proxy to the underlying Zend_Acl's allow()
     *
     * We use the controller's name as the resource and the
     * action name(s) as the privilege(s)
     *
     * @param  Zend_Acl_Role_Interface|string|array     $roles
     * @param  string|array                             $actions
     * @uses   Zend_Acl::setRule()
     * @return Places_Controller_Action_Helper_Acl Provides a fluent interface
     */
    public function allow($roles = null, $actions = null) {
        $resource = $this->_controllerName;
        $this->_acl->allow($roles, $resource, $actions);
        return $this;
    }

    /**
     * Proxy to the underlying Zend_Acl's deny()
     *
     * We use the controller's name as the resource and the
     * action name(s) as the privilege(s)
     *
     * @param  Zend_Acl_Role_Interface|string|array     $roles
     * @param  string|array                             $actions
     * @uses   Zend_Acl::setRule()
     * @return Places_Controller_Action_Helper_Acl Provides a fluent interface
     */
    public function deny($roles = null, $actions = null) {
        $resource = $this->_controllerName;
        $this->_acl->deny($roles, $resource, $actions);
        return $this;
    }
    
    private function _getIdentityForUserName($username) {
        $identity = UserAcl::loadDomainInfo(trim($username));
        $this->_auth->getStorage()->write($identity);
        $this->_logIdentityInfo($identity);
        return $identity;
    }
    
    private function _logIdentityInfo($identity) {
        //Log Info
        $userInfo = ": user login successful - {$identity->user_id} ({$identity->role}-{$identity->domain}) Discipline Roles ({$identity->disciplineRole}) Mail: {$identity->email}";           
        Zend_Registry::get('logger')->info(__METHOD__ .$userInfo);
        Zend_Registry::get('logger')->debug(__METHOD__. var_export($identity, 1));
    }

}

