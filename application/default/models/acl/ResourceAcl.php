<?php
class ResourceAcl extends UserAcl {
    
    private static $writeAccessPages = array('upload','edit','link','add','remove','sort');
    private static $uploadErr = 'You are not authorized to upload any resources';//No period at the end
    private static $editErr = 'You are not authorized to edit this resource.';
    private static $linkErr = 'You are not authorized to link any existing resources';//No period at the end
    private static $addErr = 'You are not authorized to add this resource.';
    private static $removeErr = 'You are not authorized to remove this resource.';
    private static $sortErr = 'You are not authorized to sort this resource.';
    private static $actionErr = 'You are not authorized to do this action.';
    
    /**
     * Checks if the user is allowed to access a particular action.
     * @param array $params eg. $params = array('action'=>'upload','type'=>'lo|ta','auto_id'=>123);
     * @return array $return 
     */
    public static function access($params) {
        $return = array();
        $allow = false;
        $staffError = false;
        
        //Check if invalid values are send in the $params
        $err = self::checkErrors($params);
        if($err['err'] === true) {
            self::logError($err['errMsg']);
        } else {
            if(in_array($params['action'], self::$writeAccessPages)) {
                $access = self::processWriteAccessPages($params);
                if($access === true) {
                    $allow = true;
                } else if (isset($access['err'])) {
                    if(isset($access['err']['invalid'])) {
                        self::logError($access['err']['invalid']);
                        $allow = false;
                    } else if(isset($access['err']['staff'])) {
                        $allow = false;
                        $staffError = $access['err']['staff'];
                    }
                }            
            } else {
                self::logError("Invalid action given {$params['action']}.\n".self::stackTrace());
            }
        }
        $return['allow'] = $allow;
        if($staffError !== false) {
            $return['err']['staff'] = $staffError;
        }
        return $return;
    }
    
    /**
     * Provides ACL for all resource actions which can be accessed by the current user.
     * @param array $params eg. $params = array('type'=>'lo|ta','auto_id'=>123);
     * @return array $return eg. $return = array('upload' => true, 'edit' => false ...)
     */
    public static function accessAll($params) {
        $return = array();
        $allow = false;
        $staffError = false;
        $params['action'] = 'all'; //To validate 'action' in 'checkErrors()'
        $err = self::checkErrors($params);

        if($err['err'] === true) {
            self::logError($err['errMsg']);
        } else {
            $access = self::processWriteAccessPages($params);
            if($access === true) {
                $allow = true;
            } else if (isset($access['err'])) {
                if(isset($access['err']['invalid'])) {
                    self::logError($access['err']['invalid']);
                    $allow = false;
                } else if(isset($access['err']['staff'])) {
                    $allow = false;
                    $staffError = $access['err']['staff'];
                }
            }            
        }
        return self::returnWriteAccessPagesAll($allow,$staffError);
    }
    
    /**
     * Loops through each of the $writeAccessPages and sets ACL for each of them
     * and return it as array
     * @param (boolean) $allow
     * @param (string) $staffError If $allow is false beacause of staffError send it back
     * @return (array) $return
     */
    private static function returnWriteAccessPagesAll($allow,$staffError=false) {
        $return = array();
        $allow = ($allow === true) ? true : false;

        foreach(self::$writeAccessPages as $page) {
            if($allow === false) {
                $errorStr = self::staffError($page);
                if($staffError !== false && is_string($staffError)) {
                    $errorStr .= '<br />'.$staffError;
                }
                $return[$page]['err'] = $errorStr;
            }
            $return[$page]['allow'] = $allow;
        }
        return $return;
    }
    
    /**
     * Returns the error to be send back as per different pages. 
     * @param string $page
     * @return string $staffErrorString
     */
    private static function staffError($page) {
        $staffErrorString = '';
        switch($page) {
            case 'upload':      $staffErrorString = self::$uploadErr;           break;
            case 'edit':        $staffErrorString = self::$editErr;             break;
            case 'link':        $staffErrorString = self::$linkErr;             break;
            case 'add':         $staffErrorString = self::$addErr;              break;
            case 'remove':      $staffErrorString = self::$removeErr;           break;
            case 'sort':        $staffErrorString = self::$sortErr;             break;
            default:            $staffErrorString = self::$actionErr;           break;
        }
        return $staffErrorString;
    }
    
    /**
     * It checks hether the user has access to this ta/lo given in $params
     * @param array $params
     * @return array unknown_type
     */
    private static function processWriteAccessPages($params) {
        switch($params['type']) {
            case ResourceConstants::$TYPE_lo:
                return Utilities::isMyLo((int)$params['auto_id']);
            break;
            case ResourceConstants::$TYPE_ta:
                return Utilities::isMyTa((int)$params['auto_id']);       
            break;
            case ResourceConstants::$TYPE_pbl:
                return UserAcl::isStaffOrAbove();
            break;
            case ResourceConstants::$TYPE_block:
                return UserAcl::isStaffOrAbove();
            break;
        }
        return array('err' => true);
    }
    
    /**
     * Checks whether all the values in $params are valid
     * @param array $params
     * @return array unknown_type
     */
    private static function checkErrors($params) {
        if(! empty($params) && is_array($params) && count($params) > 0 ) {
            if(isset($params['action']) && isset($params['type']) && isset($params['auto_id'])) {
                if(! in_array($params['type'], ResourceConstants::$TYPES_allowed) || (int)$params['auto_id'] <= 0) {
                    return self::createErrMsg("Incorrect value for arguments given.\n".self::stackTrace());
                } else {
                    $return['err'] = false;
                    return $return;
                }
            } else {
                return self::createErrMsg("All or one of parameters are missing.\n".self::stackTrace());
            }
        } else {
            return self::createErrMsg("No arguments given.\n".self::stackTrace());
        }
    }
    
    /**
     * Creates error messages
     * @param array $errors
     * @return array $return
     */
    private static function createErrMsg($errors) {
        $return['err'] = true;
        $return['errMsg'] = $errors;
        return $return;
    }
    
    /**
     * Return stack trace for error logging purposes
     * @return string $stackTrace
     */
    private static function stackTrace() {
        ob_start();
        debug_print_backtrace();
        $stackTrace = ob_get_contents();
        ob_end_clean();
        return $stackTrace;
    }
    
    /**
     * It logs error as per zend  
     * @param array|string $errors
     */
    private static function logError($errors) {
        if(is_array($errors)) { 
            foreach($errors as $error) {
                Zend_Registry::get('logger')->warn($error);
            }
        } else if (is_string($errors)) {
            Zend_Registry::get('logger')->warn($errors);
        }
    }
    

    
    
}
?>