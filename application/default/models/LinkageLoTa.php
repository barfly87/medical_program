<?php

class LinkageLoTa extends Compass_Db_Table_Row_Observerable {
	public function getLuceneDoc() {
		if (empty($this->lo_id) || empty($this->ta_id)) {
			return null;
		}
		
		$doc = null;
		try {
    		$loFinder = new LearningObjectives();
    		$lo = $loFinder->fetchRow('auto_id='.$this->lo_id);
    		
    		$taFinder = new TeachingActivities();
    		$ta = $taFinder->fetchRow('auto_id='.$this->ta_id);
    
    		$doc = new Zend_Search_Lucene_Document();
    		$doc->addField(Zend_Search_Lucene_Field::Keyword('docRef', $this->docRef));
    		$doc->addField(Zend_Search_Lucene_Field::Text('doctype', 'Linkage'));
    		$doc->addField(Zend_Search_Lucene_Field::Text('auto_id', $this->auto_id));
            
    		foreach (LearningObjective::$luceneFields as $k => $v) {
    			$doc->addField(Zend_Search_Lucene_Field::Text('lo_'.$k, $lo->$v));
    		}
    		
    		foreach (TeachingActivity::$luceneFields as $k => $v) {
    			if ($k == 'release_date') {
    				foreach ($ta->$v as $group => $date) {
    					$timestamp = strtotime($date);
    					$doc->addField(Zend_Search_Lucene_Field::Text('releasedate_'.$group, $timestamp));
    				}
    			} else if ($k == 'resource_links') {
    				$allresources = $ta->resource_links;
    				$doc->addField(Zend_Search_Lucene_Field::UnStored('ta_content', join(', ', $allresources['content'])));
    				$doc->addField(Zend_Search_Lucene_Field::UnStored('ta_reference', join(', ', $allresources['reference'])));
    				$doc->addField(Zend_Search_Lucene_Field::UnIndexed('ta_resource_links_staff', join(' ', $allresources['other_staff'])));
    				$doc->addField(Zend_Search_Lucene_Field::UnIndexed('ta_resource_links_student', join(' ', $allresources['other_student'])));
    			} else if ($k == 'block') {
    			    $blockNo = ($ta->block_no < 10) ? '0'.$ta->block_no : $ta->block_no;
    			    $blockVal = sprintf('<span class="block%s">%s</span>', $blockNo, $ta->$v);
    			    $doc->addField(Zend_Search_Lucene_Field::Text('ta_'.$k, $blockVal));
    			} else if ($k == 'resource_podcast') {
    			    $doc->addField(Zend_Search_Lucene_Field::unIndexed('ta_'.$k, $ta->$v));
    			} else {
    				$doc->addField(Zend_Search_Lucene_Field::Text('ta_'.$k, $ta->$v));
    			}
    		}
		} catch (Exception $ex) {
		    $error = 'LUCFAIL: '.$ex->getMessage().PHP_EOL.$ex->getTraceAsString().PHP_EOL;
		    Zend_Registry::get('logger')->warn(PHP_EOL."CLASS\t: ".__CLASS__.PHP_EOL."METHOD\t: ".__METHOD__.PHP_EOL."Error\t: ".$error.PHP_EOL);
		    
		}
		return $doc;
	}

	public function docRef() {
		return 'LinkageLoTa:'.$this->auto_id;
	}

    public function status() {
    	return $this->findParentRow("Status", "Status")->name;
    }
    
    public function new_status() {
    	return $this->findParentRow("Status", "NewStatus")->name;
    }	
	
	public function strength() {
		return $this->findParentRow("Strengths")->name;
	}

	function __get($key) {
		if (method_exists($this, $key)) {
			return $this->$key();
		}
	    //If we just need the integer value of a particular column in linkage table instead of name in lookup table, 
    	//we have to append 'ID' to the column name. This is just an easy fix instead of changing method names and use the new
    	//method names on almost all the pages.
    	if (substr($key, -2) == 'ID') {
    		return parent::__get(substr($key, 0, strlen($key) - 2));
    	}
		return parent::__get($key);
	}
	
