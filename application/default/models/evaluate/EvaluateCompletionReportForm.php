<?php
class EvaluateCompletionReportForm {
    
    private $_form              = null;
    private $_locale            = null;
    private $_elementDecorators = array(
        'ViewHelper',
        array('Errors', array('placement' => 'PREPEND')),
        array(array('data' => 'HtmlTag'), array('tag' => 'span', 'class' => 'form-elements')),
    );
    
    public function __construct() {
        $this->_form = new Zend_Form();
        $locale = Compass::getConfig('locale');
        $this->_locale = (empty($locale)) ? 'en_AU' : $locale;
    }
    
    public function getForm() {
        $this->_addStartDate();
        $this->_addEndDate();
        $this->_addUids();
        $this->_addStages();
        $this->_addCSV();
        $this->_addTaTypeIds();
        $this->_addSubmit();
        return $this->_form;
        
    }
    
    private function _addStartDate() {
        $elem = $this->_form->createElement('text', EvaluateCompletionReportConst::START_DATE, array('style'=>''));
        $elem
            ->addValidator(new Zend_Validate_Date('dd-mm-YYYY', $this->_locale))
            ->setRequired(true);
        $elem->setDecorators($this->_elementDecorators);          
        $this->_form->addElement($elem); 
    }
    
    private function _addEndDate() {
        $elem = $this->_form->createElement('text', EvaluateCompletionReportConst::END_DATE, array('style'=>''));
        $elem
            ->addValidator(new Zend_Validate_Date('dd-mm-YYYY', $this->_locale))
            ->addValidator(new EvaluateCompletionReportElementValidator(EvaluateCompletionReportConst::VALIDATE_END_DATE))
            ->setRequired(true);
        $elem->setDecorators($this->_elementDecorators);          
        $this->_form->addElement($elem); 
    }

    private function _addUids() {
        $elem = $this->_form->createElement('textarea', EvaluateCompletionReportConst::UIDS);
        $elem
            ->setAttrib('rows','10')
            ->setAttrib('cols','30')
            ->setValue(EvaluateCompletionReportConst::getUidsDefaultValue())
            ->addValidator(new EvaluateCompletionReportElementValidator(EvaluateCompletionReportConst::VALIDATE_UIDS))
            ->setDecorators($this->_elementDecorators);
        $this->_form->addElement($elem); 
    }
    
    private function _addStages() {
        $elem = $this->_form->createElement('radio', EvaluateCompletionReportConst::STAGES);
        $elem
            ->addMultiOptions(array(
                '1' => '1',
                '2' => '2'
            ))
            ->setValue('1')
            ->setDecorators($this->_elementDecorators)
            ->setRequired(true)
            ->setSeparator('');
       $this->_form->addElement($elem);
    }
    
    private function _addCSV() {
        $elem = $this->_form->createElement('checkbox', EvaluateCompletionReportConst::CSV)
                    ->setDecorators($this->_elementDecorators);
        $this->_form->addElement($elem);                    
    }
    
    private function _addTaTypeIds() {
        $evaluateTa = new EvaluateTa();
        $taTypes = $evaluateTa->getUniqueTaTypesEvaluated();
        $options = array();
        foreach($taTypes as $row) {
            $options[$row['ta_type_id']] = $row['ta_type'];
        }
        $elem = $this->_form->createElement('multiCheckbox', EvaluateCompletionReportConst::TA_TYPE_ID);
        $elem->addMultiOptions($options)
             ->setDecorators($this->_elementDecorators)
             ->setRequired(true)
             ->setValue(array_keys($options))
             ->setSeparator('<br />');
        $this->_form->addElement($elem);
    }
    
    private function _addSubmit() {
        $submit = $this->_form->createElement('submit', 'submit', array('label' => 'Submit'));
        $submit->setValue('SUBMIT');
        $submit->setDecorators($this->_elementDecorators);
        $this->_form->addElement($submit);
    }
    
}