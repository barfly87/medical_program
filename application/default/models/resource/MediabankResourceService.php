<?php

class MediabankResourceService {
    
    private $mediabank = null;
    private $mediabankResource = null;
    private $mediabankResourceType = null;
    
    public function __construct() {
        $mediabankConstants = new MediabankConstants();
        $this->mediabank = $mediabankConstants->mediabank;
        $this->mediabankResource = new MediabankResource();
        $this->mediabankResourceType = new MediabankResourceType();
    }
    
    public function getResources($id, $type, $excludeResourceType = null, $resourceTypeId = null) {
        if(is_null($resourceTypeId)) {
            $resources = $this->mediabankResource->getResources($id, $type);
        } else {
            $resources = $this->mediabankResource->getResourcesByType($id, $type, $resourceTypeId);
        }
        
        $return = array();
        $count = 0;
        if(!empty($resources)) {
            foreach($resources as $resource) {
                if(!is_null($excludeResourceType) && in_array($resource['resource_type_id'],$excludeResourceType)) {
                    continue;
                }
                $return[$count] = $this->processResource($resource);
                $count++;
            }
        }
        return $return;
    }
    
    public function getResourcesForResourceTypeIds($id, $type, $resourceTypeIds) {
        $resources = $this->mediabankResource->getResourcesByType($id, $type, $resourceTypeIds);
        
        $return = array();
        $count = 0;
        if(!empty($resources)) {
            foreach($resources as $resource) {
                $return[$count] = $this->processResource($resource);
                $count++;
            }
        }
        return $return;
    }
    
    public function processResource($resource) {
        $return = array();
        $metadata = $this->getMetaData($resource['resource_id']);
        
        $title = $metadata['title'];
        $return['metadata'] = $metadata;
        if($title !== false) {
            $return['title']    = $title;
            $return['edit']     = $this->allowEdit($resource['resource_id']);
        } else {
            $return['error']    = true;
        }
        
        $cmsLink = CmsConst::getCmsLink($resource['resource_id']);
        $richmediaEndsWith = MediabankResourceConstants::$richmediaEndsWith;
        if(!empty($cmsLink)) {
            $return['customViewUrl']    = $cmsLink.'" rel="prettyPhoto';
            $return['customImageUrl']   = Compass::baseUrl().CmsConst::$cmsResrcImage;
        } else if(substr($resource['resource_id'], -strlen($richmediaEndsWith)) == $richmediaEndsWith ) {
            $return['customViewUrl']    = '#';
            $return['customOnclick']    = MediabankResourceConstants::customUrlRichmedia($metadata['data']['resource_url']);
        }
        $mid                                = MediabankResourceConstants::encode($resource['resource_id']);
        $return['auto_id']                  = $resource['auto_id'];
        $return['mid']                      = $mid;
        $return['resource_type_id']         = $resource['resource_type_id'];
        $return['compassImageUrl']          = Compass::baseUrl().MediabankResourceConstants::$compassImageUrlBasePath.'?mid='.$mid;
        $return['compassDownloadUrl']       = Compass::baseUrl().MediabankResourceConstants::$compassDownloadUrlBasePath.'?mid='.$mid;
        $return['compassViewOrDownloadUrl'] = Compass::baseUrl().MediabankResourceConstants::$compassViewOrDownloadUrlBasePath.'?mid='.$mid;
        $autoIdAllowPair                    = $this->mediabankResourceType->fetchAutoIdAllowPair();
        
        $return['allowUser']                = false;
        $return['staffOnly']                = false;
        
        if(isset($autoIdAllowPair[$resource['resource_type_id']])) {
            $return['allowUser']                = self::isUserAllowed($autoIdAllowPair[$resource['resource_type_id']]);
            $return['staffOnly']                = MediabankResourceConstants::isResourceStaffOnly($autoIdAllowPair[$resource['resource_type_id']]);                                                   
        }
        return $return;
    }

