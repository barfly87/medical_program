<?php
class ProceduralSkillsResource extends TaResourceAbstract {
    
    public function __construct($taId) {
        $this->startProcess($taId);
    }
    
    public function getResources() {
    	if (UserAcl::isStaffOrAbove()) {
	        return $this->_getGeneralResourcesForStaff();
    	} else {
    		return $this->_getGeneralResourcesForStudents();
    	}
    }
    
    private function _getGeneralResourcesForStaff() {
        return $this->getGeneralResourcesDefault();
    }
    
    private function _getGeneralResourcesForStudents() {
        return $this->getGeneralResourcesDefault();
    }
}
?>