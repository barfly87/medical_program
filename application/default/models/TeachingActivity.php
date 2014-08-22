<?php

class TeachingActivity extends Compass_Db_Table_Row_Observerable {
	public static $luceneFields = array (
        'auto_id' => 'auto_id',
        'title' => 'name',
        'type' => 'type',
        'pbl' => 'pbl',
        'stage' => 'stage',
        'block' => 'block',
	    'block_no' => 'block_no',
        'block_week' => 'block_week',
	    'block_week_zero_padded' => 'block_week_zero_padded',
        'sequence_num' => 'sequence_num',
        'student_grp' => 'student_grp',
		'principal_teacher' => 'principal_teacher',
	    'principal_teacher_full_name' => 'principal_teacher_full_name',
		'current_teacher' => 'current_teacher',
	    'current_teacher_full_name' => 'current_teacher_full_name',
		'author_id' => 'created_by',
	    'created_by_full_name' => 'created_by_full_name',
		'date_created' => 'date_created',
		'approved_by_full_name' => 'approved_by_full_name',
		'date_approved' => 'date_approved',
		'reviewed_by' => 'reviewed_by',
        'reviewed_by_full_name' =>'reviewed_by_full_name', 
		'date_reviewed' => 'date_reviewed',
		'notes' => 'notes',
		//'linked_lo_num' => 'linkedLO',
		'release_date' => 'release_date',
	    'evaluate_count' => 'evaluate_count',
		'resource_links' => 'resource_links',
	    'resource_podcast' => 'resource_podcast',
		'owner' => 'owner',
		'audience' => 'audience',
	    'term' => 'term',
	    'activitytype_id' => 'activitytype_id'
    );
    
    private $resourceLinksExist = null;
    private $resourcesExist = null;
    private $resourcePodcastExist = null;
    
    //format: database column name => display name in error message
    public static $requiredFields = array(
    	'name' => 'Title',
    	'type' => 'Type',
    	'stage' => 'Stage',
    	'block' => 'Block',
    );
    
    
    protected function _insert() {
        if (is_null($this->date_created)) {
        	$this->date_created = date('Y-m-d H:i:s');
        }  	 
        parent::_insert();
    }

    function __get($key) {
        if(method_exists($this, $key)){
        	return $this->$key();
        }
        //If we just need the integer value of a particular column in teachingactivity table instead of name in lookup table, 
    	//we have to append 'ID' to the column name. This is just an easy fix instead of changing method names and use the new
    	//method names on almost all the pages.
    	if (substr($key, -2) == 'ID') {
    		return parent::__get(substr($key, 0, strlen($key) - 2));
    	}
        return parent::__get($key);
    }

    /** Get TA activity type */
    public function type() {
        return $this->findParentRow("ActivityTypes")->name;
    }
    
    public function activitytype_id() {
        return $this->findParentRow("ActivityTypes")->auto_id;
    }

    /** Get TA block name */
    public function block() {
        return $this->findParentRow("Blocks")->name;
    }
    
    /** Get TA block number */
    public function block_no() {
    	$block_no = null;
    	$sbp = new StageBlockSeqs();
    	try {
    		$block_no = $sbp->getBlockNo($this->blockID);
    	} catch (Exception $e) {
    		//ignore
    	}
		return $block_no;
    }    
    
    /** Get TA block Id */
    public function block_id() {
        return parent::__get('block');
    }

    /** Get TA PBL name */
    public function pbl() {
    	//$identity = Zend_Auth::getInstance()->getIdentity();
    	//$domain = $identity->domain;	
    	//if ($domain == 'Dentistry' && $this->findParentRow("Pbls")->name_dent != '')
    	//	return $this->findParentRow("Pbls")->name_dent;
    	//else
        	return $this->findParentRow("Pbls")->name;
    }

    /** Get TA student group name */
    public function student_grp() {
        return $this->findParentRow("StudentGroups")->name;
    }
    
    /** Get TA block week */
	public function block_week() {
        return $this->findParentRow("BlockWeeks")->weeknum;
    }
    
    /** Get TA block week with a leading 0 if it's less than 10 */
    public function block_week_zero_padded() {
        $weeknum = $this->findParentRow("BlockWeeks")->weeknum;
        if(strlen($weeknum) > 0 ) {
            return ($weeknum < 10) ? '0'.$weeknum : $weeknum ;
        } else {
            return $weeknum;
        }
    }
    
