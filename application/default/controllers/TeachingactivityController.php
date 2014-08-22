<?php

class TeachingactivityController extends Zend_Controller_Action {

	public function init() {
		$ajaxContext = $this->_helper->getHelper('AjaxContext');
		$ajaxContext->addActionContext('blocksforstage', 'json');
		$ajaxContext->addActionContext('weeksforblock', 'json');
		$ajaxContext->addActionContext('pblsforstage', 'json');
		$ajaxContext->addActionContext('pblsforblock', 'json');
		$ajaxContext->addActionContext('pblforweek', 'json');
		$ajaxContext->addActionContext('weekforpbl', 'json');
		$ajaxContext->addActionContext('allweeks', 'json');
		$ajaxContext->addActionContext('allpbls', 'json');
		$ajaxContext->initContext();

		$readActions = array('view', 'taform', 'blocksforstage', 'pblsforstage', 'pblsforblock', 'pblforweek',
        				'weeksforblock', 'weekforpbl', 'allweeks', 'allpbls', 'oldview', 'resourcesforevents',
						'losforevents','evaluationforevents', 'yearsforstage', 'blocksforyear');
		$writeActions = array('edit', 'editcomplete', 'addnotes', 'editreviewedby', 'handbookview');
		$blockchairActions = array('archive', 'archivecomplete');
		$stagecoordinatorActions = array('approve', 'approvecomplete', 'archivetalink', 'archivetalinkcomplete', 
						'orderlo', 'saveorder');
		$this->_helper->_acl->allow('student', $readActions);
		$this->_helper->_acl->allow('staff', $writeActions);
		$this->_helper->_acl->allow('blockchair', $blockchairActions);
		$this->_helper->_acl->allow('stagecoordinator', $stagecoordinatorActions);
	}

	public function editreviewedbyAction() {
		$this->_helper->viewRenderer->setNoRender();
		$this->_helper->layout()->disableLayout();
		$id = $this->_getParam('taid');
		$taFinder = new TeachingActivities();
		$ta = $taFinder->getTa($id);
		
		$isMine = ($this->_getParam('reviewedby_select') === 'own');
		if ($isMine) {
			$uid = Zend_Auth::getInstance()->getIdentity()->user_id;
		} else {
			$uid = stripslashes(strip_tags(trim($this->_getParam('reviewedby_uid'))));
		}
		if (!empty($uid)) {
			$ta->reviewed_by = $uid;
			$ta->date_reviewed = date('Y-m-d H:i:s');
			$ta->save();
			$linkFinder = new LinkageLoTas();
			$linkFinder->updateIndexForTa($id);
			echo $ta->date_reviewed, "|", UserService::getUidFullName($ta->reviewed_by);
		} else {
			echo "error";
		}
	}
	
	/** Allow staff member to add notes to a teaching activity */
	public function addnotesAction() {
		$this->_helper->viewRenderer->setNoRender();
		$this->_helper->layout()->disableLayout();
		$id = $this->_getParam('taid');
		$taFinder = new TeachingActivities();
		$ta = $taFinder->getTa($id);
		$notes = stripslashes(strip_tags($this->_getParam('value')));
		$ta->notes = preg_replace('/[\r\n]+/', ' ', $notes);
		$ta->save();
		echo $ta->notes;
	}

