<?php

class Compass_Acl extends Zend_Acl {
    private $_noAuth;   //url to redirect user if user hasn't login
    private $_noAcl;    //url to redirect user if user doesn't have access to the resource
    
    public function __construct() {
        $config = Zend_Registry::get('config');
        $roles = $config->acl->roles;
        $this->_addRoles($roles);
        $this->_loadRedirectionActions();
    }
    
    public function setNoAuthAction($noAuth) {
        $this->_noAuth = $noAuth;
    }
    
    public function setNoAclAction($noAcl) {
        $this->_noAcl = $noAcl;
    }
    public function getNoAuthAction() {
        return $this->_noAuth;
    }
    
    public function getNoAclAction() {
        return $this->_noAcl;
    }
    
    protected function _addRoles($roles) {
        foreach ($roles as $name => $parents) {
            if (!$this->hasRole($name)) {
                if (empty($parents)) {
                    $parents = null;
                } else {
                    $parents = explode(',', $parents);
                }
                $this->addRole(new Zend_Acl_Role($name), $parents);
            }
        }
    }

    /**
     * Set up the url to redirect user if he doesn't have login or access
     */
    protected function _loadRedirectionActions() {
        $this->_noAuth = array('module' => 'default', 'controller' => 'auth', 'action' => 'login');
        $this->_noAcl = array('module' => 'default', 'controller' => 'auth', 'action' => 'privileges');
    }
}
