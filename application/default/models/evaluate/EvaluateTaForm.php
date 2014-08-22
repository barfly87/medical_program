<?php
class EvaluateTaForm {
    
    private $_form = null;
    private $_elementDecorators = array(
        'ViewHelper',
        array('Errors', array('placement' => 'PREPEND')),
        array(array('data' => 'HtmlTag'), array('tag' => 'span', 'class' => 'form-elements')),
    );
    private $_evaluation = array();
    private $_ta = null;
    
    public function __construct() {
        $this->_form = new Zend_Form();
    }
    
    public function getForm($evaluation, $questionNos, $ta) {
        $this->_ta = $ta;
        if($evaluation !== false && is_array($evaluation)) {
            $this->_evaluation = $evaluation;
        }
        $questions = Compass::getConfig('evaluate.ta.question');
        foreach($questionNos as $questionNo => $questionTitle) {
            if(isset($questions[$questionNo])) {
                $this->_add($questions[$questionNo]);
            }
        }
        $this->_addSubmit();
        return $this->_form;
    }
    
    private function _add($question) {
        switch($question) {
            case EvaluateTaConst::$STUDENT_ATTENDANCE :
                $this->_addStudentAttendance();
                $this->_addStudentAttendanceComment();
            break;
            case EvaluateTaConst::$DELIVERY_OF_TA:
                $this->_addDeliveryOfTa();
            break;
            case EvaluateTaConst::$CONTENT_MATCH_LO:
                $this->_addContentMatched();
            break;
            case EvaluateTaConst::$INFORMATION_COVERED:
                $this->_addInformationCovered();
            break;
            case EvaluateTaConst::$SCIENTIFIC_LEVEL:
                $this->_addScientificLevel();
            break;
            case EvaluateTaConst::$OVERLAP:
                $this->_addOverlap();
                $this->_addOverlapExplanation();
            break;
            case EvaluateTaConst::$OVERALL_RATING:
                $this->_addOverallRating();
            break;
            case EvaluateTaConst::$SUGGESTIONS:
                $this->_addSuggestions();
            break;
            default:
                echo '<h1>Could not find method for config question "'.$question.'"</h1>';
            break;
        }
    }
    
    private function _addStudentAttendance() {
        $elem = $this->_form->createElement('radio', EvaluateTaConst::$STUDENT_ATTENDANCE, array('style'=>''));
        $elem
            ->addErrorMessage(EvaluateTaConst::ERROR_MSG_SELECT_ONE_OPTION)
            ->setRequired(true)
            ->setMultiOptions(EvaluateTaConst::getOptions(EvaluateTaConst::$STUDENT_ATTENDANCE, $this->_ta->type))
            ->setSeparator('');
        if(!empty($this->_evaluation) && isset($this->_evaluation[EvaluateTaConst::$STUDENT_ATTENDANCE])) {
            $elem->setValue($this->_evaluation[EvaluateTaConst::$STUDENT_ATTENDANCE]);
        }
        $elem->setDecorators($this->_elementDecorators);          
        $this->_form->addElement($elem); 
    }
    
    private function _addStudentAttendanceComment() {
        $elem = $this->_form->createElement('textarea', EvaluateTaConst::$STUDENT_ATTENDANCE_COMMENT);
        $elem
        ->setAttrib('rows','5')
        ->setAttrib('cols','80')
        ->setDecorators($this->_elementDecorators)
        ->addValidator(new EvaluateTaElementValidator(EvaluateTaConst::$VALIDATE_STUDENT_ATTENDANCE_COMMENT))
        ->addFilter(new CompassFilterCharsMoreThan(array('length' => 2000)))
        ->addFilter(new CompassFilterRemoveControlChars())
        ->setAllowEmpty(false);
        if(!empty($this->_evaluation) && isset($this->_evaluation[EvaluateTaConst::$STUDENT_ATTENDANCE_COMMENT])) {
            $elem->setValue($this->_evaluation[EvaluateTaConst::$STUDENT_ATTENDANCE_COMMENT]);
        }
        $this->_form->addElement($elem);
    }
    
