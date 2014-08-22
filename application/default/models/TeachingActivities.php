<?php

class TeachingActivities extends Zend_Db_Table_Abstract {
    protected $_name = 'teachingactivity';
    protected $_primary = 'auto_id';
    protected $_rowClass = 'TeachingActivity';
    protected $_referenceMap = array(
        'Types' => array(
            'columns' => array('type'),
            'refTableClass' => 'ActivityTypes',
            'refColumns' => array('auto_id')
        ),
        'Blocks' => array(
            'columns' => array('block'),
            'refTableClass' => 'Blocks',
            'refColumns' => array('auto_id')
        ),
        'Pbls' => array(
            'columns' => array('pbl'),
            'refTableClass' => 'Pbls',
            'refColumns' => array('auto_id')
        ),
        'StudentGroups' => array(
            'columns' => array('student_grp'),
            'refTableClass' => 'StudentGroups',
            'refColumns' => array('auto_id')
        ),
        'BlockWeeks' => array(
            'columns' => array('block_week'),
            'refTableClass' => 'BlockWeeks',
            'refColumns' => array('auto_id')
        ),
        'SequenceNumbers' => array(
            'columns' => array('sequence_num'),
            'refTableClass' => 'SequenceNumbers',
            'refColumns' => array('auto_id')
        ),
        'Stages' => array(
            'columns' => array('stage'),
            'refTableClass' => 'Stages',
            'refColumns' => array('auto_id')
        ),
        'Domains' => array(
        	'columns' => array('owner'),
        	'refTableClass' => 'Domains',
        	'refColumns' => array('auto_id')
        ),
        'Terms' => array(
        	'columns' => array('term'),
        	'refTableClass' => 'Terms',
        	'refColumns' => array('auto_id')
        ),
        'Years' => array(
        	'columns' => array('year'),
        	'refTableClass' => 'Years',
        	'refColumns' => array('auto_id')
        )
    );

    public function fetchLatest($count = 10) {
        return $this->fetchAll(null, 'date_created DESC', $count);
    }
    
    /**
     * Get teaching activity based on id
     */
    public function getTa($ta_id) {
    	try {
    		$row = $this->find($ta_id)->current();
    	} catch (Exception $e) {
    		throw new Exception("Could not find teaching activity $ta_id.");
    	}
    	if (!$row) {
    		throw new Exception("Could not find teaching activity $ta_id.");
    	}
    	return $row;
    }
    
    /**
     * Get different versions of teaching activity that has the same ta id
     * @param $ta_id
     */
    public function getTaRevisions($ta_id) {
    	$select = $this->select()->where('taid=?', $ta_id)->order('auto_id DESC');
    	return $this->fetchAll($select);
    }
    
    /**
     * Get all teaching activities of type $ta_type within block $block
     * @param $block
     * @param $ta_type
     * @param $order
     */
    public function getTaByBlock($block, $ta_type, $order = NULL) {
    	$identity = Zend_Auth::getInstance()->getIdentity();
    	
    	//get block id from block number
    	$sbp = new StageBlockSeqs();
    	$block_id = $sbp->getBlockId($block);
    	
    	$select = $this->select();
    	$select->where("block = ?", $block_id);
    	
    	if (isset($ta_type)) {
	    	$activityFinder = new ActivityTypes();
	    	$type_id = $activityFinder->getActivityId($ta_type);
	    	$select->where("type = ?", $type_id);
    	}
    	
    	if (NULL !== $order) {
    		$select->order($order);
    	}
    	$all_tas = $this->fetchAll($select);
    	
    	$result = array();
    	$linkFinder = new LinkageLoTas();
    	$statusFinder = new Status();
    	$released_status = $statusFinder->getIdForStatus(Status::$RELEASED);
    	
    	//remove teaching activities that are not currently being used
    	foreach ($all_tas as $ta) {
    		$released_links = $linkFinder->fetchAll("ta_id = {$ta->auto_id} AND status = $released_status");
    		if (count($released_links) != 0) {
    			if (UserAcl::isStudent()) {
    				$domains = array_intersect($identity->all_domains, $ta->audience_arr);
    				if (count($domains) != 0) {
    					$result[] = $ta;
    				}
    			} else {
    				if (in_array($identity->domain, $ta->audience_arr) != false) {
    					$result[] = $ta;
    				}
    			}
    		}
    	}
    	return $result;
    }
    
