<?php

class LearningObjective extends Compass_Db_Table_Row_Observerable {
	
	//Fields from learning objective that will be indexed by lucene. Format: lucene index name => value.
	public static $luceneFields = array (
        'auto_id' => 'auto_id',
		'loid' => 'loid',
	    'discipline_ids' => 'allDisciplineIds',
	    'discipline_names' => 'allDisciplineNames',
	    'curriculumarea1' => 'curriculumarea1Name',
	    'curriculumarea2' => 'curriculumarea2Name',
	    'curriculumarea3' => 'curriculumarea3Name',
	    'theme' => 'theme',
	    'skill' => 'skill',
	    'system' => 'system',
        'title' => 'lo',
	    'review' => 'review',   
	    'keywords' => 'keywords',
	    'assessment_type' => 'assessment_type',
	    'achievement' => 'achievement',
	    'jmo' => 'jmo',
	    'gradattrib' => 'gradattrib',
	    'notes' => 'notes',	
        'created_by' => 'created_by',
	    'created_by_full_name' => 'created_by_full_name',
        'date_created' => 'date_created',
        'approved_by' => 'approved_by',
	    'approved_by_full_name' => 'approved_by_full_name',
        'date_approved' => 'date_approved',
        'date_next_review' => 'date_next_review',
		//'linked_ta_num' => 'linkedTA',
	    'synonyms' => 'synonyms',
	    'parents' => 'parents',
		'owner' => 'owner',
		'audience' => 'audience',
		'numStudentResources' => 'numStudentResources',
		'numStudentResourceSummaries' => 'numStudentResourceSummaries'
		);

    function __get($key) {
    	if (method_exists($this, $key)) {
    		return $this->$key();
    	}
    	//If we just need the integer value of a particular column in learning objective table instead of name in lookup table, 
    	//we have to append 'ID' to the column name. This is just an easy fix instead of changing method names and use the new
    	//method names on almost all the pages.
    	if (substr($key, -2) == 'ID') {
    		return parent::__get(substr($key, 0, strlen($key) - 2));
    	}
    	return parent::__get($key);
    }

    /* Saves curriculum reviews to a linkage table - link_lo_review */
    public function saveReviews($reviewArr) {
    	 
    	//fetch currently linked reviews first
    	$lo_review_Finder = new LinkageLoReviews();
    	$rows = $lo_review_Finder->fetchAll('lo_id='.$this->auto_id);
    	//Zend_Registry::get('logger')->DEBUG(__METHOD__. $this->auto_id ."=".$rows->count());

    	$existingRows = array();
    	for ($i = 0; $i < $rows->count(); $i++)
    	    $existingRows[] = $rows->getRow($i)->review_id;
    	//Zend_Registry::get('logger')->DEBUG(__METHOD__. print_r($existingRows, true). print_r($reviewArr, true));
    	 
    	//compare new reviews and old reviews, delete and add reviews accordingly
    	$deleteRows = array_diff($existingRows, $reviewArr);
    	foreach ($deleteRows as $reviewid) {
    		//Zend_Registry::get('logger')->DEBUG(__METHOD__. " Deleting ". $this->auto_id . '-' . $reviewid);
    		$lo_review_Finder->delete("lo_id=$this->auto_id AND review_id=$reviewid");
    	}
    	$addRows = array_diff($reviewArr, $existingRows);
    	foreach ($addRows as $reviewid) {
    		//Zend_Registry::get('logger')->DEBUG(__METHOD__. " Adding ". $this->auto_id . '-' . $reviewid);
    		$newRow = $lo_review_Finder->createRow();
    		$newRow->lo_id = $this->auto_id;
    		$newRow->review_id = $reviewid;
    		$newRow->save();
    	}
    }

