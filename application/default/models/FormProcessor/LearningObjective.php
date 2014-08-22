<?php
class FormProcessor_LearningObjective extends FormProcessor {
	public $lo = null;
	public $oldlo = null;

	public function __construct($lo_id = NULL) {
		parent::__construct();
		$loFinder = new LearningObjectives();
		$this->lo = $loFinder->createRow();

		if ($lo_id > 0) {
			$this->oldlo = $loFinder->fetchRow('auto_id='.((int)$lo_id));
			$this->lo->version = $loFinder->fetchRow('loid='.$this->oldlo->loid, 'version DESC', 1)->versionID + 1;

			$linkedtas = $this->oldlo->getLinkedTeachingActivityWithStatus(Status::$RELEASED);
			$linkedtaid_arr = array();
			foreach ($linkedtas as $ta) {
				$linkedtaid_arr[] = $ta['auto_id'];
			}
			$this->linked_tas_arr_old = $linkedtaid_arr;
			$this->linked_tas_arr_new = $linkedtaid_arr;
			
			$this->lo_auto_id = (int)$lo_id;
			$this->discipline1 = $this->oldlo->discipline1ID;
			$this->discipline2 = $this->oldlo->discipline2ID;
			$this->discipline3 = $this->oldlo->discipline3ID;
			$this->curriculumarea1 = $this->oldlo->curriculumarea1ID;
			$this->curriculumarea2 = $this->oldlo->curriculumarea2ID;
			$this->curriculumarea3 = $this->oldlo->curriculumarea3ID;
			$this->theme1 = $this->oldlo->theme1ID;
			$this->theme2 = $this->oldlo->theme2ID;
			$this->theme3 = $this->oldlo->theme3ID;
			$this->skill = $this->oldlo->skillID;
			$this->system = $this->oldlo->systemID;

			$this->activity = NULL;
			$this->ability = NULL;
			if (preg_match('/^At the end of (.+), students should be able to ([a-z]+) (.*)$/sU', $this->oldlo->lo, $match)) {
				$scopeFinder = new LOScopes();
				$activities = $scopeFinder->getAllScopes();
				if (in_array($match[1], $activities)) {
					$activity = $match[1];
					$this->activity = $activity;
				}
				$verbFinder = new LOVerbs();
				$abilities = $verbFinder->getAllVerbs();
				if (in_array($match[2], $abilities)) {
					$ability = $match[2];
					$this->ability = $ability;
				}
				$this->lotext = $match[3];
			} else {
				$this->lotext = $this->oldlo->lo;
			}
			$this->checked = false;
			if(isset($activity) && isset($ability)) {
				$this->checked = true;
			}

			$this->reviewArr = $this->oldlo->review_ids_array;
			$this->keywords = explode('|', $this->oldlo->keywords);
			$this->assesstypeArr = $this->oldlo->assessment_type_ids_array;
			$this->achievement = $this->oldlo->achievementID;
			$this->jmo = $this->oldlo->jmoID;
			$this->gradattrib = $this->oldlo->gradattribID;
			$this->notes = $this->oldlo->notes;
		}
	}

