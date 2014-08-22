<?php

class LinkageLoTas extends Zend_Db_Table_Abstract {
	protected $_name = 'link_lo_ta';
	protected $_primary = 'auto_id';
	protected $_rowClass = 'LinkageLoTa';
	protected $_referenceMap = array(
        'LearningObjective' => array(
            'columns' => array('lo_id'),
            'refTableClass' => 'LearningObjectives',
            'refColumns' => array('auto_id')
		),
        'TeachingActivity' => array(
            'columns' => array('ta_id'),
            'refTableClass' => 'TeachingActivities',
            'refColumns' => array('auto_id')
		),
        'Status' => array(
            'columns' => array('status'),
            'refTableClass' => 'Status',
            'refColumns' => array('auto_id')
		),
        'NewStatus' => array(
            'columns' => array('new_status'),
            'refTableClass' => 'Status',
            'refColumns' => array('auto_id')
		),
        'Strength' => array(
            'columns' => array('strength'),
            'refTableClass' => 'Strengths',
            'refColumns' => array('auto_id')
		)
	);

	/** Get the number of new version requests for each block.
	 *  If a user id is supplied, only requests made by that user is counted.
	 */
	public static function getNewVersionQueues($blocks, $owner, $uid = NULL) {		
		$statusFinder = new Status();
		$status_id = $statusFinder->getIdForStatus(Status::$NEW_VERSION);
		
		$where = "t.owner = $owner AND lk.status = $status_id";
		if (isset($uid)) {
			$where = "lk.created_by = '$uid' AND $where";
		}

		$db = Zend_Registry::get("db");
		$select = $db->select()
			->from(array('t' => 'teachingactivity'), array('count' => 'count(*)', 'block' => 'block'))
			->join(array('lk' => 'link_lo_ta'), 't.auto_id = lk.ta_id', array())
			->where($where)
			->group('t.block');
		$stmt = $db->query($select);
		$results = $stmt->fetchAll();
			
		$result_arr = array(); //stores total number of submissions, which includes lo and ta for each block
		foreach ($blocks as $k => $v) {
			$result_arr[$k] = 0;
		}
		if (count($results) > 0) {
			foreach ($results as $row) {
				$result_arr[$row['block']] += $row['count'];
			}
		}
		return $result_arr;
	}

	/** Get the number of archive requests for each block.
	 *  If a user id is supplied, only requests made by that user is counted.
	 *  Archive needs to be approved, so status will stay the same until they are approved
	 */
	public static function getArchiveQueues($blocks, $owner, $uid = NULL) {
		$statusFinder = new Status();
		$status_id = $statusFinder->getIdForStatus(Status::$ARCHIVED);

		$where = "t.owner = $owner AND lk.new_status = $status_id";
		if (isset($uid)) {
			$where = "lk.modified_by = '$uid' AND $where";
		}

		$db = Zend_Registry::get("db");
		$select = $db->select()
			->from(array('t' => 'teachingactivity'), array('count' => 'count(*)', 'block' => 'block'))
			->join(array('lk' => 'link_lo_ta'), 't.auto_id = lk.ta_id', array())
			->where($where)
			->group('t.block');
		$stmt = $db->query($select);
		$results = $stmt->fetchAll();
			
		$result_arr = array(); //stores total number of requests, which includes lo, ta for each block
		foreach ($blocks as $k => $v) {
			$result_arr[$k] = 0;
		}
		if (count($results) > 0) {
			foreach ($results as $row) {
				$result_arr[$row['block']] += $row['count'];
			}
		}
		return $result_arr;
	}

	/** Get the number of in development and awaiting approval submissions for all blocks.
	 *  If a user id is supplied, only submissions made by that user is counted.
	 */
	public static function getNewQueues($status_id, $blocks, $owner, $uid = NULL) {
		$where = "t.owner = $owner AND lk.status = $status_id";
		if (isset($uid)) {
			$where = "lk.created_by = '$uid' AND $where";
		}
		
		$db = Zend_Registry::get("db");
		$select = $db->select()
			->from(array('t' => 'teachingactivity'), array('block' => 'block'))
			->join(array('lk' => 'link_lo_ta'), 'lk.ta_id = t.auto_id', array('num' => 'count(*)'))
			->where($where)
			->group('t.block');
		$stmt = $db->query($select);
		$results = $stmt->fetchAll();
			
		$result_arr = array();
		foreach ($blocks as $k => $v) {
			$result_arr[$k] = 0;
		}

		if (count($results) > 0) {
			foreach ($results as $row) {
				$result_arr[$row['block']] += $row['num'];
			}
		}
		return $result_arr;
	}

