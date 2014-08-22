<?php
class ResourceCopy {
    private $_fromType   = null;
    private $_fromTypeId = null;
    private $_toType     = null;
    private $_toTypeId   = null;
    private $_error      = false;
    private $_errorMsgs  = array();
    private $_msgs       = array();
    
    private $mediabankResource = null;
    private $mediabankResourceService = null;
    
    public function __construct() {
        $this->mediabankResource = new MediabankResource();
        $this->mediabankResourceService = new MediabankResourceService();
    }
    
    public function process($fromType, $fromTypeId, $toType,$toTypeId) {
        if(!empty($fromType) && $fromTypeId > 0 && !empty($toType) && $toTypeId > 0) {
            $this->_fromType     = $fromType;
            $this->_fromTypeId   = $fromTypeId;
            $this->_toType       = $toType;
            $this->_toTypeId     = $toTypeId;
            $this->_isDataCorrect();
            $this->_copyresources();
        } else {
            $this->_error = true;            
            $error = sprintf('Invalid details : From Type - %s : From Type ID - %s : To Type - %s : To Type ID - %s',
                            $fromType, $fromTypeId, $toType, $toTypeId);
            $this->_errorMsgs[] = $error;
            Zend_Registry::get('logger')->warn(PHP_EOL."CLASS\t: ".__CLASS__.PHP_EOL."METHOD\t: ".__METHOD__.PHP_EOL."Error\t: ".$error.PHP_EOL);
        }
        $errorText = ($this->_error === true) ? 'yes' : 'no'; 
        return array(
            'error'     => $errorText,
            'errorMsgs' => $this->_errorMsgs,
            'msgs'      => $this->_msgs
        );
    }
    
    private function _copyresources() {
        if($this->_error === false) {
            $this->mediabankResource = new MediabankResource();
            $this->mediabankResourceService = new MediabankResourceService();
            $fromRows = $this->mediabankResource->getResources($this->_fromTypeId, $this->_fromType);
            
            if($fromRows !== false) {                
                $toRows = $this->mediabankResource->getResources($this->_toTypeId, $this->_toType);
                $toMids = array();
                foreach($toRows as $toRow) {
                    $toMids[] = trim($toRow['resource_id']);
                }
                foreach($fromRows as $fromRow) {
                    if(!in_array($fromRow['resource_id'], $toMids)) {
                        $insert = $this->mediabankResource->addResource($this->_toType, $this->_toTypeId, $fromRow['resource_type_id'], $fromRow['resource_id']);
                        if($insert === false) {
                            $this->_msgs[] = 'Database error. Could not copy resource ('. $this->mediabankResourceService->getTitleForMid($fromRow['resource_id']).')';
                        } else {
                            $this->_msgs[] = 'Successfully copied resource ('. $this->mediabankResourceService->getTitleForMid($fromRow['resource_id']).')';
                        }
                    } else {
                        $this->_msgs[] = 'Could not copy. Resource already exist ('. $this->mediabankResourceService->getTitleForMid($fromRow['resource_id']).')';
                    }                            
                }
            } else {
                $this->_error = true;
                if($this->_fromType == ResourceConstants::$TYPE_ta) {
                    $this->_errorMsgs[] = 'Resources does not exist for Teaching Activity Id '. $this->_fromTypeId;
                    $this->_errorMsgs[] = 'Please make sure you have got the correct Teaching Activity ID and try again';
                }
            }
        }
    }
    
    
    
    private function _isDataCorrect() {
        if(!is_null($this->_fromType) && !is_null($this->_fromTypeId)) {
            $fromTypeTypeIdExist = $this->mediabankResource->idExist($this->_fromTypeId, $this->_fromType);
            if($fromTypeTypeIdExist === false) {
                $this->_error = true;
                $error = sprintf('From Type : %s, From Type Id : %s does not exist', strtoupper($this->_fromType), $this->_fromTypeId);
                $this->_errorMsgs[] = $error;
                Zend_Registry::get('logger')->warn(PHP_EOL."CLASS\t: ".__CLASS__.PHP_EOL."METHOD\t: ".__METHOD__.PHP_EOL."Error\t: ".$error.PHP_EOL);
            }
        }
        if(!is_null($this->_toType) && !is_null($this->_toTypeId)) {
            $toTypeTypeIdExist = $this->mediabankResource->idExist($this->_toTypeId, $this->_toType);
            if($toTypeTypeIdExist === false) {
                $this->_error = true;                
                $error = sprintf('To Type : %s, To Type Id : %s does not exist', strtoupper($this->_toType), $this->_toTypeId);
                $this->_errorMsgs[] = $error;
                Zend_Registry::get('logger')->warn(PHP_EOL."CLASS\t: ".__CLASS__.PHP_EOL."METHOD\t: ".__METHOD__.PHP_EOL."Error\t: ".$error.PHP_EOL);
            }
        }
    }
      
    
}
?>