    /**
     * Get all teaching activities of type $ta_type within block $block order by week
     * @param $block
     * @param $ta_type
     * @param $order
     */
    public function getTaInBlockByWeek($block, $ta_type) {
    	$identity = Zend_Auth::getInstance()->getIdentity();

    	//get block id from block number
    	$sbp = new StageBlockSeqs();
    	$block_id = $sbp->getBlockId($block);
    	
    	$activityFinder = new ActivityTypes();
    	$type_id = $activityFinder->getActivityId($ta_type);
    	
    	$select = $this->select();
    	$select->where("block = ?", $block_id)->where("type = ?", $type_id)->order(array('block_week', 'owner', 'sequence_num'));
    	
    	$all_tas = $this->fetchAll($select);
    	
    	$result = array();
    	$linkFinder = new LinkageLoTas();
    	$statusFinder = new Status();
    	$released_status = $statusFinder->getIdForStatus(Status::$RELEASED);
    	
    	//remove teaching activities that are not currently being used
    	foreach ($all_tas as $ta) {
    		$released_links = $linkFinder->fetchAll("ta_id = {$ta->auto_id} AND status = $released_status");
    		if (count($released_links) != 0) {
    			if (UserAcl::isStudent()) {
    				$domains = array_intersect($identity->all_domains, $ta->audience_arr);
	    			if (count($domains) != 0) {
		    			$pbl = "{$ta->block_no}.{$ta->block_week_zero_padded}";
		    			$result[$pbl][] = $ta;
	    			}
    			} else {
    				if (in_array($identity->domain, $ta->audience_arr) != false) {
	    				$pbl = "{$ta->block_no}.{$ta->block_week_zero_padded}";
	    				$result[$pbl][] = $ta;
    				}
    			}
    		}
    	}
    	return $result;
    }
    
    /**
     * Get all teaching activities of type $ta_type within stage $stage
     * @param $stage
     * @param $ta_type
     * @param $order
     */
    public function getTaByStage($stage, $ta_type, $order = NULL) {
    	//get stage id from stage string
    	$stageFinder = new Stages();
    	$stage_id = $stageFinder->getStageId($stage);
    	
    	$activityFinder = new ActivityTypes();
    	$type_id = $activityFinder->getActivityId($ta_type);
    	
    	$select = $this->select();
    	$select->where("stage = ?", $stage_id)->where("type = ?", $type_id);
    	
    	if (NULL !== $order) {
    		$select->order($order);
    	}
    	$all_tas = $this->fetchAll($select);
    	
    	$result = array();
    	$linkFinder = new LinkageLoTas();
    	$statusFinder = new Status();
    	$released_status = $statusFinder->getIdForStatus(Status::$RELEASED);
    	
    	//remove teaching activities that are not currently being used
    	foreach ($all_tas as $ta) {
    		$released_links = $linkFinder->fetchAll("ta_id = {$ta->auto_id} AND status = $released_status");
    		if (count($released_links) != 0) {
    			$result[] = $ta;
    		}
    	}
    	return $result;
    }
    
