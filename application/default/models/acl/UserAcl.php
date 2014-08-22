<?php
class UserAcl {
    public static $EDIT = "edit";
    public static $ARCHIVE = "archive";
    public static $APPROVE = "approve";
	
    protected static $role = 'unknown';
    protected static $userId = 'unknown';
    protected static $currentPbl = 'unknown';
    protected static $stage1Pbl = 'unknown';
    protected static $stage2Pbl = 'unknown';
    protected static $domainName = 'unknown';
    
    protected static $userIsAdmin = null;
    protected static $userIsDomainAdmin = null;
    protected static $userIsStageCoordinator = null;
    protected static $userIsBlockChair = null;
    protected static $userIsStaff = null;
    protected static $userIsStudent = null;
    
    protected static $userIsStudentOrAbove = null;
    protected static $userIsStaffOrAbove = null;
    protected static $userIsBlockChairOrAbove = null;
    protected static $userIsStagecoordinatorOrAbove = null;
    protected static $userIsDomainAdminOrAbove = null;
    
    /**
     * This function returns list of parent roles for $userRole given (return array contains the user role). 
     * OR list of children roles for $userRole given (return array DOES NOT contain the user role).
     * OR both 
     * 
     * @param string $userRole //The role of the user like staff, admin, blockchair etc
     * @param string $parentsOrChildrens // Takes either 'parents' or 'childrens' and returns results accordingly.
     * @param boolean $includeSiblings //Normally the role in concern is added to the 'parents' array but not sibling roles. To include sibling roles in 'parents' array pass 'true'.
     * @return mixed $return
     */
    public static function hierarchy($userRole,$parentsOrChildrens='',$includeSiblings = false) {
        $roles = Zend_Registry::get('config')->acl->roles->toArray();
        $reqChild =  Zend_Registry::get('config')->acl->roles->$userRole;
        
        $return = array();
        $return['parents'] = array();
        $return['childrens'] = array();
        
        if($includeSiblings == true) {
            foreach($roles as $role => $child) {
                if($role == $userRole || in_array($userRole,$return['parents']) || $reqChild == $child) {
                    $return['parents'][] = $role;
                } else {
                    $return['childrens'][] = $role;
                }
            }
        } else {
            foreach($roles as $role => $child) {
                if( $role == $userRole  || (in_array($userRole,$return['parents']) && $reqChild != $child) ) {
                   $return['parents'][] = $role;
                } else {
                    if($reqChild != $child) {
                        $return['childrens'][] = $role;
                    }
                }
            }
        }
        
        switch($parentsOrChildrens) {
            case 'parents' : 
                return $return['parents'];
                break;
            case 'childrens' :
                return $return['childrens'];
                break;
            default:
                return $return;
        }
    }
    
    /*
     * Check whether the current user has role equivalent to student or above
     * If the user role is student,staff,blockchair,admin.. it would return true and return false otherwise
     * @return boolean 
     */
    public static function isStudentOrAbove() {
        if(is_null(self::$userIsStudentOrAbove)) {
            $isStudentOrAbove = false;
            self::setRole();
            if(self::$role != 'unknown') {
                $rolesAllowed = self::hierarchy('student','parents',true);
                if(in_array(self::$role, $rolesAllowed)) {
                    $isStudentOrAbove = true;
                }
            }
            self::$userIsStudentOrAbove = $isStudentOrAbove;
        }
        return self::$userIsStudentOrAbove;
    } 
    
    
    /*
     * Check whether the current user has role equivalent to staff or above
     * If the user role is staff,blockchair,admin.. it would return true and return false if its student
     * @return boolean 
     */
    public static function isStaffOrAbove() {
        if(is_null(self::$userIsStaffOrAbove)) {
            $isStaffOrAbove = false;
            self::setRole();
            if(self::$role != 'unknown') {
                $rolesAllowed = self::hierarchy('staff','parents',true);
                if(in_array(self::$role, $rolesAllowed)) {
                    $isStaffOrAbove = true;
                }
            }
            self::$userIsStaffOrAbove = $isStaffOrAbove;
        }
        return self::$userIsStaffOrAbove;
    } 
    
