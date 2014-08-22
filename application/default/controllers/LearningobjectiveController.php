<?php

class LearningobjectiveController extends Zend_Controller_Action {

	/**
	 * Set up ACL info
	 */
	public function init() {
		$readActions = array('index', 'view', 'loform');
		$writeActions = array('edit', 'editcomplete');
		$blockchairActions = array('archive', 'archivecomplete');
		$stagecoordinatorActions = array('archivelolink', 'archivelolinkcomplete', 'add', 'addcomplete', 'approve', 'approvecomplete');
		$this->_helper->_acl->allow('student', $readActions);
		$this->_helper->_acl->allow('staff', $writeActions);
		$this->_helper->_acl->allow('blockchair', $blockchairActions);
		$this->_helper->_acl->allow('stagecoordinator', $stagecoordinatorActions);
	}

	/**
	 * Dislay 5 recently submitted LOs and total number of LOs in compass
	 */
	public function indexAction() {
		PageTitle::setTitle($this->view, $this->_request);
		$this->view->title = 'Learning Objective Overview';
		$loFinder = new LearningObjectives();
		$this->view->objectives = $loFinder->fetchLatest(5);
		$this->view->lo_total = count($loFinder->fetchAll());
	}

	/**
	 * Display details of a learning objective based on the id in the request
	 * Last modified by jxie on 2009-10-28
	 */
	public function viewAction() {
		$id = (int)$this->_request->getParam('id');
		$loFinder = new LearningObjectives();
		$lo = $loFinder->getLo($id);
		
		$latest_flag = ($this->_getParam('latest') == 'f') ? false : true;
		$lastest_lo_id = $lo->latestReleasedVersionId();
		if ($lastest_lo_id != $id && !UserAcl::isDomainAdminOrAbove() && $latest_flag) {
			$this->_redirect('/learningobjective/view/id/'.$lastest_lo_id);
			return;
		}
		
		PageTitle::setTitle($this->view, $this->_request, array($id));
		$released_tas = $lo->getReleasedTa();
		if (count($released_tas) == 0 && UserAcl::isStudent()) {
			throw new Exception("Learning objective $id is not released yet.");
		}
		
		$this->view->released_tas = $released_tas;
		$this->view->lo = $lo;
		
		$resourceError = false;
		try {
			$resourceService = new MediabankResourceService();
			$resources = $resourceService->getResources($id, 'lo');
			$allowAddResources = $resourceService->allowAdd();
		} catch(Exception $ex){
			$resourceError = true;
			$resources = array();
			$allowAddResources = false;
		}
		$this->view->allowAddResources = $allowAddResources;
		$this->view->resourceError = $resourceError;
		$this->view->resources = $resources;
		
        //Check if user is a staff
		$this->view->isStaffOrAbove = UserAcl::isStaffOrAbove();
        //Store ACL for each resource action which can be accessed by the current user
        //And allow/disallow those actions. 
        $this->view->resourceAcl = ResourceAcl::accessAll(array('type'=>'lo','auto_id'=>$id));
        
		$revisions = $loFinder->fetchAll("loid={$lo->loid}", 'auto_id DESC');
		$this->view->revisions = $revisions;
		$this->view->loid = $lo->loid;
		$this->view->title = 'Learning Objective - '.$lo->auto_id;
		$exambankService = new ExambankService();
		$this->view->loExambankQuestions = $exambankService->getNumberOfQuestionsByLO($lo->auto_id);
		
		if(StudentResourceService::showSocialTools()) {
			StudentResourceService::prepStudentResourceView($this, $this->_request, $lo->loid);
			$this->view->socialtools = true;
		} else {
			$this->view->socialtools = false;
		}
		
	}

	/**
	 * Allow stage coordinator to add a new learning objective. It will not be indexed by default.
	 * * Last modified by jxie on 2009-10-28
	 */
	public function addAction() {
		$request = $this->getRequest();
		PageTitle::setTitle($this->view, $request);
		$fp = new FormProcessor_LearningObjective();
		if ($request->isPost()) {
			if ($id = $fp->process($request)) {
				$session = new Zend_Session_Namespace('loaddcomplete');
				$session->lo_id = $id;
				$this->_redirect('/learningobjective/addcomplete');
			}
		}
		$this->view->fp = $fp;
	}

