<?php

class PeopleController extends Zend_Controller_Action {
	/**
	 * Set up ACL info
	 */
	public function init() {
		$readActions = array('index', 'students','view', 'photoupload', 'managephotos', 'deletephoto', 
			'actuallydeletephoto', 'setdefaultphoto', 'viewdefaultphoto', 'pblgroups', 'group','blockchairs',
			'teachingstaff', 'stafflist', 'editdetails', 'doeditdetails');
		$this->_helper->_acl->allow('student', $readActions);
		$writeActions = array('setofficialphoto','viewofficialphoto');
		$this->_helper->_acl->allow('staff', $writeActions);
		$this->_helper->_acl->allow('admin', array('cohortgroup'));
	}

	/**
	 * List the various types of people page
	 */
	public function indexAction() {
		PageTitle::setTitle($this->view, $this->_request);
		$this->view->title = 'People';
		$this->view->test='Testing...';
		$staffpageFinder = new StaffPage();
		$staffpages = $staffpageFinder->getAllNames();
		$this->view->staffpages = $staffpages;
		$config = Zend_Registry::get('config');
		$this->view->externallinks = $config->people->externallinks;
	}

	/**
	 * List of all current students
	 */
	public function studentsAction() {
		PageTitle::setTitle($this->view, $this->_request);
		$this->view->title = 'Student List';
		
		$ds = Zend_Registry::get('ds');
		$config = Zend_Registry::get('config');
		$grp = $ds->getGroup($config->ldapdirectory->studentgroup);
		$memberof_attrib = $config->ldapdirectory->attrib->memberof;
		//$this->view->grp = $grp;
		$dn = $grp['dn'];
		$tmpStudentList = array();
		$search = $this->_request->getParam('search');
		if(is_string($search)) {
			$this->view->ch = "0";
			$this->view->search_query = $search;
			$tmpStudentList = $ds->search("(&({$memberof_attrib}=$dn)(|(sn=*$search*)(givenname=*$search*)(mail=*$search*)))",null,array('cn','givenname','sn','uid'),LDAP_SCOPE_SUBTREE);
		} else {
			$ch = $this->_request->getParam('ref');
			if(!is_string($ch))
				$ch='a';
				
			$this->view->ch = $ch;
			$tmpStudentList = $ds->search("(&({$memberof_attrib}=$dn)(sn=$ch*))",null,array('cn','givenname','sn','uid'),LDAP_SCOPE_SUBTREE);
		}
		$uidlist = array();
		$studentList=array();
		foreach($tmpStudentList as $student) {
			if(!strncmp($student['givenname'][0],'Virtual',6))
				continue;
			if($student['uid'][0]=='smpbind')
				continue;
				
			$studentList[$this->sortkey($student)] = $student;
			$uidlist[] = $student['uid'][0];
		}
		uksort($studentList,'strnatcasecmp');
		$photos = PeopleService::getDefaultPhoto($uidlist);
		foreach($studentList as $key => $student) {
			if (isset($photos[$student['uid'][0]])) {
				$studentList[$key]['photo'] = MediabankResourceConstants::createCompassImageUrl($photos[$student['uid'][0]], null, 120, 120);
			}
		}
		$this->view->studentList = $studentList;
		
	}
	
