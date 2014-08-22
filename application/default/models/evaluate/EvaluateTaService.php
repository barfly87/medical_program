<?php
require_once 'php-ofc-library/open-flash-chart.php';
class EvaluateTaService {
    
    private $_linkageLoTas = null;
    private $_teachingActivities = null;
    private $_evaluateTa = null;
    private $_releasedStatusId = null;
    private $_db = null;
    private $_uids = null;
    private $_startDate = null;
    private $_endDate = null;
    private $_taTypeIds = array();
    private $_showStudentUids = false;
    private $_defaultTaColumns = null;
    private $_taTypeColumns = null;
    private $_evaluateEventsService = null;

    public function __construct() {
        $this->_linkageLoTas = new LinkageLoTas(); 
        $this->_teachingActivities = new TeachingActivities();   
        $this->_releasedStatusId  = $this->_getReleasedStatusId();
        $this->_evaluateTa = new EvaluateTa();
        $this->_evaluateEventsService = new EvaluateEventsService();
        $this->_db = Zend_Registry::get('db');
    }
    
    /**
     * Tries to update or insert evaluation feedback in the database.
     * @param array $values
     * @return boolean 
     */
    public function processForm($values) {
        try {
            $data = $this->_processValues($values);
            if(isset($values[EvaluateTaConst::$EVALUATION_AUTO_ID]) && (int)$values[EvaluateTaConst::$EVALUATION_AUTO_ID] > 0) {
                $where = $this->_evaluateTa->getAdapter()->quoteInto('auto_id = ?', (int)$values[EvaluateTaConst::$EVALUATION_AUTO_ID]);
                $updateDone = $this->_evaluateTa->update($data, $where);
                if($updateDone === 0) {
                    return false;
                }
            } else {
                $insertDone = $this->_evaluateTa->insert($data);
                if($insertDone === false) {
                    return false;
                }
            }
            return true;
        } catch (Exception $ex) {
        	$error = $ex->getMessage().PHP_EOL.$ex->getTraceAsString().PHP_EOL;
        	Zend_Registry::get('logger')->warn(PHP_EOL."CLASS\t: ".__CLASS__.PHP_EOL."METHOD\t: ".__METHOD__.PHP_EOL."Error\t: ".$error.PHP_EOL);
            return false;
        }
    }
    
    /**
     * This function gets all the columns from the database and for each column for which it finds a corresponding
     * form value submitted by the user it adds it and for all the columns set the value to null.
     * 
     * @param array $values : This are the form values that need to be process
     * @return array $data  : This are the row values that needs to be updated or inserted in the db
     */
    private function _processValues($values) {
        $cols = $this->_evaluateTa->getTableColumns();
        $data = array();
        foreach($cols as $col) {
            if($col != 'auto_id') {
                if(isset($values[$col])) {
                    $data[$col] = $values[$col];
                } else {
                    $data[$col] = null;
                }
            }
        }
        return $data;
    }
    
    public function getTaResponses($ta, $questions) {
        $taAutoIds = $this->_getTaVersions($ta->auto_id);
        $return = array();
        if(!empty($taAutoIds)) {
            $evaluations = $this->_evaluateTa->getEvaluationForTaIds($taAutoIds);
            if(!empty($evaluations)) {
                $count = 1;
                foreach($questions as $question) {
                    if($question == EvaluateTaConst::$SUGGESTIONS) {
                        $return[$question]['text'] = $this->_getEvaluationFor($evaluations, $question);
                    } else {
                        $return[$question]['url'] = $this->_createUrl($taAutoIds, $question, EvaluateTaConst::getOptions($question, $ta->type));
                        if($question == EvaluateTaConst::$STUDENT_ATTENDANCE) {
                            $return[EvaluateTaConst::$STUDENT_ATTENDANCE_COMMENT]['text'] = $this->_getEvaluationFor($evaluations, EvaluateTaConst::$STUDENT_ATTENDANCE_COMMENT);
                        } else if ($question == EvaluateTaConst::$OVERLAP) {
                            $return[EvaluateTaConst::$OVERLAP_EXPLANATION]['text'] = $this->_getEvaluationFor($evaluations, EvaluateTaConst::$OVERLAP_EXPLANATION);
                        }
                    }
                    $count++;
                }
            }
        }
        return $return;
    }
    
