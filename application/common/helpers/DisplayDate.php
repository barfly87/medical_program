<?php

class Zend_View_Helper_DisplayDate {
    function displayDate($timestamp, $format = Zend_Date::DATE_LONG){
        if (!isset($timestamp))
            return 'N/A';
        $date = new Zend_Date(strtotime($timestamp));
        //return $date->get($format);
        return $date->get(Zend_Date::YEAR).'-'.$date->get(Zend_Date::MONTH).'-'.$date->get(Zend_Date::DAY);
    }
}