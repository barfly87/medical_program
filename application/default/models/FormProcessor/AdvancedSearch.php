<?php
class FormProcessor_AdvancedSearch extends FormProcessor {

    public $queryStr = '';
    public $format = '';
    private $request = null;
    
    public function process(Zend_Controller_Request_Abstract $request)  {
        $this->request = $request;
        $process = $this->sanitize($this->request->getParam('process'));
        $searchType = $this->sanitize($this->request->getParam('searchtype'));
        $context    = $this->sanitize($this->request->getParam('context'));
        $this->format = $this->sanitize($this->request->getParam('format',''));
        SearchConstants::setSearchFormatSession($this->format);
        if($searchType == 'qq') {
            $this->processQuickQueries($process, $searchType, $context);
        } else {
            $this->processDefault($process, $searchType, $context);
        }
    }

    private function processQuickQueries($process, $searchType, $context){
        $searchServiceQuickQueries = new SearchQuickQueriesService($process, $searchType, $context);
        $methods = $searchServiceQuickQueries->methods;
        foreach($methods as $method) {
            $this->$method();
        }
        $this->luceneFields         = $searchServiceQuickQueries->luceneFields;
        $this->columns              = $searchServiceQuickQueries->columns;
    }
     
    private function processDefault($process, $searchType, $context){
        $searchService = new SearchDefaultService($process, $searchType, $context);
        $methods = $searchService->methods;
        foreach($methods as $method) {
            $this->$method();
        }
        $this->luceneFields         = $searchService->luceneFields;
        $this->columns              = $searchService->columns;
        $this->searchConfigure      = $searchService->searchConfigure;
        $this->userId               = $searchService->userId;
    }

    private function processSearchHidden(){
        $this->search = $this->sanitize($this->request->getParam('search')); 
    }
    
    private function processContext(){
        $this->context = $this->sanitize($this->request->getParam('context'));
        //$this->queryStr .= ($this->context == 'lo') ? '+doctype:("Learning Objective" OR Linkage)':
        //                                            '+doctype:("Teaching Activity" OR Linkage)';
        $this->queryStr .= '+doctype:Linkage';
        $identity = Zend_Auth::getInstance()->getIdentity();
        if ('student' == $identity->role) {
        	$domain_qstr = array();
        	foreach ($identity->all_domains as $domain) {
        		$domain_qstr[] = "(ta_audience:{$domain} AND lo_audience:{$domain})";
        	}
        	$this->queryStr .= " +(". join(" OR ", $domain_qstr). ")";
        	$stage3 = (int)(date('Y')) - 2;
        	$this->queryStr .= " +(";
        	//stage 3 student can always see stage 1 and 2 material
        	if ($identity->cohort <= $stage3) {
        		$this->queryStr .= 'ta_stage:"2" ta_stage:"1" ';
        	}
        	//stage 2 student can always see stage 1 material
        	if ($identity->cohort == $stage3 + 1) {
        		$this->queryStr .= 'ta_stage:"1" ';
        	}
        	if (isset(Zend_Registry::get('config')->event_wsdl_uri)) {
	        	foreach ($identity->groups as $group) {
	        		$this->queryStr .= "releasedate_$group:[0 TO ".strtotime('now')."] ";
	        	}
        	} else {
        		$stage = date('Y') - $identity->cohort + 1;
        		$this->queryStr .= 'ta_stage:"'.$stage.'" ';
        	}
        	$this->queryStr .= ")";
        }
        
    }
   
    private function processQstrQoption() {
        
        $this->qoption = $this->sanitize($this->request->getParam('qoption'));
        $this->qstr = stripslashes($this->sanitize($this->request->getParam('qstr')));
        if (trim($this->qstr) != '') {
            if ($this->qoption == 'all') {
                $this->queryStr .= ' +(';
                $strArr = explode(' ', $this->qstr);
                foreach ($strArr as $str) { 
                        $this->queryStr .= ' +'.$str;
                }
                $this->queryStr .= ')';
            } else if ($this->qoption == 'any') {
                $this->queryStr .= ' +('. str_replace(' ' , ' OR ' ,trim($this->qstr)) . ')';
            } else if ($this->qoption == 'lucene') {
                $this->queryStr .= ' +('.$this->qstr. ')';
            } else {
                $this->queryStr .= ' +"'.$this->qstr. '"';
                }
            }
    }
    
    private function processDomain() {
    	$this->domain = $this->request->getParam('domain');
    	$domains = array();
    	$domainStr = array();
    	if (is_array($this->domain) && count($this->domain) > 0 && !(in_array('Any',$this->domain))) {
    		$this->queryStr .= ' +(';
    	    foreach ($this->domain as $domain) {
                 $domains[] = $domain;
                 $domainStr[] = "(ta_audience:{$domain} AND lo_audience:{$domain})";
            }
            $this->queryStr .= implode(' OR ', $domainStr);
    		$this->queryStr .= ') ';
    	}
    	$this->domain = count($domains) > 0 ? $domains : array('Any');
    }
    
    private function processSessionDomain() {
    	$identity = Zend_Auth::getInstance()->getIdentity();
    	$this->queryStr .= " +(ta_audience:{$identity->domain} AND lo_audience:{$identity->domain}) ";
    }
    