    public static function isUserAllowed($user) {
        $allow = false;
        switch($user) {
            case 'student':
                $allow = UserAcl::isStudentOrAbove();
            break;
            case 'staff':
                $allow = UserAcl::isStaffOrAbove();
            break;
            case 'admin':
                $allow = UserAcl::isAdmin();
            break;                                                                                
            case 'blockchair':
                $allow = UserAcl::isBlockchairOrAbove();
            break;
            case 'stagecoordinator':
                $allow = UserAcl::isStagecoordinatorOrAbove();
            break;                                    
        }
        return $allow;
    }
    
    public function allowAdd() {
        $mid = MediabankResourceConstants::getAddUrl();
        $allowMediabankAction = $this->allowMediabankAction('add',$mid);
        if($allowMediabankAction == true) {
            return true;
        }
        return false;
    }
    
    public function allowRead($mid) {
        $mid = MediabankResourceConstants::sanitizeMid($mid);
        if(empty($mid)) {
            return false;
        }
        $allowMediabankAction = $this->allowMediabankAction('read',$mid);
        if($allowMediabankAction == true) {
            return true;
        }
        return false;
    }

    public function allowEdit($mid){
        $mid = MediabankResourceConstants::sanitizeMid($mid);
        if(empty($mid)) {
            return false;
        }
        $explode = explode('|',$mid);
        $compassResource = false;
        if(count($explode) == 3 && $explode[1] == MediabankResourceConstants::$cid) {
            $compassResource = true;
        }
        $allowMediabankAction =  $this->allowMediabankAction('update',$mid);
        if($allowMediabankAction == true) {
            return true;
        }
        return false;
    }

    public function allowMediabankAction($action, $mid){
        try {
            $user = MediabankResourceConstants::getMediabankuserObj($mid);
            return $this->mediabank->checkAccess($user,$action, $mid);
        } catch (Exception $ex) {
            Zend_Registry::get('logger')->info($ex->getMessage());
            Zend_Registry::get('logger')->info($ex->getTraceAsString());
            return false;            
        }
    }
    
    public function isUserAllowedToViewDownloadResource($type, $type_id, $mid) {
        $return = false;
        $rows = $this->mediabankResource->getResources($type_id, $type);
        $mid = MediabankResourceConstants::sanitizeMid($mid);
        $autoIdAllowPair = $this->mediabankResourceType->fetchAutoIdAllowPair();
                
        foreach($rows as $row) {
            if($row['resource_id'] == $mid && isset($autoIdAllowPair[$row['resource_type_id']]) &&
                self::isUserAllowed($autoIdAllowPair[$row['resource_type_id']])) {
                $return = true;
            }
        }
        return $return;
    }
    
    public function search($query) {
        $index = "Lucene Index";
        $searchRemote = true;
        $user = MediabankResourceConstants::getMediabankuserObjForSearch();
        return $this->mediabank->search($index, $query, $user, $searchRemote);
    }
    
    public function getTitleForMid($mid) {
        $mid = MediabankResourceConstants::sanitizeMid($mid);
        if(empty($mid)) {
            return 'Untitled';
        }
        $title = '';
        try {
            $title = 'Untitled';
            $user = MediabankResourceConstants::getMediabankuserObj($mid);
            $mid  = $this->getMepositoryidObj($mid);
            
            $objdc  = $this->mediabank->getMetadata($mid,MediabankResourceConstants::$SCHEMA_dublinCore, $user);
            $dcdom  = new DOMDocument();
            if(! is_null($objdc)) {
                $dcdom->loadXML($objdc);
                $dcroot = $dcdom->firstChild;
                $title  = $dcdom->getElementsByTagName('title')->item(0)->textContent;
            }
        } catch (Exception $e) {
            $title='Untitled - missing metadata';
            try {
                $objnative  = $this->mediabank->getMetadata($mid,MediabankResourceConstants::$SCHEMA_native, $user);
                $dom        = new DOMDocument();
                
                if(! is_null($objnative)) {
                    $dom->loadXML($objnative);
                    $root = $dom->firstChild;
                    if(isset($root)) {
                        $title = $root->getElementsByTagName('title')->item(0)->textContent;
                    }
                }
            } catch (Exception $e2) {
            }
        }
        return $title;
    }
    
