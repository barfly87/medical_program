<?php
class SearchDefaultService {
    
    public $userId = '';
    public $luceneFields = array();
    public $searchConfigure = array();
    public $methods = array();
    public $columns = array();
    public $alreadyConfigured = array();
    
    public function SearchDefaultService($process,$searchType,$context){
        $this->userId = $this->getUserId();
        $this->luceneFields = $this->getLuceneFields($process,$context);
        $this->searchConfigure = $this->getSearchConfig($searchType);
        $this->methods = $this->getMethods($process);
        if(isset($this->luceneFields['luceneFieldIds'])) {
            $this->columns = $this->getColumnNames($this->luceneFields['luceneFieldIds']);
        }
    }

    private function getMethods($process){
        $result = array();
        if(isset(SearchConstants::$config['formprocessor'][$process]['methods'])) {
            $methodIds = SearchConstants::$config['formprocessor'][$process]['methods'];
            foreach($methodIds as $methodId) {
                if(isset(SearchConstants::$methods[$methodId])) {
                    array_push($result,SearchConstants::$methods[$methodId]);
                }
            }
        }
        return $result;
    }

    public function getColumnNames ($columnIds){
        $columns = array();
        $columns = SearchConstants::columns();
        foreach($columnIds as $columnId) {
            if(isset($columns[$columnId]['displayName'])){
                $columns[$columnId]= $columns[$columnId]['displayName'];
            }
        }
        return $columns;
    }
    
    private function getUserId(){
        $auth = Zend_Auth::getInstance();
        if ($auth->hasIdentity()) {
            $user = $auth->getIdentity();
            return $user->user_id;
        } 
        return 'unknown';
    }
    
    private function getLuceneFields($process,$context)   {
        $result = array();
        if($process == 'simple' || $process == 'advanced'){
            if(!empty($context) && isset(SearchConstants::$config['columns'][$context])) {
                return $this->getLuceneDetails($context);
            }
        } else if(isset(SearchConstants::$config['columns'][$process])) {
            return $this->getLuceneDetails($process);
        }
        return array();
    }
    
    private function getLuceneDetails($context){
        $luceneFieldIds =  $this->getConfiguredFields($context);
        $linkIds = array_keys(SearchConstants::$columnLink[$context]);
        //If its a podcast search we don't need $linkHref because it is not used in RSS creation
        $linkHref = (SearchConstants::getSearchFormatSession() == SearchConstants::$formatPodcast) ?
                    array() : $this->getLinkHref($context, $linkIds);
        return array(
                    'luceneFieldNames' => $this->getLuceneFieldNames($luceneFieldIds),
                    'luceneFieldIds' => $luceneFieldIds,
                    'linkHref' => $linkHref
                );
    }
    
    private function getConfiguredFields($context){
        if(! isset($this->alreadyConfigured[$context])) {
            $systemDefaultColumnIds = SearchConstants::$config['columns'][$context];
            $mandatoryColumnIds = SearchConstants::$config['mandatoryColumns'][$context];

            // If the format is in session and if class 'SearchConstants' as only specific lucene fields
            // for this format to be returned, just return those without worrying about what user as configured
            // for themselves
            $formatInSession = SearchConstants::getSearchFormatSession();
            if(!empty($formatInSession) && isset(SearchConstants::$format[$formatInSession]['luceneFieldIds'])) {
                $userColumnIds = SearchConstants::$format[$formatInSession]['luceneFieldIds'];
            } else {
                $searchConfigure = new SearchConfigure();
                $userColumnIds = $searchConfigure->getColumns($this->userId,$context);
            }
            if($userColumnIds != false && !empty($userColumnIds)) {
                $this->alreadyConfigured[$context] = $this->processColumnIds(explode(',',$userColumnIds),$mandatoryColumnIds);
            } else {
                $this->alreadyConfigured[$context] = $this->processColumnIds($systemDefaultColumnIds,$mandatoryColumnIds);
            }
        }
        return $this->alreadyConfigured[$context];
    }
            
    private function processColumnIds($columnIds, $mandatoryColumnIds){
        $temp = array();
        $columns = SearchConstants::columns();
        foreach($columnIds as $luceneKey) {
            if(isset($columns[$luceneKey])) {
                $temp[] = $luceneKey;
            }
        }
        $columnIds = $temp;
        foreach($mandatoryColumnIds as $mandatoryColumnId) {
            if(!in_array($mandatoryColumnId, $columnIds)){
                array_unshift($columnIds, $mandatoryColumnId);
            }
        }
        return $columnIds;
    }
    
    private function getLinkHref($process, $linkIds){
        $result = array();
        $columns = SearchConstants::columns();
        foreach($linkIds as $linkId) {
            if(isset(SearchConstants::$columnLink[$process][$linkId])){
                $href_id = SearchConstants::$columnLink[$process][$linkId];
                $luceneFieldName = $columns[$linkId]['luceneIndex'];
                $result[$luceneFieldName] = SearchConstants::$links[$href_id];
            }
        }
        return $result;
    }
    
    private function getLuceneFieldNames($luceneFieldIds){
        $result = array();
        $columns = SearchConstants::columns();
        foreach($luceneFieldIds as $luceneFieldId) {
            if(isset($columns[$luceneFieldId]['luceneIndex'])) {
                array_push($result, $columns[$luceneFieldId]['luceneIndex']);
            }
        }
        return $result;
    }
    
    private function getSearchConfig($searchType){
        if($searchType == 'main') {
            return $this->processSearchConfig(array('lo','ta'));    
        } else {
            return $this->processSearchConfig(array($searchType));
        }
    }
    
    private function processSearchConfig($contexts){
        $result = array();
        foreach($contexts as $context) {
            if(isset(SearchConstants::$config['configureSearch'][$context])) {
                $searchConfigure = SearchConstants::$config['configureSearch'][$context];
                $result[$context]['checkBoxes'] = SearchConstants::processSearchConfigureCheckBoxes($searchConfigure['checkBoxes']); 
                $result[$context]['configuredColumns'] = $this->getConfiguredFields($context);
                $result[$context]['mandatoryColumns'] =  SearchConstants::$config['mandatoryColumns'][$context];
                $result[$context]['systemDefaults'] =  array_merge(
                                                            SearchConstants::$config['columns'][$context],
                                                            array(SearchConstants::getTaResourceLuceneField())
                                                       );
                $result[$context]['heading'] =  $searchConfigure['heading'];
                $result[$context]['title'] =  $searchConfigure['title'];
            }
        }
        return $result;
    }
    
}
?>