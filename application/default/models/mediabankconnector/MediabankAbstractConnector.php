<?php
abstract class MediabankAbstractConnector {
    
    protected $mediabankResourceService = null;
    protected $mediabankResource = null;

    abstract function link();
    
    protected function getAllMidsForCollection($repositoryId, $collectionId) {
        if(!is_null($repositoryId) && !is_null($collectionId)) {
            $mid = $repositoryId .'|'.$collectionId;
            $list = $this->mediabankResourceService->listCollection($mid);
            $mids = $this->mediabankResourceService->getMidsFromCollectionList($list);
            return $mids;
        } else {
            return array();
        }
    }
    
    protected function getAllMidsForCompass() {
        try {
            if(is_null($this->mediabankResource)) {
                $this->mediabankResource = new MediabankResource();
            }
            $rows = $this->mediabankResource->fetchAll();
            if($rows->count() > 0) {
                return $this->getMidsFromRows($rows);
            }
            return array();
        } catch (Exception $ex) {
        	Zend_Registry::get('logger')->debug("\n".$ex->getMessage()."\n".$ex->getTraceAsString()."\n\n");
            return array();
        }
    }
    
    protected function getAllResourcesForCompass($where = null) {
        try {
            if(is_null($this->mediabankResource)) {
                $this->mediabankResource = new MediabankResource();
            }
            if(is_null($where) || empty($where)) {
                $rows = $this->mediabankResource->fetchAll();
            } else {
                $rows = $this->mediabankResource->fetchAll($where);
            }
            if(!is_null($rows)) {
                return $rows->toArray();
            }
            return array();
        } catch (Exception $ex) {
            Zend_Registry::get('logger')->debug("\n".$ex->getMessage()."\n".$ex->getTraceAsString()."\n\n");
            return array();
        }
    }    

    protected function getMidsFromRows($rows) {
        $mids = array();
        if(! empty($rows)) {
            foreach($rows as $row) {
                $mids[] = $row->resource_id;
            }
        }
        return $mids;
    }
    
    protected function isMidLinkedToTa($taid, $mid) {
        if(is_null($this->mediabankResource)) {
            $this->mediabankResource = new MediabankResource();
        }
        $where = sprintf("type_id = %d and resource_id = '%s'", $taid, $mid);
        $result = $this->mediabankResource->fetchRow($where);
        return ($result === false) ? false : true;
    }
    
    protected function getMetadataForMid($mid, $schema = null) {
        try {
            if(! is_null($this->mediabankResourceService)) {
                return $this->mediabankResourceService->getMediabankMetaData($mid, $schema);
            }
            Zend_Registry::get('logger')->debug("\nMediabank Resource Service is not defined in the class extending this class.\n\n");
            return array();
        } catch (Exception $ex) {
        	Zend_Registry::get('logger')->debug("\n".$ex->getMessage()."\n".$ex->getTraceAsString()."\n\n");
            return array();
        }
    }
    
    protected function reindexTeachingActivities($taIds) {
        //Reindex TA IDS so that the resources data can be updated.
        if(!empty($taIds)) {
            $taIds = array_unique($taIds);
            Zend_Registry::get('logger')->warn("\nReindexing this teaching activites ".implode(',', $taIds).".\n\n");
            foreach($taIds as $taId) {
                try {
                    SearchIndexer::reindexDocument('ta', $taId);      
                } catch (Exception $ex) {
                    $error = $ex->getMessage().PHP_EOL.$ex->getTraceAsString().PHP_EOL;
                    Zend_Registry::get('logger')->warn(PHP_EOL."CLASS\t: ".__CLASS__.PHP_EOL."METHOD\t: ".__METHOD__.PHP_EOL."Error\t: ".$error.PHP_EOL);
                }
            }
        }
    }
    
}
?>