    public function getMepositoryidObj($mid) {
        return new Mepositoryid($mid);
    }
    
    public function getMetadataXmlObjForMid($mid = ''){
        $mid = MediabankResourceConstants::sanitizeMid($mid);
        if(empty($mid)) {
            return false;
        }
        $mid = new MepositoryID($mid);
        $user = MediabankResourceConstants::getMediabankuserObj($mid);
        return $this->mediabank->getMetadata($mid,MediabankResourceConstants::$SCHEMA_native, $user);
    }
    
    public function listCollection($mid){
        try {
            $user = MediabankResourceConstants::getMediabankuserObj($mid);
            return $this->mediabank->listCollection(new MepositoryID($mid),MediabankResourceConstants::$SCHEMA_native, $user);   
        } catch (Exception $ex) {
            return array();    
        }
    }

    public function getMidsFromCollectionList($list) {
        try {
            $return = array();
            if(is_array($list) && !empty($list)) {
                foreach($list as $obj) {
                    $return[] = $obj->repositoryID .'|'. $obj->collectionID .'|'.$obj->objectID;
                }            
            }
            return $return;
        } catch (Exception $ex) {
            return array();    
        }        
    }
    
    public function getMetaData($mid, $cacheData = true){
        try {
            $mid = MediabankResourceConstants::sanitizeMid($mid);
            if(empty($mid)) {
                return array();
            }
            if($cacheData === true) {
                $cache = MediabankCacheMetadata::getCache($mid);
                if($cache !== false) {
                    return $cache;
                }
            }
            
            $height = 500;
            $width = 500;
            $imgnum = 0;
            
            $result = array();
            if(strlen(trim($mid)) <= 0) {
                return $result;
            }
            $result['midEncoded']   = MediabankResourceConstants::encode($mid); 
            $result['mid']          = $mid;
            
            $user       = MediabankResourceConstants::getMediabankuserObj($mid);
            $mid        = new MepositoryID($mid);
            $objnative  = $this->mediabank->getMetadata($mid,MediabankResourceConstants::$SCHEMA_native, $user);
            $dom        = new DOMDocument();
            
            if(! is_null($objnative)) {
                $dom->loadXML($objnative);
                $root = $dom->firstChild;
            }
            $title = 'Untitled';
            
            try {
                $objdc  = $this->mediabank->getMetadata($mid,MediabankResourceConstants::$SCHEMA_dublinCore, $user);
                $dcdom  = new DOMDocument();
                if(! is_null($objdc)) {
                    $dcdom->loadXML($objdc);
                    $dcroot = $dcdom->firstChild;
                    $title  = $dcdom->getElementsByTagName('title')->item(0)->textContent;
                }
            } catch (Exception $e) {
                $title='Untitled - missing metadata';
                try {
                    if(isset($root)) {
                        $title = $root->getElementsByTagName('title')->item(0)->textContent;
                    }
                } catch (Exception $e2) {
                }
            }
                     
            $fileDesc = $this->mediabank->getObject($mid,$user);
            
            if(is_array($fileDesc->fileData)) {
                $mimeType   = (isset($fileDesc->fileData[0]->mimetype)) ? $fileDesc->fileData[0]->mimetype  : '';
                $name       = (isset($fileDesc->fileData[0]->name))     ? $fileDesc->fileData[0]->name      : '';
                $explode    = explode('.',$name);
                $name       = $explode[count($explode) - 1];
            } else {
                $mimeType   = (isset($fileDesc->fileData->mimetype))    ? $fileDesc->fileData->mimetype     : '';
                $name       = (isset($fileDesc->fileData->name))        ? $fileDesc->fileData->name         : '';
                $explode    = explode('.',$name);
                $name       = $explode[count($explode) - 1];
            }
            
            $result['fileTypeExtension'] = $name;
            $baseFileName = '';//Used by $result['audio']['url']
            $baseFileName   = 'resource_'.$mid->objectID;
            $fileName       = $baseFileName.'.'.$name;
            $result['fileName'] = MediabankResourceConstants::encode($fileName);
            
            $result['mimeType'] = MediabankResourceConstants::encode($mimeType);
            $result['decodedMimeType'] = $mimeType;
            $result['title']            = $title;
            $result['repositoryId']     = $mid->repositoryID;
            $result['collectionId']     = $mid->collectionID;
            $result['objectId']         = $mid->objectID;
            $explodeMimeType            = explode('/',$mimeType);
            $mimeTypeCategory           = strtolower($explodeMimeType[0]);
            $result['mimeTypeCategory'] = $mimeTypeCategory;
            if($mimeTypeCategory == 'text') {
                if($explodeMimeType[1] == 'html') {
                    $result['html']['val'] = MediabankResourceConstants::removeHtmlDoctype($this->getRawData($mid));
                } elseif($explodeMimeType[1] == 'x-url'){
                    $result['URL']['val'] = $this->getRawData($mid);
                } else {
                    $result['text']['val'] = $this->getRawData($mid);
                } 
            } else if ($mimeTypeCategory == 'video') {
                $height = $width * 3/4;
                if($height % 2) {
                    $height++;
                }
                $fileInfo = $this->getFileInfo($mid->__toString());
                if(isset($fileInfo['resolution-width']) && isset($fileInfo['resolution-height'])) {
                    $tempWidth  = $fileInfo['resolution-width'];
                    $tempHeight = $fileInfo['resolution-height'];
                    if($tempWidth > 200) {
                        if($tempWidth > 640) {
                            $width = 640;
                            $height = round(640*$tempHeight/$tempWidth);
                        } else {
                            $width  = $fileInfo['resolution-width'];
                            $height = $fileInfo['resolution-height'];
                        }
                    }
                }
                $result['video']['height']  = $height;
                $result['video']['width']   = $width;
                $videoFlvUrl    = 'http://'.$_SERVER['SERVER_NAME'].MediabankResourceConstants::createCompassTranscodeFlvUrl($mid->__toString(), $width, $height);
                $videoMp4Url    = 'http://'.$_SERVER['SERVER_NAME'].MediabankResourceConstants::createCompassTranscodeMp4Url($mid->__toString(), $width, $height);
                $imageUrl       = 'http://'.$_SERVER['SERVER_NAME'].MediabankResourceConstants::createCompassImageUrl($mid->__toString(),'', $width, $height).'/nodefaultimage/1';
                
                $result['video']['flvUrl']          = $videoFlvUrl;
                $result['video']['mp4Url']          = $videoMp4Url;
                $result['video']['imageUrl']        = $imageUrl;
                $result['video']['allowDownload']   = true;
            } else if ($mimeTypeCategory == 'audio') {
                $width = 256;
                $height = 256;
                $audioUrl   = 'http://'.$_SERVER['SERVER_NAME'].MediabankResourceConstants::createCompassTranscodeMp3Url($mid->__toString());
                $imageUrl   = 'http://'.$_SERVER['SERVER_NAME'].MediabankResourceConstants::createCompassImageUrl($mid->__toString(),'', $width, $height).'/nodefaultimage/1';
                $result['audio']['mp3Url'] = $audioUrl.'/'.$baseFileName.'.'.MediabankResourceConstants::$audioMp3Extension;
                $result['audio']['imageUrl']        = $imageUrl;
                $result['audio']['allowDownload']   = true;
            } else {
                $result['image']['src'] = 'http://'.$_SERVER['SERVER_NAME'].MediabankResourceConstants::createCompassImageUrl($mid->__toString(),'', $width, $height);
                //$result['download']['link'] = true;
                $result['image']['allowDownload'] = true;
            }
            $tree = array();
            try {
                if(isset($root)) {
                    $treeArray = XMLService::createArrayFromXml($dom);
                    if(is_array($treeArray) && !empty($treeArray)) {
                        foreach($treeArray as $root=>$val) {
                            $tree = $val;
                        }
                    }
                }
            } catch (Exception $e) {
    
            }        
            $result['data'] = $tree;    
            if($cacheData === false) {
                return $result;
            }
            return MediabankCacheMetadata::setCache($mid, $result);
        } catch (Exception $ex) {
            return array();
        }
    }   
    