    private function _addDeliveryOfTa() {
        $elem = $this->_form->createElement('radio', EvaluateTaConst::$DELIVERY_OF_TA);
        $elem
            ->addErrorMessage(EvaluateTaConst::ERROR_MSG_SELECT_ONE_OPTION)
            ->setMultiOptions(EvaluateTaConst::getOptions(EvaluateTaConst::$DELIVERY_OF_TA))
            ->setSeparator('')
            ->addValidator(new EvaluateTaElementValidator(EvaluateTaConst::$VALIDATE_STUDENT_ATTENDANCE))
            ->setRegisterInArrayValidator(false)
            ->setAllowEmpty(false);
        if(!empty($this->_evaluation) && isset($this->_evaluation[EvaluateTaConst::$DELIVERY_OF_TA])) {
            $elem->setValue($this->_evaluation[EvaluateTaConst::$DELIVERY_OF_TA]);
        }
        $elem->setDecorators($this->_elementDecorators);
        $this->_form->addElement($elem);            
    }

    private function _addContentMatched() {
        $elem = $this->_form->createElement('radio', EvaluateTaConst::$CONTENT_MATCH_LO);
        $elem
            ->addErrorMessage(EvaluateTaConst::ERROR_MSG_SELECT_ONE_OPTION)
            ->setMultiOptions(EvaluateTaConst::getOptions(EvaluateTaConst::$CONTENT_MATCH_LO))
            ->setSeparator('')            
            ->addValidator(new EvaluateTaElementValidator(EvaluateTaConst::$VALIDATE_STUDENT_ATTENDANCE))
            ->setRegisterInArrayValidator(false)
            ->setAllowEmpty(false);
        if(!empty($this->_evaluation) && isset($this->_evaluation[EvaluateTaConst::$CONTENT_MATCH_LO])) {
            $elem->setValue($this->_evaluation[EvaluateTaConst::$CONTENT_MATCH_LO]);
        }
        $elem->setDecorators($this->_elementDecorators);          
        $this->_form->addElement($elem); 
    }

    private function _addInformationCovered() {
        $elem = $this->_form->createElement('radio', EvaluateTaConst::$INFORMATION_COVERED);
        $elem
            ->addErrorMessage(EvaluateTaConst::ERROR_MSG_SELECT_ONE_OPTION)
            ->setMultiOptions(EvaluateTaConst::getOptions(EvaluateTaConst::$INFORMATION_COVERED))
            ->setSeparator('')
            ->addValidator(new EvaluateTaElementValidator(EvaluateTaConst::$VALIDATE_STUDENT_ATTENDANCE))
            ->setRegisterInArrayValidator(false)
            ->setAllowEmpty(false);
        if(!empty($this->_evaluation) && isset($this->_evaluation[EvaluateTaConst::$INFORMATION_COVERED])) {
            $elem->setValue($this->_evaluation[EvaluateTaConst::$INFORMATION_COVERED]);
        }
        $elem->setDecorators($this->_elementDecorators);          
        $this->_form->addElement($elem); 
    }
    
    private function _addScientificLevel() {
        $elem = $this->_form->createElement('radio', EvaluateTaConst::$SCIENTIFIC_LEVEL);
        $elem
            ->addErrorMessage(EvaluateTaConst::ERROR_MSG_SELECT_ONE_OPTION)
            ->setMultiOptions(EvaluateTaConst::getOptions(EvaluateTaConst::$SCIENTIFIC_LEVEL))
            ->setSeparator('')
            ->addValidator(new EvaluateTaElementValidator(EvaluateTaConst::$VALIDATE_STUDENT_ATTENDANCE))
            ->setRegisterInArrayValidator(false)
            ->setAllowEmpty(false);
        if(!empty($this->_evaluation) && isset($this->_evaluation[EvaluateTaConst::$SCIENTIFIC_LEVEL])) {
            $elem->setValue($this->_evaluation[EvaluateTaConst::$SCIENTIFIC_LEVEL]);
        }
        $elem->setDecorators($this->_elementDecorators);          
        $this->_form->addElement($elem); 
    }
    
    private function _addOverlap() {
        $elem = $this->_form->createElement('radio', EvaluateTaConst::$OVERLAP);
        $elem
            ->addErrorMessage(EvaluateTaConst::ERROR_MSG_SELECT_ONE_OPTION)
            ->setMultiOptions(EvaluateTaConst::getOptions(EvaluateTaConst::$OVERLAP))
            ->setSeparator('')
            ->addValidator(new EvaluateTaElementValidator(EvaluateTaConst::$VALIDATE_STUDENT_ATTENDANCE))
            ->setRegisterInArrayValidator(false)
            ->setAllowEmpty(false);
        if(!empty($this->_evaluation) && isset($this->_evaluation[EvaluateTaConst::$OVERLAP])) {
            $elem->setValue($this->_evaluation[EvaluateTaConst::$OVERLAP]);
        }
        $elem->setDecorators($this->_elementDecorators);   
        $this->_form->addElement($elem); 
    }

