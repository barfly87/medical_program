<?php
class LdapCache {
    
    private static $uidFile = null;
    private static $uid = null;
    public static $currentUids = null;
    
    public static function getUserDetails($uid) {
        try {
            if(! isset(self::$currentUids[$uid])) { 
                self::$uidFile = self::ldapPath().'/'.$uid;
                self::$uid = $uid;
                $userDetails = array();
                if(file_exists(self::$uidFile)) {
                    $week = 60 * 60 * 24 * 7;
                    $weekago = time() - $week;
                    if(filemtime(self::$uidFile) < $weekago) {
                        $userDetails = self::getUserDetailsFromLdap();
                    } else {
                        $userDetails = file_get_contents(self::$uidFile);
                        $userDetails = unserialize($userDetails);
                    }
                } else {
                    $userDetails = self::getUserDetailsFromLdap();
                }
                self::$currentUids[$uid] = $userDetails;
            }
            return self::$currentUids[$uid];
        } catch(Exception $ex) {
            return array();            
        }
    }
    
    private static function getUserDetailsFromLdap() {
        try {
            $ds = Zend_Registry::get('ds');
            $details = $ds->getUser(self::$uid);
            $serializedDetails = serialize($details);
            $fh = fopen(self::$uidFile,'w+');
            fwrite($fh,$serializedDetails,strlen($serializedDetails)); 
            fclose($fh);
            return $details;
        } catch(Exception $ex) {
            return array();            
        }
    }
    
    public static function clearCache() {
		// this part of the code will create file ldapusers.txt that is used by jquery auto complete
    	set_time_limit(0);
        $config = Zend_Registry::get('config');
        $cacheFolder = dirname($config->index_folder);
        if (!file_exists($cacheFolder .'/cache')) {
        	mkdir($cacheFolder .'/cache');
        }
        $fp = fopen($cacheFolder .'/cache/ldapusers.txt.tmp', 'w');
        $ds = Zend_Registry::get('ds');

        $listofusers = array();
        $medStaffGroup = $config->medicinestaffgroup;
        $medStaff = $ds->getGroup($medStaffGroup);
        if (!empty($medStaff)) {
	    	foreach ($medStaff['members'] as $member) {
	    		$userdetail = $ds->getUser($member, array('cn'));
				$listofusers[$member] = $userdetail['cn'][0];
			}
		}
		
        $dentStaffGroup = $config->dentistrystaffgroup;
        $dentStaff = $ds->getGroup($dentStaffGroup);
        if (!empty($dentStaff)) {
	    	foreach ($dentStaff['members'] as $member) {
	    		$userdetail = $ds->getUser($member, array('cn'));
				$listofusers[$member] = $userdetail['cn'][0];
			}
        }
        foreach($listofusers as $uid => $cn) {
        	fwrite($fp, "$cn $uid".PHP_EOL);
        }
        fclose($fp);

        if (file_exists($cacheFolder .'/cache/ldapusers.txt'))
        	unlink($cacheFolder .'/cache/ldapusers.txt');
        rename($cacheFolder .'/cache/ldapusers.txt.tmp', $cacheFolder .'/cache/ldapusers.txt');
        
        
        $return = array();
        foreach (glob(self::ldapPath().'/*') as $filename) {
            $userDetails = unserialize(file_get_contents($filename));
            $name = $userDetails['cn'][0];
            $deleted = unlink($filename);
            if($deleted ==  true) {
                $return['deleted'][] = basename($filename).' ('.$name.')';
            } else {
                $return['undeleted'][] = basename($filename).' ('.$name.')';
            }
        }
        return $return;
    }
  
    private static function ldapPath() {
        $config = Zend_Registry::get('config');
        $ldapPath = $config->cache->ldap->path;
        if(! file_exists($ldapPath)) {
            mkdir($ldapPath);
        }
        return $ldapPath;
    }
}

?>