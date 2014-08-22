<?php
class WorkflowController extends Zend_Controller_Action {

	public function init() {
		$staffActions = array('index', 'viewownblock', 'viewunknownta', 'viewownunknownta');
		$this->_helper->_acl->allow('staff', $staffActions);
		$stagecoordinatorActions = array('viewblock');
		$this->_helper->_acl->allow('stagecoordinator', $stagecoordinatorActions);
	}

	public function indexAction() {
		PageTitle::setTitle($this->view, $this->_request);
		$identity = Zend_Auth::getInstance()->getIdentity();
		$this->view->role = $identity->role;
		$user_id = $identity->user_id;
		
		$domainFinder = new Domains();
		$owner = $domainFinder->getDomainId($identity->domain);
		 
		$blockFinder = new Blocks();
		$blocks = $blockFinder->getAllNames('auto_id ASC');
		$this->view->blocks = $blocks;
		 
		//Look up the auto_id for variaous status from lookup table
		$statusFinder = new Status();
		$statusNames = $statusFinder->getAllNames();
		$status_ids = array_flip($statusNames);
		$this->view->indev = $status_ids[Status::$IN_DEVELOPMENT];
		$this->view->awaiting = $status_ids[Status::$AWAITING_APPROVAL];
		$this->view->new_version = $status_ids[Status::$NEW_VERSION];
		$this->view->archived = $status_ids[Status::$ARCHIVED];
		 
		//Admins can see in development, awaiting approval, new version, and archive queues
		//Stage coordinator can see awaiting approval, new version and archive queues
		if ($identity->role == 'admin' || $identity->role == 'domainadmin' || $identity->role == 'stagecoordinator') {
			//$this->view->unknown_block_count = LinkageLoTas::getUnknownBlockQueueCount();
			$this->view->indev_arr = LinkageLoTas::getNewQueues($status_ids[Status::$IN_DEVELOPMENT], $blocks, $owner);
			$this->view->awaiting_arr = LinkageLoTas::getNewQueues($status_ids[Status::$AWAITING_APPROVAL], $blocks, $owner);
			$this->view->new_version_arr = LinkageLoTas::getNewVersionQueues($blocks, $owner);
			$this->view->archive_arr = LinkageLoTas::getArchiveQueues($blocks, $owner);
				
			//get the list of blocks that current stage coordinator is in charge of
			if ($identity->role == 'stagecoordinator') {
				$stages = $identity->stages;
				$coordinatorblocks = array();
				$sbs = new StageBlockSeqs();

				foreach ($stages as $stage) {
					$coordinatorblocks = array_merge($coordinatorblocks, $sbs->getBlockIdsForStage($stage));
				}
				$this->view->coordinatorblocks = $coordinatorblocks;
			}
		}
		 
		//Staff memeber can only see their own submissions.
		$this->view->null_ta_count = LinkageLoTas::getNoTaQueueCount();
		$this->view->my_null_ta_count = LinkageLoTas::getNoTaQueueCount($identity->user_id);
		$indev_total = $this->view->my_null_ta_count;
		
		$this->view->my_indev_arr = LinkageLoTas::getUserNewQueues($status_ids[Status::$IN_DEVELOPMENT], $blocks, $owner, $user_id);
		foreach ($this->view->my_indev_arr as $k => $v) {
			$indev_total += $v;
		}
		$this->view->my_indev_total = $indev_total;
		 
		$this->view->my_awaiting_arr = LinkageLoTas::getUserNewQueues($status_ids[Status::$AWAITING_APPROVAL], $blocks, $owner, $user_id);
		$awaiting_total = 0;
		foreach ($this->view->my_awaiting_arr as $k => $v) {
			$awaiting_total += $v;
		}
		$this->view->my_awaiting_total = $awaiting_total;
		 
		$this->view->my_new_version_arr = LinkageLoTas::getNewVersionQueues($blocks, $owner, $user_id);
		$new_version_total = 0;
		foreach ($this->view->my_new_version_arr as $k => $v) {
			$new_version_total += $v;
		}
		$this->view->my_new_version_total = $new_version_total;
		 
		$this->view->my_archive_arr = LinkageLoTas::getArchiveQueues($blocks, $owner, $user_id);
		$archive_total = 0;
		foreach ($this->view->my_archive_arr as $k => $v) {
			$archive_total += $v;
		}
		$this->view->my_archive_total = $archive_total;
	}