    private function _addOverlapExplanation() {
        $elem = $this->_form->createElement('textarea', EvaluateTaConst::$OVERLAP_EXPLANATION);
        $elem
            ->setAttrib('rows','5')
            ->setAttrib('cols','80')
            ->setDecorators($this->_elementDecorators)
            ->addValidator(new EvaluateTaElementValidator(EvaluateTaConst::$VALIDATE_OVERLAP_EXPLANATION))    
            ->addFilter(new CompassFilterCharsMoreThan(array('length' => '2000')))
            ->addFilter(new CompassFilterRemoveControlChars())
            ->setAllowEmpty(false);
        if(!empty($this->_evaluation) && isset($this->_evaluation[EvaluateTaConst::$OVERLAP_EXPLANATION])) {
            $elem->setValue($this->_evaluation[EvaluateTaConst::$OVERLAP_EXPLANATION]);
        }
        $this->_form->addElement($elem); 
    }
    
    private function _addOverallRating() {
        $elem = $this->_form->createElement('radio', EvaluateTaConst::$OVERALL_RATING);
        $elem
            ->addErrorMessage(EvaluateTaConst::ERROR_MSG_SELECT_ONE_OPTION)
            ->setMultiOptions(EvaluateTaConst::getOptions(EvaluateTaConst::$OVERALL_RATING))
            ->setSeparator('')
            ->addValidator(new EvaluateTaElementValidator(EvaluateTaConst::$VALIDATE_STUDENT_ATTENDANCE))
            ->setRegisterInArrayValidator(false)
            ->setAllowEmpty(false);
        if(!empty($this->_evaluation) && isset($this->_evaluation[EvaluateTaConst::$OVERALL_RATING])) {
            $elem->setValue($this->_evaluation[EvaluateTaConst::$OVERALL_RATING]);
        }
        $elem->setDecorators($this->_elementDecorators);          
        $this->_form->addElement($elem); 
    }
    
    private function _addSuggestions() {
        $elem = $this->_form->createElement('textarea', EvaluateTaConst::$SUGGESTIONS);
        $elem
            ->setAttrib('rows','5')
            ->setAttrib('cols','80')
            ->addFilter(new CompassFilterCharsMoreThan(array('length' => '2000')))
            ->addFilter(new CompassFilterRemoveControlChars())
            ->setDecorators($this->_elementDecorators);
        if(!empty($this->_evaluation) && isset($this->_evaluation[EvaluateTaConst::$SUGGESTIONS])) {
            $elem->setValue($this->_evaluation[EvaluateTaConst::$SUGGESTIONS]);
        }
        $this->_form->addElement($elem); 
    }
    
    private function _addSubmit() {
        $submit = $this->_form->createElement('submit', 'submit', array('label' => 'Submit'));
        $submit->setValue('SUBMIT');
        $submit->setDecorators($this->_elementDecorators);
        $this->_form->addElement($submit);
    }
    
}

/*

In addition to validators, you can specify that an element is required, using setRequired($flag). By default, this flag is FALSE.
In combination with setAllowEmpty($flag) (TRUE by default) and setAutoInsertNotEmptyValidator($flag) (TRUE by default), the
behavior of your validator chain can be modified in a number of ways:

Using the defaults, validating an Element without passing a value, or passing an empty string for it, skips all validators and
validates to TRUE.

setAllowEmpty(false) leaving the two other mentioned flags untouched, will validate against the validator chain you defined for this
Element, regardless of the value passed to isValid().

setRequired(true) leaving the two other mentioned flags untouched, will add a 'NotEmpty' validator on top of the validator chain
(if none was already set)), with the $breakChainOnFailure flag set. This behavior lends required flag semantic meaning: if no
value is passed, we immediately invalidate the submission and notify the user, and prevent other validators from running on what
we already know is invalid data.

If you do not want this behavior, you can turn it off by passing a FALSE value to setAutoInsertNotEmptyValidator($flag); this
will prevent isValid() from placing the 'NotEmpty' validator in the validator chain.

========================================================================================================================================

Zend_Form_Element_MultiCheckbox makes this a snap. Like all other elements extending the base Multi element, you can specify a list
of options, and easily validate against that same list. The 'formMultiCheckbox' view helper ensures that these are returned as an
array in the form submission.

By default, this element registers an InArray validator which validates against the array keys of registered options. You can disable
this behavior by either calling setRegisterInArrayValidator(false), or by passing a FALSE value to the registerInArrayValidator
configuration key.

*/