    /**
     * Returns the converted line separated uid values in array
     * @param string $uidStr
     * @return array $uids
     */
    private function _processUids($uidStr) {
        if(!empty($uidStr)) {
            $explode = explode(PHP_EOL, $uidStr);
            if(count($explode) > 0) {
                $uids = array();
                foreach($explode as $uid) {
                    if(strlen(trim($uid)) > 0) {
                        $uids[] = trim($uid);
                    }
                }
                return $uids;
            }
        }
        return array();
    }

    public function createCSV($req) {
        $this->_processRequest($req);
        
        //Fetch all lecture evaluations
        $selectEvaluations = $this->_evaluateTa->select()->order(EvaluateTaConst::$TA_AUTO_ID.' ASC');
        
        if(!empty($this->_taTypeIds)) {
            $selectEvaluations->where(EvaluateTaConst::$TA_TYPE_ID.' IN (?)', $this->_taTypeIds);
        } else {
            print '<h1 style="color:red">Error !</h1>';
            print '<h3>No teaching activity types found.</h3>';
            exit;
        }
        
        //If compass is connected to events start and end would exist if provided.
        if($this->_startDate != null && $this->_endDate != null) {
            $taAutoIds = $this->_evaluateEventsService->getTaAutoIdsBetweenStartAndEndDate($this->_taTypeIds, $this->_startDate, $this->_endDate);
            if(! empty($taAutoIds)) {
                $selectEvaluations->where(EvaluateTaConst::$TA_AUTO_ID.' IN (?)', $taAutoIds);
            } else {
                die('ERROR !<br />Events did not returned any teaching activity for the dates you have entered.');
            }
        }
    
        $csvRows = array();
        $rows = $this->_evaluateTa->fetchAll($selectEvaluations);
        if($rows->count() > 0) {
            $evals                 = array();
            $studentsTaFeedbacks   = array();
            $taAutoIds             = array();
            
            foreach($rows as $row) {
                $eval = $this->_getColumnsForEval($row);
                //Add current ta id for which the evaluation was given in the case where a new ta version has been created.
                $taAutoId = $eval[EvaluateTaConst::$CURRENT_TA_AUTO_ID];
                $taAutoIds[] = $taAutoId;//Need to add current $taAutoId to the list
                
                //Functionality for lecture evaluation admins
                if(!empty($this->_uids) && isset($eval[EvaluateTaConst::$STUDENT_ID]) && in_array($eval[EvaluateTaConst::$STUDENT_ID], $this->_uids)) {
                    $studentsTaFeedbacks[$taAutoId][$eval[EvaluateTaConst::$STUDENT_ID]] = true;
                }
                $evals[$taAutoId][] = $eval;
            }
            
            //Functionality for lecture evaluation admins
            if(!empty($this->_uids)) {
                $uidsEvals = array();
                if(!empty($studentsTaFeedbacks)) {
                    foreach($studentsTaFeedbacks as $taAutoId => $students) {
                        //If user is evaluation admin we need to make sure that there are atleast three unique student feedbacks for each ta 
                        if(count($students) >= 3) {
                            $uidsEvals[$taAutoId] = $evals[$taAutoId];
                        //If user is ta evaluation super admin then the data can be displayed unconditionally
                        } else if (EvaluateTaConst::isUserEvaluationSuperAdmin()) {
                            $uidsEvals[$taAutoId] = $evals[$taAutoId];
                        }
                    }
                }
                $evals = $uidsEvals;
            }
            
            $tas = $this->_fetchAllTasInfo($taAutoIds);
            foreach($tas as $taAutoId => $ta) {
                if(isset($evals[$taAutoId])) {
                    foreach($evals[$taAutoId] as $eval) {
                        $csvRow = array_merge($ta, $eval);
                        //Functionality for lecture evaluation admins
                        if(!empty($this->_uids)) {
                            $studentId = $eval[EvaluateTaConst::$STUDENT_ID];
                            if(isset($studentsTaFeedbacks[$taAutoId])
                                            && isset($studentsTaFeedbacks[$taAutoId][$studentId])
                                            && $studentsTaFeedbacks[$taAutoId][$studentId] === true) {
                                $csvRow['feedback'] = 'SELECTED';
                            } else {
                                $csvRow['feedback'] = '';
                            }
                        }
                        //VERY IMPORTANT STEP TO DELETE STUDENT_ID BECAUSE THIS INFORMATION SHOULD NEVER BE DISPLAYED TO ANYBODY
                        //ITS ONLY VISIBLE TO SUPER ADMINS AND ONLY WHEN THEY ACTUALLY WANT THAT INFORMATION TO BE DISPLAYED.
                        if($this->_showStudentUids !== true) {
                            unset($csvRow[EvaluateTaConst::$STUDENT_ID]);
                        }
                        $csvRows[] = $csvRow;
                    }
                }
            }
            if(!empty($csvRows)) {
                usort($csvRows, array($this, '_compareTaAutoIds'));
            }
        }
        if(!empty($this->_uids) && empty($csvRows)) {
            $csvRows = array();
            $csvRows[] = array('ERROR !' => 'Compass could not find a single lecture for which the feedback was given by a minimum of 3 distinct student uids from the list of student uids given.');
        }
        $this->_throwCSV($csvRows);
    }
    