	/** Display details of teaching activity based on id */
	public function viewAction() {
		$id = $this->_getParam('id');
		$taFinder = new TeachingActivities();
		$ta = $taFinder->getTa($id);
		
		$latest_flag = ($this->_getParam('latest') == 'f') ? false : true;
		$latest_ta_id = $ta->latestReleasedVersionId();
		if ($latest_ta_id != $id && !UserAcl::isDomainAdminOrAbove() && $latest_flag) {
			$this->_redirect('/teachingactivity/view/id/'.$latest_ta_id);
			return;
		}
		$this->view->debugdate = $this->_getParam('debugdate');
		$layout = $this->_getParam('layout', '');

		PageTitle::setTitle($this->view, $this->_request, array($id));

		if ($ta->isReleased() != true) {
			throw new Exception("Teaching activity $id is not released yet.");
		}

		$this->view->ta = $ta;
		$this->view->revisions = $taFinder->getTaRevisions($ta->taid);
		$this->view->released_los = $ta->getLinkedLearningObjectiveWithStatus(Status::$RELEASED);
		
		$this->view->showGenericEvaluation = EvaluateTaConst::showGenericEvaluation($ta->typeID);
		
		if(UserAcl::isAdmin() && $this->view->showGenericEvaluation === true) {
    		$studentEvaluateService = new StudentEvaluateService();
    		$this->view->taEvaluations = $studentEvaluateService->getEvaluationForTaId($id);
    		$studentEvaluate = new StudentEvaluate();
    		$this->view->taEvaluationAvg = $studentEvaluate->getRatingAvg('ta', $id);
		}

		$this->view->display_edit_links = UserAcl::isStaffOrAbove() && UserAcl::checkTaPermission($ta, UserAcl::$EDIT) === true && count($this->view->released_los) > 0;
		$this->view->resourceAcl = ResourceAcl::accessAll(array('type'=>'ta','auto_id'=>$id));
		$this->view->resources = $ta->resources;
		
		$this->view->pblTaTypePrev = '';
		$this->view->pblTaTypeNext = '';
		
	    if(!empty($layout)) {
            switch($layout) {
                case 'pblview': 
                    $this->view->pblTaTypePrev = $this->_request->getParam('pblTaTypePrev','');
                    $this->view->pblTaTypeNext = $this->_request->getParam('pblTaTypeNext','');
                    //$this->render('pblview');
                break;
            }
        }
		$this->view->socialtools = StudentResourceService::showSocialTools();
	}
	
