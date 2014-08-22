<?php
class SearchQuickQueriesService {
    
    public $userId = '';
    public $luceneFields = array();
    public $searchConfigure = array();
    public $methods = array();
    public $columns = array();
    public $group = null;
    public $quickQuery = null;
    
    public function SearchQuickQueriesService($process,$searchType,$context){        
        $this->splitProcess($process);
        $this->methods = $this->getMethods($process);        
        $this->luceneFields = $this->getLuceneFields($process,$context);
        $this->columns = $this->getColumnNames($this->luceneFields['luceneFieldIds']);
    }

    private function createSortString($sortColumns){
        $sortString = '';
        $sortArray = array();
        $columns = SearchConstants::columns();
        foreach($sortColumns as $sortColumn => $sort){
            if(isset($columns[$sortColumn]['luceneIndex'])) {
                $sortArray[] = "'".$columns[$sortColumn]['luceneIndex']."'";
                if(isset($sort['sortType'])) {
                    $sortArray[] = $sort['sortType'];
                }
                if(isset($sort['sortOrder'])) {
                    $sortArray[] = $sort['sortOrder'];
                }
            }
        }
        $count = count($sortArray);
        if($count > 0) {
            $sortString = ($count == 1) ? $sortArray[0] : implode(',', $sortArray);
        }
        return $sortString;
    }
    
    public function splitProcess($process){
        $split = explode("|", $process);
        $this->group = $split[0];
        $this->quickQuery = $split[1];
    }
    
    private function getMethods($process){
        
        $result = array();
        $QQueries = SearchConstants::quickQueries();
        if(isset($QQueries[$this->group]['queries'][$this->quickQuery]['lucene']['methods'])) {
            $methodIds = $QQueries[$this->group]['queries'][$this->quickQuery]['lucene']['methods'];
            $methodIds = array_merge(array('m_1'),$methodIds);
            foreach($methodIds as $methodId) {
                if(isset(SearchConstants::$methods[$methodId])) {
                    array_push($result,SearchConstants::$methods[$methodId]);
                }
            }
        }
        return $result;
    }

    private function getLuceneFields($process,$context)   {
        $result = array();
        $columns = SearchConstants::columns();
        $QQueries = SearchConstants::quickQueries();
        if(isset($QQueries[$this->group]['queries'][$this->quickQuery]['lucene'])) {
            $quickQuery = $QQueries[$this->group]['queries'][$this->quickQuery]['lucene'];
            $columnIds = $quickQuery['columns'];
            $linkIds = array_keys($quickQuery['columnLink']);
            
            $linkHref = array();
            foreach($linkIds as $linkId) {
                $href_id = $quickQuery['columnLink'][$linkId];
                $luceneFieldName = $columns[$linkId]['luceneIndex'];
                $linkHref[$luceneFieldName] = SearchConstants::$links[$href_id];
            }
            
            return array(
                'luceneFieldNames' => $this->getLuceneFieldNames($columnIds),
                'luceneFieldIds' => $columnIds,
                'linkHref' => $linkHref
            );
        } else {
            return array(
                'luceneFieldNames' => array(),
                'luceneFieldIds' => array(),
                'linkHref' => array()
            );
        }
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

   public function getColumnNames ($columnIds){
        $columns = array();
        $columns = SearchConstants::columns();
        foreach($columnIds as $columnId) {
            if(isset($columns[$columnId]['displayName'])) {
                $columns[$columnId]= $columns[$columnId]['displayName'];
            }
        }
        return $columns;
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
}
?>