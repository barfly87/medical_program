<?php
class StudentEvaluateService {

    public function getStudentTaEvaluation($params = array()) {
        $return = array();
        $evaluations = $this->getTypeTa($params);
        if(!empty($evaluations)) {
            $filteredEvaluations = $this->filterEvaluations($evaluations);
            $taIds = array_keys($filteredEvaluations);
            $tas = $this->getTas($taIds, $params);
            if(! empty($tas)) {
                return $this->structureTas($tas, $filteredEvaluations);
            }
        }
        return $return;
    }
    
    public function getEvaluationForTaId($taId) {
        if((int)$taId > 0) {
            $studentEvaluate = new StudentEvaluate();
            
            $result = $studentEvaluate->getEvaluationForTaId((int)$taId);
            if($result !== false) {
                return $result;
            }
        }
        return array();
    }
    
    private function getTypeTa($params) {
        $studentEvaluate = new StudentEvaluate();
        return $studentEvaluate->getTypeTa($params);
    }
    
    private function getTas($taIds, $params) {
        try {        
            if(is_array($taIds) && !empty($taIds)) {
                $db = Zend_Registry::get("db");
                $select = $db->select()
                            ->distinct()
                            ->from(array('llt' => 'link_lo_ta'), array('llt.ta_id as id'))
                            ->join(array('t' => 'teachingactivity'),'t.auto_id = llt.ta_id',array('t.name as description','t.principal_teacher'))
                            ->join(array('ld' => 'lk_domain'),'t.owner = ld.auto_id',array('ld.name as owner'))
                            ->join(array('lstg' => 'lk_stage'),'t.stage = lstg.auto_id',array('lstg.stage as stage'))
                            ->join(array('ls' => 'lk_status'),'llt.status = ls.auto_id',array('ls.name as current_status'))
                            ->join(array('sbq' => 'stage_block_seq'),'t.stage= sbq.stage_id and t.block = sbq.block_id',array('sbq.seq_no as block_sequence'))
                            ->join(array('pbl' => 'lk_pbl'), 't.pbl = pbl.auto_id',array('pbl.name AS pbl_desc'))
                            ->join(array('bw' => 'lk_blockweek'),'t.block_week = bw.auto_id',array('bw.weeknum as block_week'))
                            ->join(array('b' => 'lk_block'),'t.block = b.auto_id',array('b.name as block_name'))
                            ->join(array('at' => 'lk_activitytype'),'t.type=at.auto_id',array('at.name as doctype'))
                            ->join(array('sn'=> 'lk_sequence_num'),'t.sequence_num = sn.auto_id',array('sn.seqnum as doctype_sequence'))
                            ->where("llt.ta_id in (" . implode(',',$taIds) . ")")
                            ->order(array('block_sequence','block_week','doctype','doctype_sequence'));
                if(!empty($params)){
                    if(!empty($params['blocks'])) {
                        $select->where('t.block in ('.implode(',',$params['blocks']).')');
                    }
                    if(!empty($params['pbls'])) {
                        $select->where("pbl.name in ('".implode("','",$params['pbls'])."')");
                    }
                    if(!empty($params['types'])) {
                        $select->where('t.type in ('.implode(',',$params['types']).')');
                    }
                }        
                return $db->fetchAll($select);
            }
            return array();
        } catch(Exception $ex) {
            Zend_Registry::get('logger')->debug("\n".$ex->getMessage()."\n".$ex->getTraceAsString()."\n\n");
            return array();
        }
        
    }
    
    private function filterEvaluations($evaluations) {
        $return = array();
        if(is_array($evaluations) && !empty($evaluations)) {
            foreach($evaluations as $evaluation) {
                $return[$evaluation['type_id']][] = $evaluation;
            } 
        }
        return $return;
    }
    
