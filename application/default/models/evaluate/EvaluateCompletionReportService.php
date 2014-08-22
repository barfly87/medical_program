<?php

class EvaluateCompletionReportService {
    private $_startDate                 = null;
    private $_endDate                   = null;
    private $_uids                      = null;
    private $_stage                     = null;
    private $_CSV                       = null;
    
    private $_taTypeIds                 = null;
    private $_teachingActivities        = null;
    private $_releasedStatus            = null;
    private $_linkageLoTas              = null;
    
    public function __construct($formValues) {
        if(isset($formValues[EvaluateCompletionReportConst::START_DATE])){
            $this->_startDate   = date('d-m-Y H:i:s', strtotime($formValues[EvaluateCompletionReportConst::START_DATE].' 00:00:01'));
        }
        if(isset($formValues[EvaluateCompletionReportConst::END_DATE])) {
            $this->_endDate     = date('d-m-Y H:i:s', strtotime($formValues[EvaluateCompletionReportConst::END_DATE].' 23:59:59'));
        }
        if(isset($formValues[EvaluateCompletionReportConst::UIDS])) {
            $uids               = trim($formValues[EvaluateCompletionReportConst::UIDS]);
            if(strlen($uids) > 0) {
                $this->_uids    = preg_split('|[\s]+|', $uids);
            }
        }
        if(isset($formValues[EvaluateCompletionReportConst::STAGES]) && (int)$formValues[EvaluateCompletionReportConst::STAGES] > 0) {
            $stage              = (int)$formValues[EvaluateCompletionReportConst::STAGES];
            $stageObj           = new Stages();
            $this->_stage       = $stageObj->getStageId((string)$stage);
        }
        if(isset($formValues[EvaluateCompletionReportConst::CSV]) && $formValues[EvaluateCompletionReportConst::CSV] != 0) {
            $this->_CSV         = $formValues[EvaluateCompletionReportConst::CSV];
        }
        if(isset($formValues[EvaluateCompletionReportConst::TA_TYPE_ID]) && !empty($formValues[EvaluateCompletionReportConst::TA_TYPE_ID])) {
            $this->_taTypeIds   = $formValues[EvaluateCompletionReportConst::TA_TYPE_ID];
        }
        $this->_linkageLoTas    = new LinkageLoTas();
        $this->_releasedStatus  = $this->_getReleasedStatusId();
        $this->_teachingActivities = new TeachingActivities();
        $this->_evaluateEventsService = new EvaluateEventsService();
    }
    
    
    public function getCompletionReport() {
        $eventsTaAutoids = $this->_evaluateEventsService->getTaAutoIdsBetweenStartAndEndDate($this->_taTypeIds, $this->_startDate, $this->_endDate);

        //Nothing returned from Events for the date search
        if(empty($eventsTaAutoids)) {
            return array();
        }
        
        $return = $this->_processEventTaAutoids($eventsTaAutoids);
        
        if($this->_CSV == 1) {
            $this->_createCSV($return);
        }
        return $return;
    }
    
    /**
     * Process eventsTaAutoids so to be displayed in a set format
     * @param $eventsTaAutoids
     * @return array
     */
    private function _processEventTaAutoids($eventsTaAutoids) {
        $return         = array();
        try {
            $select         = $this->_teachingActivities
                                ->select()
                                ->where('auto_id in (?)', $eventsTaAutoids)
                                ->order(array('type ASC','block ASC', 'block_week ASC', 'sequence_num ASC'));
            if(!is_null($this->_stage)) {
                $select->where('stage = ?', $this->_stage);
            }
            $taRows         = $this->_teachingActivities->fetchAll($select);
            $totalRows      = $taRows->count();
        } catch (Exception $ex) {
        	$error = $ex->getMessage().PHP_EOL.$ex->getTraceAsString().PHP_EOL;
        	Zend_Registry::get('logger')->warn(PHP_EOL."CLASS\t: ".__CLASS__.PHP_EOL."METHOD\t: ".__METHOD__.PHP_EOL."Error\t: ".$error.PHP_EOL);
            return array();
        }
        
        $titles         = array(EvaluateCompletionReportConst::HEADING_UID, EvaluateCompletionReportConst::HEADING_FINISHED);
        //It starts with 2 since 0 and 1 are taken by existing $titles value's as above
        $titleCount     = 2;
        //If a POST request contains UIDs param within which to search $uidsExist would be TRUE
        $uidsExist      = (!empty($this->_uids) && is_array($this->_uids));
        $evaluations    = array();
        $excludeTaNames = Compass::getConfig('evaluation.exclude.ta.names');
        
        $excludeTaNamesLowerCase = array();
        if(!empty($excludeTaNames)) {
            foreach($excludeTaNames as $excludeTaName) {
                $excludeTaNamesLowerCase[] = trim(strtolower($excludeTaName));
            }
        }
        
        foreach($taRows as $taRow) {
            if(!empty($excludeTaNamesLowerCase) && in_array(strtolower(trim($taRow->name)), $excludeTaNamesLowerCase)) {
                --$totalRows;
                continue;
            }
            $taVersions                 = $this->_getDifferentTaVersionsForTaid($taRow->taid);
            $taVersionsIds              = array_keys($taVersions);
            $currentTaAutoid            = $this->_getCurrentTaAutoid($taVersionsIds);
            $currentTa                  = $this->_teachingActivities->fetchRow('auto_id = '.$currentTaAutoid);
                
            $taId                       = ($currentTaAutoid === false) ? $taRow->auto_id : $currentTaAutoid;
            $notReleased                = ($currentTaAutoid === false) ? PHP_EOL.'(Not Released)' : '';
            $pbl                        = $taVersions[$taId]->block_no.'.'.$taVersions[$taId]->block_week_zero_padded;
            $sequence_num               = $taVersions[$taId]->sequence_num;
            $titles[$titleCount]        = sprintf('PBL %s'.PHP_EOL.'%s %d'.PHP_EOL.'%d%s', $pbl, $currentTa->type, $sequence_num, $taId, $notReleased);
            $evaluationRows             = $this->_getEvaluationsForTaids($taVersionsIds);
            foreach($evaluationRows as $evaluationRow) {
                if($uidsExist) {
                    foreach($this->_uids as $uid) {
                        if($uid == $evaluationRow['student_id'])  {
                            $evaluations[$evaluationRow['student_id']][$titleCount] = 'Done';
                        }
                    }
                } else {
                    $evaluations[$evaluationRow['student_id']][$titleCount] = 'Done';
                }
            }
            $titleCount++;
        }
        
        $return['titles']               = $titles;
        foreach($evaluations as $uid => &$evaluation) {
            $evaluationCount    = count($evaluation);
            $evaluation[1]      = sprintf('%1.2f',($evaluationCount * 100) / $totalRows).' %';
        }
        $return['evaluations']          = $evaluations;
        return $return;
    }