	/** Get the number of new version requests for a particular block.
	 *  If a user id is supplied, only requests made by that user is counted.
	 */
	public static function getBlockNewVersionQueue($block_id, $owner, $uid = NULL) {
		$statusFinder = new Status();
		$statusNames = $statusFinder->getAllNames();
		$status_ids = array_flip($statusNames);

		$where = "t.owner = $owner AND t.block = $block_id AND lk.status = {$status_ids[Status::$NEW_VERSION]}";
		if (isset($uid)) {
			$where = "lk.created_by = '$uid' AND $where";
		}

		$db = Zend_Registry::get("db");
		$select = $db->select()
			->from(array('t' => 'teachingactivity'), array('taid' => 'auto_id', 'ta_title' => 'name'))
			->join(array('lk' => 'link_lo_ta'), 't.auto_id = lk.ta_id',
				array('type' => 'type', 'created_by' => 'created_by', 'date_created' => 'date_created'))
			->join(array('l' => 'learningobjective'), 'lk.lo_id = l.auto_id', array('loid' => 'auto_id','lo_text' => 'lo'))
			->where($where);
		$stmt = $db->query($select);
		$results = $stmt->fetchAll();

		$result_arr = array();

		if (count($results) > 0) {
			foreach ($results as $row) {
				if ($row['type'] == 'TA') {
					$result_arr[] = array ('doctype' => 'TA',
	    		                'id' => $row['taid'],
        						'submitted_by' => $row['created_by'], 
        						'date_submitted' => $row['date_created'],
        						'lo_text' => $row['lo_text'],
        						'ta_title' => $row['ta_title']);
				} else if ($row['type'] == 'LO') {
					$result_arr[] = array ('doctype' => 'LO',
	    		    			'id' => $row['loid'],
        						'submitted_by' => $row['created_by'], 
        						'date_submitted' => $row['date_created'],
        						'lo_text' => $row['lo_text'],
        						'ta_title' => $row['ta_title']);
				}
			}
		}
		return $result_arr;
	}

	/** Get the number of archive requests for a particular block.
	 *  If a user id is supplied, only requests made by that user is counted.
	 *  Archive needs to be approved, so status will stay the same until they are approved
	 */
	public static function getBlockArchiveQueue($block_id, $owner, $uid = NULL) {
		$statusFinder = new Status();
		$statusNames = $statusFinder->getAllNames();
		$status_ids = array_flip($statusNames);

		$where = "t.owner = $owner AND t.block = $block_id AND lk.new_status = {$status_ids[Status::$ARCHIVED]}";
		if (isset($uid)) {
			$where = "lk.modified_by = '$uid' AND $where";
		}

		$db = Zend_Registry::get("db");
		$select = $db->select()
			->from(array('t' => 'teachingactivity'), array('taid' => 'auto_id', 'ta_title' => 'name'))
			->join(array('lk' => 'link_lo_ta'), 't.auto_id = lk.ta_id',
				array('lkid' => 'auto_id', 'type' => 'type', 'modified_by' => 'modified_by', 'date_modified' => 'date_modified'))
			->join(array('l' => 'learningobjective'), 'lk.lo_id = l.auto_id', array('loid' => 'auto_id','lo_text' => 'lo'))
			->where($where);
		$stmt = $db->query($select);
		$results = $stmt->fetchAll();

		$result_arr = array();

		if (count($results) > 0) {
			foreach ($results as $row) {
				if ($row['type'] == 'TA') {
					$result_arr[] = array ('doctype' => 'TA',
	    		                'id' => $row['taid'],
        						'submitted_by' => $row['modified_by'], 
        						'date_submitted' => $row['date_modified'],
        						'lo_text' => $row['lo_text'],
        						'ta_title' => $row['ta_title']);
				} else if ($row['type'] == 'LO') {
					$result_arr[] = array ('doctype' => 'LO',
	    		    			'id' => $row['loid'],
	    		                'taid' => $row['taid'],
        						'submitted_by' => $row['modified_by'], 
        						'date_submitted' => $row['date_modified'],
        						'lo_text' => $row['lo_text'],
        						'ta_title' => $row['ta_title']);
				} else if ($row['type'] == 'LK') {
					$result_arr[] = array ('doctype' => 'LK',
	    		    			'id' => $row['lkid'],
	    		                'taid' => $row['taid'],
        						'submitted_by' => $row['modified_by'], 
        						'date_submitted' => $row['date_modified'],
        						'lo_text' => $row['lo_text'],
        						'ta_title' => $row['ta_title']);
				}
			}
		}
		return $result_arr;
	}

