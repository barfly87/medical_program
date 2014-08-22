<?php
class FormProcessor_TeachingActivity extends FormProcessor {
	public $oldta = null;
	public $ta = null;

	public function __construct($ta_id = NULL) {
		parent::__construct();
		$taFinder = new TeachingActivities();
		$this->ta = $taFinder->createRow();
		$this->archive_option = 'y';
		if ($ta_id > 0) {
			$this->oldta = $taFinder->fetchRow('auto_id='.((int)$ta_id));
			$this->ta->version = $taFinder->fetchRow('taid='.$this->oldta->taid, 'version DESC', 1)->versionID + 1;
			$linkedlos = $this->oldta->getLinkedLearningObjectiveWithStatus(Status::$RELEASED);
			$linkedloid_arr = array();
			foreach ($linkedlos as $lo) {
				$linkedloid_arr[] = $lo['auto_id'];
			}
			$this->linked_los_arr_old = $linkedloid_arr;
			$this->linked_los_arr_new = $linkedloid_arr;
			$this->ta_auto_id = (int)$ta_id;
			$this->name = $this->oldta->name;
			$this->type = $this->oldta->typeID;
			$this->stage = $this->oldta->stageID;
			$this->year = $this->oldta->yearID;
			$this->block = $this->oldta->blockID;
			$this->block_week = $this->oldta->block_weekID;
			$this->pbl = $this->oldta->pblID;
			$this->sequence_num = $this->oldta->sequence_numID;
			$this->term = $this->oldta->termID;
			$this->student_grp = $this->oldta->student_grpID;
			$this->principal_teacher = $this->oldta->principal_teacher;
			$this->current_teacher = $this->oldta->current_teacher;
			$this->notes = $this->oldta->notes;
		}
	}

