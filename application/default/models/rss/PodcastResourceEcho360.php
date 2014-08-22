<?php
class PodcastResourceEcho360  extends PodcastResourceAbstract {

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
            $presentationId     = $this->_getPresentationId($nativeMetadataXmlObj);
            $year               = parent::_getYearFromMid($mid);
            if(!empty($mediaData)) {
                foreach($mediaData as $mediaType => $media) {
                    $url = $this->_createPodcastUrl($resource['auto_id'], $presentationId, $media['media-type'], $media['file-extension']);
                    $title = $this->_getTaTitle($year, $media['file-format-title']);
                    $rssPodcastItem = new RssPodcastItemEcho360();
                    $rssPodcastItem->setTitle($title);
                    $rssPodcastItem->setLink(parent::_getTaUrl());
                    $rssPodcastItem->setDescription(parent::_getTaName());
                    $rssPodcastItem->setItunesAuthor(parent::_getTaAuthor());
                    $rssPodcastItem->setItunesDuration($duration);
                    $rssPodcastItem->setPubDate(parent::_getPubDate());
                    $rssPodcastItem->setEnclosureAttrUrl($url);
                    $rssPodcastItem->setEnclosureAttrLength($media['file-size']);
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

    private function _createPodcastUrl($resourceId, $presentationId,  $mediaType, $fileExtension) {
        return sprintf('http://%s%s/podcast/resource/download/typeid/%d/type/ta/resource_id/%d/echo360_id/%s/media/%s/file%d.%s',
                $_SERVER['HTTP_HOST'], Compass::baseUrl(), $this->_getTaAutoId(), $resourceId, $presentationId , $mediaType, $resourceId, $fileExtension
                );
    }

    private function _getDuration(&$xmlObject) {
        $time           = '';
        if($xmlObject instanceof SimpleXMLElement) {
            $durationElem = $xmlObject->xpath('/presentation/duration-milliseconds');
            if(!empty($durationElem) && isset($durationElem[0])) {
                $milliseconds   = (string)$durationElem[0];
                $seconds        = floor($milliseconds / 1000);
                $minutes        = floor($seconds / 60);
                $hours          = floor($minutes / 60);
                $seconds        = $seconds % 60;
                $minutes        = $minutes % 60;
                $format         = '%02u:%02u:%02u';
                $time           = sprintf($format, $hours, $minutes, $seconds);
            }
        }
        return $time;
    }

    private function _getMediaData(&$xmlObject) {
        $mediaData        = array();
        if($xmlObject instanceof SimpleXMLElement) {
            $echoAudioMediaType = 'mp3';
            $audioElem = $xmlObject->xpath('//capture-medias/capture-media/media[type="'.$echoAudioMediaType.'"]/file-size-in-bytes');
            if(!empty($audioElem) && isset($audioElem[0])) {
                $mediaData['audio']['file-size']            = (string)$audioElem[0];
                $mediaData['audio']['file-extension']       = $echoAudioMediaType;
                $mediaData['audio']['file-format']          = PodcastConst::fileFormatAudio;
                $mediaData['audio']['file-format-title']    = PodcastConst::fileFormatAudioText;
                $mediaData['audio']['mime-type']            = 'audio/mp3';
                $mediaData['audio']['media-type']           = $echoAudioMediaType;
            }
            $echoVideoMediaType = 'm4v';
            $videoElem = $xmlObject->xpath('//capture-medias/capture-media/media[type="'.$echoVideoMediaType.'"]/file-size-in-bytes');
            if(!empty($videoElem) && isset($videoElem[0])) {
                $mediaData['video']['file-size']            = (string)$videoElem[0];
                $mediaData['video']['file-extension']       = $echoVideoMediaType;
                $mediaData['video']['file-format']          = PodcastConst::fileFormatVideo;
                $mediaData['video']['file-format-title']    = PodcastConst::fileFormatVideoText;
                $mediaData['video']['mime-type']            = 'video/m4v';
                $mediaData['video']['media-type']           = $echoVideoMediaType;
            }
        }
        return $mediaData;
    }

    private function _getTaTitle($year, $extraInfo) {
        $titleParts = array($year, parent::_getDefaultTaTitle(), $extraInfo);
        $title = implode(' - ', $titleParts);
        return PodcastConst::_addNumberToTitleIfTitleAlreadyExist($title);

    }

    private function _getPresentationId(&$xmlObject) {
        $presentationId = '';
        if($xmlObject instanceof SimpleXMLElement) {
            $presentationIdElem = $xmlObject->xpath('/presentation/id');
            if(!empty($presentationIdElem) && isset($presentationIdElem[0])) {
                $presentationId = (string)$presentationIdElem[0];
            }
        }
        return $presentationId;
    }
}
?>