    /** Get TA sequence number */
	public function sequence_num() {
        return $this->findParentRow("SequenceNumbers")->seqnum;
    }
    
    /** Get TA stage */
	public function stage() {
        return $this->findParentRow("Stages")->stage;
    }
    
    public function year() {
    	return $this->findParentRow("Years")->year;
    }
    
    /** Get TA term */
	public function term() {
        return $this->findParentRow("Terms")->term;
    }
    
    /** Get TA owner */
    public function owner() {
    	return $this->findParentRow("Domains")->name;
    }
    
    /** Get TA audience as a string */
    public function audience() {
    	return join(', ', $this->audience_arr);
    }
    
    /** Get TA audience as an array. Index is domain id and value is domain name*/
    public function audience_arr() {
    	$result = $this->findManyToManyRowset('Domains', 'LinkageTaDomains');
    	$count = $result->count();
    	$strArray = array();
    	for ($i = 0; $i < $count; $i++) {
    		$index = $result->getRow($i)->auto_id;
    	    $strArray[$index] = $result->getRow($i)->name;
    	}
    	ksort($strArray);
    	return $strArray;
    }
    
    /** 
     * Add audiences to a teaching activity
     * Audience id(s) are passed in as an array
     * @param $audience_arr
     */
    public function saveAudience($audience_arr) {
    	$taDomain = new LinkageTaDomains();
    	foreach ($audience_arr as $audience_id) {
    		$taDomain->addAudience($this->auto_id, $audience_id);
    	}
    }
    
    public function evaluate_count() {
        $studentEvaluate = new StudentEvaluate();
        return $studentEvaluate->getCountOfEvaluationsForTaId($this->auto_id);
    }

    public function learningobjectives() {
    	$statusFinder = new Status();
    	$status = $statusFinder->getIdForStatus(Status::$RELEASED);
    	$linkFinder = new LinkageLoTas();
    	$select = $linkFinder->select()->where('status = ?', $status);
        return $this->findManyToManyRowset("LearningObjectives", "LinkageLoTas", NULL, NULL, $select);
    }

    /**
     * Get a list of learning objectives that linked to current teaching activity that has status $statusName
     * @param $statusName
     */
    public function getLinkedLearningObjectiveWithStatus($statusName) {
    	$statusFinder = new Status();
        $status = $statusFinder->getIdForStatus($statusName);
        
        $db = Zend_Registry::get("db");
		$select = $db->select()
					->from(array('l' => 'learningobjective'))
					->joinLeft(array('lk' => 'link_lo_ta'), 'lk.lo_id = l.auto_id', array('linkstrength' => 'strength', 'lo_order' => 'lo_order'))
					->joinLeft(array('lc1' => 'lk_curriculumareas'), 'l.curriculumarea1 = lc1.auto_id', array('curriculumarea1Name' => 'curriculumarea', 'curriculumarea1order' => 'order_by'))
					->joinLeft(array('lk_disc' => 'lk_discipline'), 'l.discipline1 = lk_disc.auto_id', array('discipline1Name' => 'name'))
					->where("lk.ta_id={$this->auto_id} AND lk.status = $status")
					->order('discipline1Name')
					->order('order_by')
					->order('lo_order');
		Zend_Registry::get('logger')->DEBUG(__METHOD__ . ": ". $select->__toString());
		$stmt = $db->query($select);
		return $stmt->fetchAll();
    }
    
    /**
     * Get a list of learning objectives that linked to current teaching activity that are released and in a particular discipline and curriculum area
     */
    public function getReleasedLearningObjectiveInDisciplineAndArea($disc_id, $area_id) {
    	$statusFinder = new Status();
    	$status = $statusFinder->getIdForStatus(Status::$RELEASED);
    	
    	$db = Zend_Registry::get("db");
    	$select = $db->select()
    				->from(array('l' => 'learningobjective'))
    				->joinLeft(array('lk' => 'link_lo_ta'), 'lk.lo_id = l.auto_id', array('linkstrength' => 'strength', 'lo_order' => 'lo_order'))
    				->where("lk.ta_id={$this->auto_id} AND lk.status = $status")
    				->where("discipline1 = ?", $disc_id)
    				->where("curriculumarea1 = ?", $area_id)
    				->order("lo_order");
    	$stmt = $db->query($select);
    	Zend_Registry::get('logger')->DEBUG(__METHOD__ . ": ". $select->__toString());
    	return $stmt->fetchAll();
    }
    
