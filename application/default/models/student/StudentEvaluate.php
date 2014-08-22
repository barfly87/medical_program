<?php 

class StudentEvaluate extends Zend_Db_Table_Abstract {
    
    protected $_name = 'student_evaluate';

    public function insertComment($comment = '', $type = '', $type_id = '', $uid = '', $dataParams = array()) {
        try {
            if(empty($uid)) {
                $uid = UserAcl::getUid();
            }
            if( !empty($type) && (int)$type_id > 0 && $uid != 'unknown') {
                $data = array(
                            'uid' => $uid,
                            'type' => $type,
                            'type_id' => (int)$type_id,
                            'epoch' => time(),
                            'comment' => $comment
                        );        
                $duplicateComment = $this->_isCommentADuplicate($uid, $type, $type_id, $comment, $dataParams);
                if($duplicateComment === false) {
                    $result = $this->insert($data);   
                    if($result !== false) {
                        SearchIndexer::reindexDocument('ta', $type_id);
                        return $result;
                    }                     
                } else {
                    return 'duplicate_comment';
                } 
            }
            return false;
        } catch (Exception $ex) {
            Zend_Registry::get('logger')->warn($ex->getMessage());
            return false;
        }
    }
    
    private function _isCommentADuplicate($uid, $type, $type_id, $comment, $dataParams) {
        try {
            $select = $this->select()
                            ->from($this, 'auto_id')
                            ->where('uid = ?', $uid)
                            ->where('type = ?', $type)
                            ->where('type_id = ?', $type_id)
                            ->where('comment = ?', $comment)
                            ->order('auto_id desc');
            $rows = $this->fetchAll($select);
            $isCommentDuplicate = false;
            //No data exists in the table with matching content so comment is not a duplicate
            if($rows->count() > 0){
                //$dataParams should normally exist. It can be something like 
                //'rating'=>'1', 'clinical_school'=>'Central' or 'pbl_group'=>'5' and so on
                //If $dataParams is not empty we need to match it as well
                if( !empty($dataParams)) {
                    $rows = $rows->toArray();
                    $auto_ids = array();
                    foreach ($rows as $row) {
                        $auto_ids[] = $row['auto_id'];
                    }
                    //Each and every key and value of $dataParams should match with the data stored
                    //in student_evaluate_data table as key,val columns for it to be considered 
                    //a duplicate comment
                    $studentEvaluateData = new StudentEvaluateData();
                    $where = 'student_evaluate_id in ('.implode(',' , $auto_ids).')';
                    $studentEvaluateDataRows = $studentEvaluateData->fetchAll($where);
                    if($studentEvaluateDataRows->count() > 0) {
                        $allKeyValExists = true;
                        foreach($dataParams as $key => $val) {
                            $keyValExist = false;
                            foreach($studentEvaluateDataRows as $studentEvaluateDataRow) {
                                if($studentEvaluateDataRow->key == $key && 
                                                $studentEvaluateDataRow->val == $val) {
                                    $keyValExist = true;
                                }
                            }
                            if($keyValExist === false) {
                                //If one of the key don't exist that means its not a duplicate comment we can break safely
                                $allKeyValExists = false;
                                break;
                            }
                        }
                        //Since all the key value exists in the database we can consider it has a duplicate comment
                        if($allKeyValExists === true) {
                            $isCommentDuplicate = true;
                        }
                    }
                // Since $dataParams are empty and we have a matching row in the table we can 
                // treat it has a duplicate comment
                } else {
                    $isCommentDuplicate = true;
                }
            }
            return $isCommentDuplicate;
            
        } catch (Exception $ex) {
            $error = $ex->getMessage().PHP_EOL.$ex->getTraceAsString().PHP_EOL;
            Zend_Registry::get('logger')->warn(PHP_EOL."CLASS\t: ".__CLASS__.PHP_EOL."METHOD\t: ".__METHOD__.PHP_EOL."Error\t: ".$error.PHP_EOL);
            return false;
        }
    }
    
    public function getTypeTa($params)  {
        try {
            $query = "SELECT se.*, sed.key as data_key, sed.val as data_val FROM student_evaluate AS se LEFT JOIN student_evaluate_data AS sed ON se.auto_id = sed.student_evaluate_id WHERE TYPE = 'ta'";
            if(isset($params['years']) && !empty($params['years'])) {
                $query .= ' and (EXTRACT(YEAR FROM to_timestamp(epoch)) in ('.implode(',', $params['years']).'))';
            }
            Zend_Registry::get('logger')->info(PHP_EOL."CLASS\t: ".__CLASS__.PHP_EOL."METHOD\t: ".__METHOD__.PHP_EOL."QUERY\t: ".$query.PHP_EOL);
            $results = $this->getAdapter()->fetchAll($query);
            return $results;
        } catch (Exception $ex) {
            Zend_Registry::get('logger')->warn($ex->getMessage());
            return false;
        }
    }
    
