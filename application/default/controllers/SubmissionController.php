<?php

class SubmissionController extends Zend_Controller_Action {

	/**
	 * Set up ACL info
	 */
	public function init() {
		$staffActions = array('index', 'loandtaform', 'viewloandta', 'rightmenu', 'submitloandta',
			'editloandta', 'status', 'saveloid', 'insertloid', 'savetaid', 'savelinkage', 'insertlinkage',
			'savenewlo', 'insertnewlo', 'inserttaid', 'deleteloandta');
		$allow_staff_to_submit_ta = Compass::getConfig('allow_staff_to_submit_ta');
		if ($allow_staff_to_submit_ta == true) {
			$staffActions[] = 'savenewta';
			$staffActions[] = 'insertnewta';
			$blockchairActions = array();
		} else {
			$blockchairActions = array('savenewta', 'insertnewta');
		}
		$stagecoordinatorActions = array('approveloandta', 'approveloandtacomplete', 'resubmitloandta');

		$this->_helper->_acl->allow('staff', $staffActions);
		$this->_helper->_acl->allow('blockchair', $blockchairActions);
		$this->_helper->_acl->allow('stagecoordinator', $stagecoordinatorActions);
	}

	/**
	 * Principal teacher can not create new teaching activity, that's why 'Add new ta' link is disabled.
	 * Last modified by jxie on 2009-10-23
	 */
	public function indexAction() {
		PageTitle::setTitle($this->view, $this->_request);
		$allow_staff_to_submit_ta = Compass::getConfig('allow_staff_to_submit_ta');
		$identity = Zend_Auth::getInstance()->getIdentity();
		if ($identity->role == 'staff' && !$allow_staff_to_submit_ta) {
			$this->view->newTaLinkClassName = 'disabled';
		} else {
			$this->view->newTaLinkClassName = 'enabled';
		}
	}

	/** Allows user to edit a sumbission before submitting it for approval 
	 * Last modified by jxie on 2010-09-17
	 */
	public function editloandtaAction() {
		$id = (int)$this->_request->getParam('id');
		PageTitle::setTitle($this->view, $this->_request, array($id));
		$linkFinder = new LinkageLoTas();
		$link = $linkFinder->getLinkageById($id);

		if (($result = $link->isEditable()) !== TRUE) {
			$this->view->errormsg = $result;
			$this->render('error');
			return;
		}
		$allow_staff_to_submit_ta = Compass::getConfig('allow_staff_to_submit_ta');
		$identity = Zend_Auth::getInstance()->getIdentity();
		if ($identity->role == 'staff' && !$allow_staff_to_submit_ta) {
			$this->view->newTaLink = false;
		} else {
			$this->view->newTaLink = true;
		}
		$fp = new FormProcessor_LoandtaSubmission($id);
		$this->view->fp = $fp;
	}

	/** Allows user to view a sumbission */
	public function viewloandtaAction() {
		$id = (int)$this->_request->getParam('id');
		PageTitle::setTitle($this->view, $this->_request, array($id));
		$linkFinder = new LinkageLoTas();
		$link = $linkFinder->getLinkageById($id);

		$this->view->link = $link;

		if (empty($link->ta_id)) {
			$this->view->ta = null;
		} else {
			$taFinder = new TeachingActivities();
			$this->view->ta = $taFinder->getTa($link->ta_id);
			$this->view->ta_status = 'Existing';
			if ($link->type == 'NT' || $link->type == 'TL' || $link->type == 'TA') {
				$this->view->ta_status = '<span style="color:red">New</span>';
			}
		}

		if (empty($link->lo_id)) {
			$this->view->lo = null;
		} else {
			$loFinder = new LearningObjectives();
			$this->view->lo = $loFinder->getLo($link->lo_id);
			$this->view->lo_status = 'Existing';
			if ($link->type == 'TL' || $link->type == 'LO' || $link->type == 'NL') {
				$this->view->lo_status = '<span style="color:red">New</span>';
			}
		}
	}

	public function loandtaformAction() {
		$this->setupLo();
		$this->setupTa();
		$this->setupLinkage();
	}