	/**
	 * Confirmation page that tells user a new LO has been created
	 * Last modified by jxie on 2009-10-28
	 */
	public function addcompleteAction() {
		PageTitle::setTitle($this->view, $this->_request);
		$session = new Zend_Session_Namespace('loaddcomplete');
		if (!$session->lo_id) {
			$this->_redirect('/learningobjective/add');
			return;
		}
		$loFinder = new LearningObjectives();
		$this->view->lo = $loFinder->fetchRow('auto_id='.$session->lo_id);
		unset($session->lo_id);
		$this->view->title = 'Thank you for submitting the new learning objective';
	}


	/**
	 * Edit an existing learning objective, a new version will be created as a result.
	 * Principal teacher and block chair can propose a new version.
	 * Stage coordinator and above can create a new version immediately without approval.
	 * last modified by jxie on 2010-12-13
	 */
	public function editAction() {
		$request = $this->getRequest();
		$id = (int)$request->getParam('id');
		PageTitle::setTitle($this->view, $request, array($id));
		$loFinder = new LearningObjectives();
		$lo = $loFinder->getLo($id);

		//check if we have any linkage related to this LO that needs approval
		//LO is editable only when there are no requests of any kind that needs approval.
		if (($result = $lo->isEditable()) !== TRUE) {
			throw new Exception($result);
		}
		
		if (($result = UserAcl::checkLoPermission($lo, UserAcl::$EDIT)) !== true) {
			throw new Exception($result);
		}
		
		$fp = new FormProcessor_LearningObjective($id);
		if ($request->isPost()) {
			if ($id = $fp->process($request)) {
				$session = new Zend_Session_Namespace('loeditcomplete');
				$session->lo_id = $id;
				$this->_redirect('/learningobjective/editcomplete');
			}
		}
		$this->view->fp = $fp;
	}


	/**
	 * Confirmation page that tells user editing is successful
	 * Last modified by jxie on 2009-10-28
	 */
	public function editcompleteAction() {
		PageTitle::setTitle($this->view, $this->_request);
		$session = new Zend_Session_Namespace('loeditcomplete');
		if (!$session->lo_id) {
			$this->_redirect('/learningobjective/add');
			return;
		}
		$this->view->lo_id = $session->lo_id;
		unset($session->lo_id);
	}

	/**
	 * Allow stage coordinator and above to approve new version of learning objective
	 * Last modified by jxie on 2010-12-13
	 */
	public function approveAction() {
		$request = $this->getRequest();
		$lo_id = (int)($request->getParam('id'));
		$ta_id = (int)($request->getParam('taid'));

		$loFinder = new LearningObjectives();
		$lo = $loFinder->getLo($lo_id);
		
		$taFinder = new TeachingActivities();
		$ta = $taFinder->getTa($ta_id);
	
		$linkFinder = new LinkageLoTas();
		$link = $linkFinder->getLinkageByLoAndTaId($lo_id, $ta_id);
		if ($link->status != Status::$NEW_VERSION) {
			throw new Exception("linkage between learning objective $lo_id and teaching activity $ta_id does not need approval");
		}
		
		if (($result = UserAcl::checkApprovalPermission($ta)) !== true) {
			throw new Exception($result);
		}
		
		$link->approveNewLoVersion();

		$session = new Zend_Session_Namespace('loapprovecomplete');
		$session->lo_id = $lo_id;
		$session->ta_id = $ta_id;
		$this->_redirect("/learningobjective/approvecomplete");
	}

