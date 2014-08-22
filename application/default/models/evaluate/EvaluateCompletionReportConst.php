<?php

class EvaluateCompletionReportConst {
    CONST START_DATE                        = 'start_date';
    CONST END_DATE                          = 'end_date';
    CONST UIDS                              = 'uids';
    CONST STAGES                            = 'stages';
    CONST CSV                               = 'CSV';
    CONST TA_TYPE_ID                        = 'ta_type_id';

    CONST ERROR_MSG_UIDS_SEPARATE_LINE      = 'Please type each UID in a separate line';
    CONST ERROR_MSG_END_DATE_GR_START_DATE  = 'End Date should be greater than the Start Date';

    CONST VALIDATE_END_DATE                 = 'VALIDATE_END_DATE';
    CONST VALIDATE_UIDS                     = 'VALIDATE_UIDS';

    CONST HEADING_UID                       = 'UID';
    CONST HEADING_FINISHED                  = '% Finished';

    public static function getUidsDefaultValue() {
        return 'UID 1'.PHP_EOL.'UID 2'.PHP_EOL.'...'.PHP_EOL.'...';
    }

    public static function getUids($postUids) {
        $return = true;
        $newUids = array();
        if(!empty($postUids)) {
            $uids = explode(PHP_EOL, $postUids);
            foreach($uids as $uid) {
                $uid = trim($uid);
                if(str_word_count($uid) > 1){
                    $return = false;
                } else {
                    $newUids[] = $uid;
                }
            }
        }
        if($return === true) {
            return $newUids;
        }
        return false;
    }

}