    private function processDiscipline() {
        
        $disciplines = array();
        $disciplineIds = $this->request->getParam('discipline');
        if(is_array($disciplineIds) && count($disciplineIds) > 0) {
            foreach($disciplineIds as $id) {  
                $sanitize_id = (int)trim($id) ;
                if( $sanitize_id > 1) {      
                    array_push($disciplines,$sanitize_id);
                }
            }
        }        
        $this->disciplines = $disciplines;
        $selected = array();
        if(count($this->disciplines) > 0) {
            $this->queryStr .= ' + (';
            foreach($this->disciplines as $discipline) {
                $selected[$discipline] = true;
                $this->queryStr .=  'lo_discipline_ids:"'. $discipline .'" ';
            }
            $this->queryStr .= ' ) ';
        }
        $this->selected = $selected;
        
    }
    
    private function processStage() {
        $this->stage = $this->request->getParam('stage');
        $stages = array();
        if(is_array($this->stage) && count($this->stage) > 0 && !(in_array('Any',$this->stage))) {
            $this->queryStr .= ' + (';
            foreach($this->stage as $stage) {
                $stage = $this->sanitize($stage);
                if($stage > 0) {
                    $stages[] = $stage;
                    $this->queryStr .= 'ta_stage:"'.$stage.'" ';
                }
            }
            $this->queryStr .= ' ) ';
        } 
        $this->stage = count($stages) > 0 ? $stages : array('Any');
    }
    
    private function processBlock() {
        $this->block = $this->sanitize($this->request->getParam('block'));  
        if ($this->block != '' && $this->block != 'Any') {
            $this->queryStr .= ' +ta_block:"'.$this->block.'"';
        }
    }
    
    private function processWeekOfBlock() {
        $this->blockweek = (int)($this->request->getParam('blockweek'));
        if ($this->blockweek != '') {
            $this->queryStr .= ' +ta_block_week:'.$this->blockweek;
        }        
    }
    
    private function processPbl() {
        $this->pbl = $this->sanitize($this->request->getParam('pbl'));
        if ($this->pbl != '' && $this->pbl != 'Any') {
            $pbl = str_replace('?', '', stripslashes($this->pbl));
            $this->queryStr .= ' +ta_pbl:"'.$pbl.'"';
        }
    }
    
    private function processTheme(){
        $this->theme = $this->sanitize($this->request->getParam('theme'));
        if ($this->theme != '' && $this->theme != 'Any') {
            $this->queryStr .= ' +lo_theme:"'.$this->theme.'"';
        }        
    }
    
    private function processActivityType(){
        $activityTypesSanitized = array();
        $activityTypes = $this->request->getParam('acttype');
        if(is_array($activityTypes) && count($activityTypes) > 0) {
            foreach($activityTypes as $activityType) {  
                $sanitizeActivityType = trim($activityType) ;
                if( strlen($sanitizeActivityType) > 1) {      
                    array_push($activityTypesSanitized, $sanitizeActivityType);
                }
            }
        }        
        $this->acttypes = $activityTypesSanitized;
        $activityTypesSelected = array();
        if(count($this->acttypes) > 0) {
            $this->queryStr .= ' + (';
            foreach($this->acttypes as $acttype) {
                $activityTypesSelected[$acttype] = true;
                $this->queryStr .=  'ta_type:"'. $acttype .'" ';
            }
            $this->queryStr .= ' ) ';
        }
        $this->activityTypesSelected = $activityTypesSelected;
    }
    
    private function processSkills(){
        $this->skill = $this->sanitize($this->request->getParam('skill')); 
        if ($this->skill != '' && $this->skill != 'Any') {
            $this->queryStr .= ' +lo_skill:"'.$this->skill.'"';
        }
    }

    private function processCurriculumArea(){
        $this->curriculumarea = trim($this->sanitize($this->request->getParam('curriculumarea'))); 
        if ($this->curriculumarea != '' && $this->curriculumarea != 'Any') {
            $this->queryStr .= ' +(lo_curriculumarea1:"'.$this->curriculumarea.'" OR lo_curriculumarea2:"'.$this->curriculumarea.'" OR lo_curriculumarea3:"'.$this->curriculumarea.'")';
        }
    }

    private function processQuickQueryStudent1(){
        $limit = (int)$this->request->getParam('limit') > 0 ? (int)$this->request->getParam('limit') : 10;
        $searchServiceQuickQueriesSql = new SearchQuickQueriesSqlService();
        $lo_auto_ids = $searchServiceQuickQueriesSql->processStudent1($limit);
        if($lo_auto_ids != false) {
            $this->queryStr .= ' +lo_auto_id:('.implode(' OR ', $lo_auto_ids).')';
            $this->unique = 'lo_auto_id';
        }
    }
    
    private function processQuickQueryStudent2(){
        $limit = (int)$this->request->getParam('limit') > 0 ? (int)$this->request->getParam('limit') : 10;
        $searchServiceQuickQueriesSql = new SearchQuickQueriesSqlService();
        $ta_auto_ids = $searchServiceQuickQueriesSql->processStudent2($limit);
        if($ta_auto_ids != false) {
            $this->queryStr .= ' +ta_auto_id:('.implode(' OR ', $ta_auto_ids).')';
            $this->unique = 'ta_auto_id';
        }
    }
    
    private function processQuickQueryStaff1() {
        $auth = Zend_Auth::getInstance();  
        if ($auth->hasIdentity()) {  
            $user_id = $auth->getIdentity()->user_id;
            $this->queryStr .= ' +ta_principal_teacher:"'.$user_id.'"';
            $this->unique = 'lo_auto_id';
        }
    }
    
    private function processQuickQueryStaff2() {
        $auth = Zend_Auth::getInstance();  
        if ($auth->hasIdentity()) {  
            $user_id = $auth->getIdentity()->user_id;
            $this->queryStr .= ' +ta_principal_teacher:"'.$user_id.'"';
            $this->unique = 'to_auto_id';
        }
    }

}
?>