<?php
class UserService {
    public static $encryptCode = 'Xy3uk2POdkLo1RetA';
    public static $separator = '||';
    private static $ds = null; 
    public static $profile = 'http://sydney.edu.au/medicine/people/academics/profiles/%s.php';
    public static $image = 'http://www.mail.med.usyd.edu.au/accounttool/web/photos/large/%s.jpg';
    public static $accountTool = 'http://www.mail.med.usyd.edu.au/acct/mat.php?vic=%s';
    private static $mobileBrowser = null;
    public static $context = array(
        'basic' => array(
                        'chsedupersonsalutation', 
                        'cn',
                        'mail',
                        'telephonenumber',
                        'o',
                        'title',
                        'homedirectory'
        )
    );
    
    private static $mapping = array(
        'chsedupersonsalutation' => 'salutation',
        'cn' => 'name',
        'mail' => 'email',
        'telephonenumber' => 'phone',
        'o' => 'faculty',
        'homedirectory' => 'home'
    );

    public  function __construct() {
        self::$ds = Zend_Registry::get('ds');
    }
    
    public function getUserDetails($uid, $context = null) {
        try {
            $userDetails = array();
            $ldapUserDetails = LdapCache::getUserDetails($uid);
            if(!empty($ldapUserDetails)) {
                if(is_null($context)) {
                    $userDetails = $ldapUserDetails;
                } else {
                    foreach($context as $ldapfield) {
                        if(isset($ldapUserDetails[$ldapfield][0])) {
                            $key = (isset(self::$mapping[$ldapfield])) ? self::$mapping[$ldapfield] : $ldapfield;
                            $userDetails[$key] = trim($ldapUserDetails[$ldapfield][0]);
                        }
                    }
                }
                if(!empty($userDetails)) {
                    $userDetails = $this->addOtherInfo($userDetails,$uid);    
                }
            }
            return $userDetails;
        } catch(Exception $ex) {
            return array();
        }
    }

    private function addOtherInfo(&$userDetails,$uid) {
        $userDetails['profile'] = sprintf(self::$profile,$uid);
        $image = sprintf(self::$image,$uid);
        if (@getimagesize($image) !== false) {
            $userDetails['image'] = $image;
        }
        if(UserAcl::isAdmin()) {
            $userDetails['account_tool'] = sprintf(self::$accountTool,$uid);
        }
        return $userDetails;
    }
    
    public function getUserDetailsAsJson($uid, $context) {
        try {
            return Zend_Json::encode($this->getUserDetails($uid,$context));
        } catch(Exception $ex) {
            return false;
        }
    }
    
	public function checkIfUserExists($uid) {
        try {
            $detail = self::$ds->getUser($uid);
            if(!empty($detail)) {
                return true;
            }
            return false;
        } catch(Exception $ex) {
            return false;
        }
	}
	
