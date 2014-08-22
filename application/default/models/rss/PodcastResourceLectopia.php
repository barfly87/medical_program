<?php
class PodcastResourceLectopia extends PodcastResourceAbstract {
    private $_resources                 = null;

    public function __construct($resources, $ta) {
        parent::__construct();
        parent::_setTa($ta);
        $this->_resources = $resources;
    }

    public function process() {
        $itemsXmlStr = '';
        foreach ($this->_resources as $resource) {
            $itemsXmlStr .= $this->_getPodcastItems($resource);
        }
        return $itemsXmlStr;
    }

    private function _getPodcastItems($resource) {
        $mid                    = $resource['resource_id'];
        $nativeMetadataXmlObj   = parent::_getNativeMetadataXmlObj($mid);
        $mediabankCollection    = MediabankResourceConstants::getCollection($mid);
        $items                  = '';
        if(!empty($nativeMetadataXmlObj)) {
            $mediaData          = $this->_getMediaData($nativeMetadataXmlObj);
            $duration           = $this->_getDuration($nativeMetadataXmlObj);
            $year               = parent::_getYearFromMid($mid);
            if(!empty($mediaData)) {
                foreach($mediaData as $mediaType => $media) {
                    $url = $this->_createPodcastUrl($resource['auto_id'], $media['format']->FormatID, $media['media-type'], $media['file-extension']);
                    $title = $this->_getTaTitle($year, $media['file-format-title']);
                    $filesize = (isset($media['format']->FileSizeInKB) && $media['format']->FileSizeInKB > 0 ) ? $media['format']->FileSizeInKB * 1024 : '1';
                    
                    $rssPodcastItem = new RssPodcastItemEcho360();
                    $rssPodcastItem->setTitle($title);
                    $rssPodcastItem->setLink(parent::_getTaUrl());
                    $rssPodcastItem->setDescription(parent::_getTaName());
                    $rssPodcastItem->setItunesAuthor(parent::_getTaAuthor());
                    $rssPodcastItem->setItunesDuration($duration);
                    $rssPodcastItem->setPubDate(parent::_getPubDate());
                    $rssPodcastItem->setEnclosureAttrUrl($url);
                    $rssPodcastItem->setEnclosureAttrLength($filesize);
                    $rssPodcastItem->setEnclosureAttrType($media['mime-type']);
                    $rssPodcastItem->setItemFileFormat($media['file-format']);
                    $rssPodcastItem->setItemYear($year);
                    $rssPodcastItem->setItemMediabankCollection($mediabankCollection);
                    $items .= $rssPodcastItem->saveAsXml();
                }
            }
        }
        return $items;
    }

    private function _getDuration(&$xmlObject) {
        $time           = '00:00:01';
        if($xmlObject instanceof SimpleXMLElement) {
            $durationElem = $xmlObject->xpath('/recording/Duration');
            if(!empty($durationElem) && isset($durationElem[0])) {
                $duration = (string)$durationElem[0];
                $time = $this->_createDuration((int)$duration);
            }
        }
        return $time;
    }
    
    private function _createDuration($duration) {
        $hours = '00';
        $mins = '00';
        if($duration > 0) {
            if($duration >= 60){
                $hours = floor($duration/60);
                if($hours < 10) {
                    $hours = '0'.$hours;
                }
                $mins = $duration % 60;
                if($mins < 10) {
                    $mins = '0'.$mins;
                }
            } else {
                $mins = $duration;
            }
        }
        return sprintf('%s:%s:00', $hours, $mins);
    }
    
    private function _getMediaData(&$xmlObject) {
        $mediaData        = array();
        if($xmlObject instanceof SimpleXMLElement) {
            $echoAudioMediaType = 'mp3';
            $audioElem = $xmlObject->xpath('//format[SettingName="mp3 audio, Download, 56k"]');
            if(!empty($audioElem) && isset($audioElem[0])) {
                $mediaData['audio']['format']               = $audioElem[0];
                $mediaData['audio']['file-extension']       = $echoAudioMediaType;
                $mediaData['audio']['file-format']          = PodcastConst::fileFormatAudio;
                $mediaData['audio']['file-format-title']    = PodcastConst::fileFormatAudioText;
                $mediaData['audio']['mime-type']            = 'audio/'.$echoAudioMediaType;
                $mediaData['audio']['media-type']           = $echoAudioMediaType;
            }
            $echoVideoMediaType = 'mp4';
            $videoElem = $xmlObject->xpath('//format[SettingName="Quicktime video, Download, 1fps, 640x480 pixels, 256k"]');
            if(!empty($videoElem) && isset($videoElem[0])) {
                $mediaData['video']['format']               = $videoElem[0];
                $mediaData['video']['file-extension']       = $echoVideoMediaType;
                $mediaData['video']['file-format']          = PodcastConst::fileFormatVideo;
                $mediaData['video']['file-format-title']    = PodcastConst::fileFormatVideoText;
                $mediaData['video']['mime-type']            = 'video/'.$echoVideoMediaType;
                $mediaData['video']['media-type']           = $echoVideoMediaType;
            }
        }
        return $mediaData;
    }
    
    private function _createPodcastUrl($resourceId, $formatId, $fileExtension) {
        return sprintf('http://%s%s/podcast/resource/download/typeid/%d/type/ta/resource_id/%d/format_id/%d/file%s.%s',
                $_SERVER['HTTP_HOST'], Compass::baseUrl(), $this->_getTaAutoId(), $resourceId, $formatId , $formatId, $fileExtension
                );
    }
    
    private function _getTaTitle($year, $extraInfo) {
        $titleParts = array($year, parent::_getDefaultTaTitle(), $extraInfo);
        $title = implode(' - ', $titleParts);
        return PodcastConst::_addNumberToTitleIfTitleAlreadyExist($title);
    }
}