    /**
     * If a user is compass admin they can only request evaluation for ta type ids
     * If a user is evaluation admin they can also send date and uids
     * If a user is evaluation superadmin they can also ask whether student uids need to be returned or not
     * @param Zend_Controller_Request_Abstract $req
     */
    private function _processRequest(Zend_Controller_Request_Abstract $req) {
        $taTypeIds              = $req->getParam(EvaluateTaConst::TA_TYPE_IDS,     '');
        if(!empty($taTypeIds) && is_array($taTypeIds)) {
            foreach($taTypeIds as $taTypeId) {
                if((int)$taTypeId > 0) {
                    $this->_taTypeIds[] = (int)$taTypeId;
                }
            }
        }
        if(EvaluateTaConst::isUserEvaluationAdmin()) {
            $uids                   = $req->getParam(EvaluateTaConst::UIDS,         '');
            if(!empty($uids)) {
                $this->_uids        = $this->_processUids($uids);
            }
            if(Compass::isConnectedToEvents()) {
                $startDate              = $req->getParam(EvaluateTaConst::START_DATE,   '');
                if(!empty($startDate)) {
                    $this->_startDate   = date('d-m-Y', strtotime($startDate)).' 00:00:01';
                }
                $endDate                = $req->getParam(EvaluateTaConst::END_DATE,     '');
                if(!empty($endDate)) {
                    $this->_endDate     = date('d-m-Y', strtotime($endDate)).' 23:59:59';
                }
            }
            if(EvaluateTaConst::isUserEvaluationSuperAdmin()) {
                $showStudentUids    = $req->getParam(EvaluateTaConst::SHOW_STUDENT_UIDS, '');
                if(!empty($showStudentUids) && $showStudentUids == EvaluateTaConst::SHOW_STUDENT_UIDS_YES) {
                    $this->_showStudentUids = true;
                }
            }
        }
    }
    