    public function updatePageInfo($result, $pageInfo) {
        if(!empty($pageInfo) && is_array($pageInfo)) {
            $baseFileName  = (isset($pageInfo['type']) && strlen(trim($pageInfo['type'])) > 0 ) ? $pageInfo['type'].'_' : '' ;
            $baseFileName .= (isset($pageInfo['id']) && strlen(trim($pageInfo['id'])) > 0 )     ? $pageInfo['id'].'_'   : '' ;
            $baseFileName .= 'resource_'.$result['objectId'];
            $fileName     = $baseFileName.'.'.$result['fileTypeExtension'];
            $result['fileName'] = MediabankResourceConstants::encode($fileName);
        }
        return $result;
    }

    public function getSmpMetadata($mid) {
        $user = MediabankResourceConstants::getMediabankuserObj($mid);
        $midObj = new MepositoryID($mid);
        $objnative = $this->mediabank->getMetadata($midObj,MediabankResourceConstants::$SCHEMA_smp, $user);
        if(! is_null($objnative)) {
            $object = simplexml_load_string($objnative);
            $return['smp'] = Zend_Json::decode(Zend_Json::encode(($object), true));
            return $return;
        }
        return array();        
    }            
    
    public function getFileInfo($mid) {
        $fileInfo = array();
        $mid = MediabankResourceConstants::sanitizeMid($mid);
        if(!empty($mid)) {
            $url = MediabankConstants::fileInfoUrl().$mid;
            ob_start();
            $mediabankUtility = new MediabankUtility();
            $mediabankUtility->curlUrlGet($url);
            $fileInfo = trim(ob_get_contents()); 
            ob_end_clean();
            if(!empty($fileInfo)) {
                $object = simplexml_load_string($fileInfo);
                $fileInfo = Zend_Json::decode(Zend_Json::encode(($object), true));
                if(isset($fileInfo['file-size']) && (int)$fileInfo['file-size'] > 0) {
                    $sizeInMb = (int)$fileInfo['file-size'] / (1024 * 1024);
                    $fileInfo['file-size-in-mb'] = round($sizeInMb);
                }
                if(isset($fileInfo['resolution']) && !empty($fileInfo['resolution']) && strstr($fileInfo['resolution'],'x')) {
                    $resolution = explode('x',trim($fileInfo['resolution']));
                    if(count($resolution) == 2 && (int)$resolution[0] > 1 && (int)$resolution[1] > 1) {
                        $fileInfo['resolution-width'] = round($resolution[0]);
                        $fileInfo['resolution-height'] = round($resolution[1]);
                    }
                }

            }
        }
        return $fileInfo;
    }
    