	private function setupLo() {
		$this->view->lotype = $this->_request->getParam('lotype');
		$this->view->lo_id = $this->_request->getParam('lo_id');
		$this->view->actionname = $this->_request->getParam('actionname');
			
		//Disciplines
		$disciplineService = new DisciplineService();
		$this->view->disciplines = $disciplineService->getListOfDisciplines();
		$this->view->discipline1 = $this->_request->getParam('discipline1');
		$this->view->discipline2 = $this->_request->getParam('discipline2');
		$this->view->discipline3 = $this->_request->getParam('discipline3');

		//Curriculum areas
		$curriculumAreasService = new CurriculumAreasService();
		$this->view->curriculumareas = $curriculumAreasService->getAllCurriculumAreas();
		$this->view->curriculumarea1 = $this->_request->getParam('curriculumarea1');
		$this->view->curriculumarea2 = $this->_request->getParam('curriculumarea2');
		$this->view->curriculumarea3 = $this->_request->getParam('curriculumarea3');

		//simple lookup
		$themeFinder = new Themes();
		$this->view->themes = $themeFinder->getAllNames();
		$this->view->theme1 = (int)($this->_request->getParam('theme1'));
		$this->view->theme2 = (int)($this->_request->getParam('theme2'));
		$this->view->theme3 = (int)($this->_request->getParam('theme3'));

		$skillFinder = new Skills();
		$this->view->skills = $skillFinder->getAllNames();
		$this->view->skill = (int)($this->_request->getParam('skill'));

		$systemFinder = new Systems();
		$this->view->systems = $systemFinder->getAllNames('seq_no ASC');
		$this->view->system = (int)($this->_request->getParam('system'));

		$scopeFinder = new LOScopes();
		$this->view->activities = $scopeFinder->getAllScopesArray();
		$this->view->activity = array_search($this->_request->getParam('activity'), $this->view->activities);

		$verbFinder = new LOVerbs();
		$this->view->abilities = $verbFinder->getAllVerbsArraySorted();
		$this->view->ability = array_search($this->_request->getParam('ability'), $this->view->abilities);

		$this->view->lo = $this->_request->getParam('lo');
		$this->view->checked = $this->_request->getParam('checked');

		//many to many relationships
		$reviewFinder = new Reviews();
		$this->view->reviews = $reviewFinder->getAllNames();
		$this->view->review = $this->_request->getParam('review');

		$this->view->keywords = $this->_request->getParam('keywords');

		$assesstypeFinder = new AssessTypes();
		$this->view->assesstypes = $assesstypeFinder->getAllNames();
		$this->view->assesstype = $this->_request->getParam('assesstype');

		$achievementFinder = new Achievements();
		$this->view->achievements = $achievementFinder->getAllNames();
		$this->view->achievement = (int)($this->_request->getParam('achievement'));

		$jmoFinder = new Jmos();
		$this->view->jmos = $jmoFinder->getAllNames();
		$this->view->jmo = (int)($this->_request->getParam('jmo'));

		$gradAttribFinder = new GradAttribs();
		$this->view->gradAttribs = $gradAttribFinder->getAllNames();
		$this->view->gradattrib = (int)($this->_request->getParam('gradattrib'));

		$this->view->lo_notes = $this->_request->getParam('lo_notes');
		$mediabankResourceType = new MediabankResourceType();
		$this->view->loResourceTypes = $mediabankResourceType->fetchAutoidResourceTypePair(array('lo' => 1));
	}
	