	/** Archive a linkage between learning objective and teaching activity.
	 *  If user role is stage coordinator and above, linkage will be archived immediately.
	 *  If user role is principle teacher or block chair, then a request to arhive the linkage will be
	 *  appearing in stage coordinator's workflow queue.
	 */
	public function archive() {
		if (UserAcl::checkTaPermission($this->ta_id, UserAcl::$APPROVE)) {
			$this->approveArchive();
		} else {
			$identity = Zend_Auth::getInstance()->getIdentity();
			$statusFinder = new Status();
		
			$db = Zend_Registry::get('db');
			$db->beginTransaction();
			try {
				//update the status so that it will appear in stage coordinator's workflow queue
				$this->new_status = $statusFinder->getIdForStatus(Status::$ARCHIVED);
				$this->modified_by = $identity->user_id;
				$this->date_modified = date('Y-m-d H:i:s');
				$this->type = 'LK';
				$this->save();
				$db->commit();
			} catch (Exception $e) {
				$db->rollBack();
				throw $e;
			}
		}
	}
	
	/** Check whether linkage between ta and lo is archivable, otherwise return an error message */
	public function isArchivable() {
		//first check whether current user can perform the archive action based on teaching activity
		if (($result = UserAcl::checkTaPermission($this->ta_id, UserAcl::$ARCHIVE)) !== true) {
			return $result;
		}
		
		//check whether the linkage is already archived.
		if ($this->status == Status::$ARCHIVED) {
			return "There is no active linkage between teaching activity {$this->ta_id} and learning objective {$this->lo_id}.";
		}
		
		//then check if either ta or lo is current being used
		$linkFinder = new LinkageLoTas();
		if ($linkFinder->isTaInSubmission($this->ta_id)) {
			return "There are already requests awaiting approval in relation to teaching activity {$this->ta_id}.";
		}
		
		if ($linkFinder->isLoInSubmission($this->lo_id)) {
			return "There are already requests awaiting approval in relation to learning objective {$this->lo_id}.";
		}
		
		return true;
	}
	
	/** Allows a stage coordinator and above to approve a archive submission */
	public function approveArchive() {
		$identity = Zend_Auth::getInstance()->getIdentity();
		$statusFinder = new Status();
		
		$db = Zend_Registry::get('db');
		$db->beginTransaction();
		try {
			$this->saveToHistoryTable();
			
			//update the linkage info
			$this->status = $statusFinder->getIdForStatus(Status::$ARCHIVED);
			$this->new_status = $statusFinder->getIdForStatus(Status::$UNKNOWN);
			$this->approved_by = $identity->user_id;
			$this->date_approved = date('Y-m-d H:i:s');
			$this->save();
			$db->commit();
			$this->notifyObservers("post-delete");

			//check if deleting the linkage will archive the teaching activity
			//if that's the case, we need to update events so that no event links to this ta
			$this->updateTimetable($identity->user_id);
		} catch (Exception $e) {
			$db->rollBack();
			throw $e;
		}
	}
	
	/**
	 *  Allow stage coordinator and above to approve linkage to new version of ta
	 */
	public function approveNewTaVersion() {
		$identity = Zend_Auth::getInstance()->getIdentity();
		$statusFinder = new Status();
		
		//update the linkage info
		$this->status = $statusFinder->getIdForStatus(Status::$RELEASED);
		$this->new_status = $statusFinder->getIdForStatus(Status::$UNKNOWN);
		$this->approved_by = $identity->user_id;
		$this->date_approved = date('Y-m-d H:i:s');
		$this->save();

		$this->notifyObservers("post-insert");
		
		//check if this link is the first released link for current teaching activity
		//actually only necessary to update ta id when stage, block, week, seq, and type info changes
		$linkFinder = new LinkageLoTas();
		$count = $linkFinder->getTaLinkageCountWithStatus($this->ta_id, $this->statusID);
		if ($count == 1) {
			EventsUpdateService::refreshLinkedTAId($this->ta_id, $identity->user_id);
		}
	}
	
	/**
	 *  Allow stage coordinator and above to approve linkage to new version of lo
	 */
	public function approveNewLoVersion() {
		$identity = Zend_Auth::getInstance()->getIdentity();
		$statusFinder = new Status();
		$status = $statusFinder->getAllNames();
		$status_ids = array_flip($status);
		
		$curtimestamp = date('Y-m-d H:i:s');
		$db = Zend_Registry::get('db');
		$db->beginTransaction();
		try {
			//update the linkage info
			$this->status = $status_ids[Status::$RELEASED];
			$this->new_status = $status_ids[Status::$UNKNOWN];
			$this->approved_by = $identity->user_id;
			$this->date_approved = $curtimestamp;
			$this->save();
	
			//archive old linkage
			$loFinder = new LearningObjectives();
			$lo = $loFinder->getLo($this->lo_id);
			$linkFinder = new LinkageLoTas();
			$oldlink = $linkFinder->getLinkageByLoAndTaId($lo->parent_id, $this->ta_id);
			$olddata = $oldlink->toArray();
			unset($olddata['auto_id']);
			$historyFinder = new LinkageHistories();
			$historyFinder->insert($olddata);
			
			$oldlink->approved_by = $identity->user_id;
			$oldlink->date_approved = $curtimestamp;
			$oldlink->status = $status_ids[Status::$ARCHIVED];
			$oldlink->new_status = $status_ids[Status::$UNKNOWN];
			$oldlink->save();
			
			$db->commit();
			//update lucene indexer
			$this->notifyObservers("post-insert");
			$oldlink->notifyObservers("post-delete");
		} catch (Exception $e) {
			$db->rollBack();
			throw $e;
		}
	}
	
