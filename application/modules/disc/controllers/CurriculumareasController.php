<?php
class Disc_CurriculumareasController extends Zend_Controller_Action { 
    
    private $disc_id = null;
    private $disc_row = null;
    private $action_name = null;
    
    public function init() {
        $writeActions = array('index','list','add','save','delete','sort','archive');
        $this->_helper->_acl->allow('staff', $writeActions);
        
        $disciplineService = new DisciplineService();
        
        //Check if user is compass admin. If not they cannot edit
        $this->isUserCompassAdmin($disciplineService);

        $request = $this->getRequest();  
        $disc_id = (int)$request->getParam('disc_id','');
        
        //Check if discipline exist
        $row = $this->getDisciplineDetails($disciplineService, $disc_id);
                  
        //Check if user is allowed to edit this page
        $this->allowEdit($disciplineService, $row);
                    
        $this->disc_id = $disc_id;
        $this->disc_row = $row;
        $this->action_name = $request->getActionName();
        $this->view->assign($this->getCommonVariables());
    }

    public function indexAction() {              
        $this->_redirect(DisciplineService::$listLink);
    }
    
    public function listAction(){
        $request = $this->getRequest();
        PageTitle::setTitle($this->view, $request);
        $disc_id = (int)$request->getParam('disc_id','');
        $curriculumAreasService =  new CurriculumAreasService();
        $this->view->listOfCurriculumAreas =  $curriculumAreasService->getListOfCurriculumAreas($disc_id);
        $this->view->discId = $disc_id;
    }
    
    public function addAction(){
        $request = $this->getRequest();
        PageTitle::setTitle($this->view, $request);
        $this->view->assign($this->getCommonVariables());
        if ($request->isPost()) {
            $curriculumarea = $request->getParam('curriculumarea','');
            if(!empty($curriculumarea)) {
                $curriculumAreasService =  new CurriculumAreasService();
                $curriculumAreasService->add($this->disc_id,$curriculumarea);
                $this->_redirect('/disc/curriculumareas/list/disc_id/'.$this->disc_id);    
            }           
        } 
    }
    
    public function saveAction(){
        $request = $this->getRequest();
        $auto_id = (int)$request->getParam('la_id','');
        $curriculumarea = $request->getParam('curriculumarea','');
        
        $curriculumAreasService =  new CurriculumAreasService();
        $curriculumAreasService->save($auto_id, $this->disc_id, $curriculumarea);
        $this->_redirect('/disc/curriculumareas/list/disc_id/'.$this->disc_id);
    }
    
    public function deleteAction(){
        $request = $this->getRequest();
        $la_id = $request->getParam('la_id','');
        if(!empty($la_id)) {
            $curriculumAreasService =  new CurriculumAreasService();
            $result = $curriculumAreasService->delete($la_id);
            $this->_redirect('/disc/curriculumareas/list/disc_id/'.$this->disc_id);
        }
        
    }
    
    public function archiveAction(){
        $request = $this->getRequest();
        $ca_id = $request->getParam('ca_id','');
        if(!empty($ca_id)) {
            $curriculumAreasService =  new CurriculumAreasService();
            $result = $curriculumAreasService->archive($ca_id);
            $this->_redirect('/disc/curriculumareas/list/disc_id/'.$this->disc_id);
        } else {
            $this->throwError();
        }
        
    }
    
    public function sortAction() {
        $result = 'fail';
        $request = $this->getRequest();
        $disc_id = (int)$request->getParam('disc_id',0);
        $curriculumAreaIds = $request->getParam('curriculumAreaIds','');

        if(empty($disc_id) || empty($curriculumAreaIds) || $disc_id <= 0 || !is_array($curriculumAreaIds) || !count($curriculumAreaIds) > 0) {
            echo $result;        
        }
        
        $curriculumAreas = new CurriculumAreas();
        $sort = $curriculumAreas->sort($disc_id, $curriculumAreaIds);
        
        echo 'successful';
        exit;
    }
    
    private function getCommonVariables(){
        return array(
                    'disciplineName'    => $this->disc_row['name'],
                    'disciplineId'      => $this->disc_id,
                    'actionName'        => $this->action_name
        );
    }

    private function isUserCompassAdmin($disciplineService){
        $admin = $disciplineService->isUserCompassAdmin();
        if(!$admin) {
            $this->_redirect(DisciplineService::$listLink);
        }        
    }
    
    private function getDisciplineDetails($disciplineService, $disc_id){
        
        if(empty($disc_id) || $disc_id <= 1){
            $this->_redirect(DisciplineService::$listLink);
        }
        
        //Get row for this discipline id
        $row = $disciplineService->getRowForId($disc_id);
        
        // If row does not exist for this id, redirect to 'List' Page
        if($row === false) {
            $this->_redirect(DisciplineService::$listLink);
        } 
        return $row;
    }
    
    private function allowEdit($disciplineService, $row){
        //Check if user can edit this page
        $isAllowEdit = $disciplineService->allowEdit($row);
        if(!$isAllowEdit) {
            $this->_redirect(DisciplineService::$listLink);
        }         
    }
    
    private function throwError() {
        throw new Zend_Controller_Action_Exception("Page not found.", 404);
    }
    
}
