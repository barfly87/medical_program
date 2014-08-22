<?php
Class WebService {
    
    private $domain = null ;
    
    public function __construct() {
        $this->domain = 'http://smp.sydney.edu.au'.Compass::baseUrl();
    }
    
     /** 
     * Get the teaching activity detail based on id. It returns array
     * array (
     *          'ta'=> array(...), 
     *          'lo' => array(...), 
     *          'resource' => (array(...)
     * ) 
     * 
     * @param int $id
     * @param string $token 
     * @return array $result 
     */
    public function getTeachingActivityDetailById($id, $token) {
        if ($token == null || ($token != ServiceController::$token)) {
            Zend_Registry::get('logger')->info(get_class($this). ': Invalid token ' . $token);      
            return ServiceController::$error['cannotAuthenticate'];
        }
        if ($id == null || $id <= 0) {
            Zend_Registry::get('logger')->info(get_class($this). ': Invalid teaching activity ID '. $id);
            return ServiceController::$error['invalidTAId'];
        }
        
        try {
        	$taFinder = new TeachingActivities();
        	$ta = $taFinder->getTa($id);
        	$id = $ta->latestReleasedVersionId();
            $result = $this->processTeachingActivity($id);
            if (empty($result)) {
                Zend_Registry::get('logger')->info(get_class($this). ": Teaching activity ID $id does not exist ");
                return ServiceController::$error['invalidTAId'];
            }
            return $result;
        } catch (Exception $e) {
            Zend_Registry::get('logger')->info(get_class($this). ': Teaching activity caught exception ' . $e);
            $error = ServiceController::$error['exception'];
            $error['exception'] = $e;
            return $error;
        }
    }
    
    /*
     * This function gets teaching activity details, details about learning objective attached and
     * details about resources attached to this teaching activity.
     *
     * @param int $id           //Teaching activity ID
     * @return mixed $result    
     */
    private function processTeachingActivity($id) {
        //$queryStr = '+doctype:("Teaching Activity" OR Linkage) +(ta_auto_id:'.$id.')';
        $queryStr = '+(ta_auto_id:'.$id.')';
        $searchResultsService = new SearchResultsService();
        $luceneResults = $searchResultsService->processLuceneResults('',$queryStr);
        return $this->processLuceneResults($luceneResults, $id);
    }
    
    /*
     * This function process lucene results into proper usable format
     *
     * @param object $luceneResults
     * @return int $id  
     */
    private function processLuceneResults($luceneResults, $id) {
        $result = array();
        $count = 0;
        $columns = SearchConstants::columns();
        foreach($luceneResults as $luceneResult) {
            if($count == 0) {
                foreach($columns as $key => $value) {
                    if($value['luceneIndex'] == 'ta_resource_links') {
                        $result['ta'][$count][$value['luceneIndex']] = $luceneResult->ta_resource_links_student;
                    } else {
                        $result['ta'][$count][$value['luceneIndex']] = $luceneResult->$value['luceneIndex'];
                    }
                }
                $result['ta'][$count]['url'] = $this->domain.'/teachingactivity/view/id/'.$id;
            }
            $result['lo'][$count]['lo_title'] = $luceneResult->lo_title;
            $result['lo'][$count]['url'] = $this->domain.'/learningobjective/view/id/'.$luceneResult->lo_auto_id;
            $result['lo'][$count]['resource'] = array();
            if(((int)$luceneResult->lo_auto_id) > 0 ) {
                $result['lo'][$count]['resource'] = $this->getResources((int)$luceneResult->lo_auto_id,'lo');
            }
            $count++;
        }
        if($count != 0 ) {
            $result['resource'] = $this->getResources($id,'ta');
        }
        return $result;
    }

    /*
     * This function gets resources attached to the teaching activity.
     *
     * @param int $id           //Teaching activity ID
     * @return mixed $result    
     */
    private function getResources($id, $resource_type){
    	$lecRecording = new LectureRecordings();
        $mediabankResource = new MediabankResource();
        $resources = $mediabankResource->getResources($id,$resource_type);
        try{
            $mediabankResourceService = new MediabankResourceService();
            $result = array();
            if($resources != false && is_array($resources) && count($resources) > 0) {
                $count = 0;
                foreach($resources as $resource) {
                    $mid = MediabankResourceConstants::encode($resource['resource_id']);
                    $result[$count]['title'] = $mediabankResourceService->getTitleForMid($resource['resource_id']);
                    $metadata = $mediabankResourceService->getMetaData($mid);
                    $result[$count]['url'] = "{$this->domain}/resource/view/id/{$id}/type/{$resource_type}/resourceid/{$resource['auto_id']}/resourcetypeid/{$resource['resource_type_id']}?mid={$mid}";
                    $result[$count]['mid'] = $mid;
                    $cmsLink = CmsConst::getCmsLinkForEvents($resource['resource_id']);
                    if (!empty($cmsLink)) {
                        $result[$count]['cmsLink'] = $cmsLink;
                        $count++;
                    }
                    else {
	                    if ($resource['resource_type_id'] == 13) {
	                    	$lecRsrc = $lecRecording->processResources(array($resource));
	                    	if (!empty($lecRsrc['lectopia'])) {
	                    		foreach ($lecRsrc['lectopia'] as $year => $data) {
	                    			$result[$count]['lectopia'] = $data['url'];
	                    			$result[$count]['title'] = $result[$count]['title'] . " ({$year})";
	                    		}
	                    	}
	                    	if (!empty($lecRsrc['medvid'])) {
	                    		foreach ($lecRsrc['medvid'] as $year => $data) {
	                    			foreach ($data['info'] as $type => $typeinfo) {
	                    				if ($type == 'richmedia') {
	                    					$result[$count]['richmedia'] = $typeinfo['onclick'];
	                    				} else {
	                    					$result[$count]['fileName'] = $typeinfo['customFileName'];
	                    				}
	                    			}
	                    			$result[$count]['title'] = $result[$count]['title'] . " ({$year})";
	                    		}
	                    	}
	                    	$result[$count]['mimeType'] = $metadata['mimeType'];
	                    	$count++;
	                    } else {
	                    	//TODO Need to pass in a 'role' from web service call to differentiate between student and staff
	                    	//Currently only displaying student resources to staff memeber.
	                    	if (!in_array($resource['resource_type_id'], ResourceTypeConstants::staffOnlyResourceTypeIds())) {
		                    	if ($resource_type == 'ta') {
		                    		$result[$count]['fileName'] = base64_encode("teachingactivity_{$id}_resource_{$resource['auto_id']}.{$metadata['fileTypeExtension']}");
		                    	} else {
		                    		$result[$count]['fileName'] = base64_encode("learnningobjective_{$id}_resource_{$resource['auto_id']}.{$metadata['fileTypeExtension']}");
		                    	}
		                    	$result[$count]['mimeType'] = $metadata['mimeType'];
		                    	$count++;
	                    	}
	                    }
                    }
                }
            }
            return $result;
        } catch(Exception $ex) {
            return array();
        }
        
    }

     /** 
     * Get the teaching activity detail based on block id, block week,sequence number and activity type 
     * @param int $owner_id
     * @param int $block_no
     * @param int $week_no
     * @param int $seq_no  
     * @param int $activity_type
     * @param string $token 
     * @return array $result
     */
    public function getTeachingActivityDetailByOwnerBlockWeekSeqType($owner_id, $block_no, $week_no, $seq_no, $activity_type, $token) {
    	$acttypeFinder = new ActivityTypes();
    	$activity_type = $acttypeFinder->getCompassActivityTypeFromEventsActivityType($activity_type);

        if ($token == null || ($token != ServiceController::$token)) {
            Zend_Registry::get('logger')->info(__METHOD__. ": Invalid token '$token'");      
            return ServiceController::$error['cannotAuthenticate'];
        }
    	if ($owner_id == null || $owner_id <= 0) {
            Zend_Registry::get('logger')->info(__METHOD__. ': Invalid owner ID |'. $owner_id . '|');          
            return ServiceController::$error['invalidOwnerId'];
        }
        if ($block_no === null || $block_no < 0) {
            Zend_Registry::get('logger')->info(__METHOD__. ': Invalid block number |'. $block_no . '|');          
            return ServiceController::$error['invalidBlockNo'];
        }
        if ($week_no == null || $week_no <= 0) {
            Zend_Registry::get('logger')->info(__METHOD__. ': Invalid week number |'. $week_no . '|');           
            return ServiceController::$error['invalidWeekNo'];
        }
        if ($seq_no == null || $seq_no <= 0) {
            Zend_Registry::get('logger')->info(__METHOD__. ': Invalid sequence number |'. $seq_no . '|');            
            return ServiceController::$error['invalidSeqNo'];
        }
        if ($activity_type == null || $activity_type <= 0) {
            Zend_Registry::get('logger')->info(__METHOD__. ': Invalid activity type |'. $activity_type . '|');           
            return ServiceController::$error['invalidActivityType'];
        }        
        
        try {
            $db = Zend_Registry::get("db");
            $released = Status::$RELEASED;
            
            $sbs = new StageBlockSeqs();
            $block_Id = $sbs->getBlockId($block_no);
            
            $select = $db->select()
            		->from(array('ta' => 'teachingactivity'), array('ta_id' => 'auto_id', 'current_teacher' => 'current_teacher', 
            					'principal_teacher' => 'principal_teacher', 'ta_name' => 'name'))
            		->join(array('lk' => 'link_lo_ta'), 'lk.ta_id = ta.auto_id', array())
            		->where("ta.block=$block_Id AND
						ta.block_week=(select auto_id from lk_blockweek where weeknum=$week_no) AND 
						ta.sequence_num=(select auto_id from lk_sequence_num where seqnum = $seq_no) AND
						ta.type=$activity_type AND
						ta.owner=$owner_id AND
						lk.status=(select auto_id from lk_status where name='{$released}')");
            $result = $db->fetchRow($select);
            if ($result == NULL) {
                Zend_Registry::get('logger')->info(__METHOD__. ":Teaching activity for block $block_no, week $week_no, sequence $seq_no, activity_type $activity_type does not exist");
                Zend_Registry::get('logger')->info(__METHOD__. ": Query : \n".$select->__toString());
                return ServiceController::$error['invalidTAId'];
            }
            $lecturerUid = array();
            $lecturerUid = preg_split("/[\s,]+/", $result['current_teacher'],NULL, PREG_SPLIT_NO_EMPTY);
            if(count($lecturerUid) == 0) {
                $lecturerUid = preg_split("/[\s,]+/",$result['principal_teacher'], NULL, PREG_SPLIT_NO_EMPTY);
            }
            return array('auto_id' => $result['ta_id'],'lecturerUid' =>$lecturerUid, 'name' => $result['ta_name']);
        } catch (Exception $e) {
            Zend_Registry::get('logger')->info(__METHOD__. ':Teaching activity caught exception ' . $e);
            $error = ServiceController::$error['exception'];
            $error['exception'] = $e;
            return $error;
        }
    }
    
     /** 
     * Get all the theme names 
     * 
     * @param string $token 
     * @return array $result
     */
    public function getThemes($token) {
        if ($token == null || ($token != ServiceController::$token)) {
            Zend_Registry::get('logger')->info(get_class($this). ': Invalid token ' . $token);      
            return ServiceController::$error['cannotAuthenticate'];
        }
        $themeFinder = new Themes();
        try {
            return $themeFinder->getAllNames();
        } catch (Exception $e) {
            Zend_Registry::get('logger')->info(get_class($this). ': Fetching theme names ' . $e);
            $error = ServiceController::$error['exception'];
            $error['exception'] = $e;
            return $error;
        }
    }
    
     /** 
     * Get block name 
     * @param int $block
     * @param string $token
     * @return array $result
     */
    public function getBlockName($block, $token) {
    	Zend_Registry::get('logger')->info(__METHOD__. ": Fetching block name for $block, $token");
        if ($token == null || ($token != ServiceController::$token)) {
            Zend_Registry::get('logger')->info(__METHOD__. ': Invalid token ' . $token);      
            return ServiceController::$error['cannotAuthenticate'];
        }
        try {
        	$sbs = new StageBlockSeqs();
        	$name = $sbs->getBlockName($block);
        	return array($name);
        } catch (Exception $e) {
            Zend_Registry::get('logger')->info(__METHOD__. ': Fetching block name ' . $e);
            return ServiceController::$error['exception'];
        }
    }
    
     /** 
     * Get PBL name
     * @param int $block
     * @param int $blockweek
     * @param string $token
     * @return array $result
     */
    public function getPBLName($block, $blockweek, $token) {
    	Zend_Registry::get('logger')->info(__METHOD__. ": Fetching PBL name for $block, $blockweek, $token");
        if ($token == null || ($token != ServiceController::$token)) {
            Zend_Registry::get('logger')->info(__METHOD__. ': Invalid token ' . $token);      
            return ServiceController::$error['cannotAuthenticate'];
        }
        try {
        	$bps = new BlockPblSeqs();
        	$name = $bps->getPblNameForBlockWeek($block, $blockweek);
        	return array($name);
        } catch (Exception $e) {
            Zend_Registry::get('logger')->info(__METHOD__. ': Fetching block name ' . $e);
            return ServiceController::$error['exception'];
        }
    }
    
    /**
     * Return result set for lucene query
     * 
     * @param string $query
     * @param string $context
     * @param string $token
     * @return array $return
     */
    public function getLuceneResults($query, $context, $token){
        if ($token == null || ($token != ServiceController::$token)) {
            Zend_Registry::get('logger')->info(get_class($this). ': Invalid token ' . $token);
            return ServiceController::$error['cannotAuthenticate'];
        }
        
        if(is_null($query) || empty($query)) {
            Zend_Registry::get('logger')->info(get_class($this). ': Empty lucene query.' . $token);
            return ServiceController::$error['invalidLuceneQuery'];
        }
        
        if(is_null($context) || ! in_array($context, array('lo','ta')) ) {
            Zend_Registry::get('logger')->info(get_class($this). ': Invalid or missing context.' . $token);
            return ServiceController::$error['invalidLuceneResultContext'];
        }

        try {
        	$columns = SearchConstants::columns();
            $subContext = ($context == 'lo') ? 'ta' : 'lo' ;
            $searchResultsService = new SearchResultsService();
            $luceneResults = $searchResultsService->processLuceneResults('',$query);
            $luceneFields = array();
            foreach($columns as $key => $val) {
                if(strpos($val['luceneIndex'], 'created_by') !== false || strpos($val['luceneIndex'], 'date_created') !== false) {
                    continue;
                }
                                
                if(strpos($val['luceneIndex'], 'lo_') !== false || strpos($val['luceneIndex'], 'ta_') !== false){
                    $luceneFields['luceneFieldNames'][] = $val['luceneIndex'];
                    $luceneFields['luceneFieldIds'][] = $key;
                }
            }
            $results = $searchResultsService->processResults($luceneFields, $luceneResults, $context);
            $return = array();
            if(isset($results['context'])) {
                foreach($results['context'] as $key => $value) {
                    $return[$context][$key] = $value; 
                }
                foreach($results['subContext'] as $key => $value) {
                    if(isset($return[$context][$key])) {
                        foreach($value as $subKey => $subValue) {
                            $return[$context][$key][$subContext][$subKey] = $subValue;
                        }
                    } 
                }
            } else {
                Zend_Registry::get('logger')->info(get_class($this). ': Lucene Query did not return any records.' . $token);
                return ServiceController::$error['recordsNotFound'];
            }
            return $return;
        } catch (Exception $ex) {
            Zend_Registry::get('logger')->info(get_class($this). ': Exception.' . $token);
            Zend_Registry::get('logger')->info($ex->getTraceAsString());
            return ServiceController::$error['luceneResultsException'];
        }
    }

    /**
     * Allow eventsdb to get list of ta ids for lucene query sent.
     * 
     * @param string $query
     * @param string $token
     * @return array $return
     */
    public function getTaIdListByLucene($query, $token) {
        if ($token == null || ($token != ServiceController::$token)) {
            Zend_Registry::get('logger')->info(get_class($this). ': Invalid token ' . $token);      
            return ServiceController::$error['cannotAuthenticate'];
        }
        if(is_null($query) || empty($query)) {
            Zend_Registry::get('logger')->info(get_class($this). ': Empty lucene query.' . $token);         
            return ServiceController::$error['invalidLuceneQuery'];
        }
        $searchResultsService = new SearchResultsService();
        $luceneResults = $searchResultsService->processLuceneResults('',$query);
        $luceneFields = array();
        $columns = SearchConstants::columns();
        $taAutoIdFieldName = $columns[26]['luceneIndex'];
        $luceneFields['luceneFieldNames'][] = $taAutoIdFieldName;
        $luceneFields['luceneFieldIds'][] = 26;
        $context = 'ta';
        $results = $searchResultsService->processResults($luceneFields, $luceneResults, $context);
        $return = array();
        if(isset($results['context'])) {
            foreach($results['context'] as $key => $value) {
                $return['ta_ids'][] = $value[$taAutoIdFieldName]; 
            }
            if(isset($return['ta_ids'])) {
                sort($return['ta_ids']);
            }
        } else {
            Zend_Registry::get('logger')->info(get_class($this). ': Lucene Query did not return any records.' . $token);
            return ServiceController::$error['recordsNotFound'];
        }
        if(isset($return['ta_ids'])) {
            sort($return['ta_ids']);
        }
        return $return;
    }

    /**
     * Allow eventsdb to send student evaluation on a ta to compass
     * @param array $data
     * @param string $token
     * @return array $return
     */
    public function setEvaluation($data,  $token) {
        $return = array ('result' => false);
        if(empty($token) || ($token != ServiceController::$token)) {
            Zend_Registry::get('logger')->info(get_class($this). ': Invalid token ' . $token);      
            return ServiceController::$error['cannotAuthenticate'];
        }
        if(!isset($data['comment']) && empty($data['comment'])) {
            Zend_Registry::get('logger')->info(get_class($this). ': Invalid Evaluation Comment '. $data['comment']);           
            return ServiceController::$error['evaluationCommentEmpty'];
        } 
        if(!isset($data['type']) && empty($data['type'])) {
            Zend_Registry::get('logger')->info(get_class($this). ': Invalid Evaluation Type '. $data['type']);           
            return ServiceController::$error['evaluationTypeEmpty'];
        }
        if(!isset($data['type_id']) && empty($data['type_id']) && $data['type_id'] <= 0) {
            Zend_Registry::get('logger')->info(get_class($this). ': Invalid Evaluation Type ID '. $data['type_id']);           
            return ServiceController::$error['evaluationTypeIdEmpty'];
        }
        if(!isset($data['uid']) && empty($data['uid'])) {
            Zend_Registry::get('logger')->info(get_class($this). ': Invalid Evaluation uid '. $data['uid']);           
            return ServiceController::$error['evaluationUidEmpty'];
        }
        
        try {
            $studentEvaluate = new StudentEvaluate();
            $dataParams = StudentEvaluateConst::getDataFromRequestParams($data);
            $studentEvaluateId = $studentEvaluate->insertComment($data['comment'],$data['type'],$data['type_id'],$data['uid'], $dataParams);
            if($studentEvaluateId !== false) {
                $studentEvaluateData = new StudentEvaluateData();
                foreach($dataParams as $key => $val) {
                    $studentEvaluateData->insertData($studentEvaluateId,$key,$val);
                }
                $return['result'] = true;
            }
            return $return;
        } catch(Exception $ex) {
            return array('result' => false);
        }
        
    }
    
    
    /**
     * Allow Joe's student portal to get the list of student that does not have official photo
     * @param string $uids_str
     * @param string $token
     * @return string $return
     */
    public function getStudentWithoutOfficialPhoto($uids_str,  $token) {
        $return = array();
        if(empty($token) || ($token != ServiceController::$token)) {
            Zend_Registry::get('logger')->info(get_class($this). ': Invalid token ' . $token);      
            return ServiceController::$error['cannotAuthenticate'];
        }
        $uids = explode(',', $uids_str);
        $photo = PeopleService::getOfficialPhotoList($uids);
        $uids_with_photo = array_keys($photo);
        $return_uids = array_diff($uids, $uids_with_photo);
        return implode(',', $return_uids);
    }
    
    /**
     * Allow eventsdb to get list of of clinical schools
     * @param string $token
     * @return array $return
     */
    public function getClinicalSchools($token) {
        Zend_Registry::get('logger')->info(get_class($this). ': getClinicalSchools() called');
        if(empty($token) || ($token != ServiceController::$token)) {
            Zend_Registry::get('logger')->info(get_class($this). ': Invalid token ' . $token);      
            return ServiceController::$error['cannotAuthenticate'];
        }
        try {
            $clinicalSchool = new ClinicalSchool();
            return $clinicalSchool->fetchPairs(); 
        } catch(Exception $ex) {
            Zend_Registry::get('logger')->info(get_class($this). ': getClinicalSchools() caught exception ' . $e);
            $error = ServiceController::$error['exception'];
            $error['exception'] = $e;
            return $error;
        }
    }
    
    
    
}