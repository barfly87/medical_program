<?php
class PodcastResourceDefault  extends PodcastResourceAbstract {
    
    private $_resources                 = null;
    private $_config                    = null;
    private $_mimeTypeCategories        = null;
    private $_mimeTypes                 = null;

    public function __construct($resources, $ta) {
        parent::__construct();
        parent::_setTa($ta);
        $this->_resources           = $resources;
        $this->_config              = PodcastConst::getPodcastConfigDefault();
        $this->_mimeTypeCategories  = $this->_getMimeTypeCategories();
        $this->_mimeTypes           = $this->_getMimeTypes();
        
    }    
    
    public function process() {
        $itemsXmlStr = '';
        
        foreach ($this->_resources as $resource) {
            $mid                = $resource['resource_id'];
            $metadata           = parent::_getMetadata($mid);
            $mimeTypeCategory   = isset($metadata['mimeTypeCategory']) ? $metadata['mimeTypeCategory'] : null;
            $mimeType           = isset($metadata['decodedMimeType']) ? $metadata['decodedMimeType']: null;
            
            if($this->_canThisMimeTypeCategoryBeProcessed($mimeTypeCategory)) {
                $itemsXmlStr .= $this->_getPodcastItems($resource, $metadata, $this->_mimeTypeCategories[$mimeTypeCategory]);
            } elseif($this->_canThisMimeTypeBeProcessed($mimeType)) {
                $itemsXmlStr .= $this->_getPodcastItems($resource, $metadata, $this->_mimeTypes[$mimeType]);
            }
        }
        
        return $itemsXmlStr;
    }
    
    private function _getPodcastItems(&$resource, &$metadata, &$media) {
        $mid                    = $resource['resource_id'];
        $fileInfo               = parent::_getFileInfo($mid);
        $items                  = '';
        $mediabankCollection    = MediabankResourceConstants::getCollection($mid);
        
        if(!empty($metadata)) {
            $url = $this->_createPodcastUrl($resource['auto_id'], $metadata['midEncoded'], $metadata['mimeType'], $metadata['fileTypeExtension']);
            $duration = '00:00:01';
            if(isset($fileInfo['duration']) &&  !empty($fileInfo['duration'])) {
                $duration = $fileInfo['duration'];
            }
            $fileSize = '1';
            if(isset($fileInfo['file-size'])) {
                $fileSize = $fileInfo['file-size'];
            }
            
            $rssPodcastItem = new RssPodcastItemDefault();
            $rssPodcastItem->setTitle($this->_getTaTitle($media['file-format-text']));
            $rssPodcastItem->setLink(parent::_getTaUrl());
            $rssPodcastItem->setDescription(parent::_getTaName());
            $rssPodcastItem->setItunesAuthor(parent::_getTaAuthor());
            $rssPodcastItem->setItunesDuration($this->_getDuration($duration));
            $rssPodcastItem->setPubDate(parent::_getPubDate());
            $rssPodcastItem->setEnclosureAttrUrl($url);
            $rssPodcastItem->setEnclosureAttrLength($fileSize);
            $rssPodcastItem->setEnclosureAttrType($metadata['decodedMimeType']);
            $rssPodcastItem->setItemFileFormat($media['file-format']);
            $rssPodcastItem->setItemMediabankCollection($mediabankCollection);
            $items .= $rssPodcastItem->saveAsXml();
        }
        return $items;
    }
    
    private function _createPodcastUrl($resourceId, $mid, $mimeType, $fileExtension) {
        return sprintf('http://%s%s/podcast/resource/download/typeid/%d/type/ta/resource_id/%d/mid/%s/mt/%s/file%d.%s',
                         $_SERVER['HTTP_HOST'], Compass::baseUrl(), $this->_getTaAutoId(), $resourceId, $mid , $mimeType, $resourceId, $fileExtension
        );
    }

    private function _canThisMimeTypeCategoryBeProcessed($mimeTypeCategory) {
        if(!empty($this->_mimeTypeCategories)) {
            return in_array($mimeTypeCategory, array_keys($this->_mimeTypeCategories));
        }
        return false;
    }
    
    private function _canThisMimeTypeBeProcessed($mimeType) {
        if(!empty($this->_mimeTypes)) {
            return in_array($mimeType, array_keys($this->_mimeTypes));
        }
        return false;
    }

    private function _getMimeTypes() {
        $mimeTypes = array();
        if(!empty($this->_config) && isset($this->_config['mimeType'])) {
            foreach($this->_config['mimeType'] as $mimeType) {
                $fileFormat = null;
                $fileFormatText = null;
                switch($mimeType) {
                    case 'application/pdf':
                        $fileFormat = PodcastConst::fileFormatPdf;
                        $fileFormatText = PodcastConst::fileFormatPdfText;
                        break;
                }
                if(!is_null($fileFormat) && !is_null($fileFormatText)) {
                    $mimeTypes[$mimeType]['file-format'] = $fileFormat;
                    $mimeTypes[$mimeType]['file-format-text'] = $fileFormatText;
                } else {
                    $error = 'file-format and file-format-text does not exist for mimeType : '. $mimeType;
                    Zend_Registry::get('logger')->warn(PHP_EOL."CLASS\t: ".__CLASS__.PHP_EOL."METHOD\t: ".__METHOD__.PHP_EOL."Error\t: ".$error.PHP_EOL);
                }
            }
        }
        return $mimeTypes;
    }

    private function _getMimeTypeCategories() {
        $mimeTypeCategories = array();
        if(!empty($this->_config) && isset($this->_config['mimeTypeCategory'])) {
            foreach($this->_config['mimeTypeCategory'] as $mimeTypeCategory) {
                $fileFormat = null;
                $fileFormatText = null;
                switch($mimeTypeCategory) {
                    case 'audio':
                        $fileFormat = PodcastConst::fileFormatAudio;
                        $fileFormatText = PodcastConst::fileFormatAudioText;
                        break;
                    case 'video':
                        $fileFormat = PodcastConst::fileFormatVideo;
                        $fileFormatText = PodcastConst::fileFormatVideoText;
                        break;
                    case 'image':
                        $fileFormat = PodcastConst::fileFormatImage;
                        $fileFormatText = PodcastConst::fileFormatImageText;
                        break;
                }
                if(!is_null($fileFormat) && !is_null($fileFormatText)) {
                    $mimeTypeCategories[$mimeTypeCategory]['file-format'] = $fileFormat;
                    $mimeTypeCategories[$mimeTypeCategory]['file-format-text'] = $fileFormatText;
                } else {
                    $error = 'file-format and file-format-text does not exist for mimeTypeCategory : '. $mimeTypeCategory;
                    Zend_Registry::get('logger')->warn(PHP_EOL."CLASS\t: ".__CLASS__.PHP_EOL."METHOD\t: ".__METHOD__.PHP_EOL."Error\t: ".$error.PHP_EOL);
                }
            }
        }
        return $mimeTypeCategories;
    }
    
    private function _getDuration($duration) {
        $time = '';
        if(!empty($duration)) {
            $durationParts = explode('.', $duration);
            if(count($durationParts) == 2) {
                $time = $durationParts[0];
            }
        }
        return $time;
    }
    
    private function _getTaTitle($mediaText) {
        $titleParts = array(parent::_getDefaultTaTitle(), $mediaText);
        $title = implode(' - ', $titleParts);
        return PodcastConst::_addNumberToTitleIfTitleAlreadyExist($title);
    }
}
?>