	/** Check whether a new submission is editable by the user */
	public function isEditable() {
		$identity = Zend_Auth::getInstance()->getIdentity();
		if ($this->created_by != $identity->user_id) {
			return "You can only edit your own submission.";
		}
		if ($this->status != Status::$IN_DEVELOPMENT) {
			return "Your submission is not currently in development.";
		}
		
		if (!empty($this->ta_id)) {
			$taFinder = new TeachingActivities();
			$ta = $taFinder->getTa($this->ta_id);
			if ($ta->owner != $identity->domain) {
				return "You need to switch your domain to \"{$ta->owner}\" before editing this submission.";
			}
		}
		if (!empty($this->lo_id)) {
			$loFinder = new LearningObjectives();
			$lo = $loFinder->getLo($this->lo_id);
			if (!in_array($identity->domain, $lo->audience_arr)) {
				return "\"{$identity->domain}\" is not the audience of learning objective $this->lo_id.";
			}
		}
		return true;
	}
	
	public function isNewVersionRequest() {
		return $this->status == Status::$NEW_VERSION;
	}
	
	public function isArchiveRequest() {
		return $this->new_status == Status::$ARCHIVED;
	}
	
	public function saveToHistoryTable() {
		$data = $this->toArray();
		unset($data['auto_id']);
		
		//Add old linkage to history table
		$historyFinder = new LinkageHistories();
		$historyFinder->insert($data);
	}
	
	/** Change learning objective id of a linkage */
	public function saveLoId($lo_id) {
		$this->lo_id = $lo_id;
		$this->modified_by = Zend_Auth::getInstance()->getIdentity()->user_id;
		$this->date_modified = date('Y-m-d H:i:s');
		
		if (empty($this->type)) {
			$this->type = 'EL';
		} else if ($this->type == 'ET') {
			$this->type = 'LK';
		} else if ($this->type == 'NT') {
			$this->type = 'TA';
		}
		$this->save();
	}
	
	/** Change teaching activity id of a linkage */
	public function saveTaId($ta_id) {
		$this->ta_id = $ta_id;
		$this->modified_by = Zend_Auth::getInstance()->getIdentity()->user_id;
		$this->date_modified = date('Y-m-d H:i:s');
		
		if (empty($this->type)) {
			$this->type = 'ET';
		} else if ($this->type == 'EL') {
			$this->type = 'LK';
		} else if ($this->type == 'NL') {
			$this->type = 'LO';
		}
		$this->save();
	}
	
	/** Allow user to submit LO/TA/Linkage for approval */
	public function submitForApproval() {
		$identity = Zend_Auth::getInstance()->getIdentity();
		$user_id = $identity->user_id;
		
		//Only author can submit learning objective or teaching activity for approval
		if ($this->created_by != $user_id) {
			return "This submission is not created by you!";
		}
		
		if ($this->status != Status::$IN_DEVELOPMENT) {
			return "This submission does not need approval.";
		}
		
		if (empty($this->lo_id)) {
			return "Missing learning objective information.";
		}
		
		if (empty($this->ta_id)) {
			return "Missing teaching activity information.";
		}
		
		$loFinder = new LearningObjectives();
		$lo = $loFinder->getLo($this->lo_id);
		if (count($lo->getMissingRequiredFields) != 0) {
			return "Please fill out all required learning objective fields.";
		}
		
		$taFinder = new TeachingActivities();
		$ta = $taFinder->getTa($this->ta_id);
		if (count($ta->getMissingRequiredFields) != 0) {
			return "Please fill out all required teaching activity fields.";
		}
		
		$statusFinder = new Status();
		$this->status = $statusFinder->getIdForStatus(Status::$AWAITING_APPROVAL);
		$this->modified_by = $user_id;
		$this->date_modified = date('Y-m-d H:i:s');
		$this->save();
		return TRUE;
	}
	