	/** Get the number of new submissions for a particular block.
	 *  If a user id is supplied, only submissions made by that user is counted.
	 */
	public static function getBlockNewQueues($status_id, $block, $owner, $uid = NULL) {
		$where = "t.owner = $owner AND lk.status = $status_id AND t.block = $block";
		if (isset($uid)) {
			$where = "lk.created_by = '$uid' AND $where";
		}
		
		$db = Zend_Registry::get("db");
		$select = $db->select()
			->from(array('lk' => 'link_lo_ta'), array('lkid' => 'auto_id',
				'type' => 'type', 'created_by' => 'created_by', 'date_created' => 'date_created'))
			->join(array('t' => 'teachingactivity'), 'lk.ta_id = t.auto_id', array('ta_title' => 'name'))
			->joinLeft(array('l' => 'learningobjective'), 'lk.lo_id = l.auto_id', array('lo_text' => 'lo'))
			->where($where);
		$stmt = $db->query($select);
		$results = $stmt->fetchAll();
		
		$result_arr = array();
		if (count($results) > 0) {
			foreach ($results as $row) {
				$tmp_arr = array();
				$tmp_arr['doctype'] = $row['type'];
				$tmp_arr['id'] = $row['lkid'];
				$tmp_arr['submitted_by'] = $row['created_by'];
				$tmp_arr['date_submitted'] = $row['date_created'];
				$tmp_arr['lo_text'] = empty($row['lo_text']) ? '---' :  $row['lo_text'];
				$tmp_arr['ta_title'] = empty($row['ta_title']) ? '---' :  $row['ta_title'];
				$result_arr[] = $tmp_arr;
			}
		}
		return $result_arr;
	}
	
	/** Get the number of new submissions for all blocks.
	 *  If a user id is supplied, only submissions made by that user is counted.
	 */
	public static function getUserNewQueues($status_id, $blocks, $owner, $uid = NULL) {
		$db = Zend_Registry::get("db");
		$select = $db->select()
			->from(array('t' => 'teachingactivity'), array('block' => 'block'))
			->join(array('lk' => 'link_lo_ta'), 'lk.ta_id = t.auto_id', array('num' => 'count(*)'))
			->where("lk.status = $status_id AND lk.created_by = '$uid' AND t.owner = $owner")
			->group('t.block');
		$stmt = $db->query($select);
		$results = $stmt->fetchAll();

		$result_arr = array();
		foreach ($blocks as $k => $v) {
			$result_arr[$k] = 0;
		}

		foreach ($results as $row) {
			$result_arr[$row['block']] += $row['num'];
		}
		return $result_arr;
	}

	/**
	 * Get the count of all new submissions that has a null teaching activity id
	 * If a user id is provided, only submission from that user is counted
	 */
	public static function getNoTaQueueCount($uid = NULL) {
		$where = "lk.ta_id IS NULL";
		if (isset($uid)) {
			$where = "lk.created_by = '$uid' AND $where";
		}
		$db = Zend_Registry::get("db");
		$select = $db->select()->from(array('lk' => 'link_lo_ta'), array('num' => 'count(*)'))
			->where($where);
		$stmt = $db->query($select);
		$results = $stmt->fetchAll();
		return $results[0]['num'];
	}

	
	/**
	 * Get a list of new submissions that has a null teaching activity id
	 * If a user id is provided, only submission from that user is counted
	 */
	public static function getNoTaQueueDetail($status_id, $uid = NULL) {
		$count_arr = array();
		$db = Zend_Registry::get("db");
		
		$where = "lk.ta_id IS NULL";
		if (isset($uid)) {
			$where = "lk.created_by = '$uid' AND $where";
		}
		$select = $db->select()
			->from(array('lk' => 'link_lo_ta'), array('lkid' => 'auto_id',
				'type' => 'type', 'created_by' => 'created_by', 'date_created' => 'date_created'))
			->join(array('l' => 'learningobjective'), 'lk.lo_id = l.auto_id', array('lo_text' => 'lo'))
			->where($where);
		$stmt = $db->query($select);
		$results = $stmt->fetchAll();
		foreach ($results as $row) {
			$tmp_arr = array();
			$tmp_arr['doctype'] = $row['type'];
			$tmp_arr['id'] = $row['lkid'];
			$tmp_arr['submitted_by'] = $row['created_by'];
			$tmp_arr['date_submitted'] = $row['date_created'];
			$tmp_arr['lo_text'] = empty($row['lo_text']) ? '---' :  $row['lo_text'];
			$tmp_arr['ta_title'] = '---';
			$count_arr[] = $tmp_arr;
		}
		
		$where = "lk.ta_id is NULL and lk.lo_id is NULL";
		if (isset($uid)) {
			$where = "lk.created_by = '$uid' AND $where";
		}
		$select = $db->select()
			->from(array('lk' => 'link_lo_ta'), array('lkid' => 'auto_id',
				'type' => 'type', 'created_by' => 'created_by', 'date_created' => 'date_created'))
			 ->where($where);
		$stmt = $db->query($select);
		$results = $stmt->fetchAll();		 
		foreach ($results as $row) {
			$tmp_arr = array();
			$tmp_arr['doctype'] = $row['type'];
			$tmp_arr['id'] = $row['lkid'];
			$tmp_arr['submitted_by'] = $row['created_by'];
			$tmp_arr['date_submitted'] = $row['date_created'];
			$tmp_arr['lo_text'] = '---';
			$tmp_arr['ta_title'] = '---';
			$count_arr[] = $tmp_arr;
		}
		return $count_arr;
	}
	
	/** Change the learning objective display order on Core Curriculum teaching activity page */
	public function updateLinkageOrder($lo_id, $ta_id, $order) {
		$row = $this->getLinkageByLoAndTaId($lo_id, $ta_id);
		$row->lo_order = (int)$order;
		$row->save();
	}
	