    /*
     * Check whether the current user has role equivalent to blockchair or above
     * If the user role is blockchair,stage-coordinator, admin.. it would return true and return false if its student,staff
     * @return boolean 
     */
    public static function isBlockchairOrAbove() {
        if(is_null(self::$userIsBlockChairOrAbove)) {
            $isBlockChairOrAbove = false;
            self::setRole();
            if(self::$role != 'unknown') {
                $rolesAllowed = self::hierarchy('blockchair','parents',true);
                if(in_array(self::$role, $rolesAllowed)) {
                    $isBlockChairOrAbove = true;
                }
            }
            self::$userIsBlockChairOrAbove = $isBlockChairOrAbove;
        }
        return self::$userIsBlockChairOrAbove;
    }
     
    /*
     * Check whether the current user has role equivalent to stage coordinator or above
     * If the user role is stage-coordinator, admin.. it would return true and return false if its student,staff,blockchair
     * @return boolean 
     */
    public static function isStagecoordinatorOrAbove() {
        if(is_null(self::$userIsStagecoordinatorOrAbove)) {
            $isStagecoordinatorOrAbove = false;
            self::setRole();
            if(self::$role != 'unknown') {
                $rolesAllowed = self::hierarchy('stagecoordinator','parents',true);
                if(in_array(self::$role, $rolesAllowed)) {
                    $isStagecoordinatorOrAbove = true;
                }
            }
            self::$userIsStagecoordinatorOrAbove = $isStagecoordinatorOrAbove;
        }
        return self::$userIsStagecoordinatorOrAbove;
    }
    
    /*
     * Check whether the current user has role equivalent to domain admin or above
     * If the user role is doaminadmin, admin.. it would return true and return false if its student,staff,blockchair
     * @return boolean 
     */
    public static function isDomainAdminOrAbove() {
        if(is_null(self::$userIsDomainAdminOrAbove)) {
            $isDomainAdminOrAbove = false;
            self::setRole();
            if(self::$role != 'unknown') {
                $rolesAllowed = self::hierarchy('domainadmin','parents',true);
                if(in_array(self::$role, $rolesAllowed)) {
                    $isDomainAdminOrAbove = true;
                }
            }
            self::$userIsDomainAdminOrAbove = $isDomainAdminOrAbove;
        }
        return self::$userIsDomainAdminOrAbove;
    }
    
    /*
     * Check whether the current users role is admin or not
     */
    public static function isAdmin() {
        if(is_null(self::$userIsAdmin)) {
            $isAdmin = false;
            self::setRole();
            if(self::$role == 'admin') {
                $isAdmin = true;
            }
            self::$userIsAdmin = $isAdmin;
        }
        return self::$userIsAdmin;
    }
    
    /*
     * Check whether the current users role is domain admin or not
     */
    public static function isDomainAdmin() {
        if(is_null(self::$userIsDomainAdmin)) {
            $isDomainAdmin = false;
            self::setRole();
            if(self::$role == 'domainadmin') {
                $isDomainAdmin = true;
            }
            self::$userIsDomainAdmin = $isDomainAdmin;
        }
        return self::$userIsDomainAdmin;
    }
    
     /*
     * Check whether the current user's role is stage coordinator or not
     */
    public static function isStageCoordinator() {
        if(is_null(self::$userIsStageCoordinator)) {
            $isStageCoordinator = false;
            self::setRole();
            if(self::$role == 'stagecoordinator') {
                $isStageCoordinator = true;
            }
            self::$userIsStageCoordinator = $isStageCoordinator;
        }
        return self::$userIsStageCoordinator;
    }
    
     /*
     * Check whether the current user's role is stage coordinator or not
     */
    public static function isBlockChair() {
        if(is_null(self::$userIsBlockChair)) {
            $isBlockChair = false;
            self::setRole();
            if(self::$role == 'blockchair') {
                $isBlockChair = true;
            }
            self::$userIsBlockChair = $isBlockChair;
        }
        return self::$userIsBlockChair;
    }
    