	/**
	 * Confirmation page that tells user approval is successful
	 * Last modified by jxie on 2009-10-28
	 */
	public function approvecompleteAction() {
		PageTitle::setTitle($this->view, $this->_request);
		$session = new Zend_Session_Namespace('loapprovecomplete');
		if (!$session->lo_id || !$session->ta_id) {
			$this->_redirect('/learningobjective/index');
			return;
		}
		$this->view->lo_id = $session->lo_id;
		unset($session->lo_id);
		$this->view->ta_id = $session->ta_id;
		unset($session->ta_id);
	}

	public function loformAction() {
		$this->view->lo_auto_id = $this->_request->getParam('lo_auto_id');
		 
		$disciplineService = new DisciplineService();
		$this->view->disciplines = $disciplineService->getListOfDisciplines();
		$curriculumAreasService = new CurriculumAreasService();
		$this->view->curriculumareas = $curriculumAreasService->getAllCurriculumAreas();
		$this->view->discipline1 = (int)$this->_request->getParam('disc1');
		$this->view->discipline2 = (int)$this->_request->getParam('disc2');
		$this->view->discipline3 = (int)$this->_request->getParam('disc3');
		$this->view->curriculumarea1 = $this->_request->getParam('curriculumarea1');
		$this->view->curriculumarea2 = $this->_request->getParam('curriculumarea2');
		$this->view->curriculumarea3 = $this->_request->getParam('curriculumarea3');

		$themeFinder = new Themes();
		$this->view->themes = $themeFinder->getAllNames();
		$this->view->theme1 = (int)$this->_request->getParam('theme1');
		$this->view->theme2 = (int)$this->_request->getParam('theme2');
		$this->view->theme3 = (int)$this->_request->getParam('theme3');

		$skillFinder = new Skills();
		$this->view->skills = $skillFinder->getAllNames();
		$this->view->skill = (int)$this->_request->getParam('skill');

		$systemFinder = new Systems();
		$this->view->systems = $systemFinder->getAllNames('seq_no ASC');
		$this->view->system = (int)$this->_request->getParam('system');

		$scopeFinder = new LOScopes();
		$this->view->activities = $scopeFinder->getAllScopesArray();
		$this->view->activity = array_search($this->_request->getParam('activity'), $this->view->activities);

		$verbFinder = new LOVerbs();
		$this->view->abilities = $verbFinder->getAllVerbsArraySorted();
		$this->view->ability = array_search($this->_request->getParam('ability'), $this->view->abilities);

		$this->view->lo = $this->_request->getParam('lo');
		$this->view->checked = $this->_request->getParam('checked');

		$reviewFinder = new Reviews();
		$this->view->reviews = $reviewFinder->getAllNames();
		$reviewArr = explode(', ', $this->_request->getParam('review'));
		$this->view->review = $reviewArr;

		$this->view->keywords = $this->_request->getParam('keywords');

		$assesstypeFinder = new AssessTypes();
		$this->view->assesstypes = $assesstypeFinder->getAllNames();
		$assesstypeArr = explode(', ', $this->_request->getParam('assesstype'));
		$this->view->assesstype = $assesstypeArr;

		$achievementFinder = new Achievements();
		$this->view->achievements = $achievementFinder->getAllNames();
		$this->view->achievement = (int)$this->_request->getParam('achievement');

		$jmoFinder = new Jmos();
		$this->view->jmos = $jmoFinder->getAllNames();
		$this->view->jmo = (int)$this->_request->getParam('jmo');

		$gradAttribFinder = new GradAttribs();
		$this->view->gradAttribs = $gradAttribFinder->getAllNames();
		$this->view->gradattrib = (int)$this->_request->getParam('gradattrib');

		$this->view->notes = $this->_request->getParam('notes');

		$linked_tas_old = $this->_request->getParam('linked_tas_old');
		$this->view->linked_tas_old_arr = empty($linked_tas_old) ? array() : explode(', ', $linked_tas_old);

		$linked_tas_new = $this->_request->getParam('linked_tas_new');
		$this->view->linked_tas_new_arr = empty($linked_tas_new) ? array() : explode(', ', $linked_tas_new);
	}