	/** Return a linkage object based on link id */
	public function getLinkageById($link_id) {
		$link_id = (int)$link_id;
		$row = $this->find($link_id)->current();
		if (!$row) {
			throw new Exception("Could not find linkage $link_id.");
		}
		return $row;
	}
	
	/** Return a linkage object based on learning objective id and teaching activity id */
	public function getLinkageByLoAndTaId($lo_id, $ta_id) {
		$select = $this->select();
		$select->where('lo_id=?', $lo_id)->where('ta_id=?', $ta_id);
		$row = $this->fetchRow($select);
		if (!$row) {
			throw new Exception("Could not find linkage between learning objective $lo_id and teaching activity $ta_id.");
		}
		return $row;
	}
	
	/** Get linkages based on the status id or status name */
	public function getLinkageWithStatus($status) {
		$select = $this->select();
		if (!is_int($status)) {
			$statusFinder = new Status();
			$status = $statusFinder->getIdForStatus($status);
		}
		$select->where("status = ?", $status);
		return $this->fetchAll($select);
	}
	
	/**
	 * Get a list of linkages that link to a particular ta and have the status id(s) specified by $status parameter
	 * @param $ta_id
	 * @param $status - single status id or array of status ids
	 */
	public function getTaLinkageWithStatus($ta_id, $status) {
		$select = $this->select()->where("ta_id=?", $ta_id)->where("status IN (?)", $status);
		return $this->fetchAll($select);
	}
	
	/**
	 *  Get the number of linkages that has a particular ta id and status id(s)
	 *  @param $ta_id
	 *  @param $status - single status id or array of status ids
	 */
	public function getTaLinkageCountWithStatus($ta_id, $status) {
		$rows = $this->getTaLinkageWithStatus($ta_id, $status);
		return count($rows);
	}
	
	/**
	 * Get a list of linkages that link to a particular lo and have the status id(s) specified by $status parameter
	 * @param $lo_id
	 * @param $status
	 */
	public function getLoLinkageWithStatus($lo_id, $status) {
		$select = $this->select()->where("lo_id=?", $lo_id)->where("status IN (?)", $status);
		return $this->fetchAll($select);
	}
	
	/**
	 * Get the number of linkages that has a particular lo id and status id(s)
	 * @param $lo_id
	 * @param $status - single status id or array of status ids
	 */
	public function getLoLinkageCountWithStatus($lo_id, $status) {
		$rows = $this->getLoLinkageWithStatus($lo_id, $status);
		return count($rows);
	}
	
	/**
	 * Check whether a teaching activity is currently being involved in a submission
	 * @param $ta_id
	 */
	public function isTaInSubmission($ta_id) {
		$select = $this->select();
		$select->where("ta_id=?", $ta_id);
		$rows = $this->fetchAll($select);
		foreach ($rows as $row) {
			if (!(($row->status == Status::$RELEASED && $row->new_status == Status::$UNKNOWN) || $row->status == Status::$ARCHIVED)) {
				return true;
			}
		}
		return false;
	}
	
	/**
	 * Check whether a learning objective is currently being involved in a submission
	 * @param $lo_id
	 */
	public function isLoInSubmission($lo_id) {
		$select = $this->select()->where("lo_id=?", $lo_id);
		$rows = $this->fetchAll($select);
		foreach ($rows as $row) {
			if (!(($row->status == Status::$RELEASED && $row->new_status == Status::$UNKNOWN) || $row->status == Status::$ARCHIVED)) {
				return true;
			}
		}
		return false;
	}
	
	/** get the history of a teaching activity or learning objective */
	public function getHistory($type, $id) {
		if ($type == 'ta') {
			$query = "ta_id = $id";
		} else if ($type == 'lo') {
			$query = "lo_id = $id";
		}
		
		$statusFinder = new Status();
		$release_id = $statusFinder->getIdForStatus(Status::$RELEASED);
		$archived_id = $statusFinder->getIdForStatus(Status::$ARCHIVED);
		$query = $query . " AND (status = $release_id OR status = $archived_id)";
		
		$rows = $this->fetchAll($query);
		
		$historyFinder = new LinkageHistories();
		$historyRows = $historyFinder->fetchAll($query);
		
		$results = array();
		foreach ($rows as $row) {
			$results[] = $row;
		}
		foreach ($historyRows as $row) {
			$results[] = $row;
		}
		return $results;
	}
	