	private function sortkey($student) {
		return($student['sn'][0].'zzzzzzz'.$student['givenname'][0].'zzzzzzzz'.$student['uid'][0]);
	}
	/**
	 * Displays the profile page for a single user
	 * Last modified by daniel on 2011-12-27
	 */
	public function viewAction() {
		$uid = $this->_request->getParam('uid');
		$ds = Zend_Registry::get('ds');
		
		if($uid==null || $uid=="")
			$uid=UserAcl::getUid();
		//$person = $ds->getUser($uid);
		$person = LdapCache::getUserDetails($uid);
		$this->view->person = $person;
		
		$persontype = null;
		$config = Zend_Registry::get('config');
		if(in_array(trim($config->ldapdirectory->studentgroup), $person["groups"]))
			$persontype="Student";
		$this->view->persontype=$persontype;
		
		$fromemail = Compass::getConfig("studentemaildomain.from");
		$toemail = Compass::getConfig("studentemaildomain.to");
		$this->view->email = $person["mail"][0];
		if (in_array(trim($config->allstudentgroup), $person["groups"])) {
			$this->view->email = str_replace($fromemail, $toemail, $person["mail"][0]);
		}
		
		// Get Image URLs
		$mids = PeopleService::getPhotoList($uid);
		$bigurls = array();
		$lilurls = array();
		
		foreach($mids as $ind => $mid) {
			$bigurls[$ind] = MediabankResourceConstants::createCompassImageUrl($mid, null, 320, 320);
			$lilurls[$ind] = MediabankResourceConstants::createCompassImageUrl($mid, null, 64, 64);
		}
		$this->view->hideOfficialPhoto = true;
		if (UserAcl::isStaffOrAbove() || UserAcl::getUid() == $uid) {
			$this->view->hideOfficialPhoto = false;
		}
		
		$this->view->uid = $uid;
		$this->view->mids = $mids;
		$this->view->largePhotoURLs = $bigurls;
		$this->view->smallPhotoURLs = $lilurls;
		
		$this->view->officialPhotoMID = PeopleService::getOfficialPhoto($uid);
		$this->view->officialPhoto = MediabankResourceConstants::createCompassImageUrl($this->view->officialPhotoMID, null, 320, 320);
		$this->view->defaultPhotoMID = PeopleService::getDefaultPhoto($uid);
		$this->view->defaultPhoto = MediabankResourceConstants::createCompassImageUrl($this->view->defaultPhotoMID, null, 320, 320);
		$this->view->isMyProfile = $this->isMpauAdmin(UserAcl::getUid()) || (UserAcl::getUid() == $uid);
		
		//sort out groups
		$groups = array();
		//print_r($person['groups']);
		$sn = $person['sn'][0];
		$groups['Students'] = Compass::baseUrl().'/people/students/ref/'.substr($sn,0,1);
		foreach($person['groups'] as $group) {
			$groupname = $this->getGroupName($group);
			if($groupname !==FALSE)
				$groups[$groupname]=Compass::baseUrl().'/people/group/gid/'.$group;
		}
		$this->view->groups = $groups;
		//prep stuff for student resources uploader
		$srcFinder = new StudentResourceCategories();
		$srcnames = $srcFinder->getAllNames();
		ksort($srcnames);
		$this->view->studentResourceCategories = $srcnames;

		if(StudentResourceService::showSocialTools()) {
			StudentResourceService::prepStudentResourceView($this, $this->_request, null, $uid);		
			$this->view->socialtools = true;
		} else {
			$this->view->socialtools = false;
		}
		//get metadata about student
		$studentInfoFinder = new StudentInfo();
		$this->view->studentInfo = $studentInfoFinder->getInfo($uid);
	}
	
