<?php
 
class CmsMediabankCompare {
    
    public $compare = null;
    private $nameFormat = '+collectionID:cmsdocs-%s +native.title:"%s"';
    private $blockWeekDoctypeNameFormat = '+collectionID:cmsdocs-%s +native.block_sequence:%s +native.week:%s +native.doctypefile:"%s" +native.title:"%s"';
    private $blockDoctypeNameFormat = '+collectionID:cmsdocs-%s +native.block_sequence:%s +native.doctypefile:"%s" +native.title:"%s" +native.export_type:"BlockThemeSession"';
    private $sequenceFormat = '+collectionID:cmsdocs-%s +native.block_sequence:%s +native.week:%s +native.doctypefile:"%s" +native.sequence:%s';
    private $pblFormat = '+collectionID:cmsdocs-%s +native.block_sequence:%s +native.week:%s +native.phase:"problem Based Learning %s"';
    
    public function __construct($compare) {
        $this->compare = $compare;        
    }
    
    public function getCsv() {
        switch($this->compare) {
            case 'name':            return $this->getCsvForNameComparison();            break;
            case 'sequence':        return $this->getCsvForSequenceComparsion();        break;
            case 'sequence_name':   return $this->getCsvForSequenceNameComparison();    break;
            case 'name_doctype':    return $this->getCsvForNameDoctypeComparison();     break;
            default:                exit;                                               break;
        }
    }

    public function getCmsLinks() {
        $tas = $this->getTasForSeq();
        if($tas !== false) {
            $rows = $this->compareNameThenDoctypeSeq(&$tas);
            return $rows;
        }
    }
    
    public function getCmsPblDocs() {
        $tas = $this->getTasForPbl();
        if($tas !== false) {
            $rows = $this->comparePbl($tas);
            return $rows;
        }
    }
    
    public function sortPblResource () {
        
    }
    
    private function comparePbl(&$rows) {
        $cmsMediabank = new CmsMediabank();
        if(is_array($rows) && !empty($rows)) {
            foreach($rows as &$row) {
                $query = sprintf($this->pblFormat, $row['TA_cohort'], $row['TA_block_sequence'], $row['TA_pbl_sequence'], $row['TA_doctype_sequence']);
                $results = $cmsMediabank->search($query);          
                if($results !== false) {
                    $rowAppend = $this->processPblResults($results);
                    $row = $row + $rowAppend;
                } else {
                    $row = $row + $this->getEmptyMediabankCols();                
                }
            }
        }
        return $rows;
    }
    
    
    private function getCsvForNameComparison() {
        $tas = $this->getTasForName();
        if($tas !== false) {
            $rows = $this->compareName(&$tas);
            $cmsCsvService = new CmsCsvService();
            $cmsCsvService->arrayToCsvDump(&$rows,'Compass Mediabank NAME Comparison');
        }
    }
    
    private function getCsvForSequenceComparsion() {
        $tas = $this->getTasForSeq();
        if($tas !== false) {
            $rows = $this->compareSequence(&$tas);
            $cmsCsvService = new CmsCsvService();
            $cmsCsvService->arrayToCsvDump(&$rows,'Compass Mediabank SEQUENCE Comparison');
        }
    }
    
    private function getCsvForSequenceNameComparison() {
        $tas = $this->getTasForSeq();
        if($tas !== false) {
            $rows = $this->compareSequenceThenName(&$tas);
            $cmsCsvService = new CmsCsvService();
            $cmsCsvService->arrayToCsvDump(&$rows,'Compass Mediabank SEQUENCE Or NAME Comparison');
        }
    }
    
    private function getCsvForNameDoctypeComparison() {
        $tas = $this->getTasForSeq();
        if($tas !== false) {
            $rows = $this->compareNameThenDoctypeSeq(&$tas);
            $cmsCsvService = new CmsCsvService();
            $cmsCsvService->arrayToCsvDump(&$rows,'Compass Mediabank NAME Or DOCTYPE_SEQ Comparison');
        }
    }
    
    private function getTasForName() {
        $db = Zend_Registry::get("db");
        $select = $db->select()
                    ->from(array('ta' => 'teachingactivity'), array('auto_id AS TA_id','name AS TA_description'))
                    ->join(array('ch' => 'lk_cohort'), 'ta.cohort = ch.auto_id',array('ch.cohort AS TA_cohort'));
        return $db->fetchAll($select);
    }
    
    private function getTasForSeq() {
        $db = Zend_Registry::get("db");
        $select = $db->select()
                    ->distinct()
                    ->from(array('llt' => 'link_lo_ta'), array('llt.ta_id as TA_id'))
                    ->join(array('t' => 'teachingactivity'),'t.auto_id = llt.ta_id',array('t.name as TA_description'))
                    ->join(array('ch' => 'lk_cohort'), 't.cohort = ch.auto_id',array('ch.cohort AS TA_cohort'))
                    ->join(array('cbq' => 'cohort_block_seq'),'t.cohort = cbq.cohort_id and t.stage= cbq.stage_id and t.block = cbq.block_id',array('cbq.seq_no as TA_block_sequence'))
                    ->join(array('bw' => 'lk_blockweek'),'t.block_week = bw.auto_id',array('bw.weeknum as TA_pbl_sequence'))
                    ->join(array('at' => 'lk_activitytype'),'t.type=at.auto_id',array('at.name as TA_doctype'))
                    ->join(array('sn'=> 'lk_sequence_num'),'t.sequence_num = sn.auto_id',array('sn.seqnum as TA_doctype_sequence'))
                    ->where("llt.status != (select auto_id from lk_status where name ='Archived')")
                    ->order(array('TA_cohort','TA_block_sequence','TA_pbl_sequence','TA_doctype','TA_doctype_sequence'));
        return $db->fetchAll($select);
    }
    