    /* Saves assessment types to a linkage table - link_lo_assesstype
     * Similar to saveReviews method.
     */
    public function saveAssessTypes($assesstypeArr) {
    	$lo_assesstype_Finder = new LinkageLoAssessTypes();
    	$rows = $lo_assesstype_Finder->fetchAll('lo_id='.$this->auto_id);
    	//Zend_Registry::get('logger')->DEBUG(__METHOD__. $id ."=".$rows->count());
    	$existingRows = array();
    	for ($i = 0; $i < $rows->count(); $i++)
    	    $existingRows[] = $rows->getRow($i)->assesstype_id;
    	//Zend_Registry::get('logger')->DEBUG(__METHOD__. print_r($existingRows, true). print_r($assesstypeArr, true));
    	 
    	$deleteRows = array_diff($existingRows, $assesstypeArr);
    	foreach ($deleteRows as $assesstype_id) {
    		//Zend_Registry::get('logger')->DEBUG(__METHOD__. " Deleting ". $id . '-' . $assesstype_id);
    		$lo_assesstype_Finder->delete("lo_id=$this->auto_id AND assesstype_id=$assesstype_id");
    	}
    	$addRows = array_diff($assesstypeArr, $existingRows);
    	foreach ($addRows as $assesstype_id) {
    		//Zend_Registry::get('logger')->DEBUG(__METHOD__. " Adding ". $id . '-' . $assesstype_id);
    		$newRow = $lo_assesstype_Finder->createRow();
    		$newRow->lo_id = $this->auto_id;
    		$newRow->assesstype_id = $assesstype_id;
    		$newRow->save();
    	}
    }

    public function review() {
    	return $this->findRelatedManyToManyLinkAsStr("Reviews", "LinkageLoReviews");
    }

    public function assessment_type() {
    	return $this->findRelatedManyToManyLinkAsStr("AssessTypes", "LinkageLoAssessTypes");
    }

    /** Get LO owner */
    public function owner() {
    	return $this->findParentRow("Domains")->name;
    }
    
    /** Get LO audience as a string */
    public function audience() {
    	return join(', ', $this->audience_arr);
    }
    