    /*
     * Check whether the current users role is staff or not
     */
    public static function isStaff() {
        if(is_null(self::$userIsStaff)) {
            $isStaff = false;
            self::setRole();
            if(self::$role == 'staff') {
                $isStaff = true;
            }
            self::$userIsStaff = $isStaff;
        }
        return self::$userIsStaff;
    }
    
    /*
     * Check whether the current users role is student or not
     */
    public static function isStudent() {
        if(is_null(self::$userIsStudent)) {
            $isStudent = false;
            self::setRole();
            if(self::$role == 'student') {
                $isStudent = true;
            }
            self::$userIsStudent = $isStudent;
        }
        return self::$userIsStudent;
    }
    
    /**
     * Return current Pbl
     */
    public static function currentPbl() {
        if(self::$currentPbl == 'unknown') {
            self::setCurrentPbl();
        }
        return self::$currentPbl;
    }
    
    /*
     * Return Stage 1 Pbl
     */
    public static function stage1Pbl() {
        if(self::$stage1Pbl == 'unknown') {
            self::setStage1Pbl();
        }
        return self::$stage1Pbl;
    }
    
    /*
     * Return Stage 2 Pbl
     */
    public static function stage2Pbl() {
        if(self::$stage2Pbl == 'unknown') {
            self::setStage2Pbl();
        } 
        return self::$stage2Pbl;
    }
    
    
    /**
     * Set User Role from zend_auth  
     */
    protected static function setRole() {
        if(self::$role == 'unknown') {
            $identity = Zend_Auth::getInstance()->getIdentity();
            if(isset($identity)) {
                self::$role = $identity->role;
            }
        }
    }
    
    /**
     * Set User ID from zend_auth      
     */
    protected static function setUserId() {
        if(self::$userId == 'unknown') {
            $identity = Zend_Auth::getInstance()->getIdentity();
            if(isset($identity)) {
                self::$userId = $identity->user_id;
            }
        }
    }
    
    /**
     * Set current pbl
     */
    protected static function setCurrentPbl() {
        if(self::$currentPbl == 'unknown') {
            $identity = Zend_Auth::getInstance()->getIdentity();
            if(isset($identity) && isset($identity->currentpbl)) {
                self::$currentPbl = $identity->currentpbl;
            }
        }
    }
    
    /**
     * Set Stage 1 pbl
     */
    protected static function setStage1Pbl() {
        if(self::$stage1Pbl == 'unknown') {
            $identity = Zend_Auth::getInstance()->getIdentity();
            if(isset($identity) && isset($identity->stage1pbl)) {
                self::$stage1Pbl = $identity->stage1pbl;
            }
        }
    }
    
    /**
     * Set Stage 2 pbl
     */
    protected static function setStage2Pbl() {
        if(self::$stage2Pbl == 'unknown') {
            $identity = Zend_Auth::getInstance()->getIdentity();
            if(isset($identity) && isset($identity->stage2pbl)) {
                self::$stage2Pbl = $identity->stage2pbl;
            }
        }
    }
    
    /**
     * Get User ID for logged in user 
     */
    public static function getUid() {
        self::setUserId();
        return self::$userId;
    }
    
    /**
     * Get Domain Name
     */
    public static function getDomainName() {
        $identity = Zend_Auth::getInstance()->getIdentity();
        if(isset($identity) && isset($identity->domain)) {
            return  $identity->domain;  
        }
        return self::$domainName;
    }
    
    /**
     * Get Domain ID 
     */
    public static function getDomainId() {
        $domainId = null;
        $domainName = self::getDomainName();
        if($domainName != self::$domainName) {
            $domainFinder = new Domains();
            return $domainFinder->getDomainId($domainName);
        }
        return $domainId;
    }
    
    
    /**
     * Get Stage of student
     */
    public static function getStudentStage() {
        $identity = Zend_Auth::getInstance()->getIdentity();
        $role = $identity->role;
        if($role == 'student') {
            return($identity->stage);
        } else {
            return(0);
        }
    }
    