    /**
     * Return a combination of default columns and ta related columns.
     * Default columns are generic columns like ta_id, ta_type, role, datetime etc)
     * TA related columns are based on the config questions for that ta type since for 
     * each matching question there is a column in the 'evaluate_ta' table 
     * @param mixed $row
     */
    private function _getColumnsForEval($row) {
        $eval = array();
        //Since this function is called every time a row is processesd we only 
        //need to set default columns once when its called for the first time
        if($this->_defaultTaColumns == null) {
            $this->_defaultTaColumns = EvaluateTaConst::getDefaultTableColumns();
        }
        //Add mandatory columns
        foreach($this->_defaultTaColumns as $column) {
            //If the column happens to be ta_auto_id we need to find whats the current_ta_auto_id if a new version is created.
            if($column == EvaluateTaConst::$TA_AUTO_ID) {
                $taAutoId = $row->__get($column);
                $currentTaAutoId  = $this->_getCurrentTaAutoid($taAutoId);
                $eval[EvaluateTaConst::$CURRENT_TA_AUTO_ID] = ($currentTaAutoId !== false) ? $currentTaAutoId : $taAutoId;
                $eval[$column] = $taAutoId;
            } else {
                $eval[$column] = $row->__get($column);
            }
        }
        
        //Since this function is called every time a row is processesd we only
        //need to set ta type related columns once when its called for the first time
        if($this->_taTypeColumns == null) {
            if(!empty($this->_taTypeIds)) {
                $this->_taTypeColumns = EvaluateTaConst::getColumnsForTaTypeIds($this->_taTypeIds);
            }
        }
        //Add columns pertaining to the ta type ids
        //Only need to show the once which are configured in config for this ta type
        if(!empty($this->_taTypeColumns)) {
            foreach($this->_taTypeColumns as $taTypeColumn) {
                $eval[$taTypeColumn] = $row->__get($taTypeColumn);
                if($taTypeColumn == EvaluateTaConst::$STUDENT_ATTENDANCE) {
                    $eval[EvaluateTaConst::$STUDENT_ATTENDANCE_COMMENT] = $row->__get(EvaluateTaConst::$STUDENT_ATTENDANCE_COMMENT);
                } else if ($taTypeColumn == EvaluateTaConst::$OVERLAP) {
                    $eval[EvaluateTaConst::$OVERLAP_EXPLANATION] = $row->__get(EvaluateTaConst::$OVERLAP_EXPLANATION);
                }
            }
        }
        return $eval;
    }
    
    private function _compareTaAutoIds($a, $b) {
        $aStage = (int)$a['stage'];
        $bStage = (int)$b['stage'];
        if($aStage == $bStage) {
            $aTaTypeId = (int)$a[EvaluateTaConst::$TA_TYPE_ID];
            $bTaTypeId = (int)$b[EvaluateTaConst::$TA_TYPE_ID];
            if($aTaTypeId == $bTaTypeId) {
                $aBlockWeek = (float)$a['block_week'];
                $bBlockWeek = (float)$b['block_week'];
                if($aBlockWeek == $bBlockWeek) {
                    $aTaAutoid = (int)$a[EvaluateTaConst::$CURRENT_TA_AUTO_ID];
                    $bTaAutoid = (int)$b[EvaluateTaConst::$CURRENT_TA_AUTO_ID];
                    if($aTaAutoid == $bTaAutoid) {
                        $aTaVersion = (int)$a[EvaluateTaConst::$TA_AUTO_ID];
                        $bTaVersion =(int)$b[EvaluateTaConst::$TA_AUTO_ID];
                        if($aTaVersion == $bTaVersion) {
                            $aDate = strtotime($a[EvaluateTaConst::$DATETIME]);
                            $bDate = strtotime($b[EvaluateTaConst::$DATETIME]);
                            if($aDate == $bDate) {
                                return 0;
                            }
                            return ($bDate > $aDate) ? +1 : -1;
                        }
                        return ($bTaVersion > $aTaVersion) ? +1 : -1;
                    }
                    return ($aTaAutoid > $bTaAutoid) ? +1 : -1;
                }
                return ($aBlockWeek > $bBlockWeek) ? +1 : -1;
            }
            return ($aTaTypeId > $bTaTypeId) ? +1 : -1;
        }
        return ($aStage > $bStage) ? +1 : -1;
    }
    