	/**
	 * Allow user to update lo info in the submission
	 */
	public function updateLinkageLoInfo($link, $request) {
		$identity = Zend_Auth::getInstance()->getIdentity();
		$user_id = $identity->user_id;
		$domainFinder = new Domains();
		$owner = $domainFinder->getDomainId($identity->domain);
		
		$loFinder = new LearningObjectives();
		if (!empty($link->lo_id)) {
			$lo = $loFinder->getLo($link->lo_id);
		} else {
			$lo = $loFinder->createRow();
			$lo->owner = $owner;
		}
		
		$lo->discipline1 = (int)$request->getParam('discipline1');
		$lo->discipline2 = (int)$request->getParam('discipline2');
		$discipline3 = (int)$request->getParam('discipline3');
		$lo->discipline3 = $discipline3 == 0 ? 1 : $discipline3;
		
		$lo->curriculumarea1 = (int)$request->getParam('curriculumarea1');
		$lo->curriculumarea2 = (int)$request->getParam('curriculumarea2');
		$lo->curriculumarea3 = (int)$request->getParam('curriculumarea3');
		
		$lo->theme1 = (int)($request->getParam('theme1'));
		$lo->theme2 = (int)($request->getParam('theme2'));
		$theme3 = (int)($request->getParam('theme3'));
		$lo->theme3 = $theme3 == 0 ? 1 : $theme3;
		
		$lo->skill = (int)($request->getParam('skill'));
		$lo->system = (int)($request->getParam('system'));
		
		$activity = $request->getParam('activity');
		$ability = $request->getParam('ability');
		if (isset($activity) && isset($ability)) {
			$lo->lo = 'At the end of ' .$activity. ', students should be able to '.$ability.' ' . $request->getParam('lo');
		} else {
			$lo->lo = $request->getParam('lo');
		}
		
		$review = is_array($request->getParam('review')) ? $request->getParam('review') : array();
		$keywords = is_array($request->getParam('keywords')) ? $request->getParam('keywords') : array();
		$lo->keywords = join('|', $keywords);
		
		$assesstype = is_array($request->getParam('assesstype')) ? $request->getParam('assesstype') : array();
		
		$lo->achievement = (int)($request->getParam('achievement'));
		$lo->jmo = (int)($request->getParam('jmo'));
		$lo->gradattrib = (int)($request->getParam('gradattrib'));
		
		$notes = stripslashes($request->getParam('lo_notes'));
		if (trim($notes) != '') {
			$lo->notes = trim($notes);
		}
		
		$resources = $request->getParam('lo_mids');
		if (!isset($resources)) $resources = array();
		
		$current_timestamp = date('Y-m-d H:i:s');
		$db = Zend_Registry::get('db');
		$db->beginTransaction();
		try {
			if (empty($link->lo_id)) {
				$lo->version = 1;
				$row = $db->query('SELECT max(loid) AS maxloid FROM learningobjective')->fetch();
				$lo->loid = ++$row['maxloid'];
				$lo->created_by = $user_id;
				$lo->date_created = $current_timestamp;
			}
			$lo_id = $lo->save();
			
			$lo->saveReviews($review);
			$lo->saveAssessTypes($assesstype);
			
			if (empty($link->lo_id)) {
				$link->lo_id = $lo_id;
				$lo_audience = new LinkageLoDomains();
				$lo_audience->addAudience($lo_id, $owner);
			}
			$link->modified_by = $user_id;
			$link->date_modified = $current_timestamp;
			if (empty($link->type)) {
				$link->type = 'NL';
			} else if ($link->type == 'ET') {
				$link->type = 'LO';
			} else if ($link->type == 'NT') {
				$link->type = 'TL';
			}
			$link->save();
			
			$resourceFinder = new MediabankResource();
			$old_resources = $resourceFinder->getResources($lo_id, 'lo');
			if ($old_resources === false) {
				$old_resources = array();
			}
			foreach ($old_resources as $resource) {
				if (!in_array($resource['resource_id'], $resources)) {
					$resourceFinder->removeResource($lo_id, $resource['resource_id'], 'lo');
				}
			}
			foreach ($resources as $mid_and_resourcetypeid) {
                $explode = explode('|', $mid_and_resourcetypeid);
                if(count($explode) == 2) {
                    $result = $resourceFinder->addResource('lo', $lo_id, $explode[1], $explode[0]);
                } else {
                    Zend_Registry::get('logger')->warn('Incorrect value for lo_mids received. Expected value should be "mid|resourcetypeid". Value given '.$mid_and_resourcetypeid.PHP_EOL);
                }
			}
			$db->commit();
		} catch (Exception $e) {
			$db->rollBack();
			throw $e;
		}
	}
	
