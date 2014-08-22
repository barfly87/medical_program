<?php
class EvaluateTaConst {
    
    CONST START_DATE                                        = 'start_date';
    CONST END_DATE                                          = 'end_date';
    CONST UIDS                                              = 'uids';
    CONST TA_TYPE_IDS                                       = 'ta_type_ids';
    CONST SHOW_STUDENT_UIDS                                 = 'show_student_uids';
    CONST SHOW_STUDENT_UIDS_YES                             = 'yes';
    
    private static $_ADMINS                                 = null;
    private static $_SUPER_ADMINS                           = null;
    private static $_USER_IS_ADMIN                          = null;
    private static $_USER_IS_SUPER_ADMIN                    = null;
    
    
    public static $EVALUATION_AUTO_ID                       = 'auto_id';
    public static $TA_AUTO_ID                               = 'ta_auto_id';
    public static $CURRENT_TA_AUTO_ID                       = 'current_ta_auto_id';
    public static $TA_TYPE_ID                               = 'ta_type_id';
    public static $TA_TYPE                                  = 'ta_type';
    public static $STUDENT_ID                               = 'student_id';
    public static $DOMAIN_ID                                = 'domain_id';
    public static $DATETIME                                 = 'datetime';
    public static $ROLE                                     = 'role';
    
    public static $STUDENT_ATTENDANCE                       = 'student_attendance';
    public static $STUDENT_ATTENDANCE_QUES                  = 'My attendance:';
    public static $STUDENT_ATTENDANCE_COMMENT               = 'student_attendance_comment';
    public static $STUDENT_ATTENDANCE_COMMENT_QUES          = 'Please comment:';
    
    public static $DELIVERY_OF_TA                           = 'delivery_of_ta';
    public static $DELIVERY_OF_TA_QUES                      = 'The delivery of this %s was:';
    
    public static $CONTENT_MATCH_LO                         = 'content_match_lo';
    public static $CONTENT_MATCH_LO_QUES                    = 'The %s content matched the learning objectives:';
    
    public static $INFORMATION_COVERED                      = 'information_covered';
    public static $INFORMATION_COVERED_QUES                 = 'For the time allowed, the amount of information covered, including the number of slides, was:';
    
    public static $SCIENTIFIC_LEVEL                         = 'scientific_level';
    public static $SCIENTIFIC_LEVEL_QUES                    = 'For you at this point in your studies, the scientific or intellectual level of the %s was:';
    
    public static $OVERLAP                                  = 'overlap';
    public static $OVERLAP_QUES                             = 'Was there overlap between this %s and any other teaching session to date?';
    public static $OVERLAP_EXPLANATION                      = 'overlap_explanation';
    public static $OVERLAP_EXPLANATION_QUES                 = 'Please describe excessive overlap:';
    
    public static $OVERALL_RATING                           = 'overall_rating';
    public static $OVERALL_RATING_QUES                      = 'Overall, how would you rate this %s:';
    
    public static $SUGGESTIONS                              = 'suggestions';
    public static $SUGGESTIONS_QUES                         = 'Your suggestions for improving this %s:';
    
    public static $TA_TYPE_TEXT                             = 'lecture/seminar';
    

    public static $VALIDATE_OVERLAP_EXPLANATION             = 'VALIDATE_OVERLAP_EXPLANATION';
    public static $VALIDATE_STUDENT_ATTENDANCE_COMMENT      = 'VALIDATE_STUDENT_ATTENDANCE_COMMENT';
    public static $VALIDATE_STUDENT_ATTENDANCE              = 'VALIDATE_STUDENT_ATTENDANCE';

    CONST ERROR_MSG_SELECT_ONE_OPTION                       = '* please select one option';
    CONST ERROR_MSG_DESCRIBE                                = '* please describe';

    public static $OPTIONS_STUDENT_ATTENDANCE               = array(
                                                                '1' =>  'Attended ',
                                                                '2' =>  'Listened online',
                                                                '3' =>  'Attended and listened online',
                                                                '4' =>  'Did not attend or listen online'
                                                            );

    public static $OPTIONS_DELIVERY_OF_TA                   = array(
                                                                '5' => '5 Very clear',
                                                                '4' => '4',
                                                                '3' => '3',
                                                                '2' => '2',
                                                                '1' => '1 Very unclear',
                                                                '0' => '0 Not applicable'
                                                            );
                                                            
    public static $OPTIONS_CONTENT_MATCH_LO                 = array(
                                                                '5' =>  '5 Very well',
                                                                '4' =>  '4',
                                                                '3' =>  '3',
                                                                '2' =>  '2',
                                                                '1' =>  '1 Not at all',
                                                                '0' =>  'Unsure'
                                                            );
                                                            
    public static $OPTIONS_INFORMATION_COVERED              = array(
                                                                '1' =>  'Insufficient',
                                                                '2' =>  'About right',
                                                                '3' =>  'Excessive'
                                                            );
                                                            
    public static $OPTIONS_SCIENTIFIC_LEVEL                 = array(
                                                                '1' =>  'Too low',
                                                                '2' =>  'About right',
                                                                '3' =>  'Too high'
                                                            );
                                                            
    CONST OPTIONS_OVERLAP_KEY_N                             = 'n';   
    CONST OPTIONS_OVERLAP_KEY_Y                             = 'y';
    
    public static $OPTIONS_OVERLAP                          = array(
                                                                EvaluateTaConst::OPTIONS_OVERLAP_KEY_N =>  'No overlap',
                                                                EvaluateTaConst::OPTIONS_OVERLAP_KEY_Y =>  'Yes, some overlap',
                                                                'ye' => 'Yes, excessive overlap'
                                                            );
	
