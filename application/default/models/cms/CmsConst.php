<?php

class CmsConst {
    
    public static $cmsResrcImage = '/img/CMS_thumb.png';
    public static $cmsUrlFormat = '/apps/cds/x/%s.html';
    
    public static function isCmsResource($mid) {
        if(is_string($mid)) {
            if(preg_match("/^http:\/\/(.+)\/\|cmsdocs-smp\|(.+)/",$mid) > 0) {
                return true;
            } else if(preg_match("/^http:\/\/(.+)\/\|cmsdocs-smp\|(.+)/",MediabankResourceConstants::sanitizeMid($mid)) > 0) {
                return true;
            }
        }   
        return false;         
    }
    
    public static function getCmsLink($mid) {
        if(!is_string($mid)) {
            return '';
        }
        $cmsMid = false;
        if(preg_match("/^http:\/\/(.+)\/\|cmsdocs-smp\|(.+)/",$mid, $matches) > 0) {
            $cmsMid = true;
        } else if (preg_match("/^http:\/\/(.+)\/\|cmsdocs-smp\|(.+)/",MediabankResourceConstants::sanitizeMid($mid), $matches) > 0) {
            $mid = MediabankResourceConstants::encode($mid);
            $cmsMid = true;
        }
        if($cmsMid) {
            $identity = Zend_Auth::getInstance()->getIdentity();
            if(isset($identity)) {
                if(UserAcl::isStudent()) {
                    return sprintf(self::$cmsUrlFormat, $matches[2]);
                } else if (UserAcl::isStaffOrAbove()) {
                    return sprintf(self::$cmsUrlFormat, $matches[2]);
                } else {
                    '';
                }
            } else { //called from admin/indexstatus page
            	return sprintf(self::$cmsUrlFormat,$matches[2]);
            }
        } else {
            return '';
        }
    }
    
    public static function getCmsLinkForEvents($mid) {
        if(!is_string($mid)) {
            return '';
        }
        if(preg_match("/^http:\/\/(.+)\/\|cmsdocs-smp\|(.+)/",$mid, $matches) > 0) {
            return sprintf(self::$cmsUrlFormat,$matches[2]);
        }        
        return '';
    }
    
    public static function isDoctype($mid, $doctype) {
        if(!is_string($mid) && !is_string($doctype)) {
            return false;
        }
        $cmsMid = false;
        if(preg_match("/^http:\/\/(.+)\/\|cmsdocs-smp\|(.+)/",$mid) > 0) {
            $cmsMid = true;
        } else if (preg_match("/^http:\/\/(.+)\/\|cmsdocs-smp\|(.+)/",MediabankResourceConstants::sanitizeMid($mid)) > 0) {
            $mid = MediabankResourceConstants::encode($mid);
            $cmsMid = true;
        }
        if($cmsMid) {
            $mediabankResourceService = new MediabankResourceService();
            $midInfo = $mediabankResourceService->getMetaData($mid);
            if(isset($midInfo['data']) && isset($midInfo['data']['doctype']) && $midInfo['data']['doctype'] == $doctype) {
                return true;
            }                  
        } 
        return false;
    }
    
}

?>
