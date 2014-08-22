<?php
class Zend_View_Helper_LoggedInUser {
    protected $_view;
    
    function setView($view) {
        $this->_view = $view;
    }
    
    function loggedInUser() {
        $auth = Zend_Auth::getInstance();
        if ($auth->hasIdentity()) {
            $logoutUrl = $this->_view->url(array('module'=>'default','controller'=>'auth', 'action'=>'logout'), 'default', true);
            $user = $auth->getIdentity();
            $username = $this->_view->escape($user->user_id);
            if ($user->role === 'student') {
            	$string = "Logged in as {$username} ({$user->role})";
        	} else {
        		if (count($user->all_domains) == 1) {
            		$string = "Logged in as {$username} ({$user->role} - ". substr($user->domain, 0, 3). ")";
        		} else {
        			$string = "Logged in as {$username} <select id=\"domaindropdown\" onchange=\"changedomain();\" style=\"font-size:10px\">";
        			foreach ($user->all_domains as $domain_name => $value) {
        				$selected = ($domain_name === $user->domain) ? 'selected="selected"' : '';
        				$string .= "<option value='{$domain_name}' {$selected}>{$value['role']} - ". substr($domain_name, 0, 3) . "</option>";
        			}
        			$string .= '</select>';
        		}
        	}
            $string .= ' | <a accesskey ="o" href="' . $logoutUrl . '">Log <span class="underlineText boldText">o</span>ut</a>';
        } else {
            $loginUrl = $this->_view->url(array('module'=>'default','controller'=>'auth', 'action'=>'identify'), 'default', true);
            $string = '<a href="'. $loginUrl . '">Log in</a>';
        }
        return $string;
    }
}