    /**
     * Get all patient doctor tutorial with procedural skills for stage $stage
     */
    public function getProceduralSkillInStage($stage) {
    	$stageFinder = new Stages();
    	$stage_id = $stageFinder->getStageId($stage);
    	
    	$activityFinder = new ActivityTypes();
    	$type_id = $activityFinder->getActivityId("Procedural skills session");
    	
    	$statusFinder = new Status();
    	$released_status = $statusFinder->getIdForStatus(Status::$RELEASED);
    	
    	$db = Zend_Registry::get("db");
    	$select = $db->select()->distinct()
    				->from(array('t' => 'teachingactivity'), array('auto_id' => 'auto_id', 'name' => 'name'))
    				->join(array('lk_term' => 'lk_term'), 't.term = lk_term.auto_id', array('term' => 'term'))
    				->join(array('lk' => 'link_lo_ta'), 't.auto_id = lk.ta_id', array())
    				->join(array('sbs' => 'stage_block_seq'), 'sbs.block_id = t.block', array('seq_no'))
    				->where("t.stage = ?", $stage_id)
    				->where("t.type = ?", $type_id)
    				->where("lk.status = ?", $released_status)
    				->where("sbs.stage_id = ?", $stage_id)
    				->order("lk_term.term")
    				->order("sbs.seq_no")
    				->order("t.name");
    	
    	Zend_Registry::get('logger')->DEBUG(__METHOD__ . ": ". $select->__toString());
    	$rows = $db->fetchAll($select);
    	
    	$taFinder = new TeachingActivities();
    	$results = array();
    	foreach ($rows as $row) {
    		$results[] = $taFinder->getTa($row['auto_id']);
    	}
    	return $results;
    }
    
    /**
     * Get all patient doctor tutorial with procedural skills for block $block
     */
    public function getProceduralSkillInBlock($block) {
    	$sbs = new StageBlockSeqs();
    	$block_id = $sbs->getBlockId($block);
    	
    	$activityFinder = new ActivityTypes();
    	$type_id = $activityFinder->getActivityId("Procedural skills session");
    	
    	$statusFinder = new Status();
    	$released_status = $statusFinder->getIdForStatus(Status::$RELEASED);
    	
    	$db = Zend_Registry::get("db");
    	$select = $db->select()->distinct()
    				->from(array('t' => 'teachingactivity'), array('auto_id' => 'auto_id', 'name' => 'name'))
    				->join(array('lk_term' => 'lk_term'), 't.term = lk_term.auto_id', array('term' => 'term'))
    				->join(array('lk' => 'link_lo_ta'), 't.auto_id = lk.ta_id', array())
    				->where("t.block = ?", $block_id)
    				->where("t.type = ?", $type_id)
    				->where("lk.status = ?", $released_status)
    				->order("lk_term.term")
    				->order("t.name");
    	
    	Zend_Registry::get('logger')->DEBUG(__METHOD__ . ": ". $select->__toString());
    	$rows = $db->fetchAll($select);
    	
    	$taFinder = new TeachingActivities();
    	$results = array();
    	foreach ($rows as $row) {
    		$results[] = $taFinder->getTa($row['auto_id']);
    	}
    	return $results;
    }
    /**
     * get all current principal teachers
     */
    public function getAllCurrentPrincipalTeachers() {
    	$statusFinder = new Status();
    	$released_status = $statusFinder->getIdForStatus(Status::$RELEASED);
    	
    	$db = Zend_Registry::get("db");
    	
    	//select principal_teacher from teachingactivity where auto_id in (select ta_id from link_lo_ta where status=4);
    	$select = $db->select()
    				->from(array('t' => 'teachingactivity'), array('principal_teacher' => 'principal_teacher'))
    				->join(array('lk' => 'link_lo_ta'), 't.auto_id = lk.ta_id', array())
    				->where("lk.status = ?", $released_status);
    	Zend_Registry::get('logger')->DEBUG(__METHOD__ . ": ". $select->__toString());
    	$rows = $db->fetchAll($select);
    	$results = array();
    	foreach ($rows as $row) {
    		$ptparts = explode(',',$row['principal_teacher']);
    		foreach($ptparts as $pt) {
    			$pt = strtolower(trim($pt));
    			if(strlen($pt)>0)
    				if(isset($results[$pt]))
    					$results[$pt] = $results[$pt]+1;
    				else
    					$results[$pt] = 1;
    		}
    	}
    	return $results;
    }
    
    public function getPtDrByBlock($block, $order) {
    	return $this->getTaByBlock($block, 'Pt-Dr tutorial', $order);
    }
}