    /**
     * Whether to display stage 3 menu to student
     */
    public static function displayStage3Menu() {
    	if (self::isStudent()) {
    		$stage = self::getStudentStage();
    		if ($stage > 2) {
    			return true;
    		} else {
    			$identity = Zend_Auth::getInstance()->getIdentity();
    			$groups = $identity->groups;
    			$config = Zend_Registry::get('config');
    			if (isset($config->release_date->stage3)) {
    				$extra_dates_info = $config->release_date->stage3->toArray();
    				$extra_dates = array();
    				$year = date('Y');
    				foreach ($extra_dates_info as $s => $t) {
    					$cur_stage = substr($s, -1);
    					$extra_dates['cohort'.($year - $cur_stage + 1)] = "{$year}-{$t}";
    				}
    				$extra_groups = array_keys($extra_dates);
    				$common_groups = array_intersect($groups, $extra_groups);
    				foreach ($common_groups as $group) {
	    				if (strtotime('now') > strtotime($extra_dates[$group])) {
	    					return true;
	    				}
    				}
    			}
    		}
    	}
    	return false;
    }
    
    /**
     * Get role for logged in user 
     */
    public static function getRole() {
        self::setRole();
        return self::$role;
    }
    
    /**
     * Only allow owner to edit, archive and approve teaching activity
     */
    public static function checkTaPermission($ta, $permission) {
    	if (!($ta instanceof TeachingActivity)) {
    		$taFinder = new TeachingActivities();
    		$ta = $taFinder->getTa($ta);
    	}
    	$identity = Zend_Auth::getInstance()->getIdentity();
    	if ($identity->domain === $ta->owner) {
	    	if (UserAcl::isDomainAdminOrAbove()) {
	    		return true;
	    	}
	    	if (userAcl::isStageCoordinator()) {
	    		if (!in_array($ta->stageID, $identity->stages)) {
	    			return "Teaching activity {$ta->auto_id} is not in your stage.";
	    		} else {
	    			return true;
	    		}
	    	}
	    	if (userAcl::isBlockChair()) {
	    		if ($permission == self::$APPROVE) {
	    			return "You do not have permission to approve teaching activity {$ta->auto_id}.";
	    		}
	    		if (!in_array($ta->blockID, $identity->blocks)) {
	    			return "Teaching activity {$ta->auto_id} is not in your block.";
	    		} else {
	    			return true;
	    		}
	    	}
	    	if (userAcl::isStaff()) {
	    		if ($permission == self::$APPROVE) {
	    			return "You do not have permission to approve teaching activity {$ta->auto_id}.";
	    		}
	    		if ($permission == self::$ARCHIVE) {
	    			return "You do not have permission to archive teaching activity {$ta->auto_id}.";
	    		}
	    		if (in_array($identity->user_id, $ta->principal_teacher_uid_arr)) {
	    			return true;
	    		}
	    		return "You are not a principal teacher of teaching activity {$ta->auto_id}.";;
	    	}
	    	return false;
    	} else {
    		return "\"{$identity->domain}\" is not the owner of teaching activity {$ta->auto_id}.";
    	}
    }
    