    /** Get LO audience as an array. Index is domain id and value is domain name*/
    public function audience_arr() {
    	$result = $this->findManyToManyRowset('Domains', 'LinkageLoDomains');
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
     * Add audiences to a learning objective
     * Audience id(s) are passed in as an array
     * @param $audience_arr
     */
    public function saveAudience($audience_arr) {
    	$loDomain = new LinkageLoDomains();
    	foreach ($audience_arr as $audience_id) {
    		$loDomain->addAudience($this->auto_id, $audience_id);
    	}
    }
    
    private function findRelatedManyToManyLinkAsStr($role, $table) {
    	$result = $this->findManyToManyRowset($role, $table);
    	$count = $result->count();
    	//Zend_Registry::get('logger')->DEBUG(__METHOD__ . ": $role - $table - $count");
    	if ($count == 0)
    	    return '';
    	$strArray = array();
    	for ($i = 0; $i < $count; $i++)
    	    $strArray[] = $result->getRow($i)->name;
    	return join(', ', $strArray);
    }
    
    public function review_ids_array() {
    	$result = $this->findManyToManyRowset("Reviews", "LinkageLoReviews");
    	$count = $result->count();
    	if ($count == 0)
    	    return array();
    	$idsArray = array();
    	for ($i = 0; $i < $count; $i++)
    	    $idsArray[] = $result->getRow($i)->review_id;
    	return $idsArray;
    }

    public function assessment_type_ids_array() {
    	$result = $this->findManyToManyRowset("AssessTypes", "LinkageLoAssessTypes");
    	$count = $result->count();
    	if ($count == 0)
    	    return array();
    	$idsArray = array();
    	for ($i = 0; $i < $count; $i++)
    	    $idsArray[] = $result->getRow($i)->assesstype_id;
    	return $idsArray;    	
    }    

    private function findRelatedManyToManyLinkAsIntegers($role, $table) {
    	$result = $this->findManyToManyRowset($role, $table);
    	$count = $result->count();
    	//Zend_Registry::get('logger')->DEBUG(__METHOD__ . ": $role - $table - $count");
    	if ($count == 0)
    	    return '';
    	$idsArray = array();
    	for ($i = 0; $i < $count; $i++)
    	    $idsArray[] = $result->getRow($i)->review_id;
    	return $idsArray;
    }
        
    public function linkedTA() {
    	return $this->teachingactivities->count();
    }

    public function teachingactivities() {
    	$result = $this->findManyToManyRowset("TeachingActivities", "LinkageLoTas");
    	//Zend_Registry::get('logger')->DEBUG(__METHOD__  . "Linkage_lo_ta ". $result->count() . " entries");
    	return $result;
    }
    
    public function getLinkedTeachingActivityWithStatus($statusName) {
        $statusFinder = new Status();
        $statusNames = $statusFinder->getAllNames();
        $status = array_search($statusName, $statusNames);
        
        $db = Zend_Registry::get("db");
		$select = $db->select()
					->from(array('t' => 'teachingactivity'))
					->join(array('lk' => 'link_lo_ta'),	'lk.ta_id = t.auto_id', array('linkstrength' => 'strength'))
					->where("lk.lo_id={$this->auto_id} AND lk.status = $status");
		$stmt = $db->query($select);
		return $stmt->fetchAll();
    }

    public function getDiscipline($name, $disc) {
    	if ($result = $this->findParentRow("Discipline", $name)) {
    		return $result->$disc;
    	} else {
    		return '';
    	}
    }

    public function getCurriculumarea($name, $curriculumarea) {
    	if ($result = $this->findParentRow("CurriculumAreas", $name)) {
    		return $result->$curriculumarea;
    	} else {
    		return '';
    	}
    }

    public function curriculumarea1Name() {
    	return $this->getCurriculumarea('Curriculumarea1', 'curriculumarea');
    }

    public function curriculumarea2Name() {
    	return $this->getCurriculumarea('Curriculumarea2', 'curriculumarea');
    }

    public function curriculumarea3Name() {
    	return $this->getCurriculumarea('Curriculumarea3', 'curriculumarea');
    }
    
    public function discipline1() {
    	return $this->getDiscipline('Discipline1', 'auto_id');
    }

    public function discipline2() {
    	return $this->getDiscipline('Discipline2', 'auto_id');
    }

    public function discipline3() {
    	return $this->getDiscipline('Discipline3', 'auto_id');
    }

    public function discipline1Name() {
    	return $this->getDiscipline('Discipline1', 'name');
    }

    public function discipline2Name() {
    	return $this->getDiscipline('Discipline2', 'name');
    }

    public function discipline3Name() {
    	return $this->getDiscipline('Discipline3', 'name');
    }

    public function allDisciplineIds() {
    	return $this->discipline1 .' '. $this->discipline2 . ' '. $this->discipline3;
    }

    public function allDisciplineNames() {
    	$disciplineNames = array_map('trim', array($this->discipline1Name,$this->discipline2Name,$this->discipline3Name));
    	$result = array();
    	foreach($disciplineNames as $disciplineName) {
    		(strlen($disciplineName) > 0) ? array_push($result,$disciplineName) : '';
    	}
    	return implode(', ',$result);
    }

    public function docRef() {
    	return 'LearningObjective:'.$this->auto_id;
    }

    public function getLuceneDoc() {
    	$doc = new Zend_Search_Lucene_Document();
    	$doc->addField(Zend_Search_Lucene_Field::Keyword('docRef', $this->docRef));
    	$doc->addField(Zend_Search_Lucene_Field::Text('doctype', 'Learning Objective'));
    	foreach (self::$luceneFields as $k => $v) {
    		$doc->addField(Zend_Search_Lucene_Field::Text('lo_'.$k, $this->$v));
    	}
    	foreach (TeachingActivity::$luceneFields as $k => $v) {
    		$doc->addField(Zend_Search_Lucene_Field::Text('ta_'.$k, ''));
    	}
    	return $doc;
    }


    public function theme() {
    	return $this->theme1. ' '.$this->theme2. ' '.$this->theme3;
    }

    /** Simple lookup method */
    public function theme1() {
    	return $this->findParentRow("Themes", "Theme1")->name;
    }

    public function theme2() {
    	return $this->findParentRow("Themes", "Theme2")->name;
    }

    public function theme3() {
    	return $this->findParentRow("Themes", "Theme3")->name;
    }

    public function skill() {
    	return $this->findParentRow("Skills")->name;
    }

    public function system() {
    	return $this->findParentRow("Systems")->name;
    }

    public function achievement() {
    	return $this->findParentRow("Achievements")->name;
    }

    public function jmo() {
    	return $this->findParentRow("Jmos")->name;
    }

    public function gradattrib() {
    	return $this->findParentRow("GradAttribs")->name;
    }
    
    public function created_by_full_name() {
        return $this->getFullNames($this->created_by);
    }
    
    public function approved_by_full_name() {
        return $this->getFullNames($this->approved_by);
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
    
    public function getMissingRequiredFields() {
    	$fields = array();
    	if ($this->discipline1ID == '') {
    		$fields[] = 'Main Discipline';
    	}
        if ($this->theme1 == '') {
    		$fields[] = 'Theme';
    	}
        if ($this->system == '') {
    		$fields[] = 'System';
    	}
    	if (count($this->assessment_type_ids_array) == 0) {
    		$fields[] = 'Assessment Mothods';
    	}
    	$newlo = trim(strip_tags($this->lo));
    	if (strlen($newlo) <= 0) {
    	    $fields[] = 'Objective';
    	}
    	return $fields;
    }
    
    /** Gets synonyms for all the keywords of current learning objective */
    public function synonyms() {
        if (empty($this->keywords)) return '';
        
        $db = Zend_Registry::get('db');
        $result = array();
        $descriptorFinder = new Descriptors();
        $keyword_arr = explode('|', $this->keywords);
        foreach ($keyword_arr as $v) {
            $where = $db->quoteInto("headingtext = ?", $v);
            $syn = $descriptorFinder->fetchRow($where)->synonyms;
            if (!empty($syn))
                $result[] = $syn;
        }
        return join(', ', $result);
    }
    
    /** Gets parent names and synonyms */
    public function parents() {
        if (empty($this->keywords)) return '';
        
        $db = Zend_Registry::get('db');
        $result = array();
        $descriptorFinder = new Descriptors();
        $keyword_arr = explode('|', $this->keywords);
        foreach ($keyword_arr as $v) {
            $where = $db->quoteInto("headingtext = ?", $v);
            $treenumbers = $descriptorFinder->fetchRow($where)->treenumbers;
            $numbers_arr = explode(',', $treenumbers);
            foreach ($numbers_arr as $number) {
                $stack = explode('.', $number);
                array_pop($stack);
                while (count($stack) >= 1) {
                    $search_str = join('.', $stack);
                    $queryresult = $descriptorFinder->fetchAll("treenumbers LIKE '%{$search_str}' OR treenumbers LIKE '%{$search_str},%'");
                    foreach ($queryresult as $row) {
                        if (!empty($row->headingtext))
                            $result[] = $row->headingtext;
                        if (!empty($row->synonyms))
                            $result[] = $row->synonyms;
                    }
                    array_pop($stack);
                }
            }
        }
        return join(', ', $result);
    }
    
    /** Check whether a learning objective is editable
     *  LO is editable only when LO is released and 
     *  there are no requests of any kind that needs approval regarding this LO
     *  return true or error message
     */
    public function isEditable() {
    	$statusFinder = new Status();
    	$released = $statusFinder->getIdForStatus(Status::$RELEASED);
    	
    	$linkFinder = new LinkageLoTas();
    	$all_links = $linkFinder->fetchAll("lo_id = {$this->auto_id}");
    	//first check whether there are any requests pending approval
    	foreach ($all_links as $link) {
    		if (!(($link->status == Status::$RELEASED && $link->new_status == Status::$UNKNOWN) || $link->status == Status::$ARCHIVED)) {
    			return "There are already requests done by '{$link->created_by}' awaiting approval in relation to learning objective {$this->auto_id}.";
    		}
    	}
    	
    	//then check whether LO is currently being used
    	$count = $linkFinder->getLoLinkageCountWithStatus($this->auto_id, $released);
    	if ($count == 0) {
    		return "Learning objective {$this->auto_id} is not currently being used.";
    	}
    	return true;
    }
    
    /**
     * Get a list of released TA linked to current learning objective
     */
    public function getReleasedTa() {
    	$result = array();
    	$taFinder = new TeachingActivities();
    	
    	$ta_arr = $this->getLinkedTeachingActivityWithStatus(Status::$RELEASED);
    	foreach ($ta_arr as $row) {
    		$ta = $taFinder->getTa($row['auto_id']);
    		if ($ta->isReleased == true) {
    			$result[] = $row;
    		}
    	}
    	return $result;
    }
    
    /** Return LO creation date as YYYY/MM/DD or N/A */
    public function date_created() {
    	return Utilities::createDate(parent::__get('date_created'));
    }
    
	/** Return LO approval date as YYYY/MM/DD or N/A */
    public function date_approved() {
    	return Utilities::createDate(parent::__get('date_approved'));
    }
    
    /**
	 * Archive a learning objective. Depending on the role, it may or may not be archived immediately.
	 */
	public function archive() {
		$all_archived = 'yes';
		$identity = Zend_Auth::getInstance()->getIdentity();
		$user_id = $identity->user_id;
		$timestamp = date('Y-m-d H:i:s');
		
		$statusFinder = new Status();
		$status = $statusFinder->getAllNames();
		$status_ids = array_flip($status);
		
		//Archive the linkage first
		$linkFinder = new LinkageLoTas();
		$links = $linkFinder->getLoLinkageWithStatus($this->auto_id, $status_ids[Status::$RELEASED]);
		
		$db = Zend_Registry::get('db');
		$db->beginTransaction();
		try {
			$archived_links = array(); //needed by lucene indexer to update index
			$taFinder = new TeachingActivities();
			foreach ($links as $row) {
				$ta = $taFinder->getTa($row->ta_id);
				if ((UserAcl::checkTaPermission($ta, UserAcl::$ARCHIVE)) === TRUE && UserAcl::isStagecoordinatorOrAbove()) {
					Zend_Registry::get('logger')->DEBUG(__METHOD__ . ": Archiving linkage {$row->auto_id}");
					$row->saveToHistoryTable();
					$row->approved_by = $user_id;
					$row->date_approved = $timestamp;
					$row->status = $status_ids[Status::$ARCHIVED];
					$row->new_status = $status_ids[Status::$UNKNOWN];
					$row->save();
					
					$archived_links[] = $row;
				} else {
					Zend_Registry::get('logger')->DEBUG(__METHOD__ . ": Submit archiving linkage {$row->auto_id}");
					$row->modified_by = $user_id;
					$row->date_modified = $timestamp;
					$row->new_status = $status_ids[Status::$ARCHIVED];
					$row->type = 'LO';
					$row->save();
					$all_archived = 'no';
				}
			}
			$db->commit();
			
			foreach ($archived_links as $archived_link) {
				$archived_link->notifyObservers("post-delete");
				
				$count = $linkFinder->getLoLinkageCountWithStatus($archived_link->ta_id, $status_ids[Status::$RELEASED]);
				if ($count == 0) {
					EventsUpdateService::refreshLinkedTAId($archived_link->ta_id);
				}
			}
			return $all_archived;
		} catch (Exception $e) {
			$db->rollBack();
			throw $e;
		}
	}
	
    /**
     * Get the id of the lastest released version of learning objective
     */
    public function latestReleasedVersionId() {
    	$statusFinder = new Status();
    	$released_id = $statusFinder->getIdForStatus(Status::$RELEASED);
    	
    	$db = Zend_Registry::get("db");
    	$select = $db->select()
    				->from(array('l' => 'learningobjective'), array('auto_id'))
    				->join(array('lk' => 'link_lo_ta'), 'l.auto_id = lk.lo_id', array())
    				->where("lk.status = ? ", $released_id)
    				->where("lk.lo_id IN (SELECT auto_id FROM learningobjective WHERE loid = (SELECT loid FROM learningobjective WHERE auto_id = {$this->auto_id}))")
    				->order('version DESC');
    	$result = $db->fetchRow($select);
    	if (isset($result['auto_id'])) {
    		return $result['auto_id'];
    	} else {
    		return $this->auto_id;
    	}
    }
    /**
     * returns the number of student developed resources attached to this LO
     */
    public function numStudentResources() {
		//$srlink = new StudentResourceLink();
		$db = Zend_Registry::get("db");
		//$count = $srlink->fetchCount("loid={$loid}", 'auto_id DESC');
		$count = $db->fetchOne( 'SELECT COUNT(*) AS count FROM studentresourcelink where loid='.$this->loid );

		
		return($count);
    }
    /**
     * returns the number of student developed summaries attached to this LO. Should be 0 or 1.
     */
    public function numStudentResourceSummaries() {
		//$srlink = new StudentResourceLink();
		$db = Zend_Registry::get("db");
		//$count = $srlink->fetchCount("loid={$loid}", 'auto_id DESC');
		$count = $db->fetchOne( 'SELECT COUNT(*) AS count FROM studentresourcelink where category=\'Summary\' and loid='.$this->loid );

		
		return($count);
    }
    
}