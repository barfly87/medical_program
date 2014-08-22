<?php
class BlockConst {
    
    public static $pageLearningObjectives               = 'learningobjectives';
    public static $pageLearningObjectivesTitle          = 'Learning Objectives';
    
    public static $pageLectures                         = 'lectures';
    public static $pageLecturesTitle                    = 'Lectures';
    
    public static $pageManageResources                  = 'manageresources';
    public static $pageManageResourcesTitle             = 'Manage Resources';
    
    public static $pageClinicalReasoningSessions        = 'clinicalreasoningsessions';
    public static $pageClinicalReasoningSessionsTitle   = 'CRS';
    
    public static $pageGet                              = 'get';
    public static $pageFetch                            = 'fetch';
    
   
    public static function renameTaTypeForBlockMenu($taTypeName) {
        if(substr($taTypeName, -3) !== 'ing' && substr($taTypeName, -1) !== 's' && substr($taTypeName, -3) != 'ive') {
            $taTypeName .= 's';    
        }
        return ucwords($taTypeName);
    }
}
?>