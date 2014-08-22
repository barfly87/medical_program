<?php
class SearchViewService {
    
    public function getFormVariables(){
        return array(
        	'domains'			=> $this->getDomains(),
            'disciplines'       => $this->getDisciplines(), 
            'disciplineIdsTree' => $this->getTreeOfDisciplineIds(),       
            'stages'            => $this->getStages(),
            'blocks'            => $this->getBlocks(),
            'weeks'             => $this->getBlockWeeks(),
            'pbls'              => $this->getPbls(),
            'themes'            => $this->getThemes(),
            'types'             => $this->getActivityType(),
        	'skills'            => $this->getSkills()
        );
        
    }
    
    public function getDomains() {
    	$domainFinder = new Domains();
    	return $domainFinder->getDomainNames('auto_id');
    }
    
    public function getDisciplines() {
        $disciplineService = new DisciplineService();
        return $disciplineService->getListOfDisciplines();
    }
    
    public function getTreeOfDisciplineIds() {
        $disciplineService = new DisciplineService();
        return $disciplineService->getTreeOfDisciplineIds();
    }
    
    public function getStages(){
        $stageFinder = new Stages();
        $stages = $stageFinder->getAllStages();
        return $this->processStages($stages);
    }
    
    public function getBlocks(){
        $blockFinder = new Blocks();
        $blocks = $this->removeEmptyValues($blockFinder->getAllNames('auto_id ASC'));
        return $this->createSelectOptions($blocks);
    }
    
    public function getBlockWeeks(){
        $blockweekFinder = new BlockWeeks();
        $blockweeks = $this->removeEmptyValues($blockweekFinder->getAllWeeks());
        return $this->createSelectOptions($blockweeks);
    }
    
    public function getPbls(){
        $pblFinder = new Pbls();
        $pbls = $this->removeEmptyValues($pblFinder->getAllNames());
        return $this->createSelectOptions($pbls);
    }
    
    public function getThemes(){
        $themeFinder = new Themes();
        $themes = $this->removeEmptyValues($themeFinder->getAllNames());
        return $this->createSelectOptions($themes);
    }
    
    public function getActivityType(){
        $typeFinder = new ActivityTypes();
        $activityTypes = $this->removeEmptyValues($typeFinder->getAllNames());  
        return $this->createSelectOptions($activityTypes);
    }
    
    public function getSkills() {
    	$skillFinder = new Skills();
    	$skills = $this->removeEmptyValues($skillFinder->getAllNames());
    	return $this->createSelectOptions($skills);
    }
    
    private function removeEmptyValues($array){
        foreach($array as $key => $value ){
            if(trim($value) == '') {
                unset($array[$key]);
            }
        }
        return $array;
    }

    private function processStages($array){
        $return = array();
        foreach($array as $key => $value ){
            if(trim($value) != '') {
                $return[$value] = $value;
            }
        }
        return $return;
    }
     
    private function createSelectOptions($arr){
        $result = array ();
        //Change key=>value to value=>value for select options
        foreach($arr as $val) {
            $result[$val] = $val;
        }
        return $result;
    }
    
    public function processQuickQueries($queries){
        foreach($queries as $group=>&$config ) {
            foreach($config['queries'] as $key=>&$query) {
                $query['question'] = $this->processQuestion($query['question']);
            }
        }
        return $queries;
    }
    
    private function processQuestion($question){
        $explode = explode('###',$question);
        $explode_count = count($explode);                   
        for($z=1; $z<$explode_count; $z+=2) {
           $explode[$z] = '###'.SearchConstants::$placeholder[$explode[$z]].'###';
        }
        return implode('',$explode);
    }
}