	private function setupTa() {
		$this->view->tatype = $this->_request->getParam('tatype');
		$this->view->ta_id = $this->_request->getParam('ta_id');

		$this->view->name = $this->_request->getParam('name');

		$typeFinder = new ActivityTypes();
		$this->view->types = $typeFinder->getAllNames();
		$this->view->type = (int)($this->_request->getParam('type'));

		$stageFinder = new Stages();
		$this->view->stages = $stageFinder->getAllStages();
		$this->view->stage = (int)($this->_request->getParam('stage'));
		
		$yearFinder = new Years();
		$this->view->years = $yearFinder->getAllYears();
		$this->view->year = (int)($this->_request->getParam('year'));

		$sbs = new StageBlockSeqs();
		$this->view->blocks = $sbs->getAllBlocks();
		$this->view->block = (int)($this->_request->getParam('block'));

		$blockwkFinder = new BlockWeeks();
		$this->view->block_weeks = $blockwkFinder->getAllWeeks();
		$this->view->block_week = (int)($this->_request->getParam('block_week'));

		$bps = new BlockPblSeqs();
		$this->view->pbls = $bps->getAllPbls();
		$this->view->pbl = (int)($this->_request->getParam('pbl'));

		$seqNumFinder = new SequenceNumbers();
		$this->view->sequence_nums = $seqNumFinder->getAllSequenceNumbers();
		$this->view->sequence_num = (int)($this->_request->getParam('sequence_num'));
		
		$termFinder = new Terms();
		$this->view->terms = $termFinder->getAllTerms();
		$this->view->term = (int)($this->_request->getParam('term'));
		
		$studentgrpFinder = new StudentGroups();
		$this->view->student_grps = $studentgrpFinder->getAllNames();
		$this->view->student_grp = (int)($this->_request->getParam('student_grp'));

		$this->view->principal_teacher = $this->_request->getParam('principal_teacher');
		$this->view->current_teacher = $this->_request->getParam('current_teacher');

		$this->view->ta_notes = $this->_request->getParam('ta_notes');
        
		$mediabankResourceType = new MediabankResourceType();
        $this->view->taResourceTypes = $mediabankResourceType->fetchAutoidResourceTypePair(array('ta' => 1));
	}

	private function setupLinkage() {
		$strengthFinder = new Strengths();
		$this->view->strengths = $strengthFinder->getAllNames();
		$this->view->strength = (int)($this->_request->getParam('strength'));
		$this->view->link_notes = $this->_request->getParam('link_notes');
	}

	/** Allows principal teacher and block chair to submit a learning objective/teaching activity for approval
	 *  Last modified by jxie on 2010-09-15
	 */
	public function submitloandtaAction() {
		$id = (int)$this->_request->getParam('id');
		PageTitle::setTitle($this->view, $this->_request);
		$linkFinder = new LinkageLoTas();
		$link = $linkFinder->getLinkageById($id);

		if (($result = $link->submitForApproval()) !== TRUE) {
			$this->view->errormsg = $result;
			$this->render('error');
		}
	}

	/** Stage coordinator and admin can send submission back to the author for modification 
	 *  Last modified by jxie on 2010-09-15 
	 */
	public function resubmitloandtaAction() {
		$link_id = (int)$this->_request->getParam('id');
		$linkFinder = new LinkageLoTas();
		$link = $linkFinder->getLinkageById($link_id);
		
		if (($result = $link->revertSubmission()) !== TRUE) {
			$this->view->errormsg = $result;
			$this->render('error');
			return;
		}
		$this->view->link_id = $link_id;
	}

	/** Allow stage coordinator, domain admin to approve submissions
	 * Last modified by jxie on 2010-12-06
	 */
	public function approveloandtaAction() {
		$link_id = (int)$this->_request->getParam('id');
		PageTitle::setTitle($this->view, $this->_request);
		$linkFinder = new LinkageLoTas();
		$link = $linkFinder->getLinkageById($link_id);

		if (($result = $link->approveNewSubmission()) !== TRUE) {
			$this->view->errormsg = $result;
			$this->render('error');
			return;
		}
		
		if ($link->approved_by == $link->created_by) {
			$this->view->approve_own = true;
		} else {
			// Send email to the user notify the approval
			$view = $this->getHelper('ViewRenderer')->view;
			$userdetail = Utilities::getUserNameAndEmail($link->created_by);
			$view->username = trim($userdetail['salutation'].' '.$userdetail['cn']);
			$view->link_id = $link_id;
			//Utilities::sendEmail($userdetail, $view->render('mail/approve.phtml'));
		}
	}