    private function _throwCSV($csvRows) {
        ob_end_clean();
        $cmsCsvService = new CmsCsvService();
        $cmsCsvService->arrayToCsvDump($csvRows, 'Lecture Evaluation - '.date('Y-m-d - H_i_s ', time()));
    }
    
    /**
     * Returns the ta_id(this maps to auto_id to teachingactivity table) from the links_lo_ta table which has the status of current from $taAutoids given or false if none found. 
     * @param array $taAutoids
     * @return false|int $ta_id
     */
    private function _getCurrentTaAutoid($taAutoid) {
        try {
            $return = false;
            $queryFormat = 'SELECT ta_id FROM link_lo_ta 
                                WHERE ta_id IN 
                                    (SELECT auto_id FROM teachingactivity WHERE taid IN 
                                                (SELECT taid FROM teachingactivity WHERE auto_id = %d) ORDER BY auto_id DESC) AND status = %d ORDER BY ta_id DESC LIMIT 1';
            
            $query = sprintf($queryFormat, $taAutoid, $this->_releasedStatusId);
            $row = $this->_db->query($query)->fetch();
            if($row !== false) {
                $return = $row['ta_id'];
            }
            return $return;
        } catch (Exception $ex) {
            $error = $ex->getMessage().PHP_EOL.$ex->getTraceAsString().PHP_EOL;
            Zend_Registry::get('logger')->warn(PHP_EOL."CLASS\t: ".__CLASS__.PHP_EOL."METHOD\t: ".__METHOD__.PHP_EOL."Error\t: ".$error.PHP_EOL);
            return false;
        }
    }
    
    /**
     * Returns the auto_id from the lk_status table where the name is 'Status::$RELEASED'
     * @return int
     */
    private function _getReleasedStatusId() {
        try {
            $status = new Status();
            return $status->getIdForStatus(Status::$RELEASED);
        } catch (Exception $ex) {
            $error = $ex->getMessage().PHP_EOL.$ex->getTraceAsString().PHP_EOL;
            Zend_Registry::get('logger')->warn(PHP_EOL."CLASS\t: ".__CLASS__.PHP_EOL."METHOD\t: ".__METHOD__.PHP_EOL."Error\t: ".$error.PHP_EOL);
            return 4;
        }
    }
    
    private function _fetchAllTasInfo($taAutoIds) {
        $return = array();
        if(count($taAutoIds) > 0 && $this->_teachingActivities != null) {
            $tas = $this->_teachingActivities->fetchAll('auto_id in ('.implode(',', $taAutoIds).')', 'taid ASC');
            if($tas->count() > 0) {
                foreach($tas as $ta) {
                    $return[$ta->auto_id]['stage']                 = $ta->stage;
                    $return[$ta->auto_id]['block_week']            = $ta->block_no.'.'.$ta->block_week_zero_padded;
                    $return[$ta->auto_id]['seq_no']                = $ta->sequence_num;
                    $return[$ta->auto_id]['lecture_title']         = $ta->name;
                    $return[$ta->auto_id]['principal_teachers']    = $ta->principal_teacher_full_name;
                    $return[$ta->auto_id]['current_teachers']      = $ta->current_teacher_full_name;
                    $return[$ta->auto_id]['current_teachers']      = $ta->current_teacher_full_name;
                    
                }
            }
        }
        return $return;
    }
    
    private function _getEvaluationFor($evaluations, $question) {
        $return = array();
        foreach($evaluations as $evaluation) {
            $return[] = $evaluation[$question];
        }
        return $return;
    }