	public function viewblockAction() {
		$block_id = (int)$this->_request->getParam('id');
		if ($block_id == 0) {
			$this->_redirect('/workflow/index');
			return;
		}
		$status_id = (int)$this->_request->getParam('status');
		if ($status_id == 0) {
			$this->_redirect('/workflow/index');
			return;
		}
		
		$identity = Zend_Auth::getInstance()->getIdentity();
		$domainFinder = new Domains();
		$owner = $domainFinder->getDomainId($identity->domain);
		
		$blockFinder = new Blocks();
		$blocks = $blockFinder->getAllNames();
		$this->view->block_name = $blocks[$block_id];
		 
		$statusFinder = new Status();
		$status_arr = $statusFinder->getAllNames();
		$this->view->status_name = $status_arr[$status_id];
		
		PageTitle::setTitle($this->view, $this->_request, array($this->view->status_name, $this->view->block_name));
		if ($this->view->status_name == Status::$NEW_VERSION) {
			$this->view->result = LinkageLoTas::getBlockNewVersionQueue($block_id, $owner);
		} else if ($this->view->status_name == Status::$ARCHIVED) {
			$this->view->result = LinkageLoTas::getBlockArchiveQueue($block_id, $owner);
		} else {
			$this->view->result = LinkageLoTas::getBlockNewQueues($status_id, $block_id, $owner);
		}
		$this->view->inDevQueue = ($this->view->status_name == Status::$IN_DEVELOPMENT);
	}

	public function viewownblockAction() {
		$block_id = (int)$this->_request->getParam('id');
		if ($block_id == 0) {
			$this->_redirect('/workflow/index');
			return;
		}
		$status_id = (int)$this->_request->getParam('status');
		if ($status_id == 0) {
			$this->_redirect('/workflow/index');
			return;
		}
		
		$identity = Zend_Auth::getInstance()->getIdentity();
		$user_id = $identity->user_id;
		
		$domainFinder = new Domains();
		$owner = $domainFinder->getDomainId($identity->domain);
		
		$blockFinder = new Blocks();
		$blocks = $blockFinder->getAllNames();
		$this->view->block_name = $blocks[$block_id];
		
		$statusFinder = new Status();
		$status_arr = $statusFinder->getAllNames();
		$this->view->status_name = $status_arr[$status_id];
		
		PageTitle::setTitle($this->view, $this->_request, array($this->view->status_name, $this->view->block_name));
		if ($this->view->status_name == Status::$NEW_VERSION) {
			$this->view->result = LinkageLoTas::getBlockNewVersionQueue($block_id, $owner, $user_id);
		} else if ($this->view->status_name == Status::$ARCHIVED) {
			$this->view->result = LinkageLoTas::getBlockArchiveQueue($block_id, $owner, $user_id);
		} else {
			$this->view->result = LinkageLoTas::getBlockNewQueues($status_id, $block_id, $owner, $user_id);
		}
	}
	
	public function viewunknowntaAction() {
		PageTitle::setTitle($this->view, $this->_request);
		$statusFinder = new Status();
		$statusNames = $statusFinder->getAllNames();
		$indev_status_id = array_search(Status::$IN_DEVELOPMENT, $statusNames);
		$this->view->result = LinkageLoTas::getNoTaQueueDetail($indev_status_id);
	}
	
	public function viewownunknowntaAction() {
		PageTitle::setTitle($this->view, $this->_request);
		$identity = Zend_Auth::getInstance()->getIdentity();
		$statusFinder = new Status();
		$statusNames = $statusFinder->getAllNames();
		$indev_status_id = array_search(Status::$IN_DEVELOPMENT, $statusNames);
		$this->view->result = LinkageLoTas::getNoTaQueueDetail($indev_status_id, $identity->user_id);
	}
}