	/** Display available actions based on user's role
	 * Last modified by jxie on 2010-12-07
	 */
	public function rightmenuAction() {
		$id = $this->_request->getParam('id');
		$linkFinder = new LinkageLoTas();
		$link = $linkFinder->getLinkageById($id);

		if (empty($link->ta_id)) {
			$this->_helper->viewRenderer->setNoRender();
			return;
		}
		$taFinder = new TeachingActivities();
		$ta = $taFinder->getTa($link->ta_id);

		$identity = Zend_Auth::getInstance()->getIdentity();
		$actions = array();
		/** Stage coordinator can approve submissons */
		if (($identity->role == 'admin' || $identity->role == 'domainadmin' || ($identity->role == 'stagecoordinator' && in_array($ta->stageID, $identity->stages)))
			&& $link->status == Status::$AWAITING_APPROVAL) {
			$actions['approve'] = true;
			$actions['sendback'] = true;
			//$actions['reject'] = true;
		}

		/** Stage coordinator can approve archives */
		if (($identity->role == 'admin' || $identity->role == 'domainadmin' || ($identity->role == 'stagecoordinator' && in_array($ta->stageID, $identity->stages)))
			&& $link->new_status == Status::$ARCHIVED) {
			$actions['archive'] = true;
			//$actions['reject'] = true;
		}
		if (empty($actions)) {
			$this->_helper->viewRenderer->setNoRender();
		}
		$this->view->link_id = $id;
		$this->view->ta_id = $link->ta_id;
		$this->view->lo_id = $link->lo_id;
		$this->view->actions = $actions;
	}