    public function getEvaluationForTaId($taId=0) {
        try {
            if((int)$taId < 0) {
                return false;
            }
            
            $teachingActivities = new TeachingActivities();
            $currentTa = $teachingActivities->fetchRow('auto_id = '.$taId);
            $taRevisions = $teachingActivities->getTaRevisions($currentTa->taid);
            
            $taRows = array();
            if(!empty($taRevisions)) {
	            $taAutoIds = array();
            	foreach($taRevisions as $taRevision) {
            		$taAutoId = $taRevision->auto_id;
            		$taAutoIds[] = $taAutoId;
            		$principalTeachers = $this->getPrincipalTeachersInfo($taRevision);
            		$tutors = (empty($principalTeachers)) ? $this->getCurrentTeachersInfo($taRevision) : $principalTeachers;
            		$taRows[$taAutoId]['tutors'] = $tutors;
            	}
	            if(!empty($taAutoIds)) {
		            $select = $this->select()->from($this)->where('type_id in ('.implode(',',$taAutoIds).") and type ='ta'")->order('type_id DESC')->order('epoch DESC');
		            $results = $this->fetchAll($select);
		            $studentEvaluateData = new StudentEvaluateData();
		            if($results->count() > 0) {
		            	$evaluationRows =  $results->toArray();
		            	foreach($evaluationRows as &$evaluationRow) {
		            		$studentEvaluateDataRow = $studentEvaluateData->fetchKeyVal($evaluationRow['auto_id']);
		            		$evaluationRow['rating'] = $studentEvaluateDataRow['rating'];
		            		$evaluationRow['tutors'] = $taRows[$evaluationRow['type_id']]['tutors'];
		            	}
		            	return $evaluationRows;
		            }
	            }
            }
            return false;
        } catch (Exception $ex) {
            Zend_Registry::get('logger')->warn($ex->getMessage());
            return false;
        }
    }
    
    private function getPrincipalTeachersInfo(TeachingActivity $ta) {
    	$uids = $ta->principal_teacher_uid_arr();
    	return $this->getUidsInfo($uids);
    }
    
    private function getCurrentTeachersInfo(TeachingActivity $ta) {
    	$uids = $ta->current_teacher_uid_arr();
    	return $this->getUidsInfo($uids);
    }
    
    private function getUidsInfo($uids) {
    	$uidsInfo = array();
    	foreach($uids as $uid) {
    		$uidInfo = UserService::getUidFullName($uid);
    		if($uidInfo !== $uid) {
    			$uidInfo = sprintf('<a href="javascript:void(0);" class="tooltip-class" title="%s">%s</a>',
    					$uidInfo, $uid);
    		}
    		$uidsInfo[] = $uidInfo;
    	}
    	return implode(', ', $uidsInfo);
    }
    
    public function getCountOfEvaluationsForTaId($taId=0) {
        try {
            if((int)$taId <= 0) {
                return 0;
            }
            $db = Zend_Registry::get('db');
            $select = $db->select()->from(array('se' => 'student_evaluate'),'count(*)')->where('type_id = '.(int)$taId." and type ='ta'");
            return $db->fetchOne($select);
        } catch (Exception $ex) {
            Zend_Registry::get('logger')->warn($ex->getMessage());
            return 0;
        }
    }
    
    public function getRatingAvg($type, $typeId) {
        try {
            $type = trim($type);
            $typeId = (int)$typeId;
            
            if(empty($type) || empty($typeId)) {
                $error = "Empty type : '".$type."' or Empty Type Id : ".$typeId.' given';
                Zend_Registry::get('logger')->warn(PHP_EOL."CLASS\t: ".__CLASS__.PHP_EOL."METHOD\t: ".__METHOD__.PHP_EOL."Error\t: ".$error.PHP_EOL);
                return array();
            }
            
            $taRevisions = array();
            if($type == 'ta') {
	            $teachingActivities = new TeachingActivities();
	            $currentTa = $teachingActivities->fetchRow('auto_id = '.$typeId);
	            $taRevisions = $teachingActivities->getTaRevisions($currentTa->taid);
            }

            $rows = array();
            if(!empty($taRevisions)) {
            	$taAutoIds = array();
            	foreach($taRevisions as $taRevision) {
            		$taAutoId = $taRevision->auto_id;
            		$taAutoIds[] = $taAutoId;
            	}
            	if(!empty($taAutoIds)) {
		            $adapter = $this->getAdapter();
		            $select = $adapter->select()
                                ->from(array('SE' => $this->_name), 'epoch')
                                ->join(array('SED' => 'student_evaluate_data'),'"SE".auto_id = "SED".student_evaluate_id', 'val')
                                ->where('"SE".type =  ?' , $type)
                                ->where('"SE".type_id in ('.implode(',', $taAutoIds).')')
                                ->where('"SED".key = ? ', StudentEvaluateConst::$KEY_rating);
		            $rows = $adapter->fetchAll($select);
            	}
            }	 
            return $this->_processRatingAvg($rows);
        } catch (Exception $ex) {
            $error = $ex->getMessage().PHP_EOL.$ex->getTraceAsString().PHP_EOL;
            Zend_Registry::get('logger')->warn(PHP_EOL."CLASS\t: ".__CLASS__.PHP_EOL."METHOD\t: ".__METHOD__.PHP_EOL."Error\t: ".$error.PHP_EOL);
            return array();
        }
    }

    private function _processRatingAvg($rows) {
        $return = array();
        if(count($rows) > 0 ) {
            $count = array();
            $years = array();
            foreach($rows as $row) {
                $year = date('Y', $row['epoch']);
                if(isset($years[$year])) {
                    $count[$year]++;
                    $years[$year]['rating'] += (int)$row['val'];
                } else {
                    $count[$year] = 1;
                    $years[$year]['rating'] = (int)$row['val'];
                }
            }
            arsort($years);
            foreach($years as $year => $val) {
                $avg = ($val['rating'] / $count[$year]);
                $return['years'][$year]['ratingAvg'] = sprintf('%.1f', $avg);
                $return['years'][$year]['noOfResponses'] = $count[$year];
            }
        }
        return $return;
    }

    
}