    public static function checkLoPermission($lo, $permission) {
    	$identity = Zend_Auth::getInstance()->getIdentity();
    	if ($identity->domain === $lo->owner) {
    		if (UserAcl::isDomainAdminOrAbove()) {
    			return true;
    		}
    		$linkedtas = $lo->getLinkedTeachingActivityWithStatus(Status::$RELEASED);
    		$stages = array();
    		$blocks = array();
    		$p_teachers = array();
    		foreach ($linkedtas as $ta) {
    			if ($ta['owner'] == $lo->ownerID) {
	    			$stages[] = $ta['stage'];
	    			$blocks[] = $ta['block'];
	    			$p_teachers = array_merge($p_teachers, explode(',', $ta['principal_teacher']));
    			}
    		}
    		Zend_Registry::get('logger')->DEBUG(__METHOD__. print_r($stages, 1) . print_r($blocks, 1) . print_r($p_teachers, 1));
    		if (userAcl::isStageCoordinator()) {
    			if (count(array_intersect($identity->stages, $stages)) == 0) {
    				return "Learning objective {$lo->auto_id} is not linked to any teaching activities in your stage.";
    			} else {
    				return true;
    			}
    		}
    		if (userAcl::isBlockChair()) {
    			if ($permission == self::$APPROVE) {
					return "You do not have permission to approve learning objective {$lo->auto_id}.";
    			}
    			if (count(array_intersect($identity->blocks, $blocks)) == 0) {
    				return "Learning objective {$lo->auto_id} is not linked to any teaching activities in your block.";
    			} else {
    				return true;
    			}
    		}
    		if (userAcl::isStaff()) {
    			if ($permission == self::$APPROVE) {
    				return "You do not have permission to approve learning objective {$lo->auto_id}.";
    			}
    			if ($permission == self::$ARCHIVE) {
    				return "You do not have permission to archive learning objective {$lo->auto_id}.";
    			}
    			if (!in_array($identity->user_id, $p_teachers)) {
    				return "Learning objective {$lo->auto_id} is not linked to any teaching activities of which you are a principal teacher.";
    			} else {
    				return true;
    			}
    		}
    	} else {
    		return "\"{$identity->domain}\" is not the owner of learning objective {$lo->auto_id}.";
    	}
    }
    
    /** Allows stage coordinator and above to approve a linkage */
    public static function checkApprovalPermission($ta) {
        if (!($ta instanceof TeachingActivity)) {
    		$taFinder = new TeachingActivities();
    		$ta = $taFinder->getTa($ta);
    	}
    	
    	$identity = Zend_Auth::getInstance()->getIdentity();
    	$role = $identity->role;
    	if ($identity->domain !== $ta->owner) {
    		return "Teaching activity in this submission does not belong to \"{$identity->domain}\".";
    	}
    	
    	//Only stage coordinator, domain admin and admin can approve LO/TA/Linkage submission
		if (!(($role == 'stagecoordinator' && in_array($ta->stageID, $identity->stages)) || $role == 'domainadmin' || $role == 'admin')) {
			return "This submission in not linked to a teaching activity in your stage.";
		}
		
		return true;
    }
    
