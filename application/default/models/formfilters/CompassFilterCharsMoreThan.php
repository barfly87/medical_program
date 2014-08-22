<?php
class CompassFilterCharsMoreThan implements Zend_Filter_Interface {
    
    private $_length = 200;
    
    public function __construct($options = array()) {
        if(isset($options['length'])) {
            $this->_length = $options['length'];
        }
    }
    
    public function filter($value) {
        $strLength = strlen($value);
        if($strLength > $this->_length) {
            $value = substr($value, 0, $this->_length) .' ...';
        }
        return $value;
    }
    
}