	/** Get the status of LO, TA, and Linkage to be displayed on 'insert' and 'edit' page
	 * Last modified by jxie on 2009-10-29
	 */
	public function statusAction() {
		$this->getHelper('layout')->disableLayout();

		$link_id = (int)$this->_request->getParam('id');
		$linkFinder = new LinkageLoTas();
		$link = $linkFinder->getLinkageById($link_id);

		$resourceFinder = new MediabankResource();

		$lostatus = 'new';
		$tastatus = 'new';
		$data = array();
		$data['strength'] = $link->strength;

		if (empty($link->ta_id)) {  //User has not chosen an option for teaching activity
			$data['newtalink'] = 'Add New';
			$data['existingtalink'] = 'Choose Existing';
			$data['taresource'] = 'Resources';
		} else {
			$taFinder = new TeachingActivities();
			$ta = $taFinder->getTa($link->ta_id);

			$ta_resources_num = $resourceFinder->fetchAll("type='ta' AND type_id=".$link->ta_id)->count();
			$ta_resources_text = $ta_resources_num. ' Resource'. (($ta_resources_num > 2) ? 's' : '');

			if (!empty($ta->name)) {
				if (str_word_count($ta->name) > 5) {
					$words = explode(' ', $ta->name, 6);
					$words = array_slice($words, 0, 5);
					$short_title = join(' ', $words). ' ...'; 
				} else {
					$short_title = $ta->name;
				}
			} else {
				$short_title = '';
			}

			if ($link->type == 'NT' || $link->type == 'TA' || $link->type == 'TL') { //New teaching activity
				$missingFields = $ta->getMissingRequiredFields();
				if (empty($missingFields)) {  //All required fields are filled out
					$data['newtalink'] = "<b>New Teaching Activity</b> - {$ta->auto_id}<br/><b>Block:</b> {$ta->block}<br/><b>Type:</b> {$ta->type}<br/><b>Title:</b> $short_title";
					$tastatus = 'complete';
				} else {
					$data['newtalink'] = "<b>New Teaching Activity</b> (Incomplete)<br/><b>Block:</b> {$ta->block}<br/><b>Type:</b> {$ta->type}<br/><b>Title:</b> $short_title";
					$tastatus = 'incomplete';
				}
				$data['existingtalink'] = 'Choose Existing';
			} else if ($link->type == 'ET' || $link->type == 'LO' || $link->type == 'LK') { //Existing teaching activity
				$data['newtalink'] = 'Add New';
				$data['existingtalink'] = "<b>Existing Teaching Activity</b> - {$ta->auto_id}<br/><b>Block:</b> {$ta->block}<br/><b>Type:</b> {$ta->type}<br/><b>Title:</b> $short_title";
				$tastatus = 'existing';
			}
			$data['taresource'] = $ta_resources_text;
		}
			
		if (empty($link->lo_id)) {  //User has not chosen an option for learning objective
			$data['newlolink'] = 'Add New';
			$data['existinglolink'] = 'Choose Existing';
			$data['loresource'] = 'Resources';
		} else {
			$loFinder = new LearningObjectives();
			$lo = $loFinder->getLo($link->lo_id);
				
			$lo_resources_num = $resourceFinder->fetchAll("type='lo' AND type_id=".$link->lo_id)->count();
			$lo_resources_text = $lo_resources_num. ' Resource'. (($lo_resources_num > 2) ? 's' : '');
			
			if (!empty($lo->lo)) {
				$lo_without_tag = trim(strip_tags($lo->lo));
				$words = preg_split("/[\s]+/", $lo_without_tag, 6, PREG_SPLIT_NO_EMPTY);
				if (count($words) > 5) {
					$words = array_slice($words, 0, 5);
					$short_objective = join(' ', $words).' ...'; 
				} else {
					$short_objective = $lo_without_tag;
				}
			} else {
				$short_objective = '';
			}
				
				
			//New TA      |-- New LO      => TL
			//            |-- Existing LO => TA
			//Existing TA |-- New LO      => LO
			//            |-- Existing LO => LK
			if ($link->type == 'NL' || $link->type == 'LO' || $link->type == 'TL') { // New learning objective
				$missingFields = $lo->getMissingRequiredFields();
				if (empty($missingFields)) {  //Required fields are completeted.
					$data['newlolink'] = "<b>New Learning Objective</b> - {$lo->auto_id}<br/><b>Main Discipline:</b> {$lo->discipline1Name}<br/><b>".Zend_Registry::get('Zend_Translate')->_('Theme').":</b> {$lo->theme1}<br/><b>Objective:</b> $short_objective";
					$lostatus = 'complete';
				} else {
					$data['newlolink'] = "<b>New Learning Objective</b> (Incomplete)<br/><b>Main Discipline:</b> {$lo->discipline1Name}<br/><b>".Zend_Registry::get('Zend_Translate')->_('Theme').":</b> {$lo->theme1}<br/><b>Objective:</b> $short_objective";
					$lostatus = 'incomplete';
				}
				$data['existinglolink'] = 'Choose Existing';
			} else if ($link->type == 'EL' || $link->type == 'TA' || $link->type == 'LK'){ //Existing learning objective
				$data['newlolink'] = 'Add New';
				$data['existinglolink'] = "<b>Existing Learning Objective</b> - {$lo->auto_id}<br/><b>Main Discipline:</b> {$lo->discipline1Name}<br/><b>".Zend_Registry::get('Zend_Translate')->_('Theme').":</b> {$lo->theme1}<br/><b>Objective:</b> $short_objective";
				$lostatus = 'existing';
			}
			$data['loresource'] = $lo_resources_text;
		}
		$data['tastatus'] = $tastatus;
		$data['lostatus'] = $lostatus;
		$this->view->data = $data;
	}
	
	/** User selects an existing learning objective on the submission page 
	 * Last modified by jxie 2010-09-13
	 */
	public function insertloidAction() {
		$this->_helper->layout()->disableLayout();
		$this->_helper->viewRenderer->setNoRender();

		$lo_id = $this->_request->getParam('loid');
		$linkFinder = new LinkageLoTas();
		if (($result = $linkFinder->createLinkageUsingLoIdOK($lo_id)) === TRUE) {
			echo $linkFinder->addTaToExistingLo($lo_id);
		} else {
			echo $result;
		}
	}

	/**
	 * User changes the learning objective id on edit page
	 * Last modified by jxie on 2010-09-14
	 */
	public function saveloidAction() {
		$this->_helper->layout()->disableLayout();
		$this->_helper->viewRenderer->setNoRender();
		
		$link_id = (int)$this->_request->getParam('id');
		$lo_id = $this->_request->getParam('loid');
		
		$linkFinder = new LinkageLoTas();
		$link = $linkFinder->getLinkageById($link_id);
		// Check whether current user submitted this entry
		if ($link->created_by != Zend_Auth::getInstance()->getIdentity()->user_id) {
			echo "You can only edit your own submission.";
			exit();
		}
		if ($lo_id !== "{$link->lo_id}") {
			if (($result = $linkFinder->createLinkageUsingLoIdOK($lo_id)) !== TRUE) {
				echo $result;
				exit();
			}
			$link->saveLoId($lo_id);
		}
	}