	public function process(Zend_Controller_Request_Abstract $request) {
		$action = $request->getActionName();

		$this->name = stripslashes($this->sanitize($request->getPost('name')));
		if (strlen($this->name) == 0) {
			$this->addError('name', 'You have to enter a Title');
		}
		$this->ta->name = $this->name;

		$this->type = (int)($request->getPost('type'));
		$typeFinder = new ActivityTypes();
		$types = $typeFinder->getAllNames();
		$type_name = $types[$this->type];
		if (empty($type_name)) {
			$this->addError('type', 'You have to select a Type');
		}
		$this->ta->type = $this->type;

		$this->stage = (int)($request->getPost('stage'));
		$stageFinder = new Stages();
		$stages = $stageFinder->getAllStages();
		$stage_name = $stages[$this->stage];
		if (empty($stage_name)) {
			$this->addError('stage', 'You have to select a '. Zend_Registry::get('Zend_Translate')->_('Stage'));
		}
		$this->ta->stage = $this->stage;

		$this->year = (int)($request->getPost('year'));
		if ($this->year == 0) {
			$this->year = 1;
		}
		$this->ta->year = $this->year;
		
		$this->block = (int)($request->getPost('block'));
		$blockFinder = new Blocks();
		$blocks = $blockFinder->getAllNames();
		$block_name = $blocks[$this->block];
		if (empty($block_name)) {
			$this->addError('block', 'You have to select a '. Zend_Registry::get('Zend_Translate')->_('Block'));
		}
		$this->ta->block = $this->block;

		$this->linked_los_arr_new = is_array($request->getPost('linkedlo')) ? $request->getPost('linkedlo') : array();
		if (count($this->linked_los_arr_new) == 0 && $action == 'edit') {
			$this->addError('linkedlos', 'You have to select at least one learning objective to link to.');
		}

		$this->block_week = (int)($request->getPost('block_week'));
		if ($this->block_week == 0) {
			$this->block_week = 1;
		}
		$this->ta->block_week = $this->block_week;

		$this->pbl = (int)($request->getPost('pbl'));
		if ($this->pbl == 0) {
			$this->pbl = 1;
		}
		$this->ta->pbl = $this->pbl;

		$this->sequence_num = (int)($request->getPost('sequence_num'));
		if ($this->sequence_num == 0) {
			$this->sequence_num = 1;
		}
		$this->ta->sequence_num = $this->sequence_num;

		$this->term = (int)($request->getPost('term'));
		if ($this->term == 0) {
			$this->term = 1;
		}
		$this->ta->term = $this->term;
		
		$this->student_grp = (int)($request->getPost('student_grp'));
		$this->ta->student_grp = $this->student_grp;

		$this->principal_teacher = stripslashes($this->sanitize($request->getPost('principal_teacher')));
		$this->current_teacher = stripslashes($this->sanitize($request->getPost('current_teacher')));
		$this->ta->principal_teacher = $this->principal_teacher;
		$this->ta->current_teacher = $this->current_teacher;

		$this->ta->notes = $this->notes;
		
		$this->archive_option = stripslashes($this->sanitize($request->getPost('archiveoption')));

		if ($this->hasError()) return false;

		$statusFinder = new Status();
		$status = $statusFinder->getAllNames();
		$status_ids = array_flip($status);

		$linkFinder = new LinkageLoTas();
		$curtimestamp = date('Y-m-d H:i:s');
		$identity= Zend_Auth::getInstance()->getIdentity();
		$role = $identity->role;
		$user_id = $identity->user_id;
		$db = Zend_Registry::get('db');

		//always create the new teaching activity
		$this->ta->created_by = $user_id;
		$this->ta->date_created = $curtimestamp;
		$this->ta->taid = $this->oldta->taid;
		$this->ta->parent_id = $this->oldta->auto_id;
		$this->ta->owner = $this->oldta->ownerID;

		$added_lo_ids = array();  //needed by lucene to add the linkages to the indexer
		$archived_lo_ids = array(); //needed by lucene to remove the linkages from the indexer

		$db->beginTransaction();
		try {
			$this->ta->save();
			$this->ta->saveAudience(array_keys($this->oldta->audience_arr));

			//copy links in lk_resource to the new ta
			$resourceFinder = new MediabankResource();
			$resourceHistory = new MediabankResourceHistory();
			$resourcesResults = $resourceFinder->fetchAll("type='ta' AND type_id={$this->oldta->auto_id}");
			foreach ($resourcesResults as $resourceRow) {
				$data = $resourceRow->toArray();
				unset($data['auto_id']);
				$data['type_id'] = $this->ta->auto_id;
				$auto_id = $resourceFinder->insert($data);
				$resourceHistory->setHistory($auto_id,'add');
			}
			 
			//create new linkages in link_lo_ta table
			foreach ($this->linked_los_arr_new as $loid) {
				$linkRow = $linkFinder->fetchRow("ta_id={$this->oldta->auto_id} AND lo_id=$loid");
				$data = $linkRow->toArray();
				unset($data['auto_id']);
				unset($data['modified_by']);
				unset($data['date_modified']);
				unset($data['approved_by']);
				unset($data['date_approved']);
				$data['ta_id'] = $this->ta->auto_id;
				$data['created_by'] = $user_id;
				$data['date_created'] = $curtimestamp;
				$data['new_status'] = $status_ids[Status::$UNKNOWN];
				//stage coordinator might be a pricipal teacher as well, that's why we need to check the stage
				if (($role == 'stagecoordinator' && in_array($this->oldta->stageID, $identity->stages)) || $role == 'domainadmin' || $role == 'admin') {
					$data['status'] = $status_ids[Status::$RELEASED];
					$added_lo_ids[] = $loid;
				} else {
					$data['status'] = $status_ids[Status::$NEW_VERSION];
					$data['type'] = 'TA';
				}
				$linkFinder->insert($data);
			}

			//update old linkages in link_lo_ta table
			if ($this->archive_option == 'y') {
				$historyFinder = new LinkageHistories();
				$linkResults = $linkFinder->fetchAll("ta_id={$this->oldta->auto_id} and status!={$status_ids[Status::$ARCHIVED]}");
				foreach ($linkResults as $linkRow) {
					if (($role == 'stagecoordinator' && in_array($this->oldta->stageID, $identity->stages)) || $role == 'domainadmin' || $role == 'admin') {
						$olddata = $linkRow->toArray(); //data to store in the link history table
						unset($olddata['auto_id']);
						$historyFinder->insert($olddata);
						
						//supersede old linkages
						$linkRow->approved_by = $user_id;
						$linkRow->date_approved = $curtimestamp;
						$linkRow->status = $status_ids[Status::$ARCHIVED];
						$linkRow->new_status = $status_ids[Status::$UNKNOWN];
						$linkRow->save();
						$archived_lo_ids[] = $linkRow->lo_id;
					} else {
						$linkRow->modified_by = $user_id;
						$linkRow->date_modified = $curtimestamp;
						$linkRow->new_status = $status_ids[Status::$ARCHIVED];
						$linkRow->type = 'TA';
						$linkRow->save();
					}
				}
			}

			$db->commit();

			//Update lucene indexer
			foreach ($added_lo_ids as $loid) {
				$linkRowNew = $linkFinder->fetchRow("ta_id={$this->ta->auto_id} AND lo_id=$loid");
				$linkRowNew->notifyObservers("post-insert");
			}
			foreach ($archived_lo_ids as $loid) {
				$linkRowOld = $linkFinder->fetchRow("ta_id={$this->oldta->auto_id} AND lo_id=$loid");
				$linkRowOld->notifyObservers("post-delete");
			}
			
			//new version of ta comes into effect
			if (($role == 'stagecoordinator' && in_array($this->oldta->stageID, $identity->stages)) || $role == 'domainadmin' || $role == 'admin') {
				EventsUpdateService::refreshLinkedTaId($this->ta);
				if ($this->ta->stage != $this->oldta->stage ||
					$this->ta->block_no != $this->oldta->block_no ||
					$this->ta->block_week != $this->oldta->block_week ||
					$this->ta->type != $this->oldta->type ||
					$this->ta->sequence_num != $this->oldta->sequence_num) {
					EventsUpdateService::refreshLinkedTaId($this->oldta);
				}
			}

			return $this->ta->auto_id;
		} catch (Exception $e) {
			$db->rollBack();
			throw $e;
		}
	}
}
?>