    public function getMediabankMetaData($mid, $schema = null) {
        $user = MediabankResourceConstants::getMediabankuserObj($mid);
        $mid = new MepositoryID($mid);
        if(is_null($schema)) {
            $schema = MediabankResourceConstants::$SCHEMA_native;
        }
        $metadataXml = $this->mediabank->getMetadata($mid, $schema, $user);
        $dom = new DOMDocument();
        if(! is_null($metadataXml)) {
            $dom->loadXML($metadataXml);
            $root = $dom->firstChild;
        }
        $data = array();
        try {
            if(isset($root)) {
                foreach($root->childNodes as $childNode) {
                    if($childNode instanceof DOMElement) {
                        $data[$childNode->nodeName] = $childNode->textContent;
                    }
                }
            }
        } catch (Exception $e) {
            return array();
        }        
        return $data;
    }
    
    public function getRawData ($mid) {
        try {
            $mediabankUtility = new MediabankUtility();
            ob_start();
            $mediabankUtility->curlDownloadResource($mid);
            $text = ob_get_contents();
            ob_end_clean();
            return $text;
        } catch (Exception $ex) {
            return '';
        }
    }
    
    public function getLoTaAttachedToResource($mid){
        if(strlen(trim($mid)) > 0 ) {
            $lota = $this->mediabankResource->getResourcesAttachedToMid($mid);
            if($lota != false) {
                return $this->processAttachedLoTa($lota);
            }
        }
        return array();
    }
    
