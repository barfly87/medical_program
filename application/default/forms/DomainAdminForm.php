<?php
class DomainAdminForm extends Zend_Form {
	public $elementDecorators = array(
		'ViewHelper',
		'Errors',
        array(array('data' => 'HtmlTag'), array('tag' => 'span', 'class' => 'element')),
    );
    
	public function init() {
		$this->setMethod('post');
		$this->setAction(Compass::baseUrl().'/admin/adddomainadmin');
		
		$uid = $this->createElement('text', 'uid');
		$uid->setDecorators($this->elementDecorators);
		$uid->setAttrib('size', 20);
		$uid->setAttrib('id', 'uid');
		$uid->addErrorMessage("User ID must not be empty.");
		$uid->setRequired(TRUE);
		$this->addElement($uid);
		
		$domain = $this->createElement('select', 'domain');
		$domain->setDecorators($this->elementDecorators);
		$domainFinder = new Domains();
		$domains = $domainFinder->getAllNames();
		$domain->setMultiOptions(Utilities::removeEmpty($domains));
		$domain->setRequired(TRUE);
		$this->addElement($domain);
		
		$submit = $this->createElement('submit', 'submit');
		$submit->setDecorators($this->elementDecorators);
		$this->addElement($submit);
	}
}