	/** Pricipal teacher, block chair, stage coordinator and domain admin can change teaching activity id on edit page
	 * Last modified by jxie on 2010-12-06
	 */
	public function savetaidAction() {
		$this->_helper->layout()->disableLayout();
		$this->_helper->viewRenderer->setNoRender();
	  
		$ta_id = $this->_request->getParam('taid');
		$link_id = (int)$this->_request->getParam('id');
		$linkFinder = new LinkageLoTas();
		$link = $linkFinder->getLinkageById($link_id);
		
		// Check whether current user submitted this entry
		if ($link->created_by != Zend_Auth::getInstance()->getIdentity()->user_id) {
			echo "You can only edit your own submission.";
			exit();
		}
		
		if ($ta_id !== "{$link->ta_id}") {
			if (($result = $linkFinder->createLinkageUsingTaIdOK($ta_id)) !== TRUE) {
				echo $result;
				exit();
			}
			$link->saveTaId($ta_id);
		}
	}

	/**
	 *  Pricipal teacher, block chair, stage coordinator and domain admin can choose an existing teaching activity on the submission page
	 *  Last modified by jxie on 2010-12-06
	 */
	public function inserttaidAction() {
		$this->_helper->layout()->disableLayout();
		$this->_helper->viewRenderer->setNoRender();
		
		$ta_id = $this->_request->getParam('taid');
		$linkFinder = new LinkageLoTas();
		if (($result = $linkFinder->createLinkageUsingTaIdOK($ta_id)) === TRUE) {
			echo $linkFinder->addLoToExistingTa($ta_id);
		} else {
			echo $result;
		}
	}

	/** 
	 * User edits linkage info and notes on the edit page 
	 * Last modified by jxie on 2010-09-14
	 */
	public function savelinkageAction() {
		$this->_helper->layout()->disableLayout();
		$this->_helper->viewRenderer->setNoRender();

		$link_id = (int)$this->_request->getParam('id');
		$linkFinder = new LinkageLoTas();
		$link = $linkFinder->getLinkageById($link_id);

		// Check whether current user submitted this entry
		if ($link->created_by != Zend_Auth::getInstance()->getIdentity()->user_id) {
			echo "You can only edit your own submission.";
			exit();
		}

		$linkFinder->updateLinkageLinkInfo($link, $this->_request);
	}

	/**
	 * User provides linkage info and notes on the submission page
	 * Last modified by jxie on 2010-09-14
	 */
	public function insertlinkageAction() {
		$this->_helper->layout()->disableLayout();
		$this->_helper->viewRenderer->setNoRender();

		$linkFinder = new LinkageLoTas();
		echo $linkFinder->createNewLinkageWithLinkInfo($this->_request);
	}

	/**
	 * User can edit learning objective on submssion page
	 * Last modified by jxie on 2010-10-11
	 */
	public function savenewloAction() {
		$this->_helper->layout()->disableLayout();
		$this->_helper->viewRenderer->setNoRender();

		$link_id = (int)$this->_request->getParam('id');
		$linkFinder = new LinkageLoTas();
		$link = $linkFinder->getLinkageById($link_id);

		$identity = Zend_Auth::getInstance()->getIdentity();
		$user_id = $identity->user_id;
		if ($link->created_by != $user_id) {
			echo "You can only edit your own submission.";
			exit();
		}
		
		$linkFinder->updateLinkageLoInfo($link, $this->_request);
	}

	/**
	 * User choose to create a new learning objective on the submission page
	 * Last modified by jxie on 2010-11-10
	 */
	public function insertnewloAction() {
		$this->_helper->layout()->disableLayout();
		$this->_helper->viewRenderer->setNoRender();
		
		$linkFinder = new LinkageLoTas();
		echo $linkFinder->createNewLinkageWithNewLo($this->_request);
	}