	/**
	 * View details of teaching activity based on its id
	 */
	public function oldviewAction() {
		$id = (int)$this->_request->getParam('id');
		PageTitle::setTitle($this->view, $this->_request, array($id));
		$debugdate = $this->_request->getParam('debugdate');
		$layout = trim($this->_request->getParam('layout',''));
		
		$taFinder = new TeachingActivities();
		$ta = $taFinder->getTa($id);
		
		if ($ta->isReleased() != true) {
			throw new Exception("Teaching activity $id is not released yet.");
		}
		
		$this->view->ta = $ta;

		$resourceError = false;
		try {
			$resourceService = new MediabankResourceService();
			$resources = $resourceService->getResources($id, 'ta');
			$allowAddResources = $resourceService->allowAdd();
		} catch (Exception $ex) {
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
		$this->view->resourceAcl = ResourceAcl::accessAll(array('type'=>'ta','auto_id'=>$id));

		$this->view->revisions = $taFinder->getTaRevisions($ta->taid);
		$this->view->released_los = $ta->getLinkedLearningObjectiveWithStatus(Status::$RELEASED);
		$this->view->title = 'Teaching Activity - '.$ta->auto_id;
		
		$studentEvaluateService = new StudentEvaluateService();
		$this->view->taEvaluations = $studentEvaluateService->getEvaluationForTaId($id);

		$this->view->isMyTa = Utilities::isMyTa($ta);
		$this->view->debugdate = $debugdate;

        if(!empty($layout)) {
            switch($layout) {
                case 'pblview': 
                    $this->view->pblTaTypePrev = $this->_request->getParam('pblTaTypePrev','');
                    $this->view->pblTaTypeNext = $this->_request->getParam('pblTaTypeNext','');
                    $this->render('pblview');
                break;
            }
        }
	}

	/**
	 * Allow stage coordinator and above to approve new version of teaching activity
	 */
	public function approveAction() {
		$request = $this->getRequest();
		$ta_id = (int)($request->getParam('id'));
		$lo_id = (int)($request->getParam('loid'));

		$taFinder = new TeachingActivities();
		$ta = $taFinder->getTa($ta_id);
		if (($result = UserAcl::checkTaPermission($ta, UserAcl::$APPROVE)) !== true) {
			throw new Exception($result);
		}

		$linkFinder = new LinkageLoTas();
		$link = $linkFinder->getLinkageByLoAndTaId($lo_id, $ta_id);
		if (!$link->isNewVersionRequest()) {
			throw new Exception("Teaching activity $ta_id does not need approval.");
		}

		$link->approveNewTaVersion();
		
		$session = new Zend_Session_Namespace('taapprovecomplete');
		$session->ta_id = $ta_id;
		$session->lo_id = $lo_id;
		$this->_redirect('/teachingactivity/approvecomplete');
	}

	/**
	 * Confirmation page that tells user approval is successful
	 * Last modified by jxie on 2009-10-27
	 */
	public function approvecompleteAction() {
		PageTitle::setTitle($this->view, $this->_request);
		$session = new Zend_Session_Namespace('taapprovecomplete');
		if (!$session->ta_id || !$session->lo_id) {
			$this->_redirect('/teachingactivity/index');
			return;
		}
		$this->view->ta_id = $session->ta_id;
		unset($session->ta_id);
		$this->view->lo_id = $session->lo_id;
		unset($session->lo_id);
	}

	/**
	 * Edit an existing teaching activity, a new version will be created as a result.
	 * Principal teacher and block chair can propose a new version.
	 * Stage coordinator and above can create a new version immediately without approval.
	 */
	public function editAction() {
		$request = $this->getRequest();
		$id = (int)$request->getParam('id');
		PageTitle::setTitle($this->view, $request, array($id));
		
		$taFinder = new TeachingActivities();
		$ta = $taFinder->getTa($id);

		if (($result = $ta->isEditable()) !== true) {
			throw new Exception($result);
		}

		if (($result = UserAcl::checkTaPermission($ta, UserAcl::$EDIT)) !== true) {
			throw new Exception($result);
		}

		$fp = new FormProcessor_TeachingActivity($id);
		if ($request->isPost()) {
			if ($id = $fp->process($request)) {
				$session = new Zend_Session_Namespace('taeditcomplete');
				$session->ta_id = $id;
				$this->_redirect('/teachingactivity/editcomplete');
			}
		}
		$this->view->fp = $fp;
	}

	/**
	 * Confirmation page that tells user that editing is successful
	 * Last modified by jxie on 2009-10-27
	 */
	public function editcompleteAction() {
		PageTitle::setTitle($this->view, $this->_request);
		$session = new Zend_Session_Namespace('taeditcomplete');
		if (!$session->ta_id) {
			$this->_redirect('/teachingactivity/add');
			return;
		}
		$this->view->ta_id = $session->ta_id;
		unset($session->ta_id);
	}

	public function taformAction() {
		$this->view->ta_auto_id = $this->_request->getParam('ta_auto_id');

		$this->view->name = $this->_request->getParam('name');

		$typeFinder = new ActivityTypes();
		$this->view->types = $typeFinder->getAllNames();
		$this->view->type = (int)$this->_request->getParam('type');

		$stageFinder = new Stages();
		$this->view->stages = $stageFinder->getAllStages();
		$this->view->stage = (int)$this->_request->getParam('stage');

		$yearFinder = new Years();
		$this->view->years = $yearFinder->getAllYears();
		$this->view->year = (int)$this->_request->getParam('year');
		
		$blockFinder = new Blocks();
		$this->view->blocks = $blockFinder->getAllNames('auto_id ASC');
		$this->view->block = (int)$this->_request->getParam('block');

		$blockwkFinder = new BlockWeeks();
		$this->view->weeks = $blockwkFinder->getAllWeeks();
		$this->view->block_week = (int)$this->_request->getParam('block_week');

		$bps = new BlockPblSeqs();
		$this->view->pbls = $bps->getAllPbls();
		$this->view->pbl = (int)$this->_request->getParam('pbl');

		$seqNumFinder = new SequenceNumbers();
		$this->view->nums = $seqNumFinder->getAllSequenceNumbers();
		$this->view->sequence_num = (int)$this->_request->getParam('sequence_num');
		
		$termFinder = new Terms();
		$this->view->terms = $termFinder->getAllTerms();
		$this->view->term = (int)$this->_request->getParam('term');

		$studentgrpFinder = new StudentGroups();
		$this->view->grps = $studentgrpFinder->getAllNames();
		$this->view->student_grp = (int)$this->_request->getParam('student_grp');

		$this->view->principal_teacher = $this->_request->getParam('principal_teacher');
		$this->view->current_teacher = $this->_request->getParam('current_teacher');

		$linked_los_old = $this->_request->getParam('linked_los_old');
		$this->view->linked_los_old_arr = empty($linked_los_old) ? array() : explode(', ', $linked_los_old);

		$linked_los_new = $this->_request->getParam('linked_los_new');
		$this->view->linked_los_new_arr = empty($linked_los_new) ? array() : explode(', ', $linked_los_new);

		$this->view->archive_option = $this->_request->getParam('archive_option');
	}

	public function blocksforstageAction() {
		$stage_id = (int)($this->_request->getParam('stage_id'));
		$sbs = new StageBlockSeqs();
		$this->view->blocks = $sbs->getBlocksForStage($stage_id);
	}

	public function weeksforblockAction() {
		$stage_id = (int)($this->_request->getParam('stage_id'));
		$block_id = (int)($this->_request->getParam('block_id'));
		$bps = new BlockPblSeqs();
		$this->view->weeks = $bps->getWeeksForStageBlock($stage_id, $block_id);
	}

	public function pblsforblockAction() {
		$stage_id = (int)($this->_request->getParam('stage_id'));
		$block_id = (int)($this->_request->getParam('block_id'));
		$bps = new BlockPblSeqs();
		$this->view->pbls = $bps->getPblsForStageBlock($stage_id, $block_id);
	}
	
	public function pblsforstageAction() {
		$stage_id = (int)($this->_request->getParam('stage_id'));
		$bps = new BlockPblSeqs();
		$this->view->pbls = $bps->getPblsForStage($stage_id);
	}

	public function pblforweekAction() {
		$stage_id = (int)($this->_request->getParam('stage_id'));
		$block_id = (int)($this->_request->getParam('block_id'));
		$week_id = (int)($this->_request->getParam('week_id'));
		$bps = new BlockPblSeqs();
		$this->view->pbl = $bps->getPblForStageBlockWeek($stage_id, $block_id, $week_id);
	}

	public function weekforpblAction() {
		$stage_id = (int)($this->_request->getParam('stage_id'));
		$block_id = (int)($this->_request->getParam('block_id'));
		$pbl_id = (int)($this->_request->getParam('pbl_id'));
		$bps = new BlockPblSeqs();
		$this->view->week = $bps->getWeekForStageBlockPbl($stage_id, $block_id, $pbl_id);
	}

	public function allweeksAction() {
		$weekFinder = new BlockWeeks();
		$this->view->weeks = $weekFinder->getAllWeeks();
	}

	public function allpblsAction() {
		$bps = new BlockPblSeqs();
		$this->view->pbls = $bps->getAllPbls();
	}

	/** Allow block chair and stage coordinator to archive a teaching activity.
	 *  Block chair can only propose, which will then be approved by stage coordinator(s).
	 *  Stage coordinator can archive the teaching activity immediately.
	 */
	public function archiveAction() {
		$request = $this->getRequest();
		$id = (int)$request->getParam('id');
		PageTitle::setTitle($this->view, $request, array($id));
		
		$taFinder = new TeachingActivities();
		$ta = $taFinder->getTa($id);

		if (($result = $ta->isEditable()) !== true) {
			throw new Exception($result);
		}

		if (($result = UserAcl::checkTaPermission($ta, UserAcl::$ARCHIVE)) !== true) {
			throw new Exception($result);
		}

		if ($request->isPost()) {
			$do_archive = ('yes' === $request->getParam('sure'));
			if (!$do_archive) {
				$this->_redirect('/teachingactivity/view/id/'.$id);
				exit();
			}

			$ta->archive();
			$session = new Zend_Session_Namespace('taarchivecomplete');
			$session->ta_id = $id;
			$this->_redirect("/teachingactivity/archivecomplete");
		} else {
			$this->view->ta = $ta;
			$this->view->linkedlos = $ta->getLinkedLearningObjectiveWithStatus(Status::$RELEASED);
		}
	}

	/**
	 * Confirmation page that tells user that archiving is successful
	 */
	public function archivecompleteAction() {
		PageTitle::setTitle($this->view, $this->_request);
		$session = new Zend_Session_Namespace('taarchivecomplete');
		if (!$session->ta_id) {
			$this->_redirect('/index');
			return;
		}
		$taFinder = new TeachingActivities();
		$this->view->ta = $taFinder->fetchRow('auto_id='.$session->ta_id);
		unset($session->ta_id);
	}

	/**
	 * Allow stage coordinator and above to archive a linkage between teaching activity and learning objective.
	 */
	public function archivetalinkAction() {
		$request = $this->getRequest();
		$ta_id = (int)$request->getParam('id');
		$lo_id = (int)$request->getParam('loid');
		PageTitle::setTitle($this->view, $request, array($ta_id, $lo_id));
		
		$taFinder = new TeachingActivities();
		$ta = $taFinder->getTa($ta_id);
		if (($result = UserAcl::checkTaPermission($ta, UserAcl::$ARCHIVE)) !== true) {
			throw new Exception($result);
		}
			
		$linkFinder = new LinkageLoTas();
		$link = $linkFinder->getLinkageByLoAndTaId($lo_id, $ta_id);
		if (!$link->isArchiveRequest()) {
			throw new Exception("Teaching activity $ta_id does not need archive approval.");
		}

		if ($request->isPost()) {
			$do_archive = ('yes' === $request->getParam('sure'));
			if (!$do_archive) {
				$url = "/teachingactivity/view/id/$ta_id";
				$this->_redirect($url);
				exit();
			}

			$link->archive();
			$session = new Zend_Session_Namespace('archivetalinkcomplete');
			$session->lo_id = $lo_id;
			$session->ta_id = $ta_id;
			$this->_redirect("/teachingactivity/archivetalinkcomplete");
		} else {
			$this->view->lo = $lo;
			$this->view->ta = $ta;
		}
	}

	/**
	 * Displays a success message to the user.
	 * last modified by jxie on 2009-10-19
	 */
	public function archivetalinkcompleteAction() {
		PageTitle::setTitle($this->view, $this->_request);
		$session = new Zend_Session_Namespace('archivetalinkcomplete');
		if (!$session->ta_id) {
			$this->_redirect('/index');
			return;
		}
		$this->view->lo_id = $session->lo_id;
		$this->view->ta_id = $session->ta_id;
		unset($session->lo_id);
		unset($session->ta_id);
	}
	
	public function orderloAction() {
		$request = $this->getRequest();
		$ta_id = (int)$request->getParam('id');
		$taFinder = new TeachingActivities();
		$ta = $taFinder->getTa($ta_id);
		
		$disc_id = (int)$request->getParam('disc');
		$area_id = (int)$request->getParam('area');
		
		$this->view->ta = $ta;
		$this->view->released_los = $ta->getReleasedLearningObjectiveInDisciplineAndArea($disc_id, $area_id);
	}
	
	public function saveorderAction() {
		$this->_helper->viewRenderer->setNoRender();
		$this->_helper->layout()->disableLayout();
		$ta_id = $this->_getParam("ta_id");
		$linkFinder = new LinkageLoTas();
		foreach ($_GET['listItem'] as $position => $item) {
			$linkFinder->updateLinkageOrder($item, $ta_id, $position + 1);
		}
	}
	
	public function resourcesforeventsAction() {
	    $taId = (int)$this->_getParam('taid', 0);
	    if ($taId > 0) {
	        $teachingActivities = new TeachingActivities();
	        $ta = $teachingActivities->getTa($taId);
	        $latest_ta_id = $ta->latestReleasedVersionId();
	        if ($latest_ta_id != $taId) {
	        	$ta = $teachingActivities->getTa($latest_ta_id);
	        }
	        $this->view->ta = $ta;
	        $this->view->resources = $ta->resources;
	        $this->_helper->layout()->setLayout('popup');
	    } else {
	       $this->throwError();
	    }
	}
	
	public function losforeventsAction() {
	    $taId = (int)$this->_getParam('taid', 0);
	    if ($taId > 0) {
	        $teachingActivities = new TeachingActivities();
	        $ta = $teachingActivities->getTa($taId);
	        $latest_ta_id = $ta->latestReleasedVersionId();
	        if ($latest_ta_id != $taId) {
	        	$ta = $teachingActivities->getTa($latest_ta_id);
	        }
	        $this->view->ta = $ta;
	        $this->view->released_los = $ta->getLinkedLearningObjectiveWithStatus(Status::$RELEASED);
	        $this->_helper->layout()->setLayout('popup');
	    } else {
	       $this->throwError();
	    }
	}
	
	public function evaluationforeventsAction() {
        $taId = (int)$this->_getParam('taid', 0);
        if ($taId > 0) {
            $teachingActivities = new TeachingActivities();
            $ta = $teachingActivities->getTa($taId);
            $latest_ta_id = $ta->latestReleasedVersionId();
            if ($latest_ta_id != $taId) {
                $ta = $teachingActivities->getTa($latest_ta_id);
            }
            
            $studentEvaluateService = new StudentEvaluateService();
            $this->view->taEvaluations = $studentEvaluateService->getEvaluationForTaId($taId);
            $studentEvaluate = new StudentEvaluate();
            $this->view->taEvaluationAvg = $studentEvaluate->getRatingAvg('ta', $taId);
            $this->view->showGenericEvaluation = EvaluateTaConst::showGenericEvaluation($ta->typeID);
            $this->view->ta = $ta;
            $this->_helper->layout()->setLayout('popup');
        } else {
           $this->throwError();
        }
	}
	
	public function handbookviewAction() {		
		$id = $this->_getParam('id');
		$taFinder = new TeachingActivities();
		$ta = $taFinder->getTa($id);
		
		$this->view->ta = $ta;
		$this->view->revisions = $taFinder->getTaRevisions($ta->taid);
		$this->view->released_los = $ta->getLinkedLearningObjectiveWithStatus(Status::$RELEASED);
		
		$studentEvaluateService = new StudentEvaluateService();
		$this->view->taEvaluations = $studentEvaluateService->getEvaluationForTaId($id);
		$studentEvaluate = new StudentEvaluate();
		$this->view->taEvaluationAvg = $studentEvaluate->getRatingAvg('ta', $id);

		$this->view->display_edit_links = UserAcl::isStaffOrAbove() && UserAcl::checkTaPermission($ta, UserAcl::$EDIT) === true && count($this->view->released_los) > 0;
		$this->view->resourceAcl = ResourceAcl::accessAll(array('type'=>'ta','auto_id'=>$id));
		$this->view->resources = $ta->resources;
	}
	
	public function yearsforstageAction() {
		$stage_id = (int)($this->_request->getParam('stage_id'));
		$sbs = new StageBlockSeqs();
		$this->view->years = $sbs->getYearsForStage($stage_id);
	}
	
	public function blocksforyearAction() {
		$year_id = (int)($this->_request->getParam('year_id'));
		$sbs = new StageBlockSeqs();
		$this->view->blocks = $sbs->getBlocksForYear($year_id);
	}
	
	private function throwError() {
        throw new Zend_Controller_Action_Exception("Page not found.", 404);
    }
	
}