    /**
     * Get a list of learning objectives that linked to current teaching activity that has been released
     */
    public function linkedLO() {
        return count($this->getLinkedLearningObjectiveWithStatus(Status::$RELEASED));
    }
    
    /** Return an array of required fields that are missing */
    public function getMissingRequiredFields() {
    	$fields = array();
    	foreach (self::$requiredFields as $k => $v) {
    		if (empty($this->$k)) {
    			$fields[] = $v;
    		}
    	}
    	return $fields;
    }
    
    /** Return TA creation date as YYYY/MM/DD or N/A */
    public function date_created() {
    	return Utilities::createDate(parent::__get('date_created'));
    }
    
    public function date_created_org() {
        return parent::__get('date_created');
    }
    
	/** Return TA approval date as YYYY/MM/DD or N/A */
    public function date_approved() {
    	return Utilities::createDate(parent::__get('date_approved'));
    }
    
    /** Return TA review date as YYYY/MM/DD or N/A */
    public function date_reviewed() {
    	return Utilities::createDate(parent::__get('date_reviewed'));
    }
    
    public function release_date() {
    	try {
    		$dates = array();
    		$config = Zend_Registry::get('config');
    		//no Events application to control the release dates like Tabuk
    		if (!isset($config->event_wsdl_uri)) {
    			return $dates;
    		}
    		
    		$client = new SoapClient($config->event_wsdl_uri, array('trace' => 1, 'exceptions' => 1));
	    	//if ($this->owner == 'Dentistry') {
	    	//	$client = new Zend_Soap_Client("http://eventsdent.med.usyd.edu.au/webservice/call?wsdl");
	    	//}
	    	$r_types = $config->stage3->release_date->types->toArray();
	    	$r_blocks = $config->stage3->release_date->blocks->toArray();
	    	
    		$year = date('Y');
	    	$stage = (int)($this->stage);
	    	$block = $this->block_no;
	    	$bw = $this->block_week;
	    	$eventtype = isset(ActivityTypes::$compass_events_mapping[$this->typeID]) ? ActivityTypes::$compass_events_mapping[$this->typeID] : 0;
	    	$seq = $this->sequence_num;
	    	$domain = $this->ownerID;
	    	
	    	//release date for stage 3 material, medicine only
	    	if ($stage > 2) {
	    		//Clinical reasoning cases in CAH block
	    		if (in_array($this->typeID, $r_types) && in_array($this->blockID, $r_blocks)) {
	    			if (empty($bw)) {
	    				Zend_Registry::get('logger')->DEBUG(__METHOD__ . ": EMPTY WEEK NO.");
	    				return $dates;
	    			}
	    			//$result = $client->getReleaseDatesByDomainYearBlockWeekTypeSeq(1, $year, $block, $bw, $eventtype, $seq);
	    			$result = $client->__soapCall('getReleaseDatesByDomainYearBlockWeekTypeSeq', array(1, $year, $block, $bw, $eventtype, $seq));
	    			Zend_Registry::get('logger')->DEBUG(__METHOD__. " STAGE 3 - domain:1|y:{$year}|b:{$block}|w:{$bw}|type:{$eventtype}|seq:{$seq}");
	    			if (!isset($result)) { //web service error
	    				return $dates;
	    			}
	    			if (count($result) != 0) {
	    				foreach ($result as $v) {
	    					$groups_arr = $v['studentGroup'];
	    					foreach ($groups_arr as $group) {
	    						$dates[$group] = $v['releaseDate'];
	    					}
	    				}
	    				ksort($dates);
	    			}
	    			return $dates;
	    		} else {
	    			$result = array('cohort'.($year - 3) => "{$year}-01-01 06:00:00", 'cohort'.($year - 2) => "{$year}-01-01 06:00:00");
	    			if (isset($config->release_date->stage3)) {
	    				$extra_dates = $config->release_date->stage3->toArray();
	    				foreach ($extra_dates as $s => $t) {
	    					$cur_stage = substr($s, -1);
	    					$result['cohort'.($year - $cur_stage + 1)] = "{$year}-{$t}";
	    				}
	    			}
	    			ksort($result);
	    			return $result;
	    		}
	    	}

    		if (empty($bw)) { //unknown problem
	    		return $dates;
	    	}

	    	//release date for stage 1 and 2 material
	    	Zend_Registry::get('logger')->DEBUG(__METHOD__. " STAGE 1 & 2 - domain:{$domain}|y:{$year}|b:{$block}|w:{$bw}|type:{$eventtype}|seq:{$seq}");  	
	    	//TODO need to sync eventtype between compass and events
    		if (in_array($eventtype, ActivityTypes::$compass_events_mapping_required_arr) && !empty($seq)) {
    			Zend_Registry::get('logger')->DEBUG("Calling getReleaseDatesByDomainYearBlockWeekTypeSeq");
    			//TODO check whether clinical schools need their own milestone release dates
    			//$result = $client->getReleaseDatesByDomainYearBlockWeekTypeSeq($domain, $year, $block, $bw, $eventtype, $seq);
    			$result = $client->__soapCall('getReleaseDatesByDomainYearBlockWeekTypeSeq', array($domain, $year, $block, $bw, $eventtype, $seq));
    		    if (!isset($result)) { //web service error
    				return $dates;
    			}
    		    if (count($result) != 0) {
		    		foreach ($result as $v) {
			    		$groups_arr = $v['studentGroup'];
						foreach ($groups_arr as $group) {
							$dates[$group] = $v['releaseDate']; 
						}
			    	}
			    	ksort($dates);
			    	return $dates;
	    		}
    		}
    		
    		//no release dates based on teaching activity detail, try milestone. Only medicine has milestone dates
    		Zend_Registry::get('logger')->DEBUG("Trying milestone: Calling getMilestoneDatesByDomainYearBlockWeek");
    		//$result = $client->getMilestoneDatesByDomainYearBlockWeek(1, $year, $block, $bw);
    		$result = $client->__soapCall('getMilestoneDatesByDomainYearBlockWeek', array(1, $year, $block, $bw));

    		if (!isset($result)) { //web service error
    			return $dates;
    		}
    		if (count($result) != 0) {
	    		foreach ($result as $v) {
		    		$groups_arr = $v['studentGroup'];
					foreach ($groups_arr as $group) {
						$dates[$group] = $v['releaseDate']; 
					}
		    	}
		    	ksort($dates);
		    	
    		}
	    	return $dates;
    	} catch(Exception $e){
    		throw $e;
    	}
    }
    
