<?php

class BlockController  extends Zend_Controller_Action {
    public function init() {
        $this->_helper->_acl->allow('student',array('index','learningobjectives','get','list','fetch'));
        $this->_helper->_acl->allow('blockchair',array('manageresources','managestage1or2resources'));
    }

    public function indexAction() {
        $blockId = (int)$this->_getParam('ref', '');
        if($blockId > 0) {
            if($this->hasIntroduction($blockId) === true) {
                $url = '/block/get/me/introduction/ref/'. $blockId;
                $this->_redirect($url);
            } else {
                $this->_forward('learningobjectives','block','default',$this->_request->getParams());
            }
        } else {
            $this->throwError();
        }            
    }
    
    private function hasIntroduction($blockId) {
        $mediabankResource = new MediabankResource();
        $result = $mediabankResource->getResourcesByType($blockId, 
                        ResourceConstants::$TYPE_block, ResourceTypeConstants::$INTRODUCTION_ID);
        if($result !== false) {
            return true;
        }
        return false;
    }

    public function listAction() {
        PageTitle::setTitle($this->view, $this->getRequest());
        $blockDbService = new BlockDbService();
        $this->view->blocks = $blockDbService->getListOfBlocks();
        $taFinder = new TeachingActivities();
		$taList = $taFinder->getTaByStage("3", "Core curriculum", "name");
        
        $this->view->corecurriculum = $taList;
    }
    
    public function fetchAction() {
        $data = $this->processPage(BlockConst::$pageFetch);
        $this->renderTemplate();
    }
    
    public function learningobjectivesAction() {
        $data = $this->processPage(BlockConst::$pageLearningObjectives);
        $this->renderTemplate();
    }
    
    public function manageresourcesAction() {
        $data = $this->processPage(BlockConst::$pageManageResources);
        $this->renderTemplate();
    }
    
    public function managestage1or2resourcesAction() {
        if(UserAcl::isBlockchairOrAbove()) {
            $this->view->blockId = $this->_getParam('ref', '');
            $blocks = new Blocks();
            $this->view->blockName = (!empty($this->view->blockId)) ? 
                                        $blocks->getBlockName($this->view->blockId) : '';
        } else {
            throw new Zend_Controller_Action_Exception("You are not authorized to view this page.");
        }                                    
    }
    
    public function getAction() {
        $data = $this->processPage(BlockConst::$pageGet);
        $this->renderTemplate();
    }
    
    /**
     * PBL and Block Controller use the same view. So please make sure any changes made in this function is also
     * reflected in Block Controller. 
     */
    private function processPage($page) {
        $return = new stdClass;
        $request = $this->getRequest();
        //Get the class for form to process and which page to render
        $factory = $this->pageFactory($page);
        if(!property_exists($factory,'fp') || !property_exists($factory,'renderPage')) {
            $this->throwError();
        }   
        
        //Get the form process class to be processed        
        $fp = $factory->fp;
        $fp->process($request);
        
        //If any error found throw error
        if($fp->error) {
            $this->throwError();
        }
        
        //Set page title
        PageTitle::setTitle($this->view, $request, $fp->getPageTitle());
        
        //Get the pbl details which would be common to all the pages from 'FormProcessor_Block_Init' class
        //They are mostly used by 'blockmenu.phtml' file for displaying left hand side menu.
        $blockDetails = $fp->getBlockDetails();
        $this->view->assign($blockDetails);
        
        //Requested parameters should be given to $factory->page for any further logic that needs to be implemented 
        //and also to front end
        $requestParams = $fp->getRequestParams();
        $this->view->assign(array('requestParams' => $requestParams));
        $this->view->assign($fp->getFormsProcessed());

        $page = $factory->page;
        //Pass the block and request information to the 'page' class to be used for further processing.
        $page->setBlockDetails($blockDetails);
        $page->setRequestParams($requestParams);
        
        //getPageDetails basically returns anything that is processed by the 'page' class.
        $this->view->assign($page->getPageDetails());
        
        //getDynamicMenuLinks basically checks for all the resources which exist and create menu links accordingly.
        $dynamicMenuLinks = array('dynamicMenuLinks' => $page->getDynamicMenuLinks());
        $this->view->assign($dynamicMenuLinks);

        //PBL and Block interface both use the same static method since they share the same 'view'    
        $this->view->assign(PblBlockConst::getModuleControllerAction());
        
        //Tells template.phtml file which page to render
        $this->view->renderPage = $factory->renderPage;
        
        $this->view->pblOrBlock = PblBlockConst::$block;
        $this->view->pblOrBlockRef = $blockDetails['blockId'];
        $this->view->pblOrBlockId = $blockDetails['blockId'];
        $this->view->pblOrBlockName = $blockDetails['blockName'];
        
        $return->fp = $fp;
        $return->blockDetails = $blockDetails;
        $return->page = $page;
        return $return;
    }

    private function pageFactory($page) {
        $return = new stdClass;
        
        switch($page) {
            case BlockConst::$pageLearningObjectives:
                $return->fp = new FormProcessor_Block_LearningObjective();             
                $return->renderPage = BlockConst::$pageLearningObjectives;
                $return->page = new BlockLearningObjective();
            break;
            case BlockConst::$pageManageResources:
                $return->fp = new FormProcessor_Block_ManageResources();
                $return->renderPage = BlockConst::$pageManageResources;
                $return->page = new BlockManageResources();
            break;
            case BlockConst::$pageGet:
                $return->fp = new FormProcessor_Block_Get();
                $return->renderPage = BlockConst::$pageGet;
                $return->page = new BlockGet();
            break;
            case BlockConst::$pageFetch:
                $return->fp = new FormProcessor_Block_Fetch();
                $return->renderPage = BlockConst::$pageFetch;
                $return->page = new BlockFetch();
            break;
        }
        
        return $return;
    }
    
    
    private function renderTemplate() {
        $this->render('pbl/fragments/template',null,true);
    }

    private function throwError() {
        throw new Zend_Controller_Action_Exception("Page not found.", 404);
    }
    
    
}

?>
