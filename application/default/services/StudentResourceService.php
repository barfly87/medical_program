<?php
class StudentResourceService {
    private static $ds = null; 

    public  function __construct() {
        self::$ds = Zend_Registry::get('ds');
    }
    public static function addRatingsToResources($resources, $ratingcategories) {
    	//$config = Zend_Registry::get('config');
    	$rids = array();
    	$resourcebyid = array();
    	foreach($resources as $r) {
    		$rids[] = $r['auto_id'];
    		$resourcebyid[$r['auto_id']] = $r;
    	}
    	$where = "resource_id in (".implode(',',$rids).")";
    	$ratings = new Ratings();
    	$resratings = $ratings->fetchAll($where);
    	$uid=UserAcl::getUid();
    	foreach($resratings as $res) {
    		@$resourcebyid[$res['resource_id']]['rating'][$res['rating']]++;
    		if($res['uid']==$uid) {
    			$resourcebyid[$res['resource_id']]['myrating'][] = $res['rating'];
    		}
    	}
    	
    	$ratingscores = new RatingScores();
    	$resratingscores = $ratingscores->fetchAll($where);
    	foreach($resratingscores as $res) {
    		@$resourcebyid[$res['resource_id']]['score']+=$res['rating'];
    		@$resourcebyid[$res['resource_id']]['count']++;
    		if($res['uid']==$uid) {
    			if($res['rating']!=0)
    				$resourcebyid[$res['resource_id']]['myscore'] = $res['rating'];
    			$resourcebyid[$res['resource_id']]['mycomment'] = $res['comment'];
    		}
    		$resourcebyid[$res['resource_id']]['comments'][] = $res->toArray();
    		
    	}
    	$rcs = array();
    	foreach($ratingcategories as $rc) {
    		$rcs[$rc['auto_id']] = $rc['name'];
    	}
    	foreach($resourcebyid as $rid => $res) {
    		if(isset($res['rating'])) {
    			arsort($res['rating']);
    	
	    		foreach($res['rating'] as $ratingid => $ratingcount) {
	    			if($ratingcount * 2 > $res['count'])
	    				$resourcebyid[$rid]['ratingdescription'][] = $ratingid;
	    		}
    		}
    		if(isset($resourcebyid[$rid]['ratingdescription']))
	    		foreach($resourcebyid[$rid]['ratingdescription'] as $ind => $rating)
	    			$resourcebyid[$rid]['ratingdescription'][$ind] = $rcs[$rating];
    	}
    	return($resourcebyid);
    	
    }
    /**
     * Gets all previous versions of a resource
     */
	public static function getPreviousVersions($resource_id) {
		$myuid=UserAcl::getUid();
		$srlink = new StudentResourceLink();
		$prev_versions = array();
		$srwhere = array();
		$srwhere[] = "(uid='$myuid' or not(private='true'))";
		$srwhere[] = "auto_id=$resource_id";
		$studentresource = $srlink->fetchRow($srwhere)->toArray();
		if(isset($studentresource['previous_version_id']) && $studentresource['previous_version_id'] > 0)
			$prev_versions = StudentResourceService::getPreviousVersions($studentresource['previous_version_id']);
		$prev_versions[$studentresource['auto_id']] = $studentresource;
		return($prev_versions);
	}
    
