<?php
class PodcastResourceService {
    
    public function process($resources, $ta) {
        $lastBuilDate = '';
        $items = '';
        $xmlStart = <<<XML
<rss xmlns:itunes="http://www.itunes.com/dtds/podcast-1.0.dtd" version="2.0">
XML;
        $items .= $this->_processResources($resources, $ta);
        $xmlEnd = <<<XML

</rss>
XML;
        if(strlen(trim($items)) > 0) {
            return $xmlStart.$items.$xmlEnd;
        }
        return '';
    }
    
    private function _processResources($resources, $ta) {
        $xmlStr = '';
        if(!empty($resources) && !empty($ta)) {
            //This would filter out all the resources which are not available for students.
            $resources  = $this->_filterStudentResources($resources);
            if(!empty($resources)) {
                $collections = $this->_categoriseByCollection($resources);
                $xmlStr = $this->_processCollection($collections, $ta); 
            }
        }
        return $xmlStr;
    }

    private function _getStudentResourceTypes() {
        $mediabankResourceType = new MediabankResourceType();
        return $mediabankResourceType->getResourceTypeAutoIdsForUser('student');
    }
    
    private function _filterStudentResources($resources) {
        $filteredResources = array();
        //get the resource types which are available for students
        $studentResourceTypes = $this->_getStudentResourceTypes();
        if(!empty($studentResourceTypes)) {
            foreach($resources as $resourceTypeId => $data) {
                if(in_array($resourceTypeId, $studentResourceTypes)) {
                    $filteredResources[$resourceTypeId] = $resources[$resourceTypeId];
                }                
            }
        }
        return $filteredResources;
    }
    
    private function _processLectopiaResources($resources, $ta) {
        $podcastResourceLectopia = new PodcastResourceLectopia($resources, $ta);
        return $podcastResourceLectopia->process();
    }
    
    private function _categoriseByCollection($resources) {
        $collections = array();
        foreach($resources as $resourceTypeId => $rows) {
            foreach($rows as $row) {
                $collectionId = MediabankResourceConstants::getCollection($row['resource_id']);
                $collections[$collectionId][] = $row;       
            }
        }
        return $collections;
    }
    
    private function _processCollection($collections, $ta) {
        $xmlStr = '';
        //e.g $collections = compass_resources, cmsdocs-smp, compassresources, medvid, stage3medvid
        //                   lectopia, echo360
        foreach($collections as $collectionId => $resources) {
            $podcastResource = null;
            switch($collectionId) {
                case MediabankResourceConstants::$COLLECTION_lectopia:
                    $podcastResource = new PodcastResourceLectopia($resources, $ta);
                    break;
                case MediabankResourceConstants::$COLLECTION_echo360:
                    $podcastResource = new PodcastResourceEcho360($resources, $ta);
                    break;
                default;
                    $podcastResource = new PodcastResourceDefault($resources, $ta);
                    break;
            }
            if(! is_null($podcastResource) && $podcastResource instanceof PodcastResourceAbstract) {
                $xmlStr .= $podcastResource->process();
            }
        }
        return $xmlStr;
    }
}