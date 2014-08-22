<?php
class SearchResultsService {
    private $columns = '';
    private $indexedLuceneFields = array();
    public function getSearchResults($searchtype, $process, $fp){
        $luceneResults = $this->processLuceneResults($process,$fp->queryStr);
        $results  = $this->processResults($fp->luceneFields, $luceneResults, $fp->context);
        $return = array(
            'process'           => $process,
            'results'           => $results,
            'columns'           => $this->columns,
            'displayresult'     => true
        );
        $url = str_replace(Compass::baseUrl().'/htdocs',Compass::baseUrl(),$this->getUrl());
        $return['url'] = $url;
        
        if($searchtype == 'qq') {
            $return['quickQueriesProcessed'] = true;
        } else {
            $return['encryptedUrl'] = $this->encryptString($url);
            $return['searchConfigure'] = $fp->searchConfigure;
            $return['userId'] = $fp->userId;
        }
        return $return;
    }
    
    public function processLuceneResults($process, $queryStr){
        $index = Compass_Search_Lucene::open(SearchIndexer::getIndexDirectory());
        $results = $index->find($queryStr);
        $this->indexedLuceneFields = array_values($index->getFieldNames());
        Zend_Registry::get('logger')->DEBUG(__METHOD__ . ": ".
            "\nPROCESS                : ".$process.
            "\nQUERY                  : ".$queryStr.
            "\nTOTAL RESULTS RETURNED : ".count($results)
        );
        return $results;
    }

    public function processResults($luceneFields, $results, $context='lo') {
        try {
            $contextResults = array();
            $hrefResults = array();
            $subContextResults = array();
            $count = 0;
            $config = $this->contextConfig($context, $luceneFields);
            if(!empty($config)) {
                foreach($results as $result) {
                    $subContextCount = 0;
                    if(isset($subContextResults[$result->$config['id']])) {
                        $subContextCount = count($subContextResults[$result->$config['id']]);            
                    }
                    foreach($luceneFields['luceneFieldNames'] as $luceneField) {
                        if(in_array($luceneField, $this->indexedLuceneFields)){
                            if(strpos($luceneField, $config['lookFor']) !== false ) {
                                $contextResults[$result->$config['id']][$luceneField] = $result->$luceneField;
                            } else if(strlen(trim($result->$luceneField)) > 0) {
                                $subContextResults[$result->$config['id']][$subContextCount] = true; 
                            }
                        }
                    }
                    if(isset($subContextResults[$result->$config['id']][$subContextCount])) {
                        unset($subContextResults[$result->$config['id']][$subContextCount]);
                        foreach($luceneFields['luceneFieldNames'] as $luceneField) {
                            if(in_array($luceneField, $this->indexedLuceneFields)){
                                if(strpos($luceneField, $config['lookFor']) === false) {
                                    $subContextResults[$result->$config['id']][$subContextCount][$luceneField] = trim($result->$luceneField);
                                }
                            }
                        }
                    }
                    
                    $count++;
                    if(isset($luceneFields['linkHref'])) {
                        foreach($luceneFields['linkHref'] as $key => $value){
                           $explode = explode('%%%',$value);
                           $explode_count = count($explode); 
                           $lucenefieldEmpty = false;            
                           for($z=1; $z<$explode_count; $z+=2) {
                               if(strlen(trim($result->$explode[$z])) == 0 ) {
                                   $lucenefieldEmpty = true;
                               }                               
                               $explode[$z] = $result->$explode[$z];
                           }
                           if($lucenefieldEmpty === false) {
                               $hrefResults[$result->$config['id']][$key] = implode('',$explode);
                           }
                        }  
                    }  
                }
                return array (
                    'context'        =>  $contextResults,
                    'subContext'     =>  $subContextResults,
                    'href'           =>  $hrefResults
                );
            }
        } catch(Exception $ex) {
            Zend_Registry::get('logger')->info($ex->getMessage());
            return array (
                'context'        =>  array(),
                'subContext'     =>  array(),
                'href'           =>  array()
            );
        }
    }
    
    private function contextConfig($context, $luceneFields){
        $result = array();
        switch($context) {
            case 'lo' : 
                $result['id']                   = 'lo_auto_id';        
                $result['lookFor']              = 'lo_';
                $result['subContext']           = 'ta';
                $this->columns                  = $this->getColumnNames($context, $luceneFields);
                if(isset($this->columns['subColumn']) && count($this->columns['subColumn']) > 0) {
                    $this->columns['column'][]      = 'Teaching Activities Attached';   
                }             
            break;
            case 'ta' :
                $result['id']                   = 'ta_auto_id';        
                $result['lookFor']              = 'ta_';
                $result['subContext']           = 'lo';
                $this->columns = $this->getColumnNames($context, $luceneFields);
                if(isset($this->columns['subColumn']) && count($this->columns['subColumn']) > 0) {
                    $this->columns['column'][] = 'Learning Objectives Attached';
                }
            break;
        }
        return $result;
    }
    
    
    private function getColumnNames($context, $luceneFields){
        $result = array();
        $columns = SearchConstants::columns();
        foreach($luceneFields['luceneFieldNames'] as $key => $luceneFieldName) {
            if(in_array($luceneFieldName, $this->indexedLuceneFields)){
                if(strpos($luceneFieldName, $context.'_') !== false){
                    if(isset($columns[$luceneFields['luceneFieldIds'][$key]]['displayName'])) {
                        $result['column'][] = $columns[$luceneFields['luceneFieldIds'][$key]]['displayName'];
                    }
                } else {
                    if(isset($columns[$luceneFields['luceneFieldIds'][$key]]['displayName'])) {
                        $result['subColumn'][] = $columns[$luceneFields['luceneFieldIds'][$key]]['displayName'];
                    }
                }
            }
        }
        
        return $result;
    }
    
    private function getUrl(){
        $host = $_SERVER['HTTP_HOST'];
        $self = $_SERVER['PHP_SELF'];
        $self = str_replace('/index.php/', '/', $self);
        $query = !empty($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : null;
        $url = !empty($query) ? "http://$host$self?$query" : "http://$host$self";
        return $url;
    }
    
    public function encryptString($str){
        return md5("ConfigureSEARCH".$str."ConfigureSEARCH");
    }
    
}
?>