    private function _getTaVersions($taAutoId) {
        $revisions = array();
        if((int)$taAutoId > 0) {
            $row = $this->_teachingActivities->fetchRow('auto_id = '.(int)$taAutoId);
            if(!empty($row)) {
                $taTaId = $row->taid; 
                if((int)$taTaId > 0) {
                    $rows = $this ->_teachingActivities->getTaRevisions($taTaId);
                    foreach($rows as $row) {
                        $revisions[] = $row->auto_id;
                    }
                    sort($revisions, SORT_NUMERIC);
                }
            }
        }
        return $revisions;
    }
    
    public function fetchChart($params) {
        $chart = new open_flash_chart();

        //$title = new title("");
        //$title->set_style('font-weight:bold; font-size:15px;color:#0066CC;');
        //$chart->set_title($title);
        if(isset($params['barVals'])) {
            $bar = new bar_filled();
            $bar->colour('#C31812');
            //$bar->key('All', 12);
            $barVals = array();
            foreach($params['barVals'] as $barVal) {
                $barVals[] = (int)$barVal;
            }
            $bar->set_values($barVals);
            $chart->add_element($bar);
        }
        if(isset($params['xLabels']) && !empty($params['xLabels'])) {
            $x_labels = new x_axis_labels();
            $x_labels->rotate(45);
            $x_labels->set_size(14);
            //Strip slashes that are added by Zend framework so it works as expected
            $xLabels = array();
            foreach($params['xLabels'] as $xLabel) {
                $xLabels[] = stripslashes($xLabel);
            }
            $x_labels->set_labels($xLabels);
            $x = new x_axis();
            $x->set_labels($x_labels);
            $chart->set_x_axis($x);
        }
        if(isset($params['ystart']) && isset($params['ymax']) && isset($params['ystep'])) {
            $y = new y_axis();
            $y_max = 5;
            $y->set_range((int)$params['ystart'], (int)$params['ymax'], (int)$params['ystep']);
            $chart->set_y_axis($y);
            $y_legend = new y_legend();
            $y_legend->set_style('font-size: 14px; color: #000000; padding: 4px;');
            $y_legend->y_legend('No of Students');
            $chart->set_y_legend($y_legend);
        }
        // $chart->set_x_legend(' X Legend');
        return $chart;
    }
    
    private function _createUrl($taAutoIds, $column, $options) {
        $url = array(); 
        $xLabels = array();
        $barVals = array();
        $rows = $this->_evaluateTa->groupByColumn($taAutoIds, $column);
        $yMax = 0;
        foreach($options as $key => $option) {
            $barValFound = false;
            foreach($rows as $row){
                if(isset($row[$column]) && $row[$column] == $key) {
                    $barValFound = true;
                    $barVals[] = 'barVals[]='.$row['count'];
                    if($yMax < $row['count']) {
                        $yMax = $row['count'];
                    }
                }
            }
            if($barValFound == false) {
                $barVals[] = 'barVals[]=0';
            }
            $xLabels[] = 'xLabels[]='.$option;
            
        }
        if(!empty($xLabels)) {
            $url[] = implode('&', $xLabels);
        }
        if(!empty($barVals)) {
            $url[] = implode('&', $barVals);
        }
        $url[] = 'ystart=0';
        $roundedYMax = $yMax;
        $round = 1;
        for($x=$yMax; $x<100; $x++) {
            if(($x % 5) == 0) {
                $roundedYMax = $x;
                if($round == 1) {
                    $roundedYMax = ($x + 5);
                }
                break;
            }
            $round++;
        }
        $url[] = 'ymax='.$roundedYMax;
        $yStep = 1;
        for($y=1; $y<50; $y++) {
            if(ceil($roundedYMax/$y) > 10 ) {
                continue;
            } else {
                if($y <= 2) {
                    $yStep = $y;
                } else {
                    $yStep = 5;
                }
                
                break;
            }
        }
        $yStep = 5;
        $url[] = 'ystep='.$yStep;
        return implode('&',$url);
    }
}