	private function getGroupName($group) {
		$config = Zend_Registry::get('config');
		$specialgroups = $config->extranamedgroups->toArray();
		$namedgrouptypes = $config->namedgroup->toArray();
		if(in_array($group, $specialgroups))
			$groupname = ucwords($group);
		else {
			$groupname = '';
			foreach($namedgrouptypes as $ngt) {
				$maps = array();
				if (isset($ngt['map'])) {
					$maps = $ngt['map'];
				}
				$matchmap = array();
				foreach($maps as $mapind => $map) {
					$keyvals = explode('|', $map);
					foreach($keyvals as $keyval) {
						$keyvalparts = explode(':', $keyval);
						$matchmap[$mapind][$keyvalparts[0]] = $keyvalparts[1];
					}
				}
				if(preg_match($ngt['regexp'], $group, $matches)) {
					$trarray = array();
					foreach($matches as $ind => $match) {
						if(isset($matchmap[$ind][$match]))
							$trarray['%'.$ind] = $matchmap[$ind][$match];
						else
							$trarray['%'.$ind] = $match;
					}
					$groupname = strtr($ngt['format'],$trarray);
					
				}
			}
		}
		if(strlen($groupname)>0)
			return($groupname);
		else
			return FALSE;
	}
	/**
	 * Allows a user to upload a photo
	 * Last modified by daniel on 2011-12-23
	 */
	public function photouploadAction() {
		//print_r($_FILES);
		$filename = $_FILES["photo"]["tmp_name"];
		$config = Zend_Registry::get('config');
		$cid = $config->people->studentphotocollection;
		$uid = $this->_request->getParam('uid');
		if (!empty($uid)) {
			$ds = Zend_Registry::get('ds');
			$u = $ds->getUser($uid);
			$metadata =array("photo" => array(
				"uid" => $uid,
				"cn" => $u['givenname'][0].' '.$u['sn'][0],
				"givenname" => $u['givenname'][0],
				"sn" => $u['sn'][0],
				"defaultphoto" => "false",
				"phototype" => "user",
				"lastmodified" => time(),
				"modifiedby" => UserAcl::getUid()		
				));
			$this->view->uid = $uid;
		} else {
			$ident = Zend_Auth::getInstance()->getIdentity();
			$metadata =array("photo" => array(
				"uid" => UserAcl::getUid(),
				"cn" => $ident->firstname[0].' '.$ident->lastname[0],
				"givenname" => $ident->firstname[0],
				"sn" => $ident->lastname[0],
				"defaultphoto" => "false",
				"phototype" => "user",
				"lastmodified" => time(),
				"modifiedby" => UserAcl::getUid()		
				));
			$this->view->uid = UserAcl::getUid();
		}
		$xmlService = new XMLService();
		$xml = $xmlService->createXMLfromArray($metadata);
		$dir = "/tmp";//MediabankResourceConstants::$tempDir;, sys_get_temp_dir broken on older PHP
        $metafile =  $dir.'/'.UserAcl::getUid().time();
        file_put_contents($metafile, $xml);
		$postData = array(
			"cid" => $cid,
			"metadataFile" => '@'.$metafile,
			"dataFile0" => '@'.$filename
			);
		$mediabankUtility = new MediabankUtility();
		$mid = $mediabankUtility->addResource($postData);
		unlink($metafile);
		unlink($filename);
		$this->_redirect("/people/managephotos/uid/{$this->view->uid}");
		exit;
		//$this->view->photoURL = MediabankResourceConstants::createCompassImageUrl($mid, null, 320, 320);
	}
	/**
	 * Allows a user to upload a photo
	 * Last modified by daniel on 2011-12-27
	 */
	public function managephotosAction() {
		$uid=UserAcl::getUid();
		
		//if there's uid in the url, change current uid to that uid
		$other_uid = $this->_request->getParam('uid');
		
		//Hide "default" radio button from admin person
		$this->view->hideDefault = false;
		
		if ($this->isMpauAdmin($uid) && !empty($other_uid)) {
			$this->view->hideDefault = true;
			$uid = $other_uid;
		}
		$this->view->uid = $uid;
		
		$mids = PeopleService::getPhotoList($uid);
		$this->view->mids = $mids;
		
		$editableMids = PeopleService::getEditablePhotoList($uid);
		$this->view->editableMids = $editableMids;
		
		$urls = array();
		foreach($mids as $ind => $mid) {
			$urls[$ind] = MediabankResourceConstants::createCompassImageUrl($mid, null, 128, 128);
		}
		$this->view->photoURLs = $urls;
		$this->view->defaultPhoto = PeopleService::getDefaultPhoto($uid);
		$this->view->officialPhoto = PeopleService::getOfficialPhoto($uid);
		
	}
	/**
	 * Queries whether a user wants to delete a photo
	 * Last modified by daniel on 2011-12-27
	 */
	public function deletephotoAction() {
		$midencoded=($this->_request->getParam('mid'));
		$uid=($this->_request->getParam('uid'));
		$mid=base64_decode($midencoded);
		$this->view->uid = $uid;
		$this->view->midencoded=$midencoded;
		$this->view->photoURL = MediabankResourceConstants::createCompassImageUrl($mid, null, 320, 320);;
	}
	/**
	 * Queries whether a user wants to delete a photo
	 * Last modified by daniel on 2011-12-27
	 */
	public function actuallydeletephotoAction() {
		$midencoded=($this->_request->getParam('mid'));
		$uid=($this->_request->getParam('uid'));
		$mid=base64_decode($midencoded);
		//$uid=UserAcl::getUid();
		$this->view->uid = $uid;
		$result = PeopleService::modifyPhoto($mid, array("uid"=>"zzdeleted".UserAcl::getUid()));
		$this->view->success=$result;
	}
	/**
	 * changes which photo is the default photo
	 * Last modified by daniel on 2011-12-28
	 */
	
	public function setdefaultphotoAction() {
		$midencoded=($this->_request->getParam('mid'));
		$mid=base64_decode($midencoded);
		$uid=UserAcl::getUid();
		$result = PeopleService::setDefaultPhoto($uid, $mid);
		if($result)
			echo "success";
		else
			echo "fail";
		exit();
	}
	