    private function structureTas($tas,$evaluations) {
        $return = array();
        foreach($tas as $ta) {
            $blockSeq   = $ta['block_sequence'];
            $blockWeek  = $ta['block_week'];
            $blockName  = $ta['block_name'];
            $stage      = $ta['stage'];
            $pblDesc    = trim($ta['pbl_desc']);
            $doctype    = $ta['doctype'];
            $doctypeSeq = $ta['doctype_sequence'];
            $taDesc     = $ta['description'];
            $taId       = $ta['id'];
            $owner      = $ta['owner'];
            $status     = $ta['current_status'];
            $principalTeacherString = $ta['principal_teacher'];
            $principalTeachers = '-';
            
            if(strlen(trim($principalTeacherString)) > 0) {
                $principalTeachers = $this->getFullNames(trim($principalTeacherString));
            }
            //$pblDesc    = (empty($pblDesc)) ? 'Error ! Pbl desc not found': $pblDesc;
            
            if( !isset($return[$blockSeq]) ) {
                $return[$blockSeq] = array();
            }
            if( !isset($return[$blockSeq][$pblDesc])) {
                $return[$blockSeq][$pblDesc] = array();
            }
            if( !isset($return[$blockSeq][$pblDesc][$doctype])) {
                $return[$blockSeq][$pblDesc][$doctype] = array();
            }
            if( !isset($return[$blockSeq][$pblDesc]['pblId'])) {
                $blockWeekPadded = ($blockWeek < 10) ? '0'.$blockWeek : $blockWeek; 
                $return[$blockSeq][$pblDesc]['pblId'] = trim($blockSeq.'.'.$blockWeekPadded);
            }
            if(! isset($return[$blockSeq][$pblDesc][$doctype][$taId])) {
                $row = array();
                $row['taDesc'] = $taDesc;
                $row['owner'] = $owner;
                $row['principalTeacher'] = $principalTeachers;
                $row['blockName'] = $blockName;
                $row['stage'] = $stage;
                $row['blockWeek'] = $blockWeek;
                $row['doctypeSeq'] = $doctypeSeq;
                $row['status'] = $status;
                $row['evaluation'] = $evaluations[$taId];
                
                $return[$blockSeq][$pblDesc][$doctype][$taId] = $row;
            }
            
        }
        return $return;
        
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
    
    
    public function getFormVariables(){
        return array(
            'years'             => $this->getYears(),
            'blocks'            => $this->getBlocks(),
            'pbls'              => $this->getPbls(),
            'types'             => $this->getActivityType()
        );
        
    }
    
    public function getYears() {
        $return = array();
        $start = 2010;
        $end = date('Y', time());
        for($x=$start; $x<=$end; $x++) {
            $return[] = $x;
        }
        return $this->createSelectOptions(array_reverse($return, true));
    }

    public function getBlocks(){
        $blockFinder = new Blocks();
        return array ('' => 'Any') + $this->removeEmptyValues($blockFinder->getAllNames('auto_id ASC'));
    }
    
    public function getPbls(){
        $pblFinder = new Pbls();
        $pbls = $this->removeEmptyValues($pblFinder->getAllNames());
        return $this->createSelectOptions($pbls);
    }
    
    public function getActivityType(){
        $typeFinder = new ActivityTypes();
        return array ('' => 'Any') + $this->removeEmptyValues($typeFinder->getAllNames());  
    }
    
    private function removeEmptyValues($array){
        foreach($array as $key => $value ){
            if(trim($value) == '') {
                unset($array[$key]);
            }
        }
        return $array;
    }

    private function createSelectOptions($arr){
        $result = array ('' => 'Any');
        //Change key=>value to value=>value for select options
        foreach($arr as $val) {
            $result[$val] = $val;
        }
        return $result;
    }
    
    public function processEvaluation($type,$type_id) {
        $return = array();
        if($type == 'ta' && (int)$type_id > 0 ) {
            $ta = new TeachingActivities();
            $row = $ta->fetchRow('auto_id = '.(int)$type_id);
            if($row != null) {
                switch($row->typeID) {    
                    case 25 : #activity_type id 25 is 'clinical day' 
                    $return = StudentEvaluateConst::clinicalDay();
                    break;
                    case 4:#activity_type id 4 is 'pbl session'
                    $return = StudentEvaluateConst::pblSession();
                    break;
                    default:
                    $return = StudentEvaluateConst::taDefaults();
                    break;
                }                    
            }
        }
        return $return;
    }

    public function processFormat($data, $format, $fp) {
        $filename = $this->createFileName($fp);
        $filename = preg_replace('/[^A-Za-z_ -]/','',$filename);
        
        switch ($format) {
        	case 'csv':
        	   $this->processCsv($data, $filename);
        	break;
        	default:
        	break;
        }
    }
    
    public function createFileName($fp) {
        $filename = 'Student Evaluation-Teaching Activities';
        $input = $fp->input;
        
        //YEARs
        $filename .= (isset($input['years']))  ? '-Years-'.$this->_createFileNameComponents($input['years']) : '';
        //BLOCKs
        if(isset($input['blocks'])) {
            $blocks = array();
            $blocksFinder = new Blocks();
            $allBlocks = $blocksFinder->getAllNames('auto_id ASC');
            foreach($input['blocks'] as $block) {
                $block = (int)$block;
                if($block > 0 && isset($allBlocks[$block])) {
                   $blocks[] = $allBlocks[$block];
                }                
            }
            $filename .= '-Blocks-'.$this->_createFileNameComponents($blocks); 
        }
        //PBLs
        $filename .= (isset($input['pbls'])) ? '-Pbls-'.$this->_createFileNameComponents($input['pbls']) : '';
        //TYPEs
        if(isset($input['types'])) {
            $types = array();
            $typesFinder = new ActivityTypes();
            $allTypes = $typesFinder->getAllNames('auto_id ASC');
            foreach($input['types'] as $type) {
                $type = (int)$type;
                if($type > 0 && isset($allTypes[$type])) {
                   $types[] = $allTypes[$type];
                }                
            }
            $filename .= '-Types-'.$this->_createFileNameComponents($types);
        }
        return $filename;
    }
    
    private function _createFileNameComponents($data) {
        $return = 'All';
        if(!empty($data) && is_array($data)) {
            if(count($data) == 1) {
                return (strlen(trim($data[0])) > 0) ? trim($data[0]) : $return;
            } else {
                return implode('-', $data);
            }
        }
        return $return;
    }
    
    public function processCsv($data, $filename) {
        $csvData = $this->convertDataIntoTabularData($data);
        $cmsCsvService = new CmsCsvService();
        $cmsCsvService->arrayToCsvDump($csvData, $filename);
        exit;
    }

    public function convertDataIntoTabularData($data) {
        $return = array();
        $studentEvaluateData = new StudentEvaluateData();
        $keys = $studentEvaluateData->getUniqueKeys();
        
        if(!empty($data)) {
            foreach($data as $block=>$pbls) {
                foreach($pbls as $pbl=>$doctypes) {
                    foreach($doctypes as $doctype=>$tas) {
                        if(in_array($doctype, array('pblId'))) {
                            continue;
                        }
                        foreach($tas as $taId=>$taData) {
                            foreach($taData['evaluation'] as $evaluation) {
                                $row = array();
                                $row['DATE/TIME']               = date('Y-m-d, h:ia',$evaluation['epoch']);
                                $row['STAGE']                   = $taData['stage'];
                                $row['BLOCK_ID']                = $block;
                                $row['BLOCK_NAME']              = $taData['blockName'];
                                $row['BLOCK_WEEK']              = $taData['blockWeek'];
                                $row['PBL']                     = $pbl;
                                $row['TA TYPE']                 = $doctype;
                                $row['SEQUENCE']                = $taData['doctypeSeq'];
                                $row['TA ID']                   = $taId;
                                $row['TA STATUS']               = $taData['status'];
                                $row['TA DESCRIPTION']          = $taData['taDesc'];
                                $row['OWNER']                   = $taData['owner'];
                                $row['PRINCIPAL TEACHER(S)']    = trim($taData['principalTeacher']);
                                $row['COMMENT']                 = trim($evaluation['comment']);
                                foreach($keys as $keyUpperCased => $key) {
                                    if($evaluation['data_key'] == $key) {
                                        $row[$keyUpperCased] = $evaluation['data_val'];
                                    } else {
                                        $row[$keyUpperCased] = '';
                                    }
                                }
                                $return[] = $row;
                            }//END OF EVALUATIONS - $taData['evaluation']
                        }//END OF TAS
                    }//END OF DOCTYPES
                }//END OF PBLS
            }//END OF BLOCK
        }        
        return $return;    
    }

}