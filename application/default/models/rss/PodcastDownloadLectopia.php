<?php
class PodcastDownloadLectopia extends PodcastDownloadAbstract {
    
    public function process($GET) {
        $header = $this->_processGetParams($GET);
        if(! empty($header) && parent::_isNotAHeadRequest()) {
            parent::_throwContentTypeHeader($header['mimetype']);
            parent::_dumpContentsOfURLUsingCurl($header['url']);
        } else {
            parent::_throwMimeTypeHeaderBasedOnRequestUri();
            $error = 'Could not find URL for request params.' . PHP_EOL . print_r($GET, true);
            Zend_Registry::get('logger')->warn(PHP_EOL."CLASS\t: ".__CLASS__.PHP_EOL."METHOD\t: ".__METHOD__.PHP_EOL."Error\t: ".$error.PHP_EOL);
        }
        exit;
    }

    private function _processGetParams($GET) {
        $header = array();
        if( isset($GET['resource_id']) && (int)$GET['resource_id'] > 0
        && isset($GET['typeid']) && (int)$GET['typeid'] > 0
        && isset($GET['format_id']) && (int)$GET['format_id'] > 0
        ) {
            $typeId             = (int)$GET['typeid'];
            $resourceAutoId     = (int)$GET['resource_id'];
            $where              = sprintf("auto_id = %d and type_id = %d", $resourceAutoId, $typeId);
            
            $mediabankResource = new MediabankResource();
            $result = $mediabankResource->fetchRow($where);
            $header = array();
            if(! empty($result)) {

                //Grab Native metadata from Mediabank
                $mediabankResourceService = new MediabankResourceService();
                $xml = $mediabankResourceService->getMetadataXmlObjForMid($result['resource_id']);

                //Find the node for the requested format_id
                $simpleXmlElement = new SimpleXMLElement($xml);
                $xpath = $simpleXmlElement->xpath(sprintf('//formats/format[FormatID="%d"]', $GET['format_id']));

                if(count($xpath) > 0) {
                    //Get the 'SettingName' and 'FilePath' element from the xml node.
                    $settingName = (string)$xpath[0]->SettingName;
                    $url = (string)$xpath[0]->FilePath;
                    $mimetype = null;
                    if(stristr($settingName, 'audio')) {
                        $mimetype = 'audio/mp3';
                    } elseif (stristr($settingName, 'video')) {
                        $mimetype = 'video/mp4';
                    }

                    if(!is_null($mimetype)) {
                        $header = array (
                                    'mimetype'  => $mimetype,
                                    'url'       => $url
                                    );
                    } else {
                        $error = 'Could not find mime type for request params.'.PHP_EOL.print_r($GET, true);
                        Zend_Registry::get('logger')->warn(PHP_EOL."CLASS\t: ".__CLASS__.PHP_EOL."METHOD\t: ".__METHOD__.PHP_EOL."Error\t: ".$error.PHP_EOL);
                    }
                }
            }
        }
        return $header;
    }

}