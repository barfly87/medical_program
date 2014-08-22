<?php

require_once "htmlpurifier/HTMLPurifier.standalone.php";
class StudentresourceController extends Zend_Controller_Action {
	/**
	 * Set up ACL info
	 */
	public function init() {
		$readActions = array('index', 'upload','showmoreresources','setrating','updaterating','studentresourcesmall', 'recent', 'editresource','history','showquestion','compile','questioncompleted','quiz');
		$this->_helper->_acl->allow('student', $readActions);
	}

	/**
	 * Index action - nothing yet
	 */
	public function indexAction() {
		PageTitle::setTitle($this->view, $this->_request);
		$this->view->title = 'Student Resources';
	}

	/**
	 * Allows a user to upload a photo
	 * Last modified by daniel on 2011-12-23
	 */
	public function uploadAction() {
		//print_r($_FILES);
		if(!isset($_REQUEST['loid'])) {
			echo "Upload failed - form did not successfully submit. Possibly exceeded max upload time of ".ini_get("max_input_time").", or file size larger than ".min(ini_get("upload_max_filesize"),ini_get("post_max_size")).".<br>";
			echo "<a href=\"javascript:back();\">Back</a>";
			exit();
		}
		$xmlService = new XMLService();
		$config = HTMLPurifier_Config::createDefault();
		$purifier = new HTMLPurifier($config);
		$resourcetype = $_REQUEST["resourcetype"];
		$dir = '/tmp';// We'll need this, below
		if($resourcetype == "file") {
			$filename = $_FILES["resourcefile"]["tmp_name"];
			if($_FILES["resourcefile"]["size"] > 15728640) { // error if file large than 15 megs
				echo "Error uploading file; file exceeds upload limit of 15 megabytes";
				echo "<a href=\"javascript:back();\">Back</a>";
				exit();
			}
		} elseif($resourcetype == "url") {
			$filecontent = $_REQUEST["resourceurl"];
			$filename =  $dir.'/srurl'.UserAcl::getUid().time().'.url';
			file_put_contents($filename, $filecontent);
		} elseif($resourcetype == "question") {
			$questioncontent = array('question' => array(
				'question' => $purifier->purify(stripslashes($_REQUEST["resourcequestion"])),
				'option1' => $purifier->purify(stripslashes($_REQUEST["resourcequestionoption1"])),
				'option2' => $purifier->purify(stripslashes($_REQUEST["resourcequestionoption2"])),
				'option3' => $purifier->purify(stripslashes($_REQUEST["resourcequestionoption3"])),
				'option4' => $purifier->purify(stripslashes($_REQUEST["resourcequestionoption4"])),
				'option5' => $purifier->purify(stripslashes($_REQUEST["resourcequestionoption5"])),
				'explanation' => $purifier->purify(stripslashes($_REQUEST["resourcequestionexplanation"])),
				'correct' => $purifier->purify(stripslashes($_REQUEST["resourcequestioncorrect"]))
				));
			$questionxml = $xmlService->createXMLfromArray($questioncontent);
			$filename =  $dir.'/srquestion'.UserAcl::getUid().time().'.xml';
			file_put_contents($filename, $questionxml);
			
		} else { //it's text
			$filecontent = $purifier->purify(stripslashes($_REQUEST["resourcetext"]));
			$filename =  $dir.'/srtext'.UserAcl::getUid().time().'.html';
			file_put_contents($filename, $filecontent);
		}
		$srcFinder = new StudentResourceCategories();
		$categories = $srcFinder->getAllNames();
		$categoryname = $categories[$_REQUEST['resourcecategory']];
		$config = Zend_Registry::get('config');
		$cid = $config->studentresources->resourcecollection;
		$ident = Zend_Auth::getInstance()->getIdentity();
		$copyright='O';
		if($_REQUEST['copyright'] == 'mine')
			$copyright='M';
		if($_REQUEST['copyright'] == 'notmine')
			$copyright='N';
			
		if($_REQUEST['collaborative']=="on")
			$collaborative="true";
		else
			$collaborative="false";
		if($_REQUEST['private']=="on")
			$private="true";
		else
			$private="false";
			
		$metadata =array("studentresources" => array(
			"uid" => UserAcl::getUid(),
			"cn" => $ident->firstname[0].' '.$ident->lastname[0],
			"givenname" => $ident->firstname[0],
			"sn" => $ident->lastname[0],
			"cohort" => "user", //fix this
			"datecreated" => time(),
			"description" => $purifier->purify($_REQUEST['description']),
			'category' => $categoryname,
			'collaborative' => $collaborative,
			'private' => $private,
			'copyright' => $copyright,
			'copyrightother' => $_REQUEST['copyrightowner']
			));
		if(isset($_REQUEST['replaceresourceid'])) {
			$metadata['studentresources']['previous_version_id'] = $_REQUEST['replaceresourceid'];
		}
			
		$xml = $xmlService->createXMLfromArray($metadata);
        $metafile =  $dir.'/srmeta'.UserAcl::getUid().time();
        file_put_contents($metafile, $xml);
		$postData = array(
			"cid" => $cid,
			"metadataFile" => '@'.$metafile,
			"dataFile0" => '@'.$filename
			);
		$mediabankUtility = new MediabankUtility();
		$mid = $mediabankUtility->addResource($postData);
		
		if(strlen($mid)>255) {
			echo "There was a problem uploading your file. The file format is probably unsupported. Please try again.<br>";
			echo "<a href=\"javascript:back();\">Back</a>";
			echo $mid;
			exit();
		}
		if(trim(strlen($mid))==0) {
			echo "There was a problem uploading your file. Please try again.<br>";
			echo "<a href=\"javascript:back();\">Back</a>";
			echo "server file size was ".$_FILES["resourcefile"]["size"]."<br>";
			print_r($_FILES);
			exit();
		}
		$mrs = new MediabankResourceService();
		$midmetadata = $mrs->getMetadata($mid);
		$mimetype = $midmetadata['decodedMimeType'];
		//now add the link
		$link = new StudentResourceLink();
		$linkcontents = $metadata['studentresources'];
		$linkcontents['loid'] = $_REQUEST['loid'];
		$linkcontents['mid'] = $mid;
		$linkcontents['mimetype'] = $mimetype;
		//echo '|'.$mid.'|';exit();
		$newresourceid=-1;
		try {
			$newresourceid = $link->insert($linkcontents);
		} catch (Exception $ex) {
			echo $ex;
			print_r($linkcontents);
			exit();
		}
		unlink($metafile);

		//if there is a 'replaceresourceid' param, then delete the link to the old resource after adding the new one
		if(isset($_REQUEST['replaceresourceid'])) {
			$srlink = new StudentResourceLink();
			$where = $srlink->getAdapter()->quoteInto('auto_id = ?', $_REQUEST['replaceresourceid']);
			$data = array('archived'=> 1);
			$srlink->update($data, $where);			

			// ... and update ratings and comments 
			$ratingFinder = new Ratings();
			$ratingscoreFinder = new RatingScores();
			$where = array();
			$where[] = $ratingFinder->getAdapter()->quoteInto('resource_id = ?', $_REQUEST['replaceresourceid']);
			$data = array('resource_id'=> $newresourceid);
			$ratingFinder->update($data,$where);
			$ratingscoreFinder->update($data,$where);
		}
		//redirect
		$redirect_to = $_REQUEST['redirect_to'];
		if(strlen($redirect_to)<1) $redirect_to=$_SERVER["HTTP_REFERER"];
		//$this->_redirect($redirect_to);
		$this->getResponse()->setRedirect($redirect_to);
		$this->getResponse()->sendResponse();
		ob_end_flush();
		flush();
		if (session_id()) session_write_close();
		
		// update Lucene
		$linkFinder = new LinkageLoTas();
		$loid = $_REQUEST['loid'];
		$loFactory = new LearningObjectives();
		$lo = $loFactory->getLo($loid);
		$latestid = $lo->latestReleasedVersionId();
		$rows = $linkFinder->fetchAll("lo_id = $latestid");
		foreach ($rows as $row) {
			$row->notifyObservers('post-update');
		}
		
		
	}
	/**
	 * Sets the rating score on a student resource
	 */
	public function setratingAction() {
		$this->_helper->layout()->disableLayout();
		
		$uid=UserAcl::getUid();
		$resource_id = $this->_request->getParam('id');
		$rating = $this->_request->getParam('rating');
	
		$ratingscoreFinder = new RatingScores();
		//if no comment has been sent through, grab the old comment to preserve it
		$params = $this->_request->getParams();
		$oldresourcewhere = array();
		$oldresourcewhere[] = $ratingscoreFinder->getAdapter()->quoteInto('resource_id = ?', $resource_id);
		$oldresourcewhere[] = $ratingscoreFinder->getAdapter()->quoteInto('uid = ?', $uid);
	
		$commentresult = $ratingscoreFinder->fetchAll($oldresourcewhere);
		$comment=$commentresult[0]['comment'];
		
		//delete existing ratings
		$ratingscoreFinder->delete($oldresourcewhere);
	
		//add new ratings
		$data = array('uid'=>$uid,'resource_id'=>$resource_id,'rating'=>$rating, 'comment'=>$comment);
		$ratingscoreFinder->insert($data);

		$this->view->rating = $rating;
		/*
		$srlFinder = new StudentResourceLink();
		$resource = $srlFinder->fetchAll("auto_id={$resource_id}", 'auto_id DESC');
		$resource = $resource->toArray();
		$resource = $resource[0];
		$loid = $resource['loid'];
		$this->_helper->layout()->disableLayout();
		StudentResourceService::prepStudentResourceView($this, $this->_request,  $loid);
		$this->view->studentresources[] = $this->view->studentsummary;
		foreach($this->view->studentresources as $tmpresource) {
			//print_r($tmpresource);
			if($tmpresource['auto_id'] == $resource_id) {
				$this->view->studentresource = $tmpresource;
				break;
			}
		}*/
	}
	
