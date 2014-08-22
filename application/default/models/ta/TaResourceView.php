<?php
class TaResourceView extends TaResourceAbstract {
    
    public function __construct($taId) {
        $this->startProcess($taId);
    }
    
    public function getResources() {
        $return = array();
        $return['content']          = $this->_getContent();
        $return['references']       = $this->_getReferences();
        $return['lectures']         = $this->_getLectureRecordings();
        $return['prologue']         = $this->_getPrologue();
        
        //Get general resources based on who is currently logged in
        $generalResources           = $this->getGeneralResourcesDefault();
        $return['general_staff']    = $generalResources;
        $return['general_student']  = $generalResources;
        
        return $return;
    }
    
    private function _getContent() {
        return $this->getContentDefault();
    }
    
    private function _getReferences() {
        return $this->getReferencesDefault();
    }
    
    private function _getLectureRecordings() {
        return $this->getLectureRecordingsDefault();
    }
    
    private function _getPrologue() {
        return $this->getPrologueDefault();
    }
}
?>