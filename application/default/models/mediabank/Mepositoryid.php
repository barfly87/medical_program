<?php

class MepositoryID {
    public $collectionID;
    public $repositoryID;
    public $objectID;

    function __construct($repositoryID,$collectionID="___ZZZongroacks",$objectID="___ZZZongroacks") {
        $this->collectionID = $collectionID;
        $this->repositoryID = $repositoryID;
        $this->objectID = $objectID;
    	if($collectionID=="___ZZZongroacks" && $objectID=="___ZZZongroacks") {
    		$parts = explode("|", $repositoryID);
    		$this->repositoryID = $parts[0];
            if(isset($parts[1]) && !empty($parts[1])) {
                $this->collectionID = $parts[1];
    		} else {
                $this->collectionID = null;
    		}
    		if(isset($parts[2]) && !empty($parts[2])) {
        	   $this->objectID = $parts[2];
    		} else {
               $this->objectID = null;
    		}
    	}
    }

    public function __toString() {
        return $this->repositoryID."|".$this->collectionID."|".$this->objectID;
    }

    public static function CreateFromSOAP($soapReturnValue) {
        // Take an instance of stdClass and pull out the repo values
        return new MepositoryID($soapReturnValue->repositoryID,$soapReturnValue->collectionID,$soapReturnValue->objectID);
    }

    public static function CreateArrayFromSOAP($soapReturnValue) {
        // Take an instance of stdClass and pull out the repo values
        $returnArray = array();
        foreach($soapReturnValue->return as $mepoID) {
            array_push($returnArray, MepositoryID::CreateFromSOAP($mepoID));
        }
        return $returnArray;
    }

}

?>
