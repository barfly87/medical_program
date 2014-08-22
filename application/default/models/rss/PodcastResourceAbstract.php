<?php
abstract class PodcastResourceAbstract {

    protected $_mediabankResourceService = null;
    protected $_ta = null;
    private $_taAuthor = null;
    private $_taUrl = null;
    private $_pubDate = null;
    private $_defaultTaTitle = null;
    
    abstract public function process();
    
    protected function __construct() {
        $this->_mediabankResourceService    = new MediabankResourceService();
    } 

    protected function _setTa($ta) {
        $this->_ta = $ta;
    }
    
    protected function _getSmpMetadata($mid) {
        return $this->_mediabankResourceService->getMediabankMetaData($mid, MediabankResourceConstants::$SCHEMA_smp);
    }
    
    protected function _getMetadata($mid) {
        return $this->_mediabankResourceService->getMetaData($mid);
    }
    
    protected function _getFileInfo($mid) {
        return $this->_mediabankResourceService->getFileInfo($mid);
    }
    
    protected function _getNativeMetadataXmlObj($mid) {
        $xmlStr = $this->_mediabankResourceService->getMetadataXmlObjForMid($mid);
        if(!empty($xmlStr)) {
            return $this->_getXmlObj($xmlStr);
        }
        return null;
    }
    
    protected function _getTaUrl() {
        if($this->_taUrl == null) {
            $this->_taUrl = sprintf('http://%s%s/teachingactivity/view/id/%d',$_SERVER['HTTP_HOST'], Compass::baseUrl(), $this->_getTaAutoId());
        }
        return $this->_taUrl;
    }
    
    protected function _getTaName() {
        return $this->_ta->name;
    }
    
    protected function _getPubDate() {
        if($this->_pubDate == null) {
            $this->_pubDate = date('r',strtotime($this->_ta->date_created_org));
        }
        return $this->_pubDate;
    }

    protected function _getTaAuthor() {
        if($this->_taAuthor == null) {
            $principalTeachers = $this->_ta->principal_teacher_uid_arr;
            $author = '';
            if(! empty($principalTeachers)) {
                $authors = array();
                foreach ($principalTeachers as $principalTeacher) {
                    $fullName = UserService::getUidFullName($principalTeacher);
                    if(strlen(trim($fullName)) > 0) {
                        $authors[] = $fullName;
                    }
                }
                if(!empty($authors)) {
                    $author = implode(', ', $authors);
                }
            }
            $this->_taAuthor = $author;
        }
        return $this->_taAuthor;
    }
    
    protected function _getTaAutoId() {
        return $this->_ta->auto_id;
    }
    
    protected function _getYearFromMid($mid) {
        $smp = $this->_getSmpMetadata($mid);
        $year = (isset($smp['calendar_year'])) ? $smp['calendar_year'] : '';
        return $year;
    }
    
    protected function _getDefaultTaTitle() {
        if($this->_defaultTaTitle == null) {
            if(in_array($this->_ta->stage, array('1','2'))) {
                $this->_defaultTaTitle = $this->_getTaTitleForStage1And2Students();
            } else {
                $this->_defaultTaTitle = $this->_getTaTitleForOtherStudents();
            }
        }
        return implode(' - ',$this->_defaultTaTitle);
    }
    
    protected function _getTaTitleForStage1And2Students() {
        return array (
                'Problem '. $this->_ta->block_no.'.'.$this->_ta->block_week_zero_padded,
                $this->_ta->type.' '.$this->_ta->sequence_num,
                $this->_ta->name
            );
    }

    protected function _getTaTitleForOtherStudents() {
        return array (
                $this->_ta->block,
                $this->_ta->type.' '.$this->_ta->sequence_num,
                $this->_ta->name
            );
    }
    
    protected function _getXmlObj($xmlStr) {
        $xmlObject      = null;
        try{
            $xmlObject = new SimpleXMLElement($xmlStr);
        } catch (Exception $ex){
            $error = $ex->getMessage();
            Zend_Registry::get('logger')->warn(PHP_EOL."CLASS\t: ".__CLASS__.PHP_EOL."METHOD\t: ".__METHOD__.PHP_EOL."Error\t: ".$error.PHP_EOL);
        }
        return $xmlObject;
    }
    

    
}