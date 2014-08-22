<?php
class EvaluateCompletionReportElementValidator extends Zend_Validate_Abstract {
    
    CONST UIDS_SEPARATE_LINE_ERROR      = 'uids_error';
    CONST END_DATE_GR_START_DATE        = 'end_date_greater_than_start_date';
    
    private $_elem                      = null;
    private $_locale                    = null;
    
    public function __construct($elem) {
        $this->_elem = $elem;
        $locale = Compass::getConfig('locale');
        $this->_locale = (empty($locale)) ? 'en_AU' : $locale;
        
    }
    
    protected $_messageTemplates = array(
        self::UIDS_SEPARATE_LINE_ERROR          => EvaluateCompletionReportConst::ERROR_MSG_UIDS_SEPARATE_LINE,
        self::END_DATE_GR_START_DATE            => EvaluateCompletionReportConst::ERROR_MSG_END_DATE_GR_START_DATE
    );
 
    public function isValid($value, $context = null) {
        switch($this->_elem) {
            case EvaluateCompletionReportConst::VALIDATE_END_DATE:
                return $this->_isValidEndDate($value, $context);
                break;
            case EvaluateCompletionReportConst::VALIDATE_UIDS:
                return $this->_isValidUids($value, $context);
                break;
            default:
                return true;
                break;
        }
    }    
    
    private function _isValidUids($value, $context) {
        $newUids = EvaluateCompletionReportConst::getUids($value);
        if($newUids === false) {
            $this->_error(self::UIDS_SEPARATE_LINE_ERROR);
            return false;
        }
        return true;
    }
    
    private function _isValidEndDate($value, $context) {
        $this->_setValue(trim($value));
        if(is_array($context)) {
            if(! isset($context[EvaluateCompletionReportConst::START_DATE]) && 
                $this->_checkDateFormat($context[EvaluateCompletionReportConst::START_DATE]) === false) 
            {
                return true;
            } else {
                $endDateIsGreateThanStartDate = $this->_checkEndDateIsGreaterThanStartDate($context[EvaluateCompletionReportConst::START_DATE], $this->_value);
                if($endDateIsGreateThanStartDate === false) {
                    $this->_error(self::END_DATE_GR_START_DATE);
                    return false;
                }
            }
        }
        return true;
    }
    
    private function _checkDateFormat($value) {
        $validator = new Zend_Validate_Date('dd-mm-YYYY', $this->_locale);
        return $validator->isValid($value);
    }
    
    private function _checkEndDateIsGreaterThanStartDate($startDate, $endDate) {
        $startEpoch = strtotime($startDate);
        $endEpoch = strtotime($endDate);
        if($startDate !== false && $endEpoch !== false) {
            return $endEpoch >  $startEpoch ? true : false;
        }
        return true;
    }

}
?>