    /**
     * Upon login, store user related info (domain, role, currentpbl, ...) in the session for later use
     * @param $uid - optional user id
     */
    public static function loadDomainInfo($uid = NULL) {
    	$identity = new stdClass();
    	if ($uid === NULL) {
    		$user_arr = split("\\\\", Zend_Auth::getInstance()->getIdentity());
    		$identity->user_id = trim($user_arr[1]);
    	} else {
    		$identity->user_id = $uid;
    	}
    	Zend_Registry::get('logger')->DEBUG(__METHOD__ . ": user id - ".$identity->user_id);
    	
    	$ds = Zend_Registry::get('ds');
    	$u = $ds->getUser($identity->user_id);
    	$identity->firstname = $u['givenname'];
    	$identity->lastname = $u['sn'];
    	$identity->email = $u['mail'][0];
    	Zend_Registry::get('logger')->DEBUG(__METHOD__ . ": user groups - ".print_r($u['groups'], 1));
    	
        $identity->disciplineRole = 'student';
    	$disciplineRoles = DisciplineService::$disciplineAdminRoles;
    	foreach ($disciplineRoles as $disciplineRole) {
    		if (in_array($disciplineRole, $u['groups'])) {
    			$identity->disciplineRole .= '|'.$disciplineRole;
    		}
    	}

    	if (!isset(Zend_Registry::get('config')->event_wsdl_uri)) {
    		$client = new StageBlockSeqs();
    	} else {
    		$client = new Zend_Soap_Client(Zend_Registry::get('config')->event_wsdl_uri);
    	}
    	
    	$identity->role = 'staff';
    	$groups = preg_grep('/'.Zend_Registry::get('config')->groupprefix->medicine.'[0-9]{4}/i', $u['groups']);
    	if (count($groups) != 0) {
    		$identity->role = 'student';
    		$identity->domain = 'Medicine';
    		$identity->all_domains[] = 'Medicine';
    		$identity->groups = $groups;
    		
    		$cohort_arr = array();
    		foreach ($groups as $group) {
    			preg_match('/[0-9]{4}/', $group, $matches);
    			$cohort_arr[] = $matches[0];
    		}
    		$cohort_arr = array_unique($cohort_arr);
    		sort($cohort_arr, SORT_NUMERIC);
    		$identity->cohort = (int)$cohort_arr[0];
    		
    		$user_stage = date('Y') - $identity->cohort + 1;
    		if ($user_stage > 2) {
    			$identity->currentpbl = "0.01";
    			$identity->releasedpbl = "0.01";
    		} else {
	    		$identity->currentpbl = $client->getCurrentPbl($identity->cohort);
	    		if (isset(Zend_Registry::get('config')->event_wsdl_uri)) {
	    			$identity->releasedpbl = $client->getReleasedPbl($identity->cohort);
	    		} else {
	    			$identity->releasedpbl = $identity->currentpbl;
	    		}
    		}
    		//$domains['Medicine'] = array('groups' => $groups);
    		
    		$ncs_groups = preg_grep('/usydmpyear[0-9]{1}_'.Zend_Registry::get('config')->groupprefix->northern.'/i', $u['groups']);
    		if (count($ncs_groups) != 0) {
    			$identity->all_domains[] = 'Northern';
    			$identity->groups = array_merge($identity->groups, $ncs_groups);
    		}
    	}
        $groups = preg_grep('/'.Zend_Registry::get('config')->groupprefix->dentistry.'[0-9]{4}/i', $u['groups']);
    	if (count($groups) != 0) {
    		$identity->role = 'student';
    		$identity->domain = 'Dentistry';
    		$identity->all_domains[] = 'Dentistry';
    		$identity->groups = $groups;
    		
    		$cohort_arr = array();
    		foreach ($groups as $group) {
    			preg_match('/[0-9]{4}/', $group, $matches);
    			$cohort_arr[] = $matches[0];
    		}
    		$cohort_arr = array_unique($cohort_arr);
    		sort($cohort_arr, SORT_NUMERIC);
    		$identity->cohort = (int)$cohort_arr[0];
    		
    		$user_stage = date('Y') - $identity->cohort + 1;
    		if ($user_stage > 2) {
    			$identity->currentpbl = "0.01";
    			$identity->releasedpbl = "0.01";
    		} else {
	    		$identity->currentpbl = $client->getCurrentPbl($identity->cohort);
	    		if (isset(Zend_Registry::get('config')->event_wsdl_uri)) {
	    			$identity->releasedpbl = $client->getReleasedPbl($identity->cohort);
	    		} else {
	    			$identity->releasedpbl = $identity->currentpbl;
	    		}
    		}
    		//$domains['Dentistry'] = array('groups' => $groups);
    	}
    	
    	$groups = preg_grep('/'.Zend_Registry::get('config')->groupprefix->medicinestage.'[0-9]/i', $u['groups']);
    	if (count($groups) != 0) {
    	    foreach ($groups as $group) {
    	        preg_match('/[0-9]/', $group, $matches);
    	        $identity->stage = $matches[0];
    	    }
    	}

    	if ($identity->role === 'student') {
    		return $identity;
    	}
    	
    	$date = new Zend_Date();
    	$cur_year = $date->get(Zend_Date::YEAR);
    	$identity->stage1pbl = $client->getCurrentPbl($cur_year);
    	$identity->stage2pbl = $client->getCurrentPbl($cur_year - 1);
    	
    	$all_domains = array();
        $domainFinder = new Domains();
    	$domain_names = $domainFinder->getAllNames('auto_id ASC');
    	if (in_array('compassadmin', $u['groups'])) {
    		$identity->role = 'admin';
    		$identity->domain = 'Medicine';
    		foreach ($domain_names as $domain_name) {
    			$all_domains[$domain_name] = array('role' => 'admin');
    		}
    		$identity->all_domains = $all_domains;
    		return $identity;
    	}
    	
    	$all_domains = array();
    	$defaultDomain = true;
    	foreach ($domain_names as $domain_id => $domain_name) {
    		if ($domains = Utilities::isDomainAdmin($identity->user_id, $domain_id)) {
    			$all_domains[$domain_name] = array('role' => 'domainadmin');
    			if ($defaultDomain) {
	    			$identity->role = 'domainadmin';
	    			$identity->domain = $domain_name;
	    			$defaultDomain = false;
	    			continue;
    			}
    		}
    		if ($stages = Utilities::isStageCoordinator($identity->user_id, $domain_id)) {
    			$all_domains[$domain_name] = array('role' => 'stagecoordinator', 'stages' => $stages);
    			if ($defaultDomain) {
	    			$identity->role = 'stagecoordinator';
	    			$identity->domain = $domain_name;
	    			$identity->stages = $stages;
	    			$defaultDomain = false;
	    			continue;
    			}
    		}
    		if ($blocks = Utilities::isBlockchair($identity->user_id, $domain_id)) {
    			$all_domains[$domain_name] = array('role' => 'blockchair', 'blocks' => $blocks);
    			if ($defaultDomain) {
	    			$identity->role = 'blockchair';
	    			$identity->domain = $domain_name;
	    			$identity->blocks = $blocks;
	    			$defaultDomain = false;
	    			continue;
    			}
    		}
    	}
        if (!isset($all_domains['Dentistry'])) {
    		if (in_array(Zend_Registry::get('config')->dentistrystaffgroup, $u['groups'])) {
    			//$identity->domain = 'Dentistry';
    			$all_domains['Dentistry'] = array('role' => 'staff');
    		}
    	}
    	if (!isset($all_domains['Medicine'])) {
    		if (in_array(Zend_Registry::get('config')->medicinestaffgroup, $u['groups'])) {
    			//$identity->domain = 'Medicine';
    			$all_domains['Medicine'] = array('role' => 'staff');
    		}
    	}
    	if (!isset($all_domains['Northern'])) {
    		if (in_array(Zend_Registry::get('config')->northernstaffgroup, $u['groups'])) {
    			//$identity->domain = 'Northern';
    			$all_domains['Northern'] = array('role' => 'staff');
    		}
    	}
    	
    	if (!isset($identity->domain) && isset($all_domains['Medicine'])) {
    		$identity->domain = 'Medicine';
    	} else if (!isset($identity->domain) && isset($all_domains['Northern'])) {
    		$identity->domain = 'Northern';
    	} else if (!isset($identity->domain) && isset($all_domains['Dentistry'])) {
    		$identity->domain = 'Dentistry';
    	}

    	$identity->all_domains = $all_domains;
    	return $identity;
    }
    
