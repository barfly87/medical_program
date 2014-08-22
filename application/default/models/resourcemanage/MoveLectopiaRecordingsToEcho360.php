<?php
class MoveLectopiaRecordingsToEcho360 {
    
    public function action($do) {
        switch ($do) {
            case 'generate_ids':
                return $this->generatedRecordingsIdsToMove();
                break;
            case 'get_desc_title':    
                return $this->getDescriptionTitleForAllEcho360Recordings();
                break;
            case 'remove_lectopia_ids':
            	return $this->removeLectopiaIds();
            	break;    
        }
    }
    
    private function removeLectopiaIds() {
    	$mediabankResource = new MediabankResource();
    	$resources = $mediabankResource->fetchAll("resource_id like '%|lectopia|%'")->toArray();
    	foreach($resources as $resource) {
    		$typeId = $resource['type_id'];
    		$mid = $resource['resource_id'];
    		$type = $resource['type'];
    		$deleted = $mediabankResource->removeResource($typeId, $mid, $type);
    		if($deleted === false) {
	    		echo 'Could not delete resource auto_id "'.$resource['auto_id'].'"<br />';
    		}
    	}
    	exit;
    }
    
    private function getDescriptionTitleForAllEcho360Recordings() {
    	ob_end_clean();
    	set_time_limit(0);
    	$query = '+collectionID:"'.MediabankResourceConstants::$COLLECTION_echo360.'"';
    	$mediabankResourceService = new MediabankResourceService();
    	$mediabankSearchResults = $mediabankResourceService->search($query);
    	print '<table style="border-collapse:collapse;" border="1">';
    	if($mediabankSearchResults !== false) {
    		foreach($mediabankSearchResults as $result) {
    			$mid 			= $result->attributes['mid'];
    			$metadata 		= $mediabankResourceService->getMetaData($mid);
    			$title 			= 'NOT FOUND';
    			$description 	= 'NOT FOUND';
    			$objectId 		= $metadata['objectId'];
    			$explode 		= explode(":", $objectId);
    			$recordingId 	= $explode['2'];
    			if(isset($metadata['data']) && isset($metadata['data']['title'])) {
    				$title = $metadata['data']['title'];
    			}
    			if(isset($metadata['data']) && isset($metadata['data']['description'])) {
    				$description = $metadata['data']['description'];
    			}
    			printf("<tr><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>", $recordingId, $mid, $title, $description);
    		}
    	} 
    	print '</table>';   	
    	exit;
    }
    
    private function generatedRecordingsIdsToMove() {
    	$db 					= Zend_Registry::get("db");
    	
    	$releasedAutoId 		= sprintf("select auto_id from lk_status where name = '%s'", Status::$RELEASED);
    	$teachingActivities		= sprintf("select ta_id from link_lo_ta where status = (%s) group by ta_id", $releasedAutoId);
    	
    	//Find all teaching activity ids which are released and has a lectopia resource attached
    	$lectopiaTasSelect 		= "select type_id from lk_resource where type='ta' and type_id in ($teachingActivities) and resource_id like '%|lectopia|%' group by type_id order by type_id";
    	$stmtLectopiaTas 		= $db->query($lectopiaTasSelect);
    	$lectopiaTasRows 		= $stmtLectopiaTas->fetchAll();
    	 
		//Find all teaching activity ids which has atleast 1 lectopia resource attached and atleast 1 echo360 resource attached.
		$echo360Tas 			= "select type_id from lk_resource where type='ta' and type_id in ($lectopiaTasSelect) and resource_id like '%|echo360|%' group by type_id";
		$stmtEcho360Tas 		= $db->query($echo360Tas);
		$echo360TasRows 		= $stmtEcho360Tas->fetchAll();
		
		
		$echo360TasIds = array();		
		if(!empty($echo360TasRows)) {
			foreach($echo360TasRows as $echo360TasRow) {
				$echo360TasIds[] = $echo360TasRow['type_id'];
			}
		}
		
		$lectopiaTasIds = array();
		if(!empty($lectopiaTasRows)) {
			foreach($lectopiaTasRows as $lectopiaTasRow) {
				if(!in_array($lectopiaTasRow['type_id'], $echo360TasIds)){
					$lectopiaTasIds[] = $lectopiaTasRow['type_id'];
				}
			}
		}
		
		$allLectopiaResourcesSelect = "select type_id, resource_id from lk_resource where type_id in (".implode(',',$lectopiaTasIds).") and resource_id like '%|lectopia|%' order by type_id asc, resource_id desc";
		$allLectopiaResourcesStmt = $db->query($allLectopiaResourcesSelect);
		$allLectopiaResourcesRows = $allLectopiaResourcesStmt->fetchAll();
		print '<table border="1">';
		print '<th>Lectopia ID</th><th>Title</th><th>Description</th>';
		$lastId = null;
		$mediabankResourceService = new MediabankResourceService();
		foreach($allLectopiaResourcesRows as $allLectopiaResourcesRow){
			if($lastId != $allLectopiaResourcesRow['type_id']) {
				$mid = $allLectopiaResourcesRow['resource_id'];
				$metadata = $mediabankResourceService->getMediabankMetaData($mid, MediabankResourceConstants::$SCHEMA_native);
				$midParts = explode('|',$mid); 
				$title = (strlen(trim($metadata['LectureOutline'])) > 0) ? trim($metadata['LectureOutline']) : '-';
				$description = (strlen(trim($metadata['LectureTitle'])) > 0) ? trim($metadata['LectureTitle']) : '-';
				//if($title == '-' || $description == '-') {
					printf("<tr><td>%s</td><td>%s</td><td>%s</td></tr>", $midParts[2], $title, $description);
				//}
			}
			$lastId = $allLectopiaResourcesRow['type_id'];
		}
		print '</table>';
		
    	exit;
    }
}