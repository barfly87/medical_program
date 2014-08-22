<?php
class GuideController extends Zend_Controller_Action {

	/**
	 * Set up ACL info
	 */
    public function init() {
        $readActions = array('handbook', 'handbookbypbl', 'proceduralskills', 'patientdoctor');
        $this->_helper->_acl->allow('student', $readActions);
    }

    public function handbookAction() {
    	$this->view->block = $this->_getParam('block');
    	PageTitle::setTitle($this->view, $this->_request, array($this->view->block));
    	
    	$sbs = new StageBlockSeqs();
    	$this->view->blockname = $sbs->getBlockName($this->view->block);
    	
    	$guideService = new DynamicGuideService();
    	$this->view->learningtopics = $guideService->getEssentialReadingsInBlock($this->view->block, array('name'));
    }
    
	public function handbookbypblAction() {
    	$this->view->block = $this->_getParam('block');
    	PageTitle::setTitle($this->view, $this->_request, array($this->view->block));
    	
    	$sbs = new StageBlockSeqs();
    	$this->view->blockname = $sbs->getBlockName($this->view->block);
    	
    	$guideService = new DynamicGuideService();
    	$this->view->learningtopics = $guideService->getEssentialReadingsInBlockByWeek($this->view->block);
    }
    
    public function proceduralskillsAction() {
    	$guideService = new DynamicGuideService();
    	if (NULL !== $this->_getParam('stage')) {
	    	$this->view->stage = $this->_getParam('stage');
	    	PageTitle::setTitle($this->view, $this->_request, array(Zend_Registry::get('Zend_Translate')->_('Stage'), $this->view->stage));
	    	$this->view->procedural_skills = $guideService->getProceduralSkillsInStage($this->view->stage);
    	} else {
    		$this->view->block = $this->_getParam('block');
    		PageTitle::setTitle($this->view, $this->_request, array(Zend_Registry::get('Zend_Translate')->_('Block'), $this->view->block));
    		$this->view->procedural_skills = $guideService->getProceduralSkillsInBlock($this->view->block);
    		
    		$sbs = new StageBlockSeqs();
    		$this->view->blockname = $sbs->getBlockName($this->view->block);
    	}
    }
    
    public function patientdoctorAction() {
    	$this->view->block = $this->_getParam('block');
    	PageTitle::setTitle($this->view, $this->_request, array($this->view->block));
    	
    	$sbs = new StageBlockSeqs();
    	$this->view->blockname = $sbs->getBlockName($this->view->block);
    	
    	$guideService = new DynamicGuideService();
    	$this->view->ptdr_tas = $guideService->getPtDrTutorialInBlock($this->view->block);
    }
}
