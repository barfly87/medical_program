<?php

class SearchResult {
    public $attributes;
    public $mepositoryID;
    public $score;

    function __construct($attributes,$mepositoryID,$score) {
        $this->attributes = $attributes;
        $this->mepositoryID = $mepositoryID;
        $this->score = $score;
    }

    public function __toString() {
        return "MepositoryID: ".$this->mepositoryID." (Score: ".$this->score.")";
    }

    public static function CreateFromSOAP($soapReturnValue) {
        // Take an instance of stdClass and pull out the repo values
        $attrs = array();
        foreach($soapReturnValue->attributes->entry as $attr) {
            $attrs[$attr->key] = $attr->value;
        }
        return new SearchResult($attrs,MepositoryID::CreateFromSoap($soapReturnValue->mepositoryID),$soapReturnValue->score);
    }

    public static function CreateArrayFromSOAP($soapReturnValue) {
        // Take an instance of stdClass and pull out the repo values
        $returnArray = array();
        if (!$soapReturnValue instanceof stdClass) {
            foreach($soapReturnValue as $result) {
                $returnArray[] = SearchResult::CreateFromSOAP($result);
            }
        } else {
            return array(SearchResult::CreateFromSOAP($soapReturnValue));
        }
        return $returnArray;
    }

}

?>