    /**
     * Get release date info from lucene index
     */
    public function release_date_from_lucene() {
    	$dates = array();
    	$index = Compass_Search_Lucene::open(SearchIndexer::getIndexDirectory());
    	$results = $index->find("+ta_auto_id:{$this->auto_id}");
    	$fields = $index->getFieldNames();
    	if (isset($results[0])) {
    		$doc = $results[0]->getDocument();
    		foreach ($fields as $field) {
    			if (preg_match('/[0-9]{4}/', $field)) {
    				try {
    					$value = $doc->getFieldValue($field);
    					list($prefix, $group) = split('_', $field);
    					$dates[$group] = date('Y-m-d H:i:s', $value);
    				} catch (Exception $e) {
    					//ignore
    				}
    			}
    		}
    	}
    	return $dates;
    }
    
    public function principal_teacher_full_name() {
        return $this->getFullNames($this->principal_teacher);
    }
    
    public function current_teacher_full_name() {
        return $this->getFullNames($this->current_teacher);
    }
    
    public function created_by_full_name() {
        return $this->getFullNames($this->created_by);
    }
    
	public function approved_by_full_name() {
        return $this->getFullNames($this->approved_by);
    }
    
    public function reviewed_by_full_name() {
        return $this->getFullNames($this->reviewed_by);
    }
    
    /** Get principal teacher as an array of uids */
    public function principal_teacher_uid_arr () {
    	return $this->getUidArrayFromString($this->principal_teacher);
    }
    
    /** Get current teacher as an array of uids */
    public function current_teacher_uid_arr() {
    	return $this->getUidArrayFromString($this->current_teacher);
    }
    
