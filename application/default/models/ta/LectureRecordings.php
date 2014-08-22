<?php
class LectureRecordings {
    
    private $mediabankResourceService = null;
    private $lectureRecordingId = null;
	private $echo360Service = null;
    private $years = array(2011, 2010, 2009, 2008, 2007, 2006, 2005);
    private $types = array('pdf','mp3','mp4','richmedia','zip','ppt','avi');
    private $yearCount = array();
    
    
    public function __construct() {
        $this->mediabankResourceService = new MediabankResourceService();
        $this->lectureRecordingId = ResourceTypeConstants::$RECORDINGS_ID;
		$this->echo360Service = new Echo360Service();
    }
    
    public function getResources($taId) {
        $resource = new MediabankResource();
        $resources = $resource->getResourcesByType($taId, 'ta', $this->lectureRecordingId);
        if($resources === false) {
            return array();
        }
        return $this->processResources($resources);
    }
    
    public function processResources($resources) {
        $return = array();
        $return['medvid'] = array();
        $return['echo360'] = array();
        $return['other'] = array();
        if(empty($resources)) {
            return $return;
        }
        $mediabankResourceService = new MediabankResourceService();
        foreach($resources as $resource) {
            list($repositoryId, $collectionId, $objectId) = explode('|',$resource['resource_id']);
            $repositoryId   = trim($repositoryId);
            $collectionId   = trim($collectionId);
            $objectId       = trim($objectId);
            if($collectionId == 'medvid' || $collectionId == 'stage3medvid') {
                $this->processMedVids($return['medvid'], $objectId, $resource['resource_id']);
            } else if($collectionId == 'echo360'){ 
                $this->processEcho360($return['echo360'], $objectId, $resource['resource_id']);
            } else {
                $row = $mediabankResourceService->processResource($resource);
                $row['metadata'] = $mediabankResourceService->getMetaData($resource['resource_id']);
                $return['other'][] = $row;
            }
        }
        
        if(!empty($return['medvid'])) {
            foreach($return['medvid'] as $year=>$data) {
                if(isset($data['info']['pdf'])) {
                    $return['medvid'][$year]['image']['mid'] = $data['info']['pdf']['metadata']['mid'];
                    $return['medvid'][$year]['image']['midEncoded'] = $data['info']['pdf']['metadata']['midEncoded'];
                    $return['medvid'][$year]['duration'] = $this->sanitizeDuration($data['info']['pdf']['metadata']['data']['duration']);
                } else {
                    foreach($data['info'] as $type) {
                        $return['medvid'][$year]['image']['mid'] = $type['metadata']['mid'];
                        $return['medvid'][$year]['image']['midEncoded'] = $type['metadata']['midEncoded'];
                        $return['medvid'][$year]['duration'] = $this->sanitizeDuration($type['metadata']['data']['duration']);
                        break;
                    }
                }
            }    
        }
        return $return;
    }
    
    private function processEcho360(&$echo360, $objectId, $mid) {
        $data = MediabankCacheEcho360::getMetadata($mid);
        $metadata =& $data['metadata'];
        $smpMetadata =& $data['smpMetadata'];
        if(!empty($smpMetadata) && !empty($metadata)) {
            $year = null;
            $documentId = null;
            $presentationId = null;
            if(isset($smpMetadata['smp'])) {
                if(isset($smpMetadata['smp']['calendar_year'])){
                    $metadataYear = $smpMetadata['smp']['calendar_year'];
                    if(strstr($metadataYear, '-')) {
                        $year = trim($metadataYear);
                    } else if((int)$metadataYear > 1900) {
                        $year = (int)$metadataYear;
                    }
                }
                if(isset($smpMetadata['smp']['cms_document_id']) && (int)$smpMetadata['smp']['cms_document_id'] > 0) {
                    $documentId = (int)$smpMetadata['smp']['cms_document_id'];
                }
                if(isset($metadata['data']['id']) && !empty($metadata['data']['id']) ) {
                    $presentationId = $metadata['data']['id'];
                }
            }
            if(!is_null($year) && !is_null($presentationId)) {
				$downloadIcon = Compass::baseUrl().'/img/ta/icon_download.png';
				$viewIcon = Compass::baseUrl().'/img/ta/icon_thumbnail.gif';
				$richmediaUrl = MediabankResourceConstants::createEcho360Url($presentationId);
				
				$collection = MediabankResourceConstants::$COLLECTION_echo360;
				if(!isset($this->yearCount[$collection][$year])) {
				    $this->yearCount[$collection][$year] = 1;
				} else {
				    $this->yearCount[$collection][$year]++;
				    $year = sprintf('%d (%d)', $year, $this->yearCount[$collection][$year]);
				}
				
				$echo360[$year]['mid'] = $mid;
                $echo360[$year]['links'] = array(
					array(
						'type'	=> 'mp3',
						'url'	=> $richmediaUrl.'/media/mp3',
						'icon'	=> $downloadIcon,
						'title' => 'Download Audio - mp3',
					    'filetype' => 'mp3',
					    'aHref' => self::createRecordingsIcon($richmediaUrl.'/media/mp3', 'audio', $year, 'mp3', 'Download Audio - mp3')
					),
					array(
						'type'	=> 'm4v',
						'url'	=> $richmediaUrl.'/media/m4v',
						'icon'	=> $downloadIcon,
						'title' => 'Download Video',
					    'filetype' => 'm4v',
					    'aHref' => self::createRecordingsIcon($richmediaUrl.'/media/m4v', 'video', $year, 'm4v', 'Download Video')
					),
					array(
						'type'	=> 'richmedia',
						'url'	=> $richmediaUrl,
						'icon'	=> $viewIcon,
						'title'	=> 'View Recording',		
					    'filetype' => 'richmedia',
					    'aHref' => self::createRecordingsIcon($richmediaUrl, 'view', $year, 'richmedia', 'View Recording')
					)						
				);
            }
        }
    }