    private function getTasForPbl() {
        $db = Zend_Registry::get("db");
        $select = $db->select()
                    ->distinct()
                    ->from(array('llt' => 'link_lo_ta'), array('llt.ta_id as TA_id'))
                    ->join(array('t' => 'teachingactivity'),'t.auto_id = llt.ta_id',array('t.name as TA_description'))
                    ->join(array('ch' => 'lk_cohort'), 't.cohort = ch.auto_id',array('ch.cohort AS TA_cohort'))
                    ->join(array('cbq' => 'cohort_block_seq'),'t.cohort = cbq.cohort_id and t.stage= cbq.stage_id and t.block = cbq.block_id',array('cbq.seq_no as TA_block_sequence'))
                    ->join(array('bw' => 'lk_blockweek'),'t.block_week = bw.auto_id',array('bw.weeknum as TA_pbl_sequence'))
                    ->join(array('at' => 'lk_activitytype'),'t.type=at.auto_id',array('at.name as TA_doctype'))
                    ->join(array('sn'=> 'lk_sequence_num'),'t.sequence_num = sn.auto_id',array('sn.seqnum as TA_doctype_sequence'))
                    ->where("llt.status != (select auto_id from lk_status where name ='Archived') AND t.type = 4")
                    ->order(array('TA_cohort','TA_block_sequence','TA_pbl_sequence','TA_doctype','TA_doctype_sequence'));
        return $db->fetchAll($select);
    }
    
    
    private function compareName(&$rows) {
        $cmsMediabank = new CmsMediabank();
        if(is_array($rows) && !empty($rows)) {
            foreach($rows as &$row) {
                $query = sprintf($this->nameFormat,$row['TA_cohort'],$row['TA_description']); 
                $results = $cmsMediabank->search($query);
                if($results !== false) {
                    $rowAppend = $this->processResults($results);
                    $row = $row + $rowAppend;
                } else {
                    $row = $row + $this->getEmptyMediabankCols();
                }
            }
            return $rows;
        }
    }
    
    private function compareSequence(&$rows) {
        $cmsMediabank = new CmsMediabank();
        if(is_array($rows) && !empty($rows)) {
            foreach($rows as &$row) {
                $query = sprintf($this->sequenceFormat,$row['TA_cohort'],$row['TA_block_sequence'],$row['TA_pbl_sequence'],$row['TA_doctype'],$row['TA_doctype_sequence']);
                $results = $cmsMediabank->search($query);          
                if($results !== false) {
                    $rowAppend = $this->processResults($results);
                    $row = $row + $rowAppend;
                } else {
                    $row = $row + $this->getEmptyMediabankCols();                }
            }
        }
        return $rows;
    }
    
    private function compareSequenceThenName(&$rows) {
        $cmsMediabank = new CmsMediabank();
        if(is_array($rows) && !empty($rows)) {
            foreach($rows as &$row) {
                $seqQuery = sprintf($this->sequenceFormat,$row['TA_cohort'],$row['TA_block_sequence'],$row['TA_pbl_sequence'],$row['TA_doctype'],$row['TA_doctype_sequence']);
                $seqResults = $cmsMediabank->search($seqQuery);
                if($seqResults !== false) {
                    $rowAppend = $this->processResults($seqResults,'Sequence');
                    $row = $row + $rowAppend;
                } else {
                    $nameQuery = sprintf($this->nameFormat,$row['TA_cohort'],$description); 
                    $nameResults = $cmsMediabank->search($nameQuery);
                    if($nameResults !== false) {
                        $rowAppend = $this->processResults($nameResults,'Name');
                        $row = $row + $rowAppend;
                    } else {
                        $row = $row + $this->getEmptyMediabankCols(true);
                    }
                }
            }
        }
        return $rows;
    }

    private function compareNameThenDoctypeSeq(&$rows) {
        $cmsMediabank = new CmsMediabank();
        if(is_array($rows) && !empty($rows)) {
            foreach($rows as &$row) {
                $description = $this->filterDescription($row['TA_description']);
                $row['TA_filtered_description'] = $description;
                $nameQuery = sprintf($this->blockWeekDoctypeNameFormat,$row['TA_cohort'],$row['TA_block_sequence'],$row['TA_pbl_sequence'],$row['TA_doctype'],$description);
                $nameResults = $cmsMediabank->search($nameQuery);
                if($nameResults !== false) {
                    $rowAppend = $this->processResults($nameResults,'Name');
                    $row = $row + $rowAppend;
                } else {
                    $blockQuery = sprintf($this->blockDoctypeNameFormat,$row['TA_cohort'],$row['TA_block_sequence'],$row['TA_doctype'],$description);
                    $blockResults = $cmsMediabank->search($blockQuery);
                    if($blockResults !== false) {
                        $rowAppend  = $this->processResults($blockResults,'Block');
                        $row = $row + $rowAppend;    
                    } else {
                        $row = $row + $this->getEmptyMediabankCols(true);
                    }
                }
            }
        }
        return $rows;
    }
    