    public static $OPTIONS_OVERALL_RATING                   = array(
                                                                '5' =>  '5 Excellent',
                                                                '4' =>  '4',
                                                                '3' =>  '3',
                                                                '2' =>  '2',
                                                                '1' =>  '1 Poor'
                                                            );
    
    public static function getOptions($formField, $taType = '') {
        $options = 'OPTIONS_'.strtoupper($formField);
        $return = array();
        if(isset(EvaluateTaConst::$$options)) {
            $return = EvaluateTaConst::$$options;
            if($formField == EvaluateTaConst::$STUDENT_ATTENDANCE) {
                $return[1] = $return[1].$taType; 
            } 
        }
        return $return;
    }
    
    public static function getConfigQuestions($activityTypeId) {
        $questions = array();
        $configQuestions = Compass::getConfig('evaluate.ta.activitytypeid.'.$activityTypeId.'.questions');
        if(!empty($configQuestions)) {
            $configQuestions = explode(',', $configQuestions);
            $allQuestions = Compass::getConfig('evaluate.ta.question');
            foreach($configQuestions as $configQuestion) {
                if(isset($allQuestions[$configQuestion])) {
                    $questions[$configQuestion] = $allQuestions[$configQuestion];
                } else {
                    $error = sprintf('Config question "%s" does not have a corresponding config option "evaluate.ta.question.%s"', $configQuestion, $configQuestion);
                    Compass::error($error,  __DIR__.'/'.__CLASS__, __LINE__);
                }
            }
        } else {
            $error = sprintf('Config option "evaluate.ta.activitytypeid.%s.questions" is not set', $ta->typeID);
            Compass::error($error,  __DIR__.'/'.__CLASS__, __LINE__);
        }
        return $questions;
    }
    
    public static function getAdminUids() {
        if(self::$_ADMINS == null) {
            $userIdStr = Compass::getConfig('evaluation.ta.admins');
            $userIds = Compass::csvToArray($userIdStr);
            if(!empty($userIds)) {
                self::$_ADMINS = $userIds;
            }
        }
        return self::$_ADMINS;
    }
    
    public static function isUserEvaluationAdmin() {
        if(EvaluateTaConst::$_USER_IS_ADMIN == null) {
            $admins = EvaluateTaConst::getAdminUids();
            EvaluateTaConst::$_USER_IS_ADMIN = (in_array(UserAcl::getUid(), $admins));
        }
        return EvaluateTaConst::$_USER_IS_ADMIN;
    }
    
    
    public static function getSuperAdminsUids() {
        if(self::$_SUPER_ADMINS == null) {
            $userIdStr = Compass::getConfig('evaluation.ta.superadmins');
            $userIds = Compass::csvToArray($userIdStr);
            if(!empty($userIds)) {
                self::$_SUPER_ADMINS = $userIds;
            }
        }
        return self::$_SUPER_ADMINS;
    }
    
    public static function isUserEvaluationSuperAdmin() {
        if(EvaluateTaConst::$_USER_IS_SUPER_ADMIN == null) {
            $superadmins = EvaluateTaConst::getSuperAdminsUids();
            EvaluateTaConst::$_USER_IS_SUPER_ADMIN = (in_array(UserAcl::getUid(), $superadmins));
        }
        return EvaluateTaConst::$_USER_IS_SUPER_ADMIN;
    }
    
    public static function getUidsDefaultValue() {
        return 'UID 1'.PHP_EOL.'UID 2'.PHP_EOL.'...'.PHP_EOL.'...';
    }
    
    public static function getActivityTypes() {
        $return = array();
        $taTypeIds = Compass::getConfig('evaluate.ta.activitytypeids');
        $activityTypes = new ActivityTypes();
        $allActivityTypes = $activityTypes->getAllNames();
        if(!empty($taTypeIds)) {
            foreach($taTypeIds as $taTypeId) {
                if(isset($allActivityTypes[$taTypeId])) {
                    $return[$taTypeId] = $allActivityTypes[$taTypeId];
                }
            }
        }
        return $return;
    }
    
    public static function getDefaultTableColumns() {
        return array(
                        EvaluateTaConst::$EVALUATION_AUTO_ID,
                        EvaluateTaConst::$TA_AUTO_ID,
                        EvaluateTaConst::$TA_TYPE_ID,
                        EvaluateTaConst::$TA_TYPE,
                        EvaluateTaConst::$DOMAIN_ID,
                        EvaluateTaConst::$STUDENT_ID,
                        EvaluateTaConst::$DATETIME,
                        EvaluateTaConst::$ROLE
        );
    }
    
    public static function getColumnsForTaTypeIds($taTypeIds) {
        $return = array();
        if(is_array($taTypeIds) && !empty($taTypeIds)) {
            foreach($taTypeIds as $taTypeId) {
                $questions = EvaluateTaConst::getConfigQuestions($taTypeId);
                foreach($questions as $question) {
                    if(!in_array($question, $return)) {
                        $return[] = $question;
                    }
                }
            }
        }
        return $return;
    }
    
    public static function showGenericEvaluation($typeId) {
        $showGenericEvaluation = true;
        $customEvalActivityTypeIds = Compass::getConfig('evaluate.ta.activitytypeids');
        if(!empty($customEvalActivityTypeIds) && in_array($typeId, $customEvalActivityTypeIds)) {
            $stagesAllowed = Compass::getConfig('evaluate.ta.activitytypeid.'.$typeId.'.stages.allowed');
            if(!empty($stagesAllowed)) {
                $stagesAllowed = explode(',', $stagesAllowed);
                if( UserAcl::isAdmin() || 
                    (
                        is_array($stagesAllowed) && in_array(UserAcl::getStudentStage(), $stagesAllowed))
                    ) {
                    $showGenericEvaluation = false;
                }
            }
        }
        return $showGenericEvaluation;
    }
    
}
