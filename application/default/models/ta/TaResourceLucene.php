<?php
class TaResourceLucene extends TaResourceAbstract {

    public function __construct($taId) {
        $this->startProcess($taId);
    }
    
    public function getResources() {
        $return = array();
        $return['content']      = $this->_getContent();
        $return['reference']    = $this->_getReferences();
        $lectureRecordings      = $this->_getLectureRecordings();
        $student                = $this->_getGeneralResources(ResourceTypeConstants::$ALLOW_STUDENT);
        $staff                  = $this->_getGeneralResources(ResourceTypeConstants::$ALLOW_STAFF);
        $return['other_student']= array_merge($lectureRecordings, $student);
        $return['other_staff']  = array_merge($lectureRecordings, $staff);
        
        return $return;
    }
    
    private function _getContent() {
        $contents = $this->getContentDefault();
        return $this->getHtmlForResources($contents);
    }
    
    private function _getReferences() {
        $references = $this->getReferencesDefault();
        return $this->getHtmlForResources($references);
    }
    
    private function _getGeneralResources($user) {
        $return = array();
        $generalResources = $this->getGeneralResourcesDefault($user);
        if(!empty($generalResources)) {
            $staffOnlyText = MediabankResourceConstants::staffOnlyText();
            foreach ($generalResources as $resource) {
                if (!empty($resource['customImageUrl']) && $resource['customImageUrl'] == Compass::baseUrl().CmsConst::$cmsResrcImage) {
                    continue;
                }
                try {
                    $metadata = $this->mediabankResourceService->getMetaData($resource['mid']);
                    $staffOnlyTitle = ($resource['staffOnly'] === true) ? $staffOnlyText : '';
                    $imageSrc = $this->_getImageUrlByMimeType($metadata['decodedMimeType'], $resource['compassImageUrl'].'&size=32');
                    $fileType = (strpos($metadata['decodedMimeType'], "/") != false) ? 
                                        substr($metadata['decodedMimeType'], (strpos($metadata['decodedMimeType'], "/")+1)) : $metadata['decodedMimeType'];
                    $fileType = htmlentities($fileType);
                    $imgTitle = htmlentities($metadata['title']);
                    $return[] = <<<HEREDOCS
<a style="text-decoration: none !important;" rel="fileType:{$fileType}" href="{$resource['compassDownloadUrl']}&mt={$metadata['mimeType']}&fn={$metadata['fileName']}"><img src="{$imageSrc}" border="0" title="{$imgTitle} {$staffOnlyTitle}"></a>
HEREDOCS;
                } catch (Exception $e) {
                    Zend_Registry::get('logger')->warn($e->getMessage());
                }    
            }
        }    
        return $return;    
    }
    
    private function _getLectureRecordings() {
        $return = array();
        try {
            $lectures = $this->getLectureRecordingsDefault();
            $medvids = $lectures['medvid'];
            $echo360 = $lectures['echo360'];
            $otherResources = $lectures['other'];
            if(!empty($medvids) || !empty($echo360)) {
                $processedMediabankCollections = $this->_processMedibankCollections($medvids, $echo360);
                $return = array_merge($return, $processedMediabankCollections);
            }
            if(!empty($otherResources)) {
                $processedOtherResources = $this->_processOtherResources($otherResources);
                $return = array_merge($return, $processedOtherResources);
            }
        } catch (Exception $e) {
            Zend_Registry::get('logger')->warn($e->getMessage());
        }    
        return $return;
    }
    
    private function _processMedibankCollections($medvids, $echo360) {
        $return = array();
        foreach($medvids as $year => $data) {
            foreach($data['info'] as $type => $typeinfo) {
                $viewUrl = $this->_createLectureRecordingHref($typeinfo);
                $onclick = (isset($typeinfo['onclick'])) ? 'onclick = "'.$typeinfo['onclick'].'"' : '';
                $imageUrl = MediabankResourceConstants::createCompassImageUrl($typeinfo['metadata']['midEncoded'], null, 32, 32);                                 
                $imageSrc = $this->_getImageUrlByMimeType($typeinfo['metadata']['decodedMimeType'], $imageUrl);    
                $fileType = htmlentities($typeinfo['filetype']);
                $imgTitle = htmlentities($typeinfo['metadata']['title']);
                $return[] = <<<HEREDOCS
<a  style="text-decoration: none !important;" href="{$viewUrl}" rel="fileType:{$fileType}" {$onclick}><img src="{$imageSrc}" title="{$imgTitle} - {$year}" border="0"></a>
HEREDOCS;
            }
        }
        
        foreach($echo360 as $year => $data) {
            if(isset($data['links'])){
                foreach ($data['links']as $link) {
                    $return[] = $link['aHref'];            
                }
            } 
        }
        return $return;                
    }
    
    private function _processOtherResources($otherResources) {
        $return = array();
        foreach($otherResources as $other) {
            $viewUrl = $other['compassDownloadUrl'].'&fn='.$other['metadata']['fileName'].'&mt='.$other['metadata']['mimeType'];
            $onclick = '';
            if((isset($other['customOnclick'])) && isset($other['customViewUrl'])) {
                $onclick = 'onclick = "'.$other['customOnclick'].'"';
                $viewUrl = $other['customViewUrl'];
            }
            $imageUrl =  MediabankResourceConstants::createCompassImageUrl($other['metadata']['midEncoded'], null, 32, 32);
            $imageSrc = $this->_getImageUrlByMimeType($other['metadata']['decodedMimeType'], $imageUrl);
            $fileType = htmlentities($other['metadata']['mimeType']);
            $imgTitle = htmlentities($other['metadata']['title']);
            
            $return[] = <<<HEREDOCS
<a href="{$viewUrl}" style="text-decoration: none !important;" rel="fileType:{$fileType}" {$onclick}><img src="{$imageSrc}" title="{$imgTitle}" border="0"></a>
HEREDOCS;
        }
        return $return;
    }
    
    private function _createLectureRecordingHref($typeinfo) {
        $downloadUrlLectureRecordings = Compass::baseUrl().MediabankResourceConstants::$compassDownloadUrlBasePath.'?mid=%s&fn=%s&mt=%s';
        return (isset($typeinfo['customUrl'])) ? 
               $typeinfo['customUrl'] : 
               sprintf($downloadUrlLectureRecordings, $typeinfo['metadata']['midEncoded'],$typeinfo['customFileName'], $typeinfo['metadata']['mimeType']);
    }
    
    private function _getImageUrlByMimeType($mimeType, $url) {
        $imageUrlByMimeType = MediabankResourceConstants::getImageUrlByMimeType($mimeType, null, 32, 32);
        return ($imageUrlByMimeType != '') ? $imageUrlByMimeType : $url;
    }
    
}
?>