	public function setofficialphotoAction() {
		$uid=UserAcl::getUid();
		if (!$this->isMpauAdmin($uid)) {
			echo "fail";
			exit;
		}
		$midencoded=($this->_request->getParam('mid'));
		$uid=($this->_request->getParam('uid'));
		$mid=base64_decode($midencoded);
		$result = PeopleService::setOfficialPhoto($uid, $mid);
		if($result)
			echo "success";
		else
			echo "fail";
		exit();
	}
	
	/**
	 * Redirects user to the default photo for a UID.
	 * Preventing pages with lots of photos requiring a mediabank search before 
	 * generating the HTML. Should make loading seem faster.
	 * Last modified by daniel on 2011-12-29
	 */
	public function viewdefaultphotoAction() {
		$uid = $this->_request->getParam('uid');
		$size = $this->_request->getParam('size');
		if($size==null || $size =='')
			$size=320;
		$mid = PeopleService::getDefaultPhoto($uid);
		$url = MediabankResourceConstants::createCompassImageUrl($mid, null, $size, $size);
		//strip off the leading /compass part of url for redirect
		$url = substr($url, strlen(Compass::baseUrl()));
		$this->_redirect($url);
	}
	
	public function viewofficialphotoAction() {
		$uid = $this->_request->getParam('uid');
		$size = $this->_request->getParam('size');
		if($size==null || $size =='')
			$size=320;
		$mid = PeopleService::getOfficialPhoto($uid);
		$url = MediabankResourceConstants::createCompassImageUrl($mid, null, $size, $size);
		//strip off the leading /compass part of url for redirect
		$url = substr($url, strlen(Compass::baseUrl()));
		$this->_redirect($url);
	}
	
	/**
	 * Lists all PBL groups for the current domain
	 * Last modified by daniel on 2011-12-29
	 */
	public function pblgroupsAction() {
		$config = Zend_Registry::get('config');
		$domain = trim(strtolower(UserAcl::getDomainName()));
		$preg = $config->namedgroup->pbl->regexp;
		$format = $config->namedgroup->pbl->format;
		$ds = Zend_Registry::get('ds');
		$allgroups = $ds->getGroups();
		$pblgroups = preg_grep($preg, $allgroups);
		$grouparr = array();
		$groupnames = array();
		
		//note: could consider using getGroupName in here, but this is much more optimised version that works for PBL groups only
		//also, front-end does not use the full group name
		foreach($pblgroups as $pblgroup) {
			$matches  = array();
			preg_match($preg, $pblgroup, $matches);
			$grouparr[$matches[1]][$matches[2]]=$pblgroup;
			
			$trarray = array();
			foreach($matches as $ind => $match)
				$trarray['%'.$ind] = $match;
			$groupnames[$matches[1]][$matches[2]] = strtr($format,$trarray);
		}
		$oldestcohort=$config->oldestpblcohort;
		foreach($grouparr as $year => $groups) {
			if($year < $oldestcohort)
				unset($grouparr[$year]);
			if($year > date('Y'))
				unset($grouparr[$year]);
		}
		$this->view->groups = $grouparr;
		$this->view->groupnames = $groupnames;
	}
	
	public function cohortgroupAction() {
	    $cohort = $this->_request->getParam('cohort', 0);
	    $csv = (int)$this->_request->getParam('csv', 0);
	    $rows = array();
	    if((int)$cohort > 0) {
    	    $ldapService = new LdapService();
    	    if($csv === 1) {
        	    $ldapService->getGroupsByCohort($cohort, true);
    	    } else {   
        	    $rows = $ldapService->getGroupsByCohort($cohort);
    	    }
	    } else {
	        throw new Zend_Controller_Action_Exception("Page not found", 404);
	    }
	    $this->view->rows = $rows;
	    $this->view->cohort = $cohort;
	}
	
