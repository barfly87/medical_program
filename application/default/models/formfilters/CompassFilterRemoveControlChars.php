<?php
class CompassFilterRemoveControlChars implements Zend_Filter_Interface {

    public function filter($value) {
        return  preg_replace('/[\x00-\x1F\x7F]/', '', $value);
    }

}