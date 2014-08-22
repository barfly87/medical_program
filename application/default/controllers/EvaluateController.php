<?php 
 
class EvaluateController extends Zend_Controller_Action {

    /**
     * Set up ACL info
     */
    public function init() {
        $writeActions = array('index','ta');
        $this->_helper->_acl->allow('student', $writeActions);
        $adminActions = array('studentta','taresponseschart','taresponses','tareport','tareportcsv','completionreport','completionreportresult');
        $this->_helper->_acl->allow('admin',$adminActions);        
    }

    public function indexAction() {
        $req = $this->getRequest();
        $type = $req->getParam('type','');
        $type_id = (int)$req->getParam('type_id','');
        $display = $req->getParam('display','');

        if($req->isPost()) {
            $fp = new FormProcessor_EvaluateComment();
            $fp->process($req);
            exit;
        }
        $studentEvaluateService = new StudentEvaluateService();
        $this->view->data = $studentEvaluateService->processEvaluation($type, $type_id);
        $this->view->type = $type;
        $this->view->type_id = $type_id;
        $this->view->display = $display;
        
    }
    
    public function studenttaAction() {
    	PageTitle::setTitle($this->view, $this->_request);
        $studentEvaluateService = new StudentEvaluateService();
        $this->view->formData = $studentEvaluateService->getFormVariables();
        
        $req = $this->getRequest();
        if($req->getParam('search_evaluation','') == '1') {
            $fp = new FormProcessor_StudentTaEvaluation();
            $this->view->evaluations = $fp->process($req);
            $format = trim($req->getParam('format','')); 
            if(strlen($format) > 0) {
                $studentEvaluateService->processFormat($this->view->evaluations, $format, $fp);
            }
            $this->view->fp = $fp;
        }      
    }
    
    public function taAction() {
        $taId = (int)$this->_getParam('ta_id', 0);
        if($taId <= 0) {
            $this->throwError();
        }
        $teachingActivities = new TeachingActivities();
        $ta = $teachingActivities->getTa($taId);
        $this->view->ta = $ta;
        $taTypeIdsAllowed = Compass::getConfig('evaluate.ta.activitytypeids');
        $questions = EvaluateTaConst::getConfigQuestions($ta->typeID);
        if(empty($taTypeIdsAllowed) || !in_array($ta->typeID, $taTypeIdsAllowed) || empty($questions)) {
            $this->throwError();
        }
        $this->view->questions = $questions;
        $this->view->questionsTitle = array_flip($questions);
        
        $evaluateTa = new EvaluateTa();
        $this->view->evaluation = $evaluateTa->getEvaluationForLoggedInUserForTaid($taId);
        
        $evaluateTaForm = new EvaluateTaForm();
        $this->view->form = $evaluateTaForm->getForm($this->view->evaluation, $questions, $ta);
        
        $this->view->formValid = false;
        $this->view->formSubmitted = false;
        $this->view->databaseError = false;
         
        if($this->_request->isPost()) {
            $this->view->formSubmitted = true;
            $formData = $this->_request->getPost();
            if($this->view->form->isValid($formData)) {
                $this->view->formValid = true;
                $formValues = $this->view->form->getValues();
                if($this->view->evaluation !== false) {
                    $evaluationAutoId = $this->view->evaluation[EvaluateTaConst::$EVALUATION_AUTO_ID];
                    $formValues[EvaluateTaConst::$EVALUATION_AUTO_ID] = $evaluationAutoId;
                }
                $formValues[EvaluateTaConst::$TA_AUTO_ID]  = $taId;
                $formValues[EvaluateTaConst::$TA_TYPE_ID]  = $ta->typeID;
                $formValues[EvaluateTaConst::$TA_TYPE]     = $ta->type;
                $formValues[EvaluateTaConst::$STUDENT_ID]  = UserAcl::getUid();
                $formValues[EvaluateTaConst::$DOMAIN_ID]   = UserAcl::getDomainId();
                $formValues[EvaluateTaConst::$DATETIME]    = date('Y-m-d H:i:s', time());
                $formValues[EvaluateTaConst::$ROLE]        = UserAcl::getRole();

                $evaluateTaService = new EvaluateTaService();
                $result = $evaluateTaService->processForm($formValues, $this->view->questionsTitle);
                if($result === false) {
                    $this->view->databaseError = true;
                }
            }
        }
    }
    
    public function taresponsesAction() {
        $taId = (int)$this->_getParam('ta_id', 0);
        if($taId <= 0) {
            $this->throwError();
        }
        $teachingActivities = new TeachingActivities();
        $ta = $teachingActivities->getTa($taId);
        $this->view->ta = $ta;
        
        $taTypeIdsAllowed = Compass::getConfig('evaluate.ta.activitytypeids');
        $questions = EvaluateTaConst::getConfigQuestions($ta->typeID);
        if(empty($taTypeIdsAllowed) || !in_array($ta->typeID, $taTypeIdsAllowed) || empty($questions)) {
            $this->throwError();
        }
        $this->view->questions = $questions;
        $this->view->questionsTitle = array_flip($questions);
        
        $evaluateTaService = new EvaluateTaService();
        $this->view->data = $evaluateTaService->getTaResponses($ta, $questions);
    }
    
    public function tareportAction() {
        $evaluateTa = new EvaluateTa();
        $this->view->taTypes = $evaluateTa->getUniqueTaTypesEvaluated();
        $this->_helper->layout()->setLayout('jquery_1_4_2');
    }
    
    public function tareportcsvAction() {
        $evaluateTaService = new EvaluateTaService();
        $evaluateTaService->createCSV($this->_request);
    }
    
    private function throwError() {
        throw new Zend_Controller_Action_Exception("Page not found", 404);
    }
    
    public function completionreportAction() {
        $evaluateCompletionReportForm = new EvaluateCompletionReportForm();
        $this->view->form = $evaluateCompletionReportForm->getForm();
        if($this->_request->isPost()) {
            $formData = $this->_request->getPost();
            if($this->view->form->isValid($formData)) {
                $formValues = $this->view->form->getValues();
                $evaluateCompletionReportService = new EvaluateCompletionReportService($formValues);
                $this->view->result = $evaluateCompletionReportService->getCompletionReport();
                $this->render('completionreportresult');
            }
        }
        $this->_helper->layout()->setLayout('jquery_1_4_2');
    }
    
    public function taresponseschartAction() {
        $params = $this->_getAllParams();
        $evaluateLectureService = new EvaluateLectureService();
        ob_end_clean();
        $chart = $evaluateLectureService->fetchChart($params);
        echo $chart->toPrettyString();
        exit;
    }
}