	/** Allow user to update ta info in the submission */
	public function updateLinkageTaInfo($link, $request) {
		$identity = Zend_Auth::getInstance()->getIdentity();
		$user_id = $identity->user_id;
		$domainFinder = new Domains();
		$owner = $domainFinder->getDomainId($identity->domain);
		
		$taFinder = new TeachingActivities();
		if (!empty($link->ta_id)) {
			$ta = $taFinder->getTa($link->ta_id);
		} else {
			$ta = $taFinder->createRow();
			$ta->owner = $owner;
		}
		
		$ta->name = stripslashes($request->getParam('name'));
		$ta->type = (int)$request->getParam('type');
		$ta->stage = (int)$request->getParam('stage');
		$year = (int)$request->getParam('year');
		$ta->year = $year == 0 ? 1 : $year;
		
		$ta->block = (int)$request->getParam('block');
		
		$block_week  = (int)$request->getParam('block_week');
		$ta->block_week = $block_week == 0 ? 1 : $block_week;
		
		$pbl = (int)$request->getParam('pbl');
		$ta->pbl = $pbl == 0 ? 1 : $pbl;
		
		$sequence_num = (int)$request->getParam('sequence_num');
		$ta->sequence_num = $sequence_num == 0 ? 1 : $sequence_num;
		
		$term = (int)$request->getParam('term');
		$ta->term = $term == 0 ? 1 : $term;
		
		$ta->student_grp = (int)$request->getParam('student_grp');
		$ta->principal_teacher = trim(stripslashes($request->getParam('principal_teacher')));
		$ta->current_teacher = trim(stripslashes($request->getParam('current_teacher')));
		$ta->notes = trim(stripslashes($request->getParam('ta_notes')));
		
		$resources = $request->getParam('ta_mids');
		if (!isset($resources)) $resources = array();
		
		$current_timestamp = date('Y-m-d H:i:s');
		$db = Zend_Registry::get('db');
		$db->beginTransaction();
		try {
			if (empty($link->ta_id)) {
				$ta->version = 1;
				$row = $db->query('SELECT max(taid) AS maxtaid FROM teachingactivity')->fetch();
				$ta->taid = ++$row['maxtaid'];
				$ta->created_by = $user_id;
				$ta->date_created = $current_timestamp;
			}
			$ta_id = $ta->save();
			
			if (empty($link->ta_id)) {
				$link->ta_id = $ta_id;
				$ta_audience = new LinkageTaDomains();
				$ta_audience->addAudience($ta_id, $owner);
			}
			$link->modified_by = $user_id;
			$link->date_modified = $current_timestamp;
			if (empty($link->type)) {
				$link->type = 'NT';
			} else if ($link->type == 'NL') {
				$link->type = 'TL';
			} else if ($link->type == 'EL') {
				$link->type = 'TA';
			}
			$link->save();
			
			$resourceFinder = new MediabankResource();
			$old_resources = $resourceFinder->getResources($link->ta_id, 'ta');
			if ($old_resources === false) {
				$old_resources = array();
			}
			foreach ($old_resources as $resource) {
				if (!in_array($resource['resource_id'], $resources)) {
					$resourceFinder->removeResource($link->ta_id, $resource['resource_id'], 'ta');
				}
			}
			foreach ($resources as $mid_and_resourcetypeid) {
                $explode = explode('|', $mid_and_resourcetypeid);
                if(count($explode) == 2) {
                    $resourceFinder->addResource('ta', $link->ta_id, $explode[1], $explode[0]);
                } else {
                    Zend_Registry::get('logger')->warn('Incorrect value for ta_mids received. Expected value should be "mid|resourcetypeid". Value given '.$mid_and_resourcetypeid.PHP_EOL);
                }
                    
			}
			$db->commit();
		} catch (Exception $e) {
			$db->rollBack();
			throw $e;
		}
	}
	
	/** Allow user to update link info in a submission */
	public function updateLinkageLinkInfo($link, $request) {
		$link->strength = (int)$request->getParam('strength');
		$link->notes = stripslashes($request->getParam('link_notes'));
		$link->modified_by = Zend_Auth::getInstance()->getIdentity()->user_id;
		$link->date_modified = date('Y-m-d H:i:s');
		$link->save();
	}
	
	/** Links a teaching activity to an existing learning objective */
	public function addTaToExistingLo($lo_id) {
		$row = $this->createNewLinkage();
		$row->lo_id = $lo_id;
		$row->type = 'EL';
		return $row->save();
	}
	
	/** Links a learning objective to an existing teaching activity */
	public function addLoToExistingTa($ta_id) {
		$row = $this->createNewLinkage();
		$row->ta_id = $ta_id;
		$row->type = 'ET';
		return $row->save();
	}
	
	// creates a new linkage with default values
	public function createNewLinkage() {
		$row = $this->createRow();
		$row->date_created = date('Y-m-d H:i:s');
		
		//set default value for strength
		$strengthFinder = new Strengths();
		$row->strength = $strengthFinder->getIdForStrength('');
		
		//set default value for status and new_status
		$statusFinder = new Status();
		$row->status = $statusFinder->getIdForStatus(Status::$IN_DEVELOPMENT);
		$row->new_status = $statusFinder->getIdForStatus(Status::$UNKNOWN);
		
		$identity = Zend_Auth::getInstance()->getIdentity();
		$row->created_by = $identity->user_id;
		
		return $row;
	}
	
	// creates a new linkage with link info
	public function createNewLinkageWithLinkInfo($request) {
		$link = $this->createNewLinkage();
		$link->strength = (int)$request->getParam('strength');
		$link->notes = stripslashes($request->getParam('link_notes'));
		echo $link->save();
	}
	