	public function process(Zend_Controller_Request_Abstract $request) {
		$action = $request->getActionName();

		// synonyms are stored as 55s though the actual id is 55
		// using (int) would removes 's' from 55s into 55
		$this->discipline1 = (int)($request->getPost('discipline1'));
		$this->curriculumarea1 = (int)$request->getPost('curriculumarea1');
		$disc_service = new DisciplineService();
		$disc_name = $disc_service->getNameOfDiscipline($this->discipline1);
		if (empty($disc_name)) {
			$this->addError('discipine1', 'You have to select a Main Discipline.');
		}
		$this->discipline2 = (int)($request->getPost('discipline2'));
		$this->curriculumarea2 = (int)$request->getPost('curriculumarea2');
		$this->discipline3 = (int)($request->getPost('discipline3'));
		if ($this->discipline3 == 0) {
			$this->discipline3 = 1;
		}
		$this->curriculumarea3 = (int)$request->getPost('curriculumarea3');

		$this->lo->discipline1 = $this->discipline1;
		$this->lo->curriculumarea1 = $this->curriculumarea1;
		$this->lo->discipline2 = $this->discipline2;
		$this->lo->curriculumarea2 = $this->curriculumarea2;
		$this->lo->discipline3 = $this->discipline3;
		$this->lo->curriculumarea3 = $this->curriculumarea3;

		$this->theme1 = (int)($request->getPost('theme1'));
		$themeFinder = new Themes();
		$themes = $themeFinder->getAllNames();
		$theme1_name = $themes[$this->theme1];
		if (empty($theme1_name)) {
			$this->addError('theme1', 'You have to select a '.Zend_Registry::get('Zend_Translate')->_('Theme').'.');
		}
		$this->theme2 = (int)($request->getPost('theme2'));
		$this->theme3 = (int)($request->getPost('theme3'));
		if ($this->theme3 == 0) {
			$this->theme3 = 1;
		}

		$this->lo->theme1 = $this->theme1;
		$this->lo->theme2 = $this->theme2;
		$this->lo->theme3 = $this->theme3;

		$this->skill = (int)($request->getPost('skill'));
		$this->lo->skill = $this->skill;

		$this->system = (int)($request->getPost('system'));
		$systemFinder = new Systems();
		$systems = $systemFinder->getAllNames();
		$system_name = $systems[$this->system];
		if (empty($system_name)) {
			$this->addError('system', 'You have to select a System.');
		}
		$this->lo->system = $this->system;

		$activity = $request->getPost('activity');
		$this->activity = stripslashes($this->sanitize($activity));
		$ability = $request->getPost('ability');
		$this->ability = stripslashes($this->sanitize($ability));
		$this->lotext = $request->getPost('lo');
		if (strlen($this->lotext) == 0) {
			$this->addError('lo', 'You have to enter a Learning Objective.');
		}

		if (isset($activity) && isset($ability)) {
			$this->lo->lo = 'At the end of '. $this->activity .
				', students should be able to '. $this->ability . ' ' . $this->lotext;
			$this->checked = true;
		} else {
			$this->lo->lo = $this->lotext;
			$this->checked = false;
		}

		$this->reviewArr = is_array($request->getPost('review')) ? $request->getPost('review') : array();

		$this->keywords = is_array($request->getPost('keywords')) ? $request->getPost('keywords') : array();
		$this->lo->keywords = join('|', $this->keywords);

		$this->assesstypeArr = is_array($request->getPost('assesstype')) ? $request->getPost('assesstype') : array();
		if (count($this->assesstypeArr) == 0) {
			$this->addError('assesstype', 'You have to select at least one Assessment Method.');
		}

		$this->achievement = (int)($request->getPost('achievement'));
		$this->lo->achievement = $this->achievement;

		$this->jmo = (int)($request->getPost('jmo'));
		$this->lo->jmo = $this->jmo;

		$this->gradattrib = (int)($request->getPost('gradattrib'));
		$this->lo->gradattrib = $this->gradattrib;

		$this->notes = stripslashes($this->sanitize($request->getPost('notes')));
		$this->lo->notes = $this->notes;

		$this->linked_tas_arr_new = is_array($request->getPost('linkedta')) ? $request->getPost('linkedta') : array();
		if (count($this->linked_tas_arr_new) == 0 && $action == 'edit') {
			$this->addError('linkedtas', 'You have to select at least one teaching activity to link to.');
		}

		if ($this->hasError()) return false;

		//all required fields are filled out, create or edit the learning objecive.
		$statusFinder = new Status();
		$status = $statusFinder->getAllNames();
		$status_ids = array_flip($status);

		$linkFinder = new LinkageLoTas();
		$curtimestamp = date('Y-m-d H:i:s');
		$identity = Zend_Auth::getInstance()->getIdentity();
		$role = $identity->role;
		$user_id = $identity->user_id;
		$db = Zend_Registry::get('db');
		
		//TODO check whether we need the add action
		if ($action === 'add') {
			$this->lo->created_by = $user_id;
			$this->lo->date_created = $curtimestamp;
			$this->lo->version = 1;

			$db->beginTransaction();
			try {
				$row = $db->query('SELECT max(loid) AS maxloid FROM learningobjective')->fetch();
				$this->lo->loid = ++$row['maxloid'];
				$this->lo->save();
				$this->lo->saveReviews($this->reviewArr);
				$this->lo->saveAssessTypes($this->assesstypeArr);
				$db->commit();
				$this->lo->notifyObservers("post-insert");
				return $this->lo->auto_id;
			} catch (Exception $e) {
				$db->rollBack();
				throw $e;
			}
		} else {
			//always create the new learning objective
			$this->lo->created_by = $user_id;;
			$this->lo->date_created = $curtimestamp;
			$this->lo->loid = $this->oldlo->loid;
			$this->lo->parent_id = $this->oldlo->auto_id;
			$this->lo->owner = $this->oldlo->ownerID;

			$taFinder = new TeachingActivities();
			$archived_ta_ids = array(); //needed by lucene to remove the linkages from the indexer

			$db->beginTransaction();
			try {
				$this->lo->save();
				$this->lo->saveReviews($this->reviewArr);
				$this->lo->saveAssessTypes($this->assesstypeArr);
				$this->lo->saveAudience(array_keys($this->oldlo->audience_arr));

				//copy resource links in lk_resource to the new lo
				$resourceFinder = new MediabankResource();
				$resourceHistory = new MediabankResourceHistory();
				$resourcesResults = $resourceFinder->fetchAll("type='lo' AND type_id={$this->oldlo->auto_id}");
				foreach ($resourcesResults as $resourceRow) {
					$data = $resourceRow->toArray();
					unset($data['auto_id']);
					$data['type_id'] = $this->lo->auto_id;
					$auto_id = $resourceFinder->insert($data);
					$resourceHistory->setHistory($auto_id,'add');
				}
					
				//create new linkages and update old linkages in link_lo_ta table
				foreach ($this->linked_tas_arr_new as $taid) {
					$linkRow = $linkFinder->fetchRow("lo_id={$this->oldlo->auto_id} AND ta_id=$taid");
					$olddata = $linkRow->toArray(); //data to store in the link history table
					$data = $linkRow->toArray();
					unset($data['auto_id']);
					unset($data['modified_by']);
					unset($data['date_modified']);
					unset($data['approved_by']);
					unset($data['date_approved']);
					//new linkage info
					$data['lo_id'] = $this->lo->auto_id;
					$data['created_by'] = $user_id;
					$data['date_created'] = $curtimestamp;
					$data['new_status'] = $status_ids[Status::$UNKNOWN];
					
					$taRow = $taFinder->fetchRow("auto_id=$taid");
					if ((($role == 'stagecoordinator' && in_array($taRow->stageID, $identity->stages)) || $role == 'domainadmin' || $role == 'admin') 
						&& ($taRow->owner == $identity->domain)) {
						//if stage coordinator have permission to archive old linkage
						$linkRow->approved_by = $user_id;
						$linkRow->date_approved = $curtimestamp;
						$linkRow->status = $status_ids[Status::$ARCHIVED];
						$linkRow->new_status = $status_ids[Status::$UNKNOWN];
						$linkRow->save();
						
						$data['status'] = $status_ids[Status::$RELEASED];
						$archived_ta_ids[] = $taid; //ids used by lucene
						
						unset($olddata['auto_id']);
						$historyFinder = new LinkageHistories();
						$historyFinder->insert($olddata);
					} else {
						$linkRow->modified_by = $user_id;
						$linkRow->date_modified = $curtimestamp;
						$linkRow->new_status = $status_ids[Status::$OLD_VERSION];
						$linkRow->save();
						
						$data['status'] = $status_ids[Status::$NEW_VERSION];
						$data['type'] = 'LO';
					}
					$linkFinder->insert($data);
				}					
				$db->commit();

				//Update lucene indexer
				foreach ($archived_ta_ids as $taid) {
					$linkRowOld = $linkFinder->fetchRow("lo_id={$this->oldlo->auto_id} AND ta_id=$taid");
					$linkRowOld->notifyObservers("post-delete");
					$linkRowNew = $linkFinder->fetchRow("lo_id={$this->lo->auto_id} AND ta_id=$taid");
					$linkRowNew->notifyObservers("post-insert");
				}
				return $this->lo->auto_id;
			} catch (Exception $e) {
				$db->rollBack();
				throw $e;
			}
		}
	}
}
?>