    /**
     * Creates and dumps CSV data using CmsCsvService() in the web browser
     * @param $data
     * @return void
     */
    private function _createCSV($data) {
        $csvData = array();
        if(!empty($data)) {
            if(isset($data['titles']) && !empty($data['titles']) 
                && isset($data['evaluations']) && !empty($data['evaluations'])) {
                foreach($data['evaluations'] as $uid => $evaluation) {
                    $csvRowData = array();
                    foreach($data['titles'] as $titleKey => $titleVal) {
                        if(isset($evaluation[$titleKey])) {
                            $csvRowData[$titleVal] = $evaluation[$titleKey] ;
                        } else {
                            $csvRowData[$titleVal] = '';
                        }                                              
                    }
                    $csvRowData[EvaluateCompletionReportConst::HEADING_UID] = $uid;
                    $csvData[] = $csvRowData;
                }                    
            }
        }
        $cmsCsvService = new CmsCsvService();
        $fileName = 'lecture_evaluation_'.date('Y_m_d_h_i', time());
        $cmsCsvService->arrayToCsvDump($csvData, $fileName);
    }
    
    /**
     * Returns different versions of a teaching activities from teachingactivity table where taid = $taTaid.
     * Each time a new version is created for a teaching activity only the auto_id gets changed but the taid remains the same.
     * @param int $taTaid
     * @return array $return
     */
    private function _getDifferentTaVersionsForTaid($taTaid) {
        try {
            $select = $this->_teachingActivities
                            ->select()
                            ->from($this->_teachingActivities, array('auto_id','taid','block','block_week','sequence_num'))
                            ->where('taid = ?', $taTaid);
            $rows = $this->_teachingActivities->fetchAll($select);
            $return =  array();
            foreach($rows as $row) {
                $return[$row->auto_id] = $row;
            }
            return $return;
        } catch (Exception $ex) {
            $error = $ex->getMessage().PHP_EOL.$ex->getTraceAsString().PHP_EOL;
            Zend_Registry::get('logger')->warn(PHP_EOL."CLASS\t: ".__CLASS__.PHP_EOL."METHOD\t: ".__METHOD__.PHP_EOL."Error\t: ".$error.PHP_EOL);
            return array();
        }
    }

    /**
     * Returns the ta_id(this maps to auto_id to teachingactivity table) from the links_lo_ta table which has the status of current from $taAutoids given or false if none found. 
     * @param array $taAutoids
     * @return false|int $ta_id
     */
    private function _getCurrentTaAutoid($taAutoids) {
        try {
            if(! is_null($this->_linkageLoTas)) {
                $select = $this->_linkageLoTas
                                ->select()
                                ->from($this->_linkageLoTas, 'ta_id')
                                ->where('ta_id IN (?)', $taAutoids)
                                ->where('status = ?', $this->_releasedStatus)
                                ->limit(1);
                $row = $this->_linkageLoTas->fetchRow($select);
                if(count($row) <= 0) {
                    return false;
                } else {
                    return $row['ta_id'];    
                }
            }
            return false;
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

    /**
     * Return Evaluations for given $taAutoids
     * @param $taAutoids
     * @return array
     */
    private function _getEvaluationsForTaids($taAutoids) {
        $evaluateTa = new EvaluateTa();
        return $evaluateTa->getEvaluationForTaIds($taAutoids);
    }

}
