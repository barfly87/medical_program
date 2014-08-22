<?php
class PblcoordinatorForm extends Zend_Form {
    public $elementDecorators = array(
        'ViewHelper',
        'Errors',
        array(array('data' => 'HtmlTag'), array('tag' => 'span', 'class' => 'element')),
    );
    
    public function init() {
        $this->setMethod('post');
        $this->setAction(Compass::baseUrl().'/admin/addpblcoordinator');
        
        $identity = Zend_Auth::getInstance()->getIdentity();
        $mydomain = $identity->domain;
        $myrole = $identity->role;
        
        $pbl = $this->createElement('select', 'pbl');
        $pbl->setDecorators($this->elementDecorators);
        
        $blockPblSeqs = new BlockPblSeqs();
        $pbls = $blockPblSeqs->getAllPbls();
        $pbl->setMultiOptions($pbls);
        $pbl->addValidator(new Zend_Validate_GreaterThan(1));
        $pbl->addErrorMessage("Select one pbl from the list.");
        $pbl->setRequired(TRUE);
        $this->addElement($pbl);
        
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