    public static function getLdapAuthAdapter($fmData) {
        $config = Zend_Registry::get('config');
        $options = $config->ldap->toArray();
        unset($options['log_path']);
        return new Zend_Auth_Adapter_Ldap($options, $fmData['username'], $fmData['password']);
    }
    
    public static function httpAuthenticationAllowed($controller, $action) {
        $httpAuthenticationAllowed = false;
        $config = Zend_Registry::get('config');
        if(isset($config->http_auth) && isset($config->http_auth->controller)) {
            $configControllers = $config->http_auth->controller->toArray();
            foreach($configControllers as $configController => $configActions) {
                if($configController == $controller) {
                    foreach($configActions as $configAction) {
                        if($configAction == $action) {
                            $httpAuthenticationAllowed = true;
                        }
                    }
                }
            }
        }
        return $httpAuthenticationAllowed;
    }
    
    public static function promptHttpAuth() {
        header('WWW-Authenticate: Basic realm="My Realm"');
        header('HTTP/1.0 401 Unauthorized');
        exit;
    }
    
    public static function isSetHttpAuth() {
        return isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW']);
    }
    
    public static function getHttpAuthUsernamePassword() {
        $return = array();
        $return['username'] = '';
        $return['password'] = '';
        if(self::isSetHttpAuth()) {
            $return['username'] = $_SERVER['PHP_AUTH_USER']; 
            $return['password'] = $_SERVER['PHP_AUTH_PW'];
        }
        return $return;
    }
}
    

?>
