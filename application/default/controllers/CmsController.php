<?php
class CmsController extends Zend_Controller_Action {

    /**
     * Set up ACL info
     */
    public function init() {
        $writeActions = array('index','do','link');
        $this->_helper->_acl->allow('admin', $writeActions);
    }

    public function indexAction() {
        
    }
    
    public function doAction() {
        $req = $this->getRequest();
        $service = $req->getParam('service','');
        if(! empty($service)) {
            $cmsService = new CmsService();
            $cmsService->run($service);
        }
        $this->_helper->viewRenderer->setNoRender();
    }
    
    public function linkAction() {
        $req = $this->getRequest();
        $doctype = $req->getParam('doctype','');
        $results = array();
        $linkDoctype = null;

        if(! empty($doctype)) {
            switch($doctype) {
                case 'trigger':
                    $linkDoctype = new LinkDoctypeTrigger();
                break;
                case 'patientdatasheet':
                    $linkDoctype = new LinkDoctypePatientdatasheet();
                break;
                case 'mechanism':
                    $linkDoctype = new LinkDoctypeMechanism();
                break;
                case 'casesummary':
                    $linkDoctype = new LinkDoctypeCasesummary();
                break;
                case 'medicalhumanities':
                    $linkDoctype = new LinkDoctypeMedicalhumanities();
                break;
            }
        }
        if(! is_null($linkDoctype) && $linkDoctype instanceof LinkDoctypeAbstract ) {
            $results = $linkDoctype->process();
        }
        $this->view->doctype = $doctype;
        $this->view->results = $results;
        
    }
    
    
}