    private function getEmptyMediabankCols($comparison = false) {
        $return = array();
        $return['MEDIABANK_document_id'] = '';
        $return['MEDIABANK_title'] = '';
        $return['MEDIABANK_score'] = '';
        $return['MEDIABANK_mid'] = '';
        if($comparison === true) {
            $return['comparison'] = '';
        }
        $return['count'] = 0;
        return $return;
    }
    
    private function processPblResults($results,$comparison = false) {
        $return = array();
        $cnt = 0;
        $mediabank = array();
        $mediabankResourceService = new MediabankResourceService();
        foreach($results as $result) {
            if(isset($result->score) && $result->score > 0.50) {
                $info = $mediabankResourceService->getMetaData($result->attributes['mid']);
                if(! isset($info['data']) ) {
                    continue;
                }
                if(isset($info['data']['doctype']) && $info['data']['doctype'] == 'oneoff' 
                && isset($info['data']['title'])   && strtolower($info['data']['title']) == 'learning objectives') {
                    continue;
                }
                if(isset($info['data']['doctype']) && $info['data']['doctype'] != 'results') {
                    $mediabank['MEDIABANK_doctype'][$cnt]       =  $info['data']['doctype'];
                    $mediabank['MEDIABANK_document_id'][$cnt]   = $result->mepositoryID->objectID;
                    $mediabank['MEDIABANK_title'][$cnt]         = $result->attributes['title'];
                    $mediabank['MEDIABANK_score'][$cnt]         = $result->score;
                    $mediabank['MEDIABANK_mid'][$cnt]           = $result->attributes['mid'];
                    $doctypefile = (isset($info['data']) && isset($info['data']['doctypefile']) && ! empty($info['data']['doctypefile'])) ? $info['data']['doctypefile'] : '';
                    $mediabank['MEDIABANK_doctypefile'][$cnt]   = $doctypefile;
                    $cnt++;
                }
            }           
        }
        if($cnt > 0) {
            array_multisort(
                        $mediabank['MEDIABANK_doctypefile'],SORT_DESC,
                        $mediabank['MEDIABANK_title'],SORT_ASC,
                        $mediabank['MEDIABANK_document_id'],SORT_ASC,
                        $mediabank['MEDIABANK_mid'],SORT_ASC,
                        $mediabank['MEDIABANK_score'],SORT_ASC,
                        $mediabank['MEDIABANK_doctype'],SORT_ASC
                        ); 
            $return['MEDIABANK_mid'] = $mediabank['MEDIABANK_mid'];
            $return['MEDIABANK_doctype'] = $mediabank['MEDIABANK_doctype'];
        }                    
        //$return['MEDIABANK'] = $mediabank;
        $return['count'] = $cnt;
        return $return;
    }
    
    private function processResults($results,$comparison = false) {
        $return = array();
        $resultsCnt = 0;
        $document_id = '';
        $title = '';
        $score = '';
        $mid = '';
        foreach($results as $result) {
            if(isset($result->score) && $result->score > 0.50) { 
                $resultsCnt++;
                if($resultsCnt > 1) {
                    $document_id .= ' ** ';
                    $title .= ' ** ';
                    $score .= ' ** ';
                    $mid .= ' ** ';
                }
                $document_id .= $result->mepositoryID->objectID;
                $title .= $result->attributes['title'];
                $score .= $result->score;
                $mid .= $result->attributes['mid'];
            }           
        }
        $return['MEDIABANK_document_id'] = $document_id;
        $return['MEDIABANK_title'] = $title;
        $return['MEDIABANK_score'] = empty($score) ? '' : $score;
        $return['MEDIABANK_mid'] = $mid;
        if($comparison !== false) {
            $return['comparison'] = (($resultsCnt) > 0) ? $comparison : '';
        }
        $return['count'] = $resultsCnt;
        return $return;
    }
    
    private function filterDescription($description) {
        $desc = trim($description);
        $valueChanged = false;
        
        if(stripos($desc,'The ') === 0) {
            $valueChanged = true;
            $desc = substr($desc,4);
        }
        if(preg_match('/^Session(.*):/i',$desc) > 0) {
            $valueChanged = true;
            $desc = preg_replace('/^Session(.*):/i','',$desc);
        } 
        if(preg_match('/\(.*/', $desc) > 0) {
            $valueChanged = true;
            $desc = preg_replace('/\(.*/','',$desc);
        }
        if($valueChanged) {
            return trim($desc);
        }
        return $description;
    }
    
}//END OF CLASS
