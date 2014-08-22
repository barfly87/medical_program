<?php

class Utilities {
    
	public static function help_text() {
		$arr = array(
		    'main discipline' => 'The main discipline to which the content of your learning objective applies.',
		    'additional discipline' => 'Additional disciplines which may apply to the content of your learning objective.',
		    'theme' => 'The key '. Zend_Registry::get('Zend_Translate')->_('Theme') . ' Theme to which your learning objective applies.', 
		    'clinical skills' => 'The type of Clinical Skill to which this objective applies. These are mainly Patient-Doctor skills, but also includes Basic and Clinical Science management skills.'
		);
		return $arr;
	}
	
	private static $copyrightUni = null;
	private static $copyrightFooterText = null;
	
	public static function sort_timestamp($arr1, $arr2) {
		return $arr1['timestamp'] - $arr2['timestamp'];
	}
	
    public static function getTimestamp($str) {
    	if (empty($str))
    		return -1;
    	$timestamp = strtotime($str);
    	return ($timestamp === false) ? -1 : $timestamp;
    }
    
    public static function replaceEmptyWithAny($arr) {
    	foreach ($arr as $k => &$v) {
    		if (empty($v))
    			$v = 'Any';
    	}
    	return $arr;
    }
    
    public static function removeEmpty($arr) {
    	foreach ($arr as $k => $v) {
    		if (empty($v))
    			unset($arr[$k]);
    	}
    	return $arr;
    }
    
    public static function isBlockchair($uid, $domain_id) {
    	$blockchairFinder = new Blockchairs();
    	$rows = $blockchairFinder->fetchAll(array('uid = ?' => $uid, 'domain_id = ?' => $domain_id));
    	$blocks = array();
    	foreach ($rows as $row) {
    		$blocks[] = $row['block_id'];
    	}
    	return (count($blocks) > 0) ? $blocks : NULL; 
    }
    
    public static function isStageCoordinator($uid, $domain_id) {
    	$stagecoordinatorFinder = new StageCoordinators();
    	$rows = $stagecoordinatorFinder->fetchAll(array('uid = ?' => $uid, 'domain_id = ?' => $domain_id));
    	$stages = array();
    	foreach ($rows as $row) {
    		$stages[] = $row['stage_id'];
    	}
    	return (count($stages) > 0) ? $stages : NULL; 
    }
    
    public static function isDomainAdmin($uid, $domain_id) {
    	$domainadminFinder = new DomainAdmins();
    	$rows = $domainadminFinder->fetchAll(array('uid = ?' => $uid, 'domain_id = ?' => $domain_id));
    	$domains = array();
    	foreach ($rows as $row) {
    		$domains[] = $row['domain_id'];
    	}
    	return (count($domains) > 0) ? $domains : NULL; 
    }
    
    /**
     * Get domain name for user $uid
     * @param $uid
     * @return domain name or NULL if user is not a domain administrator
     */
    public static function isDomainAdministrator($uid) {
    	$daFinder = new DomainAdmins();
    	return $daFinder->getDomainForUser($uid);
    }
    
    public static function sendEmail($userNameAndEmail, $tpl) {
		// extract the subject from the first line
		list($subject, $body) = preg_split('/\r|\n/', $tpl, 2);
		
		// now set up and send the e-mail
		$mail = new Zend_Mail();
		$mail->addTo($userNameAndEmail['mail'], $userNameAndEmail['cn']);
		
		// get the admin 'from' details from the config
		$mail->setFrom(Zend_Registry::get('config')->email->from->email, Zend_Registry::get('config')->email->from->name);
		
		// set the subject and body and send the mail
		$mail->setSubject(trim($subject));
		$mail->setBodyText(trim($body));
		$mail->send();
    }
    