	/**
	 * Block chair, stage coordinator and domain amdin can edit teaching activity on the edit page
	 * Last modified by jxie on 2010-12-06
	 */
	public function savenewtaAction() {
		$this->_helper->layout()->disableLayout();
		$this->_helper->viewRenderer->setNoRender();

		$link_id = (int)$this->_request->getParam('id');
		$linkFinder = new LinkageLoTas();
		$link = $linkFinder->getLinkageById($link_id);

		$identity = Zend_Auth::getInstance()->getIdentity();
		$user_id = $identity->user_id;
		if ($link->created_by != $user_id) {
			echo "You can only edit your own submission.";
			exit();
		}

		//Block chair can only create teaching activity within his/her own block
		$this->isTaInMyBlock($identity, (int)$this->_request->getParam('block'));

		//stage coordinator can only create teacing activity with his/her own stage
		$this->isTaInMyStage($identity, (int)$this->_request->getParam('stage'));

		$linkFinder->updateLinkageTaInfo($link, $this->_request);
	}

	/**
	 * Block chair, stage coordinator and domain admin can create new teaching activity on submission page
	 * Last modified by jxie on 2010-12-06
	 */
	public function insertnewtaAction() {
		$this->_helper->layout()->disableLayout();
		$this->_helper->viewRenderer->setNoRender();

		$identity = Zend_Auth::getInstance()->getIdentity();

		//Block chair can only create teaching activity within his/her own block
		$this->isTaInMyBlock($identity, (int)$this->_request->getParam('block'));

		//stage coordinator can only create teacing activity within his/her own stage
		$this->isTaInMyStage($identity, (int)$this->_request->getParam('stage'));

		$linkFinder = new LinkageLoTas();
		echo $linkFinder->createNewLinkageWithNewTa($this->_request);
	}

	/** Allows user to delete his/her own sumbission
	 * Last modified by jxie on 2010-09-14
	 */
	public function deleteloandtaAction() {
		$id = (int)$this->_request->getParam('id');
		PageTitle::setTitle($this->view, $this->_request, array($id));
		$linkFinder = new LinkageLoTas();
		$link = $linkFinder->getLinkageById($id);

		$identity = Zend_Auth::getInstance()->getIdentity();
		if ($link->created_by != $identity->user_id) {
			$this->view->errormsg = "You can only delete your own submission.";
			$this->render('error');
			return;
		}

		$status = $link->status;
		if ($status != Status::$IN_DEVELOPMENT) {
			$this->view->errormsg = "You can only delete submission that is still in development.";
			$this->render('error');
			return;
		}
		
		$ta = null;
		if ($link->ta_id == null) {
			$this->view->block = 0;
		} else {
			$taFinder = new TeachingActivities();
			$ta = $taFinder->getTa($link->ta_id);
			$this->view->block = $ta->blockID;
		}
		
		$lo = null;
		if ($link->lo_id != null) {
			$loFinder = new LearningObjectives();
			$lo = $loFinder->getLo($link->lo_id);
		}
		
		if ($this->getRequest()->isPost()) {
			if ('yes' === $this->_request->getPost("sure")) {
				if ($link->type == 'NT' || $link->type == 'TA') {
					$ta->delete();
				} else if ($link->type == 'NL' || $link->type == 'LO') {
					$lo->delete();
				} else if ($link->type == 'TL') {
					$ta->delete();
					$lo->delete();
				}
				$link->delete();
			}
			$block = $this->_request->getPost("block");
			if ($block == '0') {
				$this->_redirect("/workflow/viewownunknownta");
			} else {
				$this->_redirect("/workflow/viewownblock/id/{$block}/status/2");
			}
			exit();
		}
	}

	//If user is a block chair, check whether the teaching activity is within his/her block
	private function isTaInMyBlock($identity, $block_id) {
		if ($identity->role == 'blockchair' && !in_array($block_id, $identity->blocks)) {
			echo "You can only create teaching activity in your own block.";
			exit();
		}
	}

	//If user is a stage coordinator, check whether the teaching activity is within his/her stage
	private function isTaInMyStage($identity, $stage_id) {
		if ($identity->role == 'stagecoordinator' && !in_array($stage_id, $identity->stages)) {
			echo "You can only create teaching activity in your own stage.";
			exit();
		}
	}
}
