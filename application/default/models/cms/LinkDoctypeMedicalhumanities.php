<?php

class LinkDoctypeMedicalhumanities extends LinkDoctypeAbstract {

    private $doctype = null;
    private $resourceTypeId = null;
    
    public function __construct() {
        $this->doctype = 'Medical Humanities';
        //$this->resourceTypeId = ResourceTypeConstants::$MEDICAL_HUMANITIES_ID;
        die('MEDICAL HUMANITIES ID DOES NOT EXIST IN lk_resourcetype TABLE. This needs to be fixing before you run this script');        
    }
    
    public function process() {
        return $this->processPblResourceType($this->doctype, $this->resourceTypeId);
    }
    
}
?>