	/**
	 * Allow user to submit a new linkage with a learning objective
	 * Learning objective info should be present in http request
	 * @param $request
	 */
	public function createNewLinkageWithNewLo($request) {
		$link = $this->createNewLinkage();
		
		$loFinder = new LearningObjectives();
		$lo = $loFinder->createRow();
		
		$domainFinder = new Domains();
		$domain_id = $domainFinder->getDomainId(Zend_Auth::getInstance()->getIdentity()->domain);
		
		$lo->discipline1 = (int)$request->getParam('discipline1');
		$lo->discipline2 = (int)$request->getParam('discipline2');
		$discipline3 = (int)$request->getParam('discipline3');
		$lo->discipline3 = $discipline3 == 0 ? 1 : $discipline3;
		
		$lo->curriculumarea1 = (int)$request->getParam('curriculumarea1');
		$lo->curriculumarea2 = (int)$request->getParam('curriculumarea2');
		$lo->curriculumarea3 = (int)$request->getParam('curriculumarea3');
		
		$lo->theme1 = (int)($request->getParam('theme1'));
		$lo->theme2 = (int)($request->getParam('theme2'));
		$theme3 = (int)($request->getParam('theme3'));
		$lo->theme3 = $theme3 == 0 ? 1 : $theme3;
		
		$lo->skill = (int)($request->getParam('skill'));
		$lo->system = (int)($request->getParam('system'));
		
		$activity = $request->getParam('activity');
		$ability = $request->getParam('ability');
		if (isset($activity) && isset($ability)) {
			$lo->lo = 'At the end of ' .$activity. ', students should be able to '.$ability.' ' . $request->getParam('lo');
		} else {
			$lo->lo = $request->getParam('lo');
		}
		
		$review = is_array($request->getParam('review')) ? $request->getParam('review') : array();
		$keywords = is_array($request->getParam('keywords')) ? $request->getParam('keywords') : array();
		$lo->keywords = join('|', $keywords);
		
		$assesstype = is_array($request->getParam('assesstype')) ? $request->getParam('assesstype') : array();
		
		$lo->achievement = (int)($request->getParam('achievement'));
		$lo->jmo = (int)($request->getParam('jmo'));
		$lo->gradattrib = (int)($request->getParam('gradattrib'));
		$lo->owner = $domain_id;
		
		$lo->version = 1;
		$lo->created_by = $link->created_by;
		$lo->date_created = $link->date_created;
		
		$notes = stripslashes($request->getParam('lo_notes'));
		if (trim($notes) != '') {
			$lo->notes = trim($notes);
		}
		
		$resources = $request->getParam('lo_mids');
		if (!isset($resources)) $resources = array();
		
		$current_timestamp = date('Y-m-d H:i:s');
		$db = Zend_Registry::get('db');
		$db->beginTransaction();
		try {
			$row = $db->query('SELECT max(loid) AS maxloid FROM learningobjective')->fetch();
			$lo->loid = ++$row['maxloid'];
			$lo_id = $lo->save();
			
			$lo->saveReviews($review);
			$lo->saveAssessTypes($assesstype);
			
			$link->lo_id = $lo_id;
			$link->type = 'NL';
			$link_id = $link->save();
			
			$lo_audience = new LinkageLoDomains();
			$lo_audience->addAudience($lo_id, $domain_id);
			
			$resourceFinder = new MediabankResource();
			foreach ($resources as $mid_and_resourcetypeid) {
                $explode = explode('|', $mid_and_resourcetypeid);
                if(count($explode) == 2) {
                    $resourceFinder->addResource('lo', $lo_id, $explode[1], $explode[0]);
                } else {
                    Zend_Registry::get('logger')->warn('Incorrect value for lo_mids received. Expected value should be "mid|resourcetypeid". Value given '.$mid_and_resourcetypeid.PHP_EOL);
                }
			}
			$db->commit();
			echo $link_id;
		} catch (Exception $e) {
			$db->rollBack();
			throw $e;
		}
	}
	