	/** Allow stage coordinator and admin to send back the submission to the author for modification */
	public function revertSubmission() {
		$identity = Zend_Auth::getInstance()->getIdentity();
		$role = $identity->role;
		
		$taFinder = new TeachingActivities();
		$ta = $taFinder->getTa($this->ta_id);
		//Only stage coordinator and admin can ask the author to resubmit LO/TA/Linkage
		if (!(($role == 'stagecoordinator' && in_array($ta->stageID, $identity->stages)) || $role == 'admin')) {
			return "This submission in not linked to a teaching activity in your stage.";
		}
		
		//Only awaiting approval can be sent back
		if ($this->status != Status::$AWAITING_APPROVAL) {
			return "This submission can not be sent back for midification.";
		}
		
		$statusFinder = new Status();
		$this->status = $statusFinder->getIdForStatus(Status::$IN_DEVELOPMENT);
		$this->modified_by = $identity->user_id;
		$this->date_modified = date('Y-m-d H:i:s');
		$this->save();
		return TRUE;
	}
	
	/** Allow stage coordinator and domain admin to approve a new submission */
	public function approveNewSubmission() {
		$identity = Zend_Auth::getInstance()->getIdentity();
		$user_id = $identity->user_id;
		$role = $identity->role;
		
		if (empty($this->lo_id)) {
			return "Missing learning objective information.";
		}
		if (empty($this->ta_id)) {
			return "Missing teaching activity information.";
		}

		$loFinder = new LearningObjectives();
		$lo = $loFinder->getLo($this->lo_id);
		if (count($lo->getMissingRequiredFields) != 0) {
			return "Missing required learning objective field(s).";
		}
		
		$taFinder = new TeachingActivities();
		$ta = $taFinder->getTa($this->ta_id);
		if (count($ta->getMissingRequiredFields) != 0) {
			return "Missing required teaching activity field(s).";
		}
		
		if ($this->created_by != $user_id) {
			if ($this->status != Status::$AWAITING_APPROVAL) {
				return "This learning objective and/or teaching activity has not been submitted for approval.";
			}
		} else { //stage coordinator does not need do the submission
			if (($this->status != Status::$IN_DEVELOPMENT) && ($this->status != Status::$AWAITING_APPROVAL)) {
				return "This learning objective and/or teaching activity does not need approval.";
			}
		}
		
		if (($result = UserAcl::checkApprovalPermission($ta)) !== TRUE) {
			return $result;
		}
		
		$statusFinder = new Status();
		$released_id = $statusFinder->getIdForStatus(Status::$RELEASED);
		$this->status = $released_id;
		$this->approved_by = $user_id;
		$this->date_approved = date('Y-m-d H:i:s');
		
		$resrc = array();
		if(!empty($link->type)) {
		    switch($link->type) {
		        case 'LO' : $resrc = array('lo' => $link->lo_id);                       break;
		        case 'TL' : $resrc = array('lo' => $link->lo_id, 'ta' => $link->ta_id); break;
		        case 'TA' : $resrc = array('ta' => $link->ta_id);                       break;
		    }
		}
		$db = Zend_Registry::get('db');
		$db->beginTransaction();
		try {
		    if (count($resrc) > 0) {
                $mediabankResourceHistory = new MediabankResourceHistory();
                $mediabankResourceHistory->setAddHistory($resrc);
		    }
			$this->save();
			$db->commit();
			$this->notifyObservers("post-insert");
		} catch (Exception $e) {
			$db->rollBack();
			throw $e;
		}
			
		$linkFinder = new LinkageLoTas();
		$count = $linkFinder->getTaLinkageCountWithStatus($this->ta_id, $released_id);
		if ($count == 1) {
			EventsUpdateService::refreshLinkedTAId($ta);
		}
		return TRUE;
	}
	
	/** If after deleting a linkage between learning objective and teaching activity makes a teaching activity
	 *  no long being used, then we need to update timetable so that no events is dependent on this ta.
	 */
	private function updateTimetable($uid) {
		$taFinder = new TeachingActivities();
		$ta = $taFinder->getTa($this->ta_id);
		
		$statusFinder = new Status();
		$rows = $ta->findDependentRowset('LinkageLoTas', 'TeachingActivity', $this->select()->where('status=?', $statusFinder->getIdForStatus(Status::$RELEASED)));
		
		//Zend_Registry::get('logger')->info("counter = ".count($rows));
		if (count($rows) == 0) {
			Zend_Registry::get('logger')->info(__METHOD__."- Refreshing teaching activity {$this->ta_id}");
			EventsUpdateService::refreshLinkedTAId($ta, $uid);
		}
	}
}