    /** Return an array of uids from a string of uids spearated by comma */
    private function getUidArrayFromString($str) {
    	$result = array();
    	$id_arr = explode(',', $str);
    	foreach ($id_arr as $id) {
    		$id = trim($id);
    		if ('' != $id) {
    			$result[] = $id;
    		}
    	}
    	return $result;
    }
    
    private function getFullNames($uidString) {
        if(! empty($uidString)) {
            $uidsFullName = UserService::getUidsFullName($uidString);
            if(! empty($uidsFullName)) {
                if(is_array($uidsFullName)) {
                    return implode(', ',$uidsFullName);
                } else if(is_string($uidsFullName)) {
                    return $uidsFullName;
                }
            }
        }        
        return $uidString;
    }
    
    
    /** Check whether a teaching activity is editable
     *  TA is editable only when TA is released and 
     *  there are no requests of any kind that needs approval regarding this TA
     *  return true or error message
     */
    public function isEditable() {
    	$statusFinder = new Status();
    	$released = $statusFinder->getIdForStatus(Status::$RELEASED);
    	$linkFinder = new LinkageLoTas();
    	
    	//first check whether there are any requests pending approval
    	$all_links = $linkFinder->fetchAll("ta_id = {$this->auto_id}");
    	foreach ($all_links as $link) {
    		if (!(($link->status == Status::$RELEASED && $link->new_status == Status::$UNKNOWN) || $link->status == Status::$ARCHIVED)) {
    			return "There are already requests awaiting approval in relation to teaching activity {$this->auto_id}.";
    		}
    	}
    	
    	//then check whether TA is currently being used
    	$count = $linkFinder->getTaLinkageCountWithStatus($this->auto_id, $released);
    	if ($count == 0) {
    		return "Teaching activity {$this->auto_id} is not currently being used.";
    	}
    	return true;
    }
    
    /**
     * Check whether a student can see the teaching activity
     */
	public function isReleased() {
		if (UserAcl::isStudent()) {
			$identity = Zend_Auth::getInstance()->getIdentity();
			
			//student has to be on the audience list to view teaching activity
			if (count(array_intersect($identity->all_domains, $this->audience_arr)) == 0) {
				return false;
			}

			if (!isset(Zend_Registry::get('config')->event_wsdl_uri)) {
				if ($identity->cohort <= date('Y') + 1 - (int)($this->stage)) {
					return true;
				} else {
					return false;
				}
			} else {
				$stage3 = (int)(date('Y')) - 2;
				$ta_stage = (int)($this->stage);
				
				//stage 3 student can see stage 1 and stage 2 material
				if ($identity->cohort <= $stage3) {
					if ($ta_stage == 1 || $ta_stage == 2) {
						return true;
					}
				}
			
				//stage 2 student can see stage 1 material
				if ($ta_stage == 1 && ($identity->cohort == $stage3 + 1)) {
					return true;
				}
				//otherwise release date is based on group permission
				//special case for CRS type within CAH block in stage 3
				$dates = $this->release_date;
				foreach($identity->groups as $group) {
					if (isset($dates[$group]) && (strtotime('now') > strtotime($dates[$group]))) {
						return true;
					}
				}
				return false;
			}
		}
		return true;
	}
	
