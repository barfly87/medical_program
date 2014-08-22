<?php
class StaffForm extends Zend_Form {
	public $elementDecorators = array(
		'ViewHelper',
		'Errors',
        array(array('data' => 'HtmlTag'), array('tag' => 'span', 'class' => 'element')),
    );
    
	public function init() {
		$this->setMethod('post');
		$this->setAction(Compass::baseUrl().'/admin/addstaff');
		
		$identity = Zend_Auth::getInstance()->getIdentity();
		$mydomain = $identity->domain;
		$myrole = $identity->role;
		
		$stafftype = $this->createElement('select', 'stafftype');
		$stafftype->setDecorators($this->elementDecorators);
		$stafftypes = new StaffType();
		$stafftypes = $stafftypes->getAllNames();
		$stafftypes[-1] = "None";
		$stafftype->setMultiOptions(Utilities::removeEmpty($stafftypes));
		$stafftype->setRequired(TRUE);
		$this->addElement($stafftype);

		$staffpage = $this->createElement('select', 'staffpage');
		$staffpage->setDecorators($this->elementDecorators);
		$staffpages = new StaffPage();
		$staffpages = $staffpages->getAllNames();
		$staffpage->setMultiOptions(Utilities::removeEmpty($staffpages));
		$staffpage->setRequired(TRUE);
		$this->addElement($staffpage);
		
		$uid = $this->createElement('text', 'uid');
		$uid->setDecorators($this->elementDecorators);
		$uid->setAttrib('size', 20);
		$uid->setAttrib('id', 'uid');
		$uid->addErrorMessage("User ID must not be empty.");
		$uid->setRequired(TRUE);
		$this->addElement($uid);
		
		$description = $this->createElement('text', 'description');
		$description->setDecorators($this->elementDecorators);
		$description->setAttrib('size', 60);
		$description->setAttrib('id', 'description');
		$description->addErrorMessage("Error in description");
		$description->setRequired(FALSE);
		$this->addElement($description);
		
		$domain = $this->createElement('select', 'domain_id');
		$domain->setDecorators($this->elementDecorators);
		if ( $myrole!='admin' ) {
			$domainFinder = new Domains();
			$domainid = $domainFinder->getDomainId($mydomain);
			$domains[$domainid] = $mydomain;
		} else {
			$domainFinder = new Domains();
			$domains = $domainFinder->getAllNames();
		}
		$domain->setMultiOptions(Utilities::removeEmpty($domains));
		$domain->setRequired(TRUE);
		$this->addElement($domain);
				
		$submit = $this->createElement('submit', 'submit');
		$submit->setDecorators($this->elementDecorators);
		$this->addElement($submit);
	}
}