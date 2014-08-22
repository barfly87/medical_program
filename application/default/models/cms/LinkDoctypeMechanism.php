<?php
class LinkDoctypeMechanism extends LinkDoctypeAbstract {
    
    private $doctype = null;
    private $resourceTypeId = null;
    
    public function __construct() {
        $this->doctype = 'Mechanism';
        //$this->resourceTypeId = ResourceTypeConstants::$MECHANISMS_ID;
        die('MECHANISMS_ID ID DOES NOT EXIST IN lk_resourcetype TABLE. This needs to be fixing before you run this script');        
    }
    
    public function process() {
        return $this->processPblResourceType($this->doctype, $this->resourceTypeId);
    }

}
?>