	/**
	 * Archive a teaching activity. Depending on the role, it may or may not be archived immediately.
	 */
	public function archive() {
		$identity = Zend_Auth::getInstance()->getIdentity();
		$user_id = $identity->user_id;
		$statusFinder = new Status();
		$timestamp = date('Y-m-d H:i:s');
		
		$linkFinder = new LinkageLoTas();
		$links = $linkFinder->getTaLinkageWithStatus($this->auto_id, $statusFinder->getIdForStatus(Status::$RELEASED));
		
		$db = Zend_Registry::get('db');
		$db->beginTransaction();
		try {
			if (UserAcl::isStagecoordinatorOrAbove()) {
				$this->approved_by = $user_id;
				$this->date_approved = $timestamp;
				$this->save();
				
				foreach ($links as $row) {
					Zend_Registry::get('logger')->DEBUG(__METHOD__ . ": Archiving linkage {$row->auto_id}");
					$row->saveToHistoryTable();
					$row->approved_by = $user_id;
					$row->date_approved = $timestamp;
					$row->status = $statusFinder->getIdForStatus(Status::$ARCHIVED);
					$row->new_status = $statusFinder->getIdForStatus(Status::$UNKNOWN);
					$row->save();
				}
			} else {
				foreach ($links as $row) {
					Zend_Registry::get('logger')->DEBUG(__METHOD__ . ": Submit archiving linkage {$row->auto_id}");
					$row->modified_by = $user_id;
					$row->date_modified = $timestamp;
					$row->new_status = $statusFinder->getIdForStatus(Status::$ARCHIVED);
					$row->type = 'TA';
					$row->save();
				}
			}
			$db->commit();
			
			if (UserAcl::isStagecoordinatorOrAbove()) {
				Zend_Registry::get('logger')->DEBUG(__METHOD__ . ": Updating lucene index");
				foreach ($links as $row) {
					$row->notifyObservers("post-delete");
				}
				
				Zend_Registry::get('logger')->DEBUG(__METHOD__ . ": Updating Events using ta id {$this->auto_id}");
				EventsUpdateService::refreshLinkedTAId($this);
			}
		} catch (Exception $e) {
			$db->rollBack();
			throw $e;
		}
	}
	
    public function getResourcesByType($typeid) {
    	$resourceFinder = new MediabankResource();
    	return $resourceFinder->getResourcesByType($this->auto_id, 'ta', $typeid);
    }
    
    public function getResourcesContentByType($typeid) {
        $typeid = (int)$typeid;
        $result = array();
        if($typeid > 0) {
            $mediabankService = new MediabankResourceService();
            $resources = $this->getResourcesByType($typeid);
            $allowEdit = UserAcl::checkTaPermission($this, UserAcl::$EDIT);
            foreach($resources as $resource) {
                $row = array();
                $row['allowEdit'] = $allowEdit;
                $row['resource'] = $resource;
                $result[] = $row;
            }
        }
        return $result;
    }
    
    public function isUserAllowedToEditResource() {
        return UserAcl::checkTaPermission($this,UserAcl::$EDIT);
    }
 
    public function resources() {
        if(is_null($this->resourcesExist)) {
            $taResourceView = new TaResourceView($this->auto_id);
            $this->resourcesExist = $taResourceView->getResources();
        }
        return $this->resourcesExist;
    }
    
    public function resource_links() {
        if(is_null($this->resourceLinksExist)) {
            $taResourceLucene = new TaResourceLucene($this->auto_id);
            $this->resourceLinksExist = $taResourceLucene->getResources();
        }
        return $this->resourceLinksExist;
    }
    
    public function resource_podcast() {
        if(is_null($this->resourcePodcastExist)) {
            $taResourcePodcast = new TaResourcePodcast($this->auto_id);
            $this->resourcePodcastExist = $taResourcePodcast->getResources();
        }
        return $this->resourcePodcastExist;
    }
    
    public function prevTaId() {
    	$taFinder = new TeachingActivities();
    	$select = $taFinder->select()
    				->where('block = ?', $this->blockID)
    				->where('block_week = ?', $this->block_weekID)
    				->where('type = ?', $this->typeID)
    				->where('sequence_num = ?', $this->sequence_numID - 1);
    	$rows = $taFinder->fetchAll($select);
    	
    	$linkFinder = new LinkageLoTas();
    	$statusFinder = new Status();
    	$statusId = $statusFinder->getIdForStatus(Status::$RELEASED);
    	foreach ($rows as $row) {
    		$releasedRows = $linkFinder->fetchAll("ta_id = {$row->auto_id} AND status= $statusId");
    		if (count($releasedRows) != 0) {
    			return $row->auto_id;
    		}
    	}
    	return NULL;
    }
    
    public function nextTaId() {
    	$taFinder = new TeachingActivities();
    	$select = $taFinder->select()
    				->where('block = ?', $this->blockID)
    				->where('block_week = ?', $this->block_weekID)
    				->where('type = ?', $this->typeID)
    				->where('sequence_num = ?', $this->sequence_numID + 1);
		$rows = $taFinder->fetchAll($select);

    	$linkFinder = new LinkageLoTas();
    	$statusFinder = new Status();
    	$statusId = $statusFinder->getIdForStatus(Status::$RELEASED);
    	foreach ($rows as $row) {
    		$releasedRows = $linkFinder->fetchAll("ta_id = {$row->auto_id} AND status= $statusId");
    		if (count($releasedRows) != 0) {
    			return $row->auto_id;
    		}
    	}
    	return NULL;
    }
    