    public static function getUserNameAndEmail($uid) {
    	$details = array();
        $ds = Zend_Registry::get('ds');
        $entry = $ds->getUser($uid, array('chsedupersonsalutation', 'cn', 'mail'));
        $details['cn'] = $entry['cn'][0];
        if (isset($entry['chsedupersonsalutation'][0]))
        	$details['salutation'] = $entry['chsedupersonsalutation'][0];
        else
        	$details['salutation'] = '';
        $details['mail'] = $entry['mail'][0];
        return $details;
    }
    
    public static function getLastModifiedBy($data) {
    	return ($data->modified_by === NULL) ? $data->created_by : $data->modified_by;
    }
    
    public static function isMyTa($taId_Or_taRow) {
    	$result = UserAcl::checkTaPermission($taId_Or_taRow, UserAcl::$EDIT);
    	if ($result !== true) {
    		$return['err']['staff'] = $result;
    		return $return;
    	} else {
    		return true;
    	}
    }
    
    private static function processTa($taId_Or_taRow) {
        $taRow = array();
        $return = array();
        if(is_int($taId_Or_taRow)) {
            $taId = $taId_Or_taRow;
            if((int)$taId <= 0) {
                $return['err']['invalid'] = "Invalid teaching activity id {$taId}.";
                return $return;
            }
            $ta = new TeachingActivities();
            $taRow = $ta->fetchRow('auto_id = '.(int)$taId);
            if($taRow === false) {
                $return['err']['invalid'] = "Could not find teaching activity {$taId}.";
                return $return;
            }
            return $taRow;
        } else if(is_object($taId_Or_taRow)) {
            $taRow = $taId_Or_taRow;
            if($taRow === false || ! isset($taRow->auto_id)) {
                $return['err']['invalid'] = "Could not validate teaching activity object.";
                return $return;
            }
            return $taRow;
        } else {
            $return['err']['invalid'] = "Invalid request parameters send.";
            return $return;
        }
    }
    
    public static function isMyLo($lo_id) {
        $identity = Zend_Auth::getInstance()->getIdentity();
        if ($identity->role == 'admin') {
            return true;
        }
        $link_lo_tas = new LinkageLoTas();
        $rows = $link_lo_tas->fetchAll("lo_id = $lo_id and status = (select auto_id from lk_status where name = '".Status::$RELEASED."')");
        if(! is_null($rows)) {
            $rows = $rows->toArray();
            $ta = new TeachingActivities();
            foreach($rows as $row) {
                if(isset($row['ta_id'])) {
                    $taRow = $ta->fetchRow('auto_id = '. (int)$row['ta_id']);
                    if($taRow !== false) {
                        if (self::isMyTa($taRow) === true) {
                        	return true;
                        }
                    }
                }                
            }
        }
        return false;
    }
    
	public static function createDate($timestamp){
		$epoch = strtotime($timestamp) ;
		return (strlen(trim($epoch)) < 1 ) ? 'N/A' : date('Y-m-d', $epoch);
	}

    public static function getCopyrightUni() {
        if(is_null(self::$copyrightUni)) {
            $config = Zend_Registry::get('config');
            $university = $config->copyright->university;
            self::$copyrightUni = $university;
        }
        return self::$copyrightUni;
    }	
	
    public static function getCopyrightFooterText() {
        if(is_null(self::$copyrightFooterText)) {
            $config = Zend_Registry::get('config');
            $footerText = $config->copyright->footer->text;
	    $trarray = array("%Y" => date("Y"));
	    $footerText = strtr($footerText, $trarray);
            self::$copyrightFooterText = $footerText;
        }
        return self::$copyrightFooterText;
    }
	
    public static function isStudent($uid) {
    	$student_group = compass::getConfig("allstudentgroup");
    	$ds = Zend_Registry::get('ds');
    	$user_detail = $ds->getUser($uid);
    	if ($user_detail != false) {
    		$groups = $user_detail['groups'];
    		return in_array($student_group, $groups);
    	} else {
    		return false;
    	}
    }
}
