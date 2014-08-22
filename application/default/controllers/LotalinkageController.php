<?php

class LotalinkageController extends Zend_Controller_Action {

	public function init() {
		$staffActions = array('addlo', 'addta', 'delete', 'deletecomplete', 'tainfo', 'loinfo', 'history', 'errorajax');
		$this->_helper->_acl->allow('staff', $staffActions);

		$stagecoordinatorActions = array('approvedelete', 'approvedeletecomplete');
		$this->_helper->_acl->allow('stagecoordinator', $stagecoordinatorActions);
		
		$ajaxContext = $this->_helper->getHelper('AjaxContext');
		$ajaxContext->addActionContext('tainfo', 'html');
		$ajaxContext->addActionContext('loinfo', 'html');
		$ajaxContext->addActionContext('errorajax', 'html');
		$ajaxContext->initContext();
	}

	/**
	 * Display some basic information about a teaching activity
	 */
	public function tainfoAction() {
		$ta_Finder = new TeachingActivities();
		try {
			$this->view->ta = $ta_Finder->getTa($this->_getParam('id'));
		} catch (Exception $e) {
			$this->view->errormsg = $e->getMessage();
			$this->render('errorajax');
		}
	}

	/**
	 * Display some basic information about a learning objective
	 */
	public function loinfoAction() {
		$loFinder = new LearningObjectives();
		try {
			$this->view->lo = $loFinder->getLo($this->_getParam('id'));
		} catch (Exception $e) {
			$this->view->errormsg = $e->getMessage();
			$this->render('errorajax');
		}
	}


	/**
	 * Deletes a linkage between a teaching activity and learning objective
	 * Stage coordinator and above can delete the link instantly, while principal teacher and block chair can only propose.
	 */
	public function deleteAction() {
		$request = $this->getRequest();
		$lo_id = (int)$request->getParam('loid');
		$ta_id = (int)$request->getParam('taid');
		PageTitle::setTitle($this->view, $request, array($lo_id, $ta_id));
		$type = $request->getParam('type'); //whether user comes from TA or LO view page

		$linkFinder = new LinkageLoTas();
		$link = $linkFinder->getLinkageByLoAndTaId($lo_id, $ta_id);

		if (($result = $link->isArchivable()) !== TRUE) {
			$this->view->errormsg = $result;
			$this->render('error');
			return;
		}
		
		if ($request->isPost()) {
			$do_archive = ('yes' === $request->getParam('sure'));
			if (!$do_archive) {
				if ($type == 'ta') {
					$this->_redirect('/teachingactivity/view/id/'.$ta_id);
				} else {
					$this->_redirect('/learningobjective/view/id/'.$lo_id);
				}
				exit();
			}

			$link->archive();
			
			$session = new Zend_Session_Namespace('lkarchivecomplete');
			$session->ta_id = $ta_id;
			$session->lo_id = $lo_id;
			$this->_redirect("/lotalinkage/deletecomplete");
		} else {
			$this->view->ta_id = $ta_id;
			$this->view->lo_id = $lo_id;
		}
	}
	
	/**
	 * Confirmation page that tells user that archiving is successful
	 * Last modified by jxie on 2009-11-03
	 */
	public function deletecompleteAction() {
		PageTitle::setTitle($this->view, $this->_request);
		$session = new Zend_Session_Namespace('lkarchivecomplete');
		if (!$session->ta_id || !$session->lo_id) {
			$this->_redirect('/index');
			return;
		}

		$this->view->ta_id = $session->ta_id;
		$this->view->lo_id = $session->lo_id;
		unset($session->ta_id);
		unset($session->lo_id);
	}