    public function getCurrentStatus() {
    	$status = 'Not being used';
    	
    	$linkFinder = new LinkageLoTas();
    	$select = $linkFinder->select(array('status', 'new_status'))->where('ta_id = ?', $this->auto_id);
    	$rows = $linkFinder->fetchAll($select);
    	
    	$inSubmission = FALSE;
    	foreach ($rows as $row) {
    		if ($row['status'] === Status::$RELEASED) {
    			$status = 'Released';
    		    if ($row['new_status'] !== Status::$UNKNOWN) {
	    			$status =  'Released, request pending';
	    			break;
    			}
    		}
    		if ($row['status'] === Status::$IN_DEVELOPMENT || $row['status'] === Status::$AWAITING_APPROVAL || 
    			$row['status'] === Status::$NEW_VERSION) {
    			$inSubmission = TRUE;
    		}
    	}
    	if ($inSubmission) {
	    	if ($status === 'Released') {
	    		$status = 'Released, part of submission';
	    	} else if ($status === 'Not being used') {
	    		$status = 'Not released, part of submission';
	    	}
    	}
    	if (strpos($status, "Released") === 0) {
    		return '<span style="color:green">'.$status.'</span>';
    	} else {
    		return '<span style="color:red">'.$status.'</span>';
    	}
    }
    
    /**
     * Get the lastest version of released teaching activity
     */
    public function latestReleasedVersionId() {
    	$statusFinder = new Status();
    	$released_id = $statusFinder->getIdForStatus(Status::$RELEASED);
    	
    	$db = Zend_Registry::get("db");
    	$select = $db->select()
    				->from(array('t' => 'teachingactivity'), array('auto_id'))
    				->join(array('lk' => 'link_lo_ta'), 't.auto_id = lk.ta_id', array())
    				->where("lk.status = ? ", $released_id)
    				->where("lk.ta_id IN (SELECT auto_id FROM teachingactivity WHERE taid = (SELECT taid FROM teachingactivity WHERE auto_id = {$this->auto_id}))")
    				->order('version DESC');
    	$result = $db->fetchRow($select);
    	if (isset($result['auto_id'])) {
    		return $result['auto_id'];
    	} else {
    		return $this->auto_id;
    	}
    	
    }
    
    
    /**
     * Get the url in Events that will display this teaching activity
     */
    public function getEventUrl() {
    	$config = Zend_Registry::get('config');
    	if (!isset($config->event_wsdl_uri)) {
    		return '';
    	}
    	
    	$eventtype = isset(ActivityTypes::$compass_events_mapping[$this->typeID]) ? ActivityTypes::$compass_events_mapping[$this->typeID] : 0;
    	$bw = $this->block_week;
    	$seq = $this->sequence_num;
    	if ($eventtype == 0 || empty($bw) || empty($seq)) {
    		return '';
    	}
    	
    	$domain = $this->ownerID;
    	$block = $this->block_no;
    	$client = new Zend_Soap_Client($config->event_wsdl_uri);
    	$result = $client->findEventDateByDomainBlockWeekTypeSeq($domain, $block, $bw, $eventtype, $seq);
    	if (!isset($result) || $result == '') { //web service error or no dates found
    		return '';
    	}
    	
    	$event_app_uri = substr($config->event_wsdl_uri, 0, strpos($config->event_wsdl_uri, 'webservice'));
    	$identity = Zend_Auth::getInstance()->getIdentity();
    	if (UserAcl::isAdmin()) {
    		return $event_app_uri. 'calendar/week/anchor/'.$result;
    	} else if (UserAcl::isDomainAdminOrAbove() && ($identity->domain == $this->owner)) {
    		return $event_app_uri. 'calendar/week/anchor/'.$result;
    	} else if (UserAcl::isStaffOrAbove()) {
    		return $event_app_uri. 'calendar/weekpreview/anchor/'.$result;
    	} else {
    		$stage = (int)($this->stage);
    		if ($identity->stage != $stage) {
    			return '';
    		}
    		return $event_app_uri.'calendar/weekmine/anchor/'.$result;
    	}
    }
}
