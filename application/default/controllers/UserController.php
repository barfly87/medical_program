<?php
class UserController extends Zend_Controller_Action {

	/**
	 * Set up ACL info
	 */
	public function init() {
		$readActions = array('info');
		$writeActions = array('profile');
		$approveActions = array();
		$this->_helper->_acl->allow('student', $readActions);
		$this->_helper->_acl->allow('staff', $writeActions);
		$this->_helper->_acl->allow('blockchair', $approveActions);
	}
    
	public function profileAction() {
	    $uid             = trim($this->_getParam('uid',	''));
	    $view            = trim($this->_getParam('view',''));
	    $encryptedStr    = trim($this->_getParam('q',  	''));

	    $encryptStr      = md5(UserService::$encryptCode.$uid.UserService::$encryptCode);
    	if(empty($uid) || empty($encryptedStr) || $encryptedStr != $encryptStr) {
    		$this->throwError();
    		exit;
    	}
    	try {
        	$context  = UserService::$context['basic'];
        	if(!empty($view) && isset(UserService::$context[$view])) {
    	        $context  = UserService::$context[$view];
    	    }
            $userService = new UserService();
            $this->view->user = $userService->getUserDetails($uid, $context);
            $this->_helper->layout()->setLayout('ajax');  
    	} catch(Exception $ex) {
    	    $this->throwError();
    	    exit;
    	}
	}
	
    private function throwError() {
        throw new Zend_Controller_Action_Exception("Page not found.", 404);
    }
    
    public function infoAction() {
        $uid             = trim($this->_getParam('uid',     ''));
        $view            = trim($this->_getParam('view',    ''));
        $encryptedStr    = trim($this->_getParam('q',       ''));
        $color           = trim($this->_getParam('color',   'DBDED7'));
        $uids = array();
        $encryptedStrs = array();
        
        if(strstr($uid,UserService::$separator) !== false && strstr($encryptedStr,UserService::$separator) !== false){ 
            $uids = explode(UserService::$separator, $uid);
            $encryptedStrs = explode(UserService::$separator, $encryptedStr);
        } else {
            $uids[] = $uid;
            $encryptedStrs[] = $encryptedStr;
        }
        $uidCount = count($uids);
        for($x=0; $x<$uidCount; $x++) {
            $encryptStr      = md5(UserService::$encryptCode.$uids[$x].UserService::$encryptCode);
            if(empty($uids[$x]) || empty($encryptedStrs[$x]) || $encryptedStrs[$x] != $encryptStr) {
                $this->throwError();
                exit;
            }
            try {
                $border = '';
                if($uidCount > 1 && ($uidCount-1) != $x) {
                    $border = 'border-bottom:1px solid #'.$color.';';
                }
                print '<div style="padding-top:5px;padding-bottom:5px;'.$border.'">'.Compass::userInfoHtmlOnly($uids[$x]).'</div>';
                
            } catch(Exception $ex) {
                $this->throwError();
            }
        }
        exit;
    }
    
    
	
}
?>