	/**
	 * Allow stage coordinator to approve the deletion of a linkage
	 * Last modified by jxie on 2010-06-30
	 */
	public function approvedeleteAction() {
		$request = $this->getRequest();
		PageTitle::setTitle($this->view, $request);
		$ta_id = (int)($request->getParam('taid'));
		$lo_id = (int)($request->getParam('loid'));

		$linkFinder = new LinkageLoTas();
		$link = $linkFinder->getLinkageByLoAndTaId($lo_id, $ta_id);
		
		if (UserAcl::checkTaPermission($ta_id, UserAcl::$ARCHIVE) !== TRUE) {
			$this->view->errormsg = $result;
			$this->render('error');
			return;
		}
		
		if ($request->isPost()) {			
			$do_archive = ('yes' === $request->getPost('sure'));
			$type = $request->getParam('type');
			if (!$do_archive) {
				if ($type == 'lo') {
					$this->_redirect('/learningobjective/view/id/'.$lo_id);
				} else {
					$this->_redirect('/teachingactivity/view/id/'.$ta_id);
				}
				exit();
			}
			
			$link->approveArchive();
			$session = new Zend_Session_Namespace('approvedeletecomplete');
			$session->ta_id = $ta_id;
			$session->lo_id = $lo_id;
			$this->_redirect('/lotalinkage/approvedeletecomplete');
		} else {
			$this->view->ta_id = $ta_id;
			$this->view->lo_id = $lo_id;
		}
	}
	
	/**
	 * Confirmation page that tells user that approval of deletion is successful
	 * Last modified by jxie on 2009-11-03
	 */
	public function approvedeletecompleteAction() {
		PageTitle::setTitle($this->view, $this->_request);
		$session = new Zend_Session_Namespace('approvedeletecomplete');
		if (!$session->ta_id || !$session->lo_id) {
			$this->_redirect('/index');
			return;
		}

		$this->view->ta_id = $session->ta_id;
		$this->view->lo_id = $session->lo_id;
		unset($session->ta_id);
		unset($session->lo_id);
	}
	
	/**
	 * Fetches history attached for add and delete action for this teaching activity
	 */
	public function historyAction(){
		$request = $this->getRequest();
		$id = (int)($request->getParam('id'));
		$type = $request->getParam('type');

		$this->view->id = $id;
		$this->view->type = $type;
		$linkFinder = new LinkageLoTas();
		switch ($type) {
			case 'lo':
				PageTitle::setTitle($this->view, $request, array('Teaching Activities', 'Learning Objective', $id));
				$this->view->typeColumnName = 'Teaching Activity';
				$this->view->typeMessage    = 'Learning Objective';
				$this->view->type_id        = 'ta_id';
				$this->view->rows           = $linkFinder->getHistory($type, $id);
				break;
			case 'ta':
				PageTitle::setTitle($this->view, $request, array('Learning Objectives', 'Teaching Activity', $id));
				$this->view->typeColumnName = 'Learning Objective';
				$this->view->typeMessage    = 'Teaching Activity';
				$this->view->type_id        = 'lo_id';
				$this->view->rows           = $linkFinder->getHistory($type, $id);
				break;
			default:
				$this->view->typeColumnName = '';
				$this->view->typeMessage    = '';
				$this->view->rows           = array();
				break;
		}

	}

	/**
	 * Adds a new learning objective link to a teaching activity
	 * Last modified by jxie on 2010-06-29
	 */
	public function addloAction() {
		$ta_id = (int)($this->_getParam('id'));
		
		//Check whether teaching activity id is valid
		$taFinder = new TeachingActivities();
		$ta = $taFinder->getTa($ta_id);

		if (($result = UserAcl::checkTaPermission($ta, UserAcl::$EDIT)) !== true) {
			$this->view->errormsg = $result;
			$this->render('error');
			return;
		}

		//check whether teaching activity is already involved in another submission
		$linkFinder = new LinkageLoTas();
		if ($linkFinder->isTaInSubmission($ta_id)) {
			$this->view->errormsg = "There are already requests awaiting approval in relation to teaching activity $ta_id.";
			$this->render('error');
			return;
		}
		
		$link_id = $linkFinder->addLoToExistingTa($ta_id);
		$this->_redirect("/submission/editloandta/id/$link_id");
	}

	/**
	 * Adds a new teaching activity link to a learning objective
	 * Last modified by jxie on 2010-06-29
	 */
	public function addtaAction() {
		$lo_id = (int)($this->_getParam('id'));

		//Check whether learning objective id is valid
		$loFinder = new LearningObjectives();
		$loFinder->getLo($lo_id);

		//check whether learning objective is already involved in another submission
		$linkFinder = new LinkageLoTas();
		if ($linkFinder->isLoInSubmission($lo_id)) {
			$this->view->errormsg = "There are already requests awaiting approval in relation to learning objective $lo_id.";
			$this->render('error');
			return;
		}
		
		$link_id = $linkFinder->addTaToExistingLo($lo_id);
		$this->_redirect("/submission/editloandta/id/$link_id");
	}
}