	/**
	 * Sets details about a rating on a student resource
	 */
	public function updateratingAction() {
		$uid=UserAcl::getUid();
		$resource_id = $this->_request->getParam('id');
		$ratings = $this->_request->getParam('ratings');
		$comment = base64_decode(strtr($this->_request->getParam('comment'),'_','/'));
		$ratings = explode(',',$ratings);
		foreach($ratings as $ind => $rating) {
			$ratings[$ind] = trim($rating);
			if(strlen($ratings[$ind])==0)
				unset($ratings[$ind]);
		}
		$rcFinder = new RatingCategories();
		$ratingCategories = $rcFinder->fetchAll() ->toArray();
		foreach($ratingCategories as $rc) {
			$ratingCategories[$rc['name']] = $rc;
		}

		
		$ratingFinder = new Ratings();
		$ratingscoreFinder = new RatingScores();
		
		//preserve score - this is set in the "setrating" action, not here in "updaterating"
		$params = $this->_request->getParams();
		$oldresourcewhere = array();
		$oldresourcewhere[] = $ratingFinder->getAdapter()->quoteInto('resource_id = ?', $resource_id);
		$oldresourcewhere[] = $ratingFinder->getAdapter()->quoteInto('uid = ?', $uid);

		$oldresource = $ratingscoreFinder->fetchAll($oldresourcewhere);
		$score = $oldresource[0]['rating'];
		//if no comment has been sent through, grab the old comment to preserve it
		if(!isset($params['comment'])) {
			$comment=$oldresource[0]['comment'];
		}
		
		//delete existing ratings
		$ratingFinder->delete($oldresourcewhere);		
		$ratingscoreFinder->delete($oldresourcewhere);		
		
		//add new ratings
		//$score=0;
		foreach($ratings as $rating) {
			$rc = $ratingCategories[$rating];
			//$score += $rc['rating'];
			$data = array('uid'=>$uid,'resource_id'=>$resource_id,'rating'=>$rc['auto_id']);
			$ratingFinder->insert($data);
		}
		if(count($ratings)>0 || strlen(trim($comment))>0) {
			//if($score>=0) $score=1; else $score=-1;
			//if(count($ratings)==0) $score=0;
			$data = array('uid'=>$uid,'resource_id'=>$resource_id,'rating'=>$score, 'comment'=>$comment);
			$ratingscoreFinder->insert($data);
		}
		$srlFinder = new StudentResourceLink();
		$resource = $srlFinder->fetchAll("auto_id={$resource_id}", 'auto_id DESC');
		$resource = $resource->toArray();
		$resource = $resource[0];
		$loid = $resource['loid'];
		$this->_helper->layout()->disableLayout();
		StudentResourceService::prepStudentResourceView($this, $this->_request,  $loid);
		$this->view->studentresources[] = $this->view->studentsummary;
		foreach($this->view->studentresources as $tmpresource) {
			//print_r($tmpresource);
			if($tmpresource['auto_id'] == $resource_id) {	
				$this->view->studentresource = $tmpresource;
				break;
			}
		}
	}
	/**
	 * Shows the student-generated resources for an LO on a TA
	 */
	public function studentresourcesmallAction() {
		$this->_helper->layout()->disableLayout();
		$loid = $this->_request->getParam('id');
		$this->view->includeJquery = $this->_request->getParam('jquery','yes');
		$this->view->loid = $loid;
		if(StudentResourceService::showSocialTools()) {
			StudentResourceService::prepStudentResourceView($this, $this->_request,  $loid);
			$this->view->socialtools = true;
		} else {
			$this->view->socialtools = false;
		}
	}
	/**
	 * Shows the most recent student-generated resources
	 */
	public function recentAction() {
		$this->_helper->layout()->disableLayout();
		if(strlen($this->_request->getParam('count')>0))
			$count = $this->_request->getParam('count');
		else
			$count=10;
		$ident = Zend_Auth::getInstance()->getIdentity();
		$uids=null;
		if($ident->role == "student") {
			$config = Zend_Registry::get('config');
			$domain = strtolower($ident->domain);
			$grpprefix = $config->groupprefix->$domain;
			$gid=$grpprefix.$ident->cohort;
			$ds = Zend_Registry::get('ds');
			$group = $ds->getGroup($gid);
			$users = array();
			foreach($group['members'] as $member) {
				$memberdetails = LdapCache::getUserDetails($member);
				if(!strncmp($memberdetails['givenname'][0],'Virtual',6))
					continue;
				if($memberdetails['uid'][0]=='smpbind')
					continue;
				
				$users[] = $memberdetails;
			}
			$this->view->members = $users;
			$uids = array();
			foreach($users as $user) {
				$uids[] = $user['uid'][0];
			}
		}
		
		if(StudentResourceService::showSocialTools()) {
			StudentResourceService::prepStudentResourceView($this, $this->_request,  null, $uids, $count);
			$this->view->socialtools = true;
		} else {
			$this->view->socialtools = false;
		}
		$this->view->widthhint=30;
		$this->render("studentresourcelarge");
	}
	/**
	 * Allows editing of a resource
	 */
	public function editresourceAction() {
		$resourceid = $this->_request->getParam('resourceid');
		$srlink = new StudentResourceLink();
		$select = $srlink->select()->where($srlink->getAdapter()->quoteInto('auto_id = ?', $resourceid));
		$rows = $srlink->fetchAll($select);
		$therow = null;
		foreach ($rows as $row) {
			$therow=$row;
		}
		//print_r($therow);
		$this->view->resourcelink = $therow;
		
		$mediabankUtility = new MediabankUtility();
		ob_start();
		$mediabankUtility->curlDownloadResource($therow['mid'],false);
		$content = ob_get_contents();
		ob_end_clean();
		$this->view->content = $content;		
		$this->view->redirect_to = $_SERVER["HTTP_REFERER"];
		StudentResourceService::prepStudentResourceView($this, $this->_request,  $therow->loid);
		if($therow['category']=='Summary') //summary has been stripped out since a summary exists; add it back in if this one is a summary
			$this->view->studentResourceCategories[6]='Summary';
	}
	/**
	 * Shows the history for a resource
	 */
	public function historyAction() {
		$this->_helper->layout()->disableLayout();
		$resourceid = $this->_request->getParam('id');
		$versions = StudentResourceService::getPreviousVersions($resourceid);
		$this->view->versions = $versions;
	}	
	/**
	 * Displays a question
	 */
	public function showquestionAction() {
		$this->_helper->layout()->disableLayout();
		$mid = MediabankResourceConstants::sanitizeMid($this->_getParam('mid',''));
		$mediabankUtility = new MediabankUtility();
		ob_start();
		$mediabankUtility->curlDownloadResource($mid,false);
		$content = ob_get_contents();
		ob_end_clean();
		$xmlService = new XMLService();
		$question = $xmlService->createArrayFromXml($content);		
		$this->view->question = $question;		
		$this->view->midencoded = $this->_getParam('mid','');
		$this->view->id = $this->_getParam('id','');
		$this->view->showstats = $this->_getParam('hidestats','')==null || $this->_getParam('hidestats','')=='false';
		$qs = new QuestionScores();
		$select = $qs->select()->from(array('qs'=>'questionscores'), array('count' => 'count(*)', 'choice' => 'choice'))->where($qs->getAdapter()->quoteInto('mid = ?', $mid))->group("choice");
		$rows = $qs->fetchAll($select);
		$qresults = array();
		$totalresponses=0;
		foreach($rows->toArray() as $arr) {
			$qresults[$arr['choice']] = $arr['count'];
			$totalresponses += $arr['count'];
		}
		$this->view->qstats = $qresults;
		$this->view->qstatstotal = $totalresponses;
	}	
	/**
	 * Stores the user's response to a question
	 */
	public function questioncompletedAction() {
		$this->_helper->layout()->disableLayout();
		$mid = MediabankResourceConstants::sanitizeMid($this->_getParam('mid',''));
		$correct = $this->_getParam('correct','');
		$choice = $this->_getParam('choice','');
		$uid=UserAcl::getUid();
		$response = array(
			"uid" => $uid,
			"choice" => $choice,
			"correct" => $correct,
			"mid" => $mid,
			"rtime" => time()
			);
		$qs = new QuestionScores();
		$this->view->resp = $qs->insert($response);
		
		//Now send the stats back
		
		//all time
		$select = $qs->select()->from(array('qs'=>'questionscores'), array('avg' => 'avg(CASE WHEN choice=correct THEN 1 ELSE 0 END)', 'numcorrect' => 'sum(CASE WHEN choice=correct THEN 1 ELSE 0 END)', 'totalresponses' => 'count(*)'))->where($qs->getAdapter()->quoteInto('uid = ?', $uid));
		$rows = $qs->fetchAll($select);
		foreach ($rows as $row) {
			$therow=$row;
		}
		$this->view->avg = $therow['avg'];
		$this->view->numcorrect = $therow['numcorrect'];
		$this->view->totalresponses = $therow['totalresponses'];
		
		//by week
		$select = $qs->select()->from(array('qs'=>'questionscores'), array('avg' => 'avg(CASE WHEN choice=correct THEN 1 ELSE 0 END)', 'numcorrect' => 'sum(CASE WHEN choice=correct THEN 1 ELSE 0 END)', 'totalresponses' => 'count(*)', 'weekstart' => '(604800 * floor((rtime+259200)/604800)-259200)'))->where($qs->getAdapter()->quoteInto('uid = ?', $uid))->group('(604800 * floor((rtime+259200)/604800)-259200)');
		$rows = $qs->fetchAll($select);
		$tmpweeks = $rows->toArray();
		ksort($tmpweeks);
		$min = $rows[0]['weekstart'];
		$max = $rows[0]['weekstart'];
		$weeks = array();
		$this->view->midencoded = strtr(base64_encode($mid),array('='=>''));
		foreach($tmpweeks as $week) {
			if($week['weekstart']<$min) $min = $week['weekstart'];
			if($week['weekstart']>$max) $max = $week['weekstart'];
			$weeks[$week['weekstart']] = $week;
		}
		$continueavg= $rows[0]['avg'];
		for($i=$min;$i<$max;$i+=604800) {
			if(isset($weeks[$i]))
				$continueavg = $week[$i]['avg'];
			else
				$weeks[$i] = array('weekstart'=>$i,'numcorrect'=>0, 'avg'=>$continueavg,'totalresponses'=>0);
		}
		ksort($weeks);
		$this->view->byweekresults = $weeks;
		//select count(*), 604800 * floor(rtime/604800) from questionscores where uid='daniel' group by floor(rtime/604800);
				
	}	
	