	/**
	 * Displays a group profile page
	 * Last modified by daniel on 2011-12-30
	 */
	public function groupAction() {
		$gid = $this->_request->getParam('gid');
		$ds = Zend_Registry::get('ds');
		$group = $ds->getGroup($gid);
		$this->view->group = $group;
		
		$groupname = $gid;
		$config = Zend_Registry::get('config');

		$groupname = $this->getGroupName($gid);
			
		$roomFinder = new PblRooms();
    	$this->view->roomname = $roomFinder->getPblRoom($gid);

		$this->view->groupname = $groupname;
		$users = array();
		foreach($group['members'] as $member) {
			//$memberdetails = $ds->getUser($member);
			$memberdetails = LdapCache::getUserDetails($member);
			if(!strncmp($memberdetails['givenname'][0],'Virtual',6))
				continue;
			if($memberdetails['uid'][0]=='smpbind')
				continue;
			
			$users[$this->sortkey($memberdetails)] = $memberdetails;
		}
		uksort($users,'strnatcasecmp');
		$this->view->members = $users;
		$uids = array();
		foreach($users as $user) {
			$uids[] = $user['uid'][0];
		}
		StudentResourceService::prepStudentResourceView($this, $this->_request, null, $uids);		
		
	}
	
	/** This page shows the list of block chairs */
	public function blockchairsAction() {
		$maxblock=10;
		PageTitle::setTitle($this->view, $this->_request);
		$blockchairFinder = new Blockchairs();

		$identity = Zend_Auth::getInstance()->getIdentity();
		$domain = $identity->domain;
		$role = $identity->role;
		
		if ( $role=='admin' ) {
			$blockchairs = $blockchairFinder->getAllChairs();
		} else {
			$blockchairs = $blockchairFinder->getAllChairs($domain);
		}
		
		$blocks = array();
		foreach($blockchairs as $ind => $chair) {
			if($chair['block']>$maxblock) {
				unset($blockchairs[$ind]);
				continue;
			}
			$blocknum = $chair['block'];
			$blocks[$blocknum]['name'] = $chair['name'];
			$blocks[$blocknum]['uids'][] = $chair['uid'];
		}
		$this->view->blocks = $blocks;
	}
	/** This page lists all current principal teachers */
	public function teachingstaffAction() {
		$teachingactivities = new TeachingActivities();
		$teachers = $teachingactivities->getAllCurrentPrincipalTeachers();
		$this->view->teachers = $teachers;
	}
	
		/** This page shows the list of staff */
	public function stafflistAction() {
		$page = $this->_request->getParam('page');
		
		PageTitle::setTitle($this->view, $this->_request);
		$staffFinder = new Staff();

		$identity = Zend_Auth::getInstance()->getIdentity();
		$domain = $identity->domain;
		$role = $identity->role;
		$this->view->page = $page;
		
		if ( $role=='admin' ) {
			$this->view->staff = $staffFinder->getAllStaffForPage($page);
		} else {
			$this->view->staff = $staffFinder->getAllStaffForPage($page,$domain);
		}
	}
	/**
	 * Allows a student to modify their biographical info
	 * Last modified by daniel on 2012-09-01
	 */
	public function editdetailsAction() {
		$uid=UserAcl::getUid();
		//get metadata about student
		$studentInfoFinder = new StudentInfo();
		$this->view->studentInfo = $studentInfoFinder->getInfo($uid);
		
	}
	/**
	 * Stores data from editdetails form
	 * Last modified by daniel on 2012-09-01
	 */
	public function doeditdetailsAction() {
		$uid=UserAcl::getUid();
		//get metadata about student
		$studentInfoFinder = new StudentInfo();
		$myInfo = $studentInfoFinder->getInfo($uid);
		$this->view->studentInfo = $myInfo;
     
	    $data = array(
	    	'mobile_phone' => stripslashes($this->_request->getParam('mobile_phone')),
	    	'mobile_publicity' => stripslashes($this->_request->getParam('mobile_publicity')),
	        'interests' => stripslashes($this->_request->getParam('interests')),
	        'education' => stripslashes($this->_request->getParam('education'))
	    );
	     
	    $where = $studentInfoFinder->getAdapter()->quoteInto('uid = ?', $uid);
	     
	    if(isset($myInfo[0])) {
	    	$studentInfoFinder->update($data, $where);
	    } else {
	    	$data['uid'] = $uid;
	    	$studentInfoFinder->insert($data);
	    }
	    $this->_helper->redirector('view');
	    
	}
	
	private function isMpauAdmin($uid) {
		//check whether user is in group mpau_readwrite or mpau_admin
		$ds = Zend_Registry::get('ds');
		$u = $ds->getUser($uid);
		$isMpauAdmin = false;
		if (in_array('mpau_readwrite', $u['groups']) || in_array('mpau_admin', $u['groups'])) {
			$isMpauAdmin = true;
		}
		return $isMpauAdmin;
	}
}
