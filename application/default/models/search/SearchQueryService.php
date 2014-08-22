<?php
/* This class build commonly-used serach queries */
class SearchQueryService {
    /**
     * Pass $type to look for specific teaching activity type
     * Pass level to either look for tas attached to pbl or to the whole block
     * @param $type
     * @param $level
     * @return string $queryStr;
     */
    static function getReleaseDateQuery($blockId, $blockWeek = null) {
        try {          
            $queryStr = '+doctype:Linkage';
            
            if (UserAcl::getRole() != 'student') {
                $domain = UserAcl::getDomainName();
            	$queryStr .= " +(ta_audience:{$domain} AND lo_audience:{$domain})";
            } else {
                //Add student info in the query
                $queryStr .= SearchQueryService::createQueryForStudent();
            }
            
            //Add block info in the query
            $blockFinder = new Blocks();
            $blockNames = $blockFinder->getAllNames();
            $blockName = $blockNames[$blockId];
            $queryStr .= " +(ta_block:\"{$blockName}\")";
            if($blockWeek!=null)
            	$queryStr .= " +(ta_block_week:{$blockWeek})";
            Zend_Registry::get('logger')->debug(__METHOD__ . " $queryStr");
            return $queryStr;
            
        } catch(Exception $ex) {
            Zend_Registry::get('logger')->debug("\n".$ex->getMessage()."\n".$ex->getTraceAsString()."\n\n");
            return '';            
        }
        
    }
    
    private static function createQueryForStudent() {
        $queryStr = '';
        $identity = Zend_Auth::getInstance()->getIdentity();
        if ('student' == $identity->role) {
            $domain_qstr = array();
            foreach ($identity->all_domains as $domain) {
                $domain_qstr[] = "(ta_audience:{$domain} AND lo_audience:{$domain})";
            }
            $queryStr .= " +(". join(" OR ", $domain_qstr). ")";
            
            $stage3 = (int)(date('Y')) - 2;
            //stage 1 and 2 student
            if ($identity->cohort > $stage3) {
                $queryStr .= " +(";
                //stage 2 student can always see stage 1 material
                if ($identity->cohort == $stage3 + 1) {
                    $queryStr .= 'ta_stage:"1" ';
                }
                if (isset(Zend_Registry::get('config')->event_wsdl_uri)) {
                    foreach ($identity->groups as $group) {
                        $queryStr .= "releasedate_$group:[0 TO ".strtotime('now')."] ";
                    }
                } else {
                    $stage = date('Y') - $identity->cohort + 1;
                    $queryStr .= 'ta_stage:"'.$stage.'" ';
                }
                $queryStr .= ")";
            }
        }
        Zend_Registry::get('logger')->debug(__METHOD__ . " $queryStr");
        return $queryStr;
    }

}
?>