	/**
	 * Allows a student to compile student resources for a problem or block
	 */
	public function compileAction() {
		$probid = $this->_request->getParam('problem');
		if(!empty($probid)) {
			$explode = explode(".",$probid);
			$pblBlock = (int)$explode[0];
			$pblBlockWeek = (int)$explode[1];
			$sbs = new StageBlockSeqs();
			$pblBlock = $sbs->getBlockId($pblBlock);
			$this->view->summaryfor = "Problem ".$probid;
		}
		$blockid = $this->_request->getParam('block');
		if(!empty($blockid)) {
			$sbs = new StageBlockSeqs();
			$pblBlock = $sbs->getBlockId($blockid);
			$this->view->summaryfor = "Block ".$blockid;
			$pblBlockWeek = null;
			
		}
		$indexer = Compass_Search_Lucene::open(SearchIndexer::getIndexDirectory());
		$querystr = SearchQueryService::getReleaseDateQuery($pblBlock,$pblBlockWeek);
		//echo $querystr;exit();
		$LOs = $indexer->find($querystr,'lo_discipline_ids');
		$loids = array();
		$lodata = array();
		foreach ($LOs as $LO) {
			//print_r($LO);exit();
			$loids[] = $LO->lo_loid;
			$lodata[$LO->lo_loid]['title'] = $LO->lo_title;
			//echo $LO->lo_title.'<br><hr>';
		}
		StudentResourceService::prepStudentResourceView($this, $this->_request, $loids);
		foreach($this->view->studentresources as $sr) {
			$lodata[$sr['loid']]['studentresources'][] = $sr;
		}
		
		$this->view->lodata = $lodata;
		
	}	
	/**
	 * Allows a student to do a series of questions as a quiz
	 */
	public function quizAction() {
		StudentResourceService::prepStudentResourceView($this, $this->_request, null, null, 100);
		// check if we're currently in a quiz
		if($this->_request->getParam('quizid')!=null) {
			$quizid = $this->_request->getParam('quizid');
		} else {
			 $firstresource = reset($this->view->studentresources);
			 $quizid = $firstresource['auto_id'];
		}
		// now get scores
		$quizids = $this->_request->getParam('quizids');
		$quizids = explode(',',$quizids);
		//prevent SQL injections
		foreach($quizids as $key => $val)
			if(!is_numeric($val)) unset($quizids[$key]);
		
		$quizmids = array();
		foreach($quizids as $tmpquizid) {
			$quizmids[$tmpquizid] = "'".$this->view->studentresources[$tmpquizid]['mid']."'";
		}
		
		$qs = new QuestionScores;
		$select = $qs->select()->where("uid = '".UserAcl::getUid()."'")->where('mid in ('.implode(',',$quizmids).')')->order("auto_id"); //probably should change this to only return the latest response to each question
		$scores = $qs->fetchAll($select);
		$scores = $scores->toArray();
		$midscores = array();
		foreach($scores as $score) {
			$midscores[$score['mid']] = ($score['correct']==$score['choice'])?1:0;
		}
		foreach($this->view->studentresources as $key => $res)
			if(isset($midscores[$res['mid']]))
				$this->view->studentresources[$key]['qscore'] = $midscores[$res['mid']];
		
		$this->view->quizid = $quizid;
		$this->view->quizids = $quizids;
		if($this->_request->getParam('complete')=="true") {
			$this->view->quizcomplete = true;
		} else {
			$this->view->currentquestion = $this->view->studentresources[$quizid];	
		}
		if($this->_request->getParam('return')==null)
			$this->view->return = base64_encode($_SERVER['HTTP_REFERER']);
		else 
			$this->view->return = $this->_request->getParam('return');
	}	
}
