<?php
class BlockFetch extends BlockAbstract {
    
    public function __construct() {
        parent::__construct();
    }
    
    public function getPageDetails() {
        $return  = array(
                    'tas' => $this->getTaForBlockAndType($this->requestParams['taTypeId'])
        );
        if($this->coreResults === true) {
            $return['coreResults'] = true;
        }
        return $return;
    }

}
?>
