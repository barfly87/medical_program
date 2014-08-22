<?php
class BlockchairForm extends Zend_Form {
	public $elementDecorators = array(
		'ViewHelper',
		'Errors',
        array(array('data' => 'HtmlTag'), array('tag' => 'span', 'class' => 'element')),
    );
    
	public function init() {
		$this->setMethod('post');
		$this->setAction(Compass::baseUrl().'/admin/addchair');
		
		$identity = Zend_Auth::getInstance()->getIdentity();
		$mydomain = $identity->domain;
		$myrole = $identity->role;
		
		$block = $this->createElement('select', 'block');
		$block->setDecorators($this->elementDecorators);
		$sbs = new StageBlockSeqs();
		$blocks = $sbs->getAllBlocks();
		$block->setMultiOptions(Utilities::removeEmpty($blocks));
		$block->setRequired(TRUE);
		$this->addElement($block);
		
		$uid = $this->createElement('text', 'uid');
		$uid->setDecorators($this->elementDecorators);
		$uid->setAttrib('size', 20);
		$uid->setAttrib('id', 'uid');
		$uid->addErrorMessage("User ID must not be empty.");
		$uid->setRequired(TRUE);
		$this->addElement($uid);
		
		$domain = $this->createElement('select', 'domain');
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