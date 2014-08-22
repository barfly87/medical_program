<?php

class Zend_View_Helper_DisplayUser {
    function displayUser($uid){
    	if (!isset($uid))
    		return "N/A";
        $result = "<span class=\"userid\">$uid</span><div class=\"userdetail\">";
        $ds = Zend_Registry::get('ds');
        $detail = $ds->getUser($uid, array('chsedupersonsalutation', 'cn', 'mail', 'telephonenumber'));
        if (isset($detail['chsedupersonsalutation'][0]))
        	$result .= "{$detail['chsedupersonsalutation'][0]} ";
        $result .= "{$detail['cn'][0]}<br/>";
        if (isset($detail['mail'][0]))
        	$result .= "E: {$detail['mail'][0]}<br/>";
        if (isset($detail['telephonenumber'][0]))
        	$result .= "T: {$detail['telephonenumber'][0]}</div>";
        return $result;
    }
}