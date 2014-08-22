<?php
class BlockGet extends BlockAbstract {

    private $resourceType = '';
    private $resourceTypeId = 0;
    private $resourceUrlName = null;
    private $resourceId = 0;
    private $resourceTitle = '';
    
    public function __construct() {
        parent::__construct();
    }
    
    public function getPageDetails() {
        $this->resourceType = $this->requestParams['resourceType'];
        $this->resourceTypeId = $this->requestParams['resourceTypeId'];
        $this->resourceUrlName = $this->requestParams['resourceUrlName']; 
        
        $pblBlockResource = new PblBlockResource();
        $where = "url_name = '{$this->resourceUrlName}'";
        $resourceData = $pblBlockResource->getAllResources(ResourceConstants::$TYPE_block, $this->blockDetails['blockId'], $where);
        
        return array(
                    'resourceTypeId'    => $this->resourceTypeId,
                    'resourceUrlName'   => $this->resourceUrlName,
                    'resourceData'      => $resourceData,
                    'resourceTitle'     => $this->resourceTitle
        );
    }
}

?>