    public function getTypeDetail ($types) {
        $return = array();
        $cnt = 0;
        foreach($types as $type => $data) {
            foreach($data as $type_id) {
                $rows = $this->mediabankResource->fetchAll("type = '$type' and type_id = $type_id");
                if($rows !== null) {
                    foreach($rows as $row) {
                        $mid                            = $row['resource_id'];
                        $return[$cnt]['type']           = $type;
                        $return[$cnt]['type_id']        = $type_id;
                        $return[$cnt]['mid']            = MediabankResourceConstants::encode($mid);
                        $return[$cnt]['resourceTitle']  = $this->getTitleForMid($mid);
                        $return[$cnt]['editable']       = $this->allowEdit($mid);
                        $cnt++;
                    }
                }
            } 
        }
        return $return;
    }
    
    public function getHistoryOfResources($type, $id) {
        $type   = trim($type);
        $id     = (int)$id;
        if(! in_array($type, ResourceConstants::$TYPES_allowed) || $id <= 0) {
            return array();
        }
        
        $history = new MediabankResourceHistory();
        $result = array(
                        'type'  => $type,
                        'id'    => $id,
                        'rows'  => $history->getRowsForType($type, $id)
        );
        return $result;
    }
    
    private function processAttachedLoTa($rows){
        $result = array();
        $loCount = 0;
        $taCount = 0;
        $lo = new LearningObjectives();
        $ta = new TeachingActivities();
        
        if(is_array($rows) && count($rows) > 0) {
            foreach($rows as $row) {
                if($row['type'] == 'lo') {
                    $loDetail = $lo->fetchRow('auto_id ='.$row['type_id']);
                    if(!is_null($loDetail)) {
                        $result['lo'][$loCount]['auto_id'] = $loDetail->auto_id;
                        $result['lo'][$loCount]['lo'] = trim($loDetail->lo);
                        $loCount++;
                    }
                } else if($row['type'] == 'ta') {
                    $taDetail = $ta->fetchRow('auto_id ='.$row['type_id']);
                    if(!is_null($taDetail)) {
                        $result['ta'][$taCount]['auto_id'] = $taDetail->auto_id;
                        $result['ta'][$taCount]['block'] = trim($taDetail->block);
                        $result['ta'][$taCount]['pbl'] = trim($taDetail->pbl);
                        $result['ta'][$taCount]['name'] = trim($taDetail->name);
                        $taCount++;
                    }
                }
             }
        }
        return $result;
    }

    public function getListOfCollectionIds() {
        try {
            $return = array();
            $collections = $this->mediabank->getCollectionIDs(MediabankConstants::getMediabankBasePath());
            foreach($collections as $collection) {
                $return[] = $collection->collectionID;
            }
            return $return;
        } catch (Exception $ex) {
        	$error = $ex->getMessage().PHP_EOL.$ex->getTraceAsString().PHP_EOL;
        	Zend_Registry::get('logger')->warn(PHP_EOL."CLASS\t: ".__CLASS__.PHP_EOL."METHOD\t: ".__METHOD__.PHP_EOL."Error\t: ".$error.PHP_EOL);
            return array();
        }
    }
}