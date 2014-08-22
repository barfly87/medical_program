<?php
class XMLService {
    
    /**
     * @param $array 
     * @return $xml_string
     * $array = array (root => array (elem=>text, elem1=>text1 ...));
     */
    public static function createXMLfromArray($array) {
       $doc = new DomDocument('1.0');
        
        foreach($array as $key=>$val) {
            $root = $doc->createElement($key);
            $root = $doc->appendChild($root);
            foreach($val as $subKey=>$subVal){
                $elem = $doc->createElement($subKey);
                $elem = $root->appendChild($elem);
                $text = $doc->createTextNode($subVal);
                $text = $elem->appendChild($text);
            }
            break;
        }
        
        return $doc->saveXML();
    }
    
    public static function createArrayFromXml($xml, $excludeAttributes = true) {
        try {
            $xmlString = '';
            if(is_string($xml)) {
                $xmlString = $xml;
            } else if($xml instanceof DOMDocument) {
                $xmlString = $xml->saveXML();
            } else {
                return array();
            }
            if(!empty($xmlString)) {
                $json = Zend_Json::fromXml($xmlString, $excludeAttributes);
                return Zend_Json::decode($json);
            }
            return array();
        } catch(Exception $ex) {
            Zend_Registry::get('logger')->warn($ex->getMessage());
            return array();
        }
    }
    
}
?>