	/** Allow block chair and stage coordinator to archive a learning objective.
	 *  Block chair can only propose, which will then be approved by stage coordinator(s).
	 *  Stage coordinator can archive the linkages to TA(s) in his/her stage immediately.
	 */
	public function archiveAction() {
		$request = $this->getRequest();
		$id = (int)$request->getParam('id');
		PageTitle::setTitle($this->view, $request, array($id));
		$loFinder = new LearningObjectives();
		$lo = $loFinder->getLo($id);

        if (($result = $lo->isEditable()) !== TRUE) {
        	throw new Exception($result);
        }

        if (($result = UserAcl::checkLoPermission($lo, UserAcl::$ARCHIVE)) !== TRUE) {
        	throw new Exception($result);
        }

		if ($request->isPost()) {
			$do_archive = ('yes' === $request->getParam('sure'));
			if (!$do_archive) {
				$this->_redirect("/learningobjective/view/id/$id");
				exit();
			}
			$session = new Zend_Session_Namespace('loarchivecomplete');
			$session->lo_id = $id;
			$session->all_archived = $lo->archive();
			$this->_redirect("/learningobjective/archivecomplete");
		} else {
			$this->view->lo = $lo;
			$this->view->linkedtas = $linkedtas;
		}
	}

	/** Displays a thank you page for archive action
	 *  Last modified by jxie on 2009-10-28
	 */
	public function archivecompleteAction() {
		PageTitle::setTitle($this->view, $this->_request);
		$session = new Zend_Session_Namespace('loarchivecomplete');
		if (!$session->lo_id) {
			$this->_redirect('/index');
			return;
		}
		$this->view->lo_id = $session->lo_id;
		$this->view->all_archived = $session->all_archived;
		unset($session->lo_id);
		unset($session->all_archived);
	}

	/** Allow stage coordinator to archive a linkage between learning objective and teaching activity.
	 *  Last modified by jxie on 2011-01-25
	 */
	public function archivelolinkAction() {
		$request = $this->getRequest();
		$id = (int)$request->getParam('id');
		$taid = (int)$request->getParam('taid');
		PageTitle::setTitle($this->view, $request, array($id, $taid));
		
		$loFinder = new LearningObjectives();
		$lo = $loFinder->getLo($id);

		$taFinder = new TeachingActivities();
		$ta = $taFinder->getTa($taid);
		
		if (($result = UserAcl::checktaPermission($ta, UserAcl::$APPROVE)) !== TRUE) {
			throw new Exception($result);
		}

		$statusFinder = new Status();
		$status_id = $statusFinder->getIdForStatus(Status::$ARCHIVED);
		
		$linkFinder = new LinkageLoTas();
		$link = $linkFinder->fetchRow("lo_id=$id AND ta_id=$taid AND new_status=$status_id");
		if (empty($link)) {
			throw new Exception("Could not find linkage between learning objective $id and teaching activity $taid.");
		}
		
		if ($request->isPost()) {
			$do_archive = ('yes' === $request->getParam('sure'));
			if (!$do_archive) {
				$url = "/learningobjective/view/id/$id";
				$this->_redirect($url);
				exit();
			}
			
			$link->approveArchive();
			$session = new Zend_Session_Namespace('archivelolinkcomplete');
			$session->lo_id = $id;
			$session->ta_id = $taid;
			$this->_redirect("/learningobjective/archivelolinkcomplete");
		} else {
			$this->view->lo = $lo;
			$this->view->ta = $ta;
		}
	}

	/** Displays a success message to the user.
	 *  Last modified by jxie on 2009-10-14
	 */
	public function archivelolinkcompleteAction() {
		PageTitle::setTitle($this->view, $this->_request);
		$session = new Zend_Session_Namespace('archivelolinkcomplete');
		if (!$session->lo_id) {
			$this->_redirect('/index');
			return;
		}
		$this->view->lo_id = $session->lo_id;
		$this->view->ta_id = $session->ta_id;
		unset($session->lo_id);
		unset($session->ta_id);
	}
}
