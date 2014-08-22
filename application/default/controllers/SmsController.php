1<?php
class SmsController extends Zend_Controller_Action {

	/**
	 * Set up ACL info
	 */
	public function init() {
		$actions = array('index', 'sendsms', 'sendemail');		
		$this->_helper->_acl->allow('staff', $actions);
	}
	
	public function indexAction() {	
		$smsaccounts = SmsService::$smsaccounts;
		$ds = Zend_Registry::get('ds');
		$identity = Zend_Auth::getInstance()->getIdentity();
		$u = $ds->getUser($identity->user_id);
		
		$display_accounts = array();
		for ($i = 0; $i < count($smsaccounts); $i++) {
			$checkgroup = $smsaccounts[$i]["accessgroup"];
			if (!(is_array($checkgroup))) {
				$checkgroup = array($checkgroup);
			}
			$accessOK = true;
			foreach ($checkgroup as $grp) {
				if (in_array($grp, $u['groups']) === false) {
					$accessOK = false;
					break;
				}
			}
			if ($accessOK) {
				$display_accounts[$i] = $smsaccounts[$i];
			}
		}

		if (count($display_accounts) == 0) {
			throw new Exception("You don't have permission to visit this page.");
		}
		$this->view->smsaccounts = $display_accounts;
		
		$config = Zend_Registry::get('config');
		$sms_groups = $config->sms->groups->toArray();
		
		$expanded_sms_groups = array();
		
		foreach ($sms_groups as $group) {
			if (!strncmp(trim($group), "+ldap:",6)) {
				$grps = $ds->getGroups(substr($group, 6));
				natsort($grps);
				$expanded_sms_groups = array_merge($expanded_sms_groups, $grps);
			} else {
				array_push($expanded_sms_groups, $group);
			}
		}
		$this->view->expanded_sms_groups = $expanded_sms_groups;
		
		$request = $this->getRequest();
		if ($request->isPost()) {
			$smsaccount = $this->_getParam("smsaccount");
			$errors = array(); 
			//check form fields
			$groups = $this->_getParam("group");
			if (count($groups) == 0) {
				$errors["group"] = 'Missing group information';
				$groups = array();
			}
			$message_original = trim($this->_getParam("message"));
            $message = str_replace(array("\n"), ' ', $message_original);
            //$message = trim($this->_getParam("message"));
			if (empty($message)) {
				$errors["message"] = 'Missing message text';
			}
			if (count($errors) == 0) {
				$this->_helper->redirector->gotoSimple('sendsms', 'sms', null, array('smsaccount'=>$smsaccount, "group"=>$groups, "message"=>$message));
			}
			$this->view->errors = $errors;
			$this->view->groups = $groups;
			$this->view->message = $message;
		} else {
			$this->view->groups = array();
			$this->view->message = '';
		}
	}
	
	public function sendsmsAction() {
		set_time_limit(0);
		$smsaccount = $this->_getParam("smsaccount");
		$groups = $this->_getParam("group");
		$message_original = trim($this->_getParam("message"));
        $message = str_replace(array("\n"), ' ', $message_original);
        //$message = trim($this->_getParam("message"));
		
		$config = Zend_Registry::get('config');
		$proxy_account_arr = explode(',', $config->ldap->server1->username);
		$proxy_account = substr(trim($proxy_account_arr[0]), 3);
		$proxy_password = $config->ldap->server1->password;
		$proxy_host = $config->sms->proxy_host;
		$proxy_port = $config->sms->proxy_port;

		$ds = Zend_Registry::get('ds');
		$sender_details = $ds->getUser(Zend_Auth::getInstance()->getIdentity()->user_id, array('cn','mail'));
		
		$users = array();
		if (!is_array($groups)) {
			$groups = array($groups);
		}
		$groups = array_unique($groups);
		foreach ($groups as $thegroup) {
			$details = $ds->getGroup($thegroup);
			$users = array_merge($users, $details["members"]);
		}
		$users = array_unique($users);
		
		$fromemail = Compass::getConfig("studentemaildomain.from");
		$toemail = Compass::getConfig("studentemaildomain.to");
		
		$studentFinder = new StudentInfo();
		$results = array();
		foreach ($users as $user) {
			$user_details = $ds->getUser($user, array('cn','mail'));
			
			//remove test user from email and sms
			if (substr($user, 0, 4) == 'med_') {
				continue;
			}
			
			$email = $user_details['mail'][0];
			//only replace student email address
			if (Utilities::isStudent($user)) {
				$email = str_replace($fromemail, $toemail, $user_details['mail'][0]);
			}
			
			$msg = "Emailed";
			$mobile_number = $studentFinder->getMobileNumber($user);
			if (!empty($mobile_number)) {
				$resp = SmsService::sendsms($mobile_number, $message, SmsService::$smsaccounts[$smsaccount], $proxy_host, $proxy_port, $proxy_account, $proxy_password);
				//IF SUCCESS, UPDATE RESULT MESSAGE
				if (substr($resp, 0, 2) == 'OK') {
					$msg = "SMSed ($resp)";
				} else {
					SmsService::sendEmail(array("address"=>$email,"name"=>$user_details['cn'][0]), "SMS message", $message, array("address"=>$sender_details['mail'][0], "name"=>$sender_details['cn'][0]));
					$msg = "SMS Failed($resp), Emailed";
				}
			} else {
				//SEND EMAIL;
				SmsService::sendEmail(array("address"=>$email,"name"=>$user_details['cn'][0]), "SMS message", $message, array("address"=>$sender_details['mail'][0], "name"=>$sender_details['cn'][0]));
			}
			$results[$user] = array($user_details['cn'][0], $msg);
		}


		$alert_users = $config->sms->alertuser->toArray();
		foreach ($alert_users as $user) {
			SmsService::sendAlertEmail(array("address"=>$user,"name"=>''), "SMS message sent", $message, 
				array("address"=>$sender_details['mail'][0], "name"=>$sender_details['cn'][0]), SmsService::$smsaccounts[$smsaccount], implode(' ', $groups));
		}
		$this->view->results = $results;
	}
}
	