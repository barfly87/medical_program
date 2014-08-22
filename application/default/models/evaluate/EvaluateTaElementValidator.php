<?php
class EvaluateTaElementValidator extends Zend_Validate_Abstract {
    
    CONST SELECT_OPTION     = 'selectOption';
    CONST DESCRIBE          = 'describe';
    
    private $_elem          = null;
    
    public function __construct($elem) {
        $this->_elem = $elem;
    }
    
    protected $_messageTemplates = array(
        self::SELECT_OPTION => EvaluateTaConst::ERROR_MSG_SELECT_ONE_OPTION,
        self::DESCRIBE => EvaluateTaConst::ERROR_MSG_DESCRIBE
    );
 
    public function isValid($value, $context = null) {
        switch($this->_elem) {
            case EvaluateTaConst::$VALIDATE_STUDENT_ATTENDANCE:
                return $this->_isValidStudentAttendance($value, $context);
                break;
            case EvaluateTaConst::$VALIDATE_STUDENT_ATTENDANCE_COMMENT:
                return $this->_isValidStudentAttendanceComment($value, $context);
                break;
            case EvaluateTaConst::$VALIDATE_OVERLAP_EXPLANATION:
                return $this->_isValidOverlapExplanation($value, $context);
                break;
            default:
                return true;
                break;
        }
    }    
    
    public function _isValidStudentAttendance($value, $context) {
        $this->_setValue(trim($value));
        if (is_array($context)) {
            if (! isset($context[EvaluateTaConst::$STUDENT_ATTENDANCE]) ||
                in_array($context[EvaluateTaConst::$STUDENT_ATTENDANCE], array(4)) ||
                empty($context[EvaluateTaConst::$STUDENT_ATTENDANCE])
            ) {
                return true;
            }
        }
        if(trim($value) == '') {
            $this->_error(self::DESCRIBE);
            return false;
        }
        return true;
    }
    
    public function _isValidStudentAttendanceComment($value, $context) {
        $this->_setValue(trim($value));
        if (is_array($context)) {
            if (! isset($context[EvaluateTaConst::$STUDENT_ATTENDANCE]) ||
                in_array($context[EvaluateTaConst::$STUDENT_ATTENDANCE], array(1, 2, 3)) ||
                empty($context[EvaluateTaConst::$STUDENT_ATTENDANCE])
            ) {
                return true;
            }
        }
        if(trim($value) == '') {
            $this->_error(self::DESCRIBE);
            return false;
        }
        return true;
    }
    
    public function _isValidOverlapExplanation($value, $context) {
        $this->_setValue(trim($value));
        if (is_array($context)) {
            if (! isset($context[EvaluateTaConst::$STUDENT_ATTENDANCE]) ||
                            in_array($context[EvaluateTaConst::$STUDENT_ATTENDANCE], array(4)) ||
                            empty($context[EvaluateTaConst::$STUDENT_ATTENDANCE])
            ) {
                return true;
            }
            if (! isset($context[EvaluateTaConst::$OVERLAP]) ||
                in_array(
                    $context[EvaluateTaConst::$OVERLAP], 
                    array(EvaluateTaConst::OPTIONS_OVERLAP_KEY_N, EvaluateTaConst::OPTIONS_OVERLAP_KEY_Y)) ||
                    empty($context[EvaluateTaConst::$OVERLAP])
            ) {
                return true;
            }
        }
        if(trim($value) == '') {
            $this->_error(self::DESCRIBE);
            return false;
        }
        return true;
    }
}
