<?php
abstract class LinkDoctypeAbstract {
    
    private $doctypeQuery = "+collectionID:compassresources +native.cmsresourcetype:'%s'";
    
    abstract function process();
    
    // 1 - PROCESS TEACHING RESOURCE TYPES
    protected function processDocType($doctype) {
        $results = $this->getResults(trim($doctype));
        Zend_Registry::get('logger')->warn('No of Results found for '.$doctype.' '.count($results).PHP_EOL);
        if(!empty($results)) {
            return $results;
        }
        return array();
    }
    
    // 1.1, 2.1
    private function getResults($doctype) {
        $searchResults = $this->searchMediabankForDoctype($doctype);
        return $this->processResults($searchResults);
    }
    
    // 1.1.1
    private function searchMediabankForDoctype($doctype) {
        if(!is_null($doctype) && strlen(trim($doctype)) > 0 && is_string($doctype)) {
            $cmsMediabank = new CmsMediabank();
            $query = sprintf($this->doctypeQuery, $doctype);
            $results = $cmsMediabank->search($query);
            return $results;
        }
        die('Please set the docytpe before processing');
    }
    
    //1.1.2
    private function processResults($searchResults) {
        $mediabankResourceService = new MediabankResourceService();
        $return = array();
        if(!empty($searchResults)) {
            foreach($searchResults as $key => $result) {
                $mid = $result->attributes['mid'];
                $data = $mediabankResourceService->getMediabankMetaData($mid);
                $data['mid'] = $mid;
                $return[] = $data;
            }
        }
        return $return;
    }
    
    //2 PROCESSING PBL RESOURCE TYPES
    protected function processPblResourceType($doctype, $resourceTypeId) {
        $return = array();
        $insert = array();
        $doctype = (string)$doctype;
        $resourceTypeId = (int)$resourceTypeId;
        //DEBUG START
        $notFound = array();
        $found = array();
        //DEBUG END
        if(strlen(trim($doctype)) > 0 && $resourceTypeId > 0) {
            $results = $this->getResults(trim($doctype));
            $resultsProcessed = 0;
            Zend_Registry::get('logger')->warn('No of Results found for '.$doctype.' '.count($results).PHP_EOL);
            if(!empty($results)) {
                foreach($results as $result) {
                    if(isset($result['notes']) && !empty($result['notes'])) {
                        $notes = $this->filterNotes($result['notes']);
                        if(! empty($notes)) {
                            $pblId = $this->getPblId($notes);
                            if($pblId !== false) {
                                $data['type'] = 'pbl';
                                $data['type_id'] = $pblId;
                                $data['resource_type'] = ResourceConstants::$TYPE_MEDIABANK;
                                $data['resource_id'] = $result['mid'];
                                $data['resource_type_id'] = $resourceTypeId;
                                $data['order_by'] = 1;
                                $insert[] = $data;
                                //DEBUG START
                                $resultsProcessed++;
                                $found[] = 'PBL FOUND '.$result['notes'].' => '. $result['title'].PHP_EOL;
                                //DEBUG END
                            } else {
                                //DEBUG START
                                $notFound[] = 'PBL NOT FOUND '.$result['notes'].' => '. $result['title'].PHP_EOL;
                                //DEBUG END
                            }
                        } else {
                            Zend_Registry::get('logger')->warn('Notes empty '.PHP_EOL.print_r($notes, true).PHP_EOL.print_r($result, true).PHP_EOL);
                        }
                    }                    
                }
            }
            
            sort($found,SORT_STRING);
            sort($notFound,SORT_STRING);
            Zend_Registry::get('logger')->warn(print_r($found,true));
            Zend_Registry::get('logger')->warn(print_r($notFound,true));
            Zend_Registry::get('logger')->warn('No of Results processed for '.$doctype.' '.$resultsProcessed.PHP_EOL);
        }
        $this->insert($insert);
        $return['pbl']['found'] = $found;
        $return['pbl']['notFound'] = $notFound;
        return $return;
    }
    
    //2.1
    private function filterNotes($notesString) {
        $return = array();
        if(strlen(trim($notesString)) > 0) {
            $notesExploded = explode('|', trim($notesString));
            foreach($notesExploded as $note) {
                $noteExploded = explode(':', $note);
                if(count($noteExploded) == 2) {
                    $key = trim($noteExploded[0]);
                    $val = trim($noteExploded[1]);
                    $return[$key] = $val;
                }
            }
        }
        return $return;
    }
    
    //2.2
    private function getPblId($notes) {
        if(isset($notes['pbl']) && strlen(trim($notes['pbl'])) > 0 ) {
            $pbl = new Pbl($notes['pbl']);
            $error = $pbl->hasError();
            if($error === false) {
                $pblData = $pbl->getPblDetails();
                if(isset($pblData['pblId']) && (int)$pblData['pblId'] > 0) {
                    return (int)$pblData['pblId'];                    
                }
            }
        }
        return false;
    }
    
    //2.3
    private function insert($return) {
        $count = count($return);
        Zend_Registry::get('logger')->warn(PHP_EOL.'Rows to insert in lk_resource table '.$count.PHP_EOL);
        $rowsInserted = 0;
        if(!empty($return)) {
            $mediabankResource = new MediabankResource();
            foreach($return as $row) {
                try {
                    $inserted = $mediabankResource->insert($row);
                    if($inserted > 0) {
                        $rowsInserted++;
                    }
                } catch(Exception $ex) {
                    Zend_Registry::get('logger')->warn('Could not insert this row in lk_resource table '. PHP_EOL.print_r($row,true).PHP_EOL);
                }                             
            }
        }
        Zend_Registry::get('logger')->warn(PHP_EOL.'Rows inserted in lk_resource table '.$rowsInserted.PHP_EOL);
    }
    
}
?>