<?php
class StudentEvaluateConst {
    
    public static $KEY_rating = 'rating';
    
    public static function taDefaults() {
        $return = array();
        $return['rating']['title']      = 'Please rate this teaching activity.';
        $return['rating']['mandatory']  = true;
        $return['comment']['mandatory'] = false;
        $return['comment']['style']     = 'long';
        $return['comment']['text']      = 'Please explain your rating';
        
        return $return;
    }
    
    public static function clinicalDay () {
        $return = array();
        $return['feedback']['title']            = 'Please give us any feedback on this teaching day.';
        $return['comment']['mandatory']         = true;
        $return['comment']['style']             = 'short';
        $return['comment']['text']              = 'Comment';
        $return['clinicalSchool']['list']       = self::getClinicalSchools();
        $return['clinicalSchool']['mandatory']  = true;
        return $return;
    }
    
    public static function pblSession() {
        $return = array();
        $return['feedback']['title']    = 'Please give us any feedback on this teaching activity.';
        $return['comment']['mandatory'] = true;
        $return['comment']['style']     = 'short';
        $return['comment']['text']      = 'Comment';
        $return['pblgroup']['list']     = self::getPblGroups();
        $return['pblgroup']['mandatory']= true;      
        return $return;
    }
    
    public static function getPblGroups(){
        $return = range(0,32);
        unset($return[0]);
        return $return;
    }
    
    public static function getClinicalSchools() {
        $clinicalSchool = new ClinicalSchool();
        return $clinicalSchool->fetchPairs();
    }
    
    public static function getDataFromRequestParams($params) {
        $data = array();
        $filterStripTags = new Zend_Filter_StripTags();
        if(count($params) > 0) {
            foreach($params as $key => $val) {
                if(preg_match('/^DATA_/',$key)) {
                    $key = str_replace('DATA_','',$key);
                    $val = $filterStripTags->filter(trim($val));
                    $data[$key] = $val;
                }
            }
        }
        return $data;
    }
    
    
    
}
?>