	/**
	 * Allow user to submit a new linkage with a teaching activity
	 * Teaching activity info should be present in http request
	 * @param $request
	 */
	public function createNewLinkageWithNewTa($request) {
		$link = $this->createNewLinkage();
		
		$taFinder = new TeachingActivities();
		$ta = $taFinder->createRow();
		
		$domainFinder = new Domains();
		$domain_id = $domainFinder->getDomainId(Zend_Auth::getInstance()->getIdentity()->domain);
		
		$ta->name = stripslashes($request->getParam('name'));
		$ta->type = (int)$request->getParam('type');
		$ta->stage = (int)$request->getParam('stage');
		$year = (int)$request->getParam('year');
		$ta->year = $year == 0 ? 1 : $year;
		
		$ta->block = (int)$request->getParam('block');
		
		$block_week  = (int)$request->getParam('block_week');
		$ta->block_week = $block_week == 0 ? 1 : $block_week;
		
		$pbl = (int)$request->getParam('pbl');
		$ta->pbl = $pbl == 0 ? 1 : $pbl;
		
		$sequence_num = (int)$request->getParam('sequence_num');
		$ta->sequence_num = $sequence_num == 0 ? 1 : $sequence_num;
		
		$term = (int)$request->getParam('term');
		$ta->term = $term == 0 ? 1 : $term;
		
		$ta->student_grp = (int)$request->getParam('student_grp');
		$ta->principal_teacher = trim(stripslashes($request->getParam('principal_teacher')));
		$ta->current_teacher = trim(stripslashes($request->getParam('current_teacher')));
		$ta->notes = trim(stripslashes($request->getParam('ta_notes')));
		$ta->owner = $domain_id;
		
		$ta->version = 1;
		$ta->created_by = $link->created_by;
		$ta->date_created = $link->date_created;
		
		$resources = $request->getParam('ta_mids');
		if (!isset($resources)) $resources = array();
		
		$db = Zend_Registry::get('db');
		$db->beginTransaction();
		try {
			$row = $db->query('SELECT max(taid) AS maxtaid FROM teachingactivity')->fetch();
			$ta->taid = ++$row['maxtaid'];
			$ta_id = $ta->save();
			
			$link->ta_id = $ta_id;
			$link->type = 'NT';
			$link_id = $link->save();
			
			$ta_audience = new LinkageTaDomains();
			$ta_audience->addAudience($ta_id, $domain_id);
			
			$resourceFinder = new MediabankResource();
			foreach ($resources as $mid_and_resourcetypeid) {
                $explode = explode('|', $mid_and_resourcetypeid);
                if(count($explode) == 2) {
                    $resourceFinder->addResource('ta', $ta_id, $explode[1], $explode[0]);
                } else {
                    Zend_Registry::get('logger')->warn('Incorrect value for ta_mids received. Expected value should be "mid|resourcetypeid". Value given '.$mid_and_resourcetypeid.PHP_EOL);
                }
			}
			$db->commit();
			return $link_id;
		} catch (Exception $e) {
			$db->rollBack();
			throw $e;
		}
	}
	
	/**
	 *  Try to create a linkage using an existing learning objective id 
	 *  Return true if it's possible
	 *  Otherwise return a detailed error message
	 */
	public function createLinkageUsingLoIdOK($lo_id) {
		// Frist check if $lo_id is valid
		$loFinder = new LearningObjectives();
		try {
			$lo = $loFinder->getLo($lo_id);
		} catch (Exception $e) {
			return $e->getMessage();
		}
		
		//Then check whether learning objective is currently being used
		//Otherwise there's no guarantee that all required information has been filled out
		$releasedTas = $lo->getLinkedTeachingActivityWithStatus(Status::$RELEASED);
		if (count($releasedTas) == 0) {
			return "Learning objective $lo_id is not currently being used.";
		}
		
		//Finally check if this learning objective is already being used in a submission
		if ($this->isLoInSubmission($lo_id)) {
			return "There are already requests awaiting approval regarding learning objective $lo_id.";
		}
		
		//check whether current user's domain is in the audience list
		$user_domain = Zend_Auth::getInstance()->getIdentity()->domain;
		if (!in_array($user_domain, $lo->audience_arr)) {
			return "\"{$user_domain}\" is not the audience of learning objective $lo_id.";
		}
		
		return TRUE;
	}
	
	/** Try to create a linkage using an existing teaching activity id 
	 *  Return true if it's possible
	 *  Otherwise return a detailed error message
	 * */
	public function createLinkageUsingTaIdOK($ta_id) {
		// Frist check if $ta_id is valid
		$taFinder = new TeachingActivities();
		try {
			$ta = $taFinder->getTa($ta_id);
		} catch (Exception $e) {
			return $e->getMessage();
		}
		
		//Then check whether teaching activity is currently being used
		//Otherwise there's no guarantee that all required information has been filled out
		$releaseLos = $ta->getLinkedLearningObjectiveWithStatus(Status::$RELEASED);
		if (count($releaseLos) == 0) {
			return "Teaching activity $ta_id is not currently being used.";
		}
		
		//Then check if this teaching activity is already being used in a submission
		if ($this->isTaInSubmission($ta_id)) {
			return "There are already requests awaiting approval regarding teaching activity $ta_id.";
		}
		
		//Finally check whether current user has permisson to add LO to this TA
		if (($result = UserAcl::checkTaPermission($ta, UserAcl::$EDIT)) !== TRUE) {
			return $result;
		}
		
		return TRUE;
	}
	
	// Reindex a teaching activity with new info
	public function updateIndexForTa($ta_id) {
		$statusFinder = new Status();
		$s_released = $statusFinder->getIdForStatus(Status::$RELEASED);
		$rows = $this->getTaLinkageWithStatus($ta_id, $s_released);
		foreach ($rows as $row) {
			$row->notifyObservers("post-delete");
		}
		foreach ($rows as $row) {
			$row->notifyObservers("post-insert");
		}
	}
}