    public static function getLectureImage() {
        return Compass::baseUrl().'/img/ta/default_lecture_thumb.jpg';
    }
    
    private function sanitizeDuration($duration) {
        if(strlen(trim($duration)) > 0) {
            $explode = explode(':', trim($duration));
            if(count($explode) == 3 && $explode[0] == '00') {
                return $explode[1].':'.$explode[2].' minutes';
            } else {
                return $duration.' minutes';         
            }
        }
        return '';
    }
    
    private function processMedVids(&$medvid, $objectId, $mid) {
        $explodeObjectId = explode('_',$objectId);
        if(count($explodeObjectId) > 1) {
            $year = (int)$explodeObjectId[0];
            $type = $explodeObjectId[count($explodeObjectId) - 1];
            if(in_array($year, $this->years) && in_array($type, $this->types)) {
                $medvid[$year]['info'][$type] = $this->processMedvidTypes($type, $mid);
            }            
        }
    }
    
    private function processMedvidTypes($type, $mid) {
        $return = array();
        $metadata = $this->mediabankResourceService->getMetaData($mid);
        switch($type) {
            case 'pdf':
                $return['icon'] = Compass::baseUrl().'/img/ta/icon_download.png';
                $return['title'] = 'Download PDF';
                $return['filetype'] = 'pdf';
            break;
            case 'ppt':
                $return['icon'] = Compass::baseUrl().'/img/ta/icon_download.png';
                $return['title'] = 'Download Powerpoint';
                $return['filetype'] = 'ppt';
            break;
            case 'mp3':
                $return['icon'] = Compass::baseUrl().'/img/ta/icon_download.png';
                $return['title'] = 'Download Audio -  mp3';
                $return['filetype'] = 'mp3';
            break;
            case 'avi':
                $return['icon'] = Compass::baseUrl().'/img/ta/icon_download.png';
                $return['title'] = 'Download Video - avi';
                $return['filetype'] = 'avi';
            break;
            case 'mp4':
                $return['icon'] = Compass::baseUrl().'/img/ta/icon_download.png';
                $return['title'] = 'Download Video -  mp4';
                $return['filetype'] = 'mp4';
            break;
            case 'richmedia':
                $return['icon'] = Compass::baseUrl().'/img/ta/icon_thumbnail.gif';
                $return['title'] = 'View Rich Media';
                $return['customUrl'] = '#';
                $return['onclick'] = MediabankResourceConstants::customUrlRichmedia($metadata['data']['resource_url']);
                $return['normalUrl'] = $metadata['data']['resource_url'];
                $return['filetype'] = 'richmedia';
            break;
            case 'zip':
                $return['icon'] = Compass::baseUrl().'/img/ta/icon_download.png';
                $return['title'] = 'Download Rich Media';
                $return['filetype'] = 'zip';
            break;
        }
        
        if(!empty($return)) {
            $return['metadata'] = $metadata;
            $fileNameFormat = 'Block %d Week %d %s %d.%s';
            $return['customFileName']  = MediabankResourceConstants::encode(sprintf($fileNameFormat, $metadata['data']['block'], $metadata['data']['week'], ucwords($metadata['data']['ta_type']), $metadata['data']['sequence'],$metadata['data']['file_extension']));
        }
        return $return;
    }
    
    public static function createRecordingsIcon($url, $mediaType, $year, $filetype, $title) {
        if(in_array($mediaType, array('audio', 'video', 'view')) && (int)$year > 1900) {
            $mediaTypeLC = strtolower($mediaType);
            $mediaTypeUC = strtoupper($mediaType);
            $year = (int)$year;
            return <<<AHREF
<a href="{$url}" class="recordings-a" rel="fileType:{$filetype}" title="{$title} - {$year}" target="_blank"><div class="recordings-{$mediaTypeLC}"><div class="recordings-year">{$year}</div><div class="recordings-title">{$mediaTypeUC}</div></div></a>&nbsp;
AHREF;
        }
        return '';
    }
    
    public static function getNoRecordingsAvailableTaTypeIds() {
        $taTypeIds = array();
        $configTaTypeIds = Compass::getConfig('ta.activitytypeids.norecordingavailable.showimage');
        if(!empty($configTaTypeIds)) {
            $explode = explode(',', $configTaTypeIds);
            foreach($explode as $taTypeId) {
                $taTypeIds[] = trim($taTypeId);
            }
        }
        return $taTypeIds;
    }
}
?>