    /**
     * Gather all the stuff a controller action needs for showloresource
     */
	public static function prepStudentResourceView($that, $request, $loid, $uid = null, $count=100) {
		$myuid=UserAcl::getUid();
		//prep ratings
		$ratingcategories = new RatingCategories();
		$ratingcategories = $ratingcategories->fetchAll()->toArray();
		$that->view->ratingcategories = $ratingcategories;
		
		//check for resource sort order request
		$sortorder = $request->getParam('sortby');
		if(strlen($sortorder)>0) {
			setcookie('compass_sr_order', $sortorder,time()+60*60*24*366, '/');
		} elseif(strlen($request->getCookie('compass_sr_order'))>0) {
			$sortorder =$request->getCookie('compass_sr_order');
		} else {
			$sortorder='rating';
		}
		//check that consent has been given
		$that->view->studyconsent = StudentResourceService::checkSocialToolConsent();
		
		//prep stuff for student resources view
		$srlink = new StudentResourceLink();
		$srwhere = array();
		$srwhere[] = "(uid='$myuid' or not(private='true'))";
		$srwhere[] = "archived=0";
		if($request->getParam('quizids')!=null) {
			$quizids = $request->getParam('quizids');
			$quizids = explode(',',$quizids);
			//prevent SQL injections
			foreach($quizids as $key => $val)
				if(!is_numeric($val)) unset($quizids[$key]);
			$select = $srlink->select()->where($srwhere[0])->where($srwhere[1])->where('auto_id in ('.implode(',',$quizids).')')->order("dateadded DESC")->limit($count,0);
			$studentresources = $srlink->fetchAll($select);
		} else if ($uid==null && $loid == null) {
			// Get the most recently posted resources
			$select = $srlink->select()->where($srwhere[0])->where($srwhere[1])->order("dateadded DESC")->limit($count,0);
			$studentresources = $srlink->fetchAll($select);
			$sortorder='date';
		} else if($uid==null) {
			// then it's a request for the resources for an LO
			if(is_array($loid)) {
				$srwhere[]='loid in ('.implode(',',$loid).')';
			} else {
				$srwhere[] = "loid={$loid}";
			}
			//print_r($srwhere);
			$studentresources = $srlink->fetchAll($srwhere, 'auto_id DESC');
			//print_r($studentresources);exit();
			
		} elseif(is_array($uid)) {
			// then it's a groupof users
			$uidsquoted=array();
			foreach($uid as $u)
				$uidsquoted[] = "'".$u."'";
  			$uidsin = implode(',',$uidsquoted);
  				
  			$srwhere[] = "uid in ({$uidsin})";
  			$select = $srlink->select()->where($srwhere[0])->where($srwhere[1])->where($srwhere[2])->order("dateadded DESC")->limit($count,0);
			$studentresources = $srlink->fetchAll($select);
		} else {
			//then it's a single user
			$srwhere[] = "uid='{$uid}'";
			$studentresources = $srlink->fetchAll($srwhere, 'auto_id DESC');
		}
		if (isset($studentresources)) {			
			$studentresources = $studentresources->toArray();
		}
		
		//now add ratings data
		if(count($studentresources)>0)
			$studentresources = self::addRatingsToResources($studentresources,$ratingcategories);
		if($loid==null) {
			$that->view->showstudentresourceloid=true;
			$loFinder = new LearningObjectives();
			foreach($studentresources as $ind => $sr) {
				$tmploid=$sr['loid'];
				$lo = $loFinder->getLoByLOID($tmploid);
				$studentresources[$ind]['currentloid'] = $lo->latestReleasedVersionId();
			}
		}
		
		foreach($studentresources as $ind => $sr) {
			$studentresources[$ind]['description'] = stripslashes($studentresources[$ind]['description']);
		}
		$lo_has_summary = false;
		foreach($studentresources as $ind => $resource) {
			if($resource['mimetype'] == 'text/x-url' || $resource['mimetype'] == 'text/url') {
				$mediabankUtility = new MediabankUtility();
				ob_start();
				$mediabankUtility->curlDownloadResource($resource['mid'],false);
				$url = ob_get_contents();
				ob_end_clean();
				$studentresources[$ind]['url'] = trim($url);
			}
			$studentresources[$ind]['editable'] = false;
			if($resource['mimetype'] == 'text/html' && (($resource['uid'] == $myuid)||($resource['category']=='Summary')||($resource['collaborative']=='true')))
				$studentresources[$ind]['editable'] = $that->view->studyconsent; //true only if they have consented
			if($resource['category']=='Summary' && $loid != null) {
				$lo_has_summary = true;
				$that->view->studentsummary = $studentresources[$ind];
				unset($studentresources[$ind]);
			}
		}
		if($sortorder!='date')
			uasort($studentresources, array("StudentResourceService", "cmp_studentresource_rating"));
		$that->view->studentresources = $studentresources;
		
		$that->view->studentresourcesortorder = $sortorder;
		
		//prep stuff for student resources uploader
		$srcFinder = new StudentResourceCategories();
		$srcnames = $srcFinder->getAllNames();
		if($lo_has_summary)
			unset($srcnames[6]);
		ksort($srcnames);
		$that->view->studentResourceCategories = $srcnames;
		$that->view->hassummaryresource = $lo_has_summary;
		
		
	}
	/**
	 * method for sorting list of resources by rating 
	 */
	static function cmp_studentresource_rating ($a, $b) {
		if(@$b['score'] == @$a['score'])
			return(@$b['count'] - @$a['count']);
		return(@$b['score'] - @$a['score']);	
	}
	/**
	 * Decides whether the current user has access to the social tools
	 */
	public static function showSocialTools() {
		$config = Zend_Registry::get('config');
		// showsocialtools config entry can be "all"|"groups"|"consentgroups"|"no"; note that PHP converts "no" to ""
		$socialconf = $config->studentresources->showsocialtools;
		if($socialconf == "all")
			return(true);
		if($socialconf == "")
			return(false);
		$allowedgroups = $config->studentresources->allowedgroups->toArray();
		$uid=UserAcl::getUid();
		try {
    		$ds = Zend_Registry::get('ds');
    		$person = $ds->getUser($uid);
    		$mygroups = $person['groups'];
    		//print_r($person['groups']);
    		$intersect = array_intersect($mygroups, $allowedgroups);
    		return(count($intersect)>0);
		} catch (Exception $ex) {
		    //If logger does not work for any reason we don't want page to stop loading.
		    try {
    		    $error = $ex->getMessage().PHP_EOL.$ex->getTraceAsString().PHP_EOL;
    		    Zend_Registry::get('logger')->warn(PHP_EOL."CLASS\t: ".__CLASS__.PHP_EOL."METHOD\t: ".__METHOD__.PHP_EOL."Error\t: ".$error.PHP_EOL);
		    } catch (Exception $ex) {
		    }
		}
	    return false;
	}
	/**
	 * Decides whether the current user has consented to the study of the social tools (if they need to consent)
	 */
	public static function checkSocialToolConsent() {
		//check if it's in the session
		$config = Zend_Registry::get('config');
		$socialconf = $config->studentresources->showsocialtools;
		if($socialconf == "all" || $socialconf == "groups")
			return(true);
		if($socialconf == "")
			return(false);
		if(isset($_SESSION['COMPASS_SHARED']['social_study_consent'])) {
			return($_SESSION['COMPASS_SHARED']['social_study_consent']);
		} else {
			$uid=UserAcl::getUid();
			$consentchecker = new StudentSocialStudyConsent();
			$consentresults = $consentchecker->fetchAll("uid = '{$uid}'", 'auto_id DESC');
			$latestconsent = $consentresults->current();
			if($latestconsent==null) return(null);
			
			if($latestconsent->consent==1) {
				$_SESSION['COMPASS_SHARED']['social_study_consent']=true;
				return(true);
			}
			if($latestconsent->consent==-1) {
				$_SESSION['COMPASS_SHARED']['social_study_consent']=false;
				return(false);
			}
			return(null); //indeterminate result; send to consent form
		}
	}
	/**
	 * increments the download counter for the requested resource
	 */
	public static function incrementDownloadCount($mid) {
		$srlink = new StudentResourceLink();
		$where = $srlink->getAdapter()->quoteInto('mid = ?', $mid);
		$data = array('downloadcount'=>new Zend_Db_Expr('downloadcount + 1'));
		$srlink->update($data,$where);
	}
}
?>