	public static function isMobileBrowser() {
	    if(is_null(self::$mobileBrowser)) {
            $mobile_agents = array(
                'w3c ','acs-','alav','alca','amoi','audi','avan','benq','bird','blac',
                'blaz','brew','cell','cldc','cmd-','dang','doco','eric','hipt','inno',
                'ipaq','java','jigs','kddi','keji','leno','lg-c','lg-d','lg-g','lge-',
                'maui','maxo','midp','mits','mmef','mobi','mot-','moto','mwbp','nec-',
                'newt','noki','oper','palm','pana','pant','phil','play','port','prox',
                'qwap','sage','sams','sany','sch-','sec-','send','seri','sgh-','shar',
                'sie-','siem','smal','smar','sony','sph-','symb','t-mo','teli','tim-',
                'tosh','tsm-','upg1','upsi','vk-v','voda','wap-','wapa','wapi','wapp',
                'wapr','webc','winw','winw','xda','xda-');
            $mobile_ua = strtolower(substr($_SERVER['HTTP_USER_AGENT'],0,4));
            
            if( preg_match('/(up.browser|up.link|mmp|symbian|smartphone|midp|wap|phone)/i', strtolower($_SERVER['HTTP_USER_AGENT'])) ||
                (strpos(strtolower(@$_SERVER['HTTP_ACCEPT']),'application/vnd.wap.xhtml+xml')>0) or ((isset($_SERVER['HTTP_X_WAP_PROFILE']) or isset($_SERVER['HTTP_PROFILE']))) ||
                (strpos(strtolower(@$_SERVER['ALL_HTTP']),'operamini')>0) ||
                (strpos(strtolower($_SERVER['HTTP_USER_AGENT']),' ppc;')>0) ||
                (strpos(strtolower($_SERVER['HTTP_USER_AGENT']),'iemobile')>0) ||
                (in_array($mobile_ua,$mobile_agents)) ||
                (strpos(strtolower($_SERVER['HTTP_USER_AGENT']),'windows ce')>0)
                ) {
                self::$mobileBrowser = true;
            } else {
                self::$mobileBrowser = false;
            }
            
            if (strpos(strtolower($_SERVER['HTTP_USER_AGENT']),'windows')>0) {
                self::$mobileBrowser = false;
            }             
	    }
        return self::$mobileBrowser;
	}
	
    public static function isBrowserIE($versionOrLess = null) {
        if (isset($_SERVER['HTTP_USER_AGENT'])) {
            if(is_null($versionOrLess) && (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== false)) {
                return true;
            } else if(preg_match('/MSIE (\d{1,2}).0/',$_SERVER['HTTP_USER_AGENT'],$matches)) {
                if(isset($matches[1]) && (int)$matches[1] > 0) {
                    $usersIEVersion = (int)$matches[1];
                    if( $usersIEVersion <= (int)$versionOrLess ) {
                        return true;
                    }
                }
            }
        }
        return false;
    }
    
    public static function getUidsFullName($uidString) {
        $uidFullNames = array();
        if(strpos($uidString,',') !== false) {
            $uids = explode(',',$uidString);
            foreach($uids as $uid) {
                $uidFullName = self::getFullName($uid);
                if(!empty($uidFullName)) {
                    $uidFullNames[] = trim($uidFullName);
                }
            }
            if(!empty($uidFullNames)) {
                return $uidFullNames;    
            }
        } 
        return self::getFullName($uidString);
    }
    
    public static function getUidFullNameAndOrg($uid) {
    	$user = LdapCache::getUserDetails(trim($uid));
    	if (!empty($user)) {
    		$salutation = '';
    		if (isset($user['chsedupersonsalutation'][0]) && strlen(trim($user['chsedupersonsalutation'][0])) > 0)  {
    			$salutation = trim($user['chsedupersonsalutation'][0]).' ';
    		}
    		$result = $salutation.$user['cn'][0];
	    	if ((isset($user['o'][0]) && strlen(trim($user['o'][0]))) > 0) {
	    		$result = $result . ', '. trim($user['o'][0]);
	    	}
	    	return $result;
    	}
    	return $uid;
    }
    
    public static function getUidFullName($uid) {
        $uidFullName = self::getFullName($uid);
        if(! empty($uidFullName)) {
            return $uidFullName;
        }
        return $uid;
    }
    
    public static function getUidFullNameOnly($uid) {
        $user = LdapCache::getUserDetails(trim($uid));
        if (!empty($user)) {
        	return $user['cn'][0];
        }
        return $uid;
    }
    
    private static function getFullName($uid) {
        $userFullName = '';
        if(strlen(trim($uid)) > 0) {
            $user = LdapCache::getUserDetails(trim($uid));
            if(! empty($user)) {
                $salutation = '';
                if(isset($user['chsedupersonsalutation'][0]) && strlen(trim($user['chsedupersonsalutation'][0])) > 0)  {
                    $salutation = trim($user['chsedupersonsalutation'][0]).' ';
                }
                $userFullName = $salutation.$user['cn'][0];  
            }
        } 
        return $userFullName;               
    }
    
	
}
?>