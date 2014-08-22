<?php
class LinkDoctypeCasesummary extends LinkDoctypeAbstract {

    private $doctype = null;
    private $resourceTypeId = null;
    
    public function __construct() {
        $this->doctype = 'Summary';
        $this->resourceTypeId = ResourceTypeConstants::$CASE_SUMMARY_ID;
    }
    
    public function process() {
        return $this->processPblResourceType($this->doctype, $this->resourceTypeId);
    }
    
}
?>