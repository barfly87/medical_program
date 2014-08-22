<?php

abstract class PodcastDownloadAbstract {
    
    abstract protected function process($GET);
    
    protected function _dumpContentsOfURLUsingCurl($url) {
        $mediabankUtility = new MediabankUtility();
        $mediabankUtility->curlUrlGet($url);
    }

    /**
     * It seems that iTunes sends a HEAD request just to check the mimeType so we should just return the mimeType for those requests.
     * @return boolean
     */
    protected function _isNotAHeadRequest() {
        return isset($_SERVER['REQUEST_METHOD']) && strtolower($_SERVER['REQUEST_METHOD']) != 'head';
    }
    
    protected function _throwMimeTypeHeaderBasedOnRequestUri() {
        // e.g Echo360 URL 
        //$reqUrl = /compass/podcast/resource/download/typeid/1265/type/ta/resource_id/18753/echo360_id/2152d779-585f-4643-bbe3-58a7ca78a4c8/media/m4v/file18753.m4v
        // e.g Lectopia URL 
        //$reqUrl = compass/podcast/resource/download/typeid/2125/type/ta/resource_id/10979/format_id/151601/file151601.mp3
        $reqUrl = $_SERVER['REQUEST_URI'];
        if(preg_match('/(.*)file(.*)\.(.*)/', $reqUrl, $matches) !== false) {
            if(isset($matches[3]) && !empty($matches[3])) {
                $fileExtension = $matches[3];
                if(!empty($fileExtension)) {
                    $mimeTypes = Compass::getConfig('podcast.mimetype.for.fileextension');
                    if(!empty($mimeTypes) && is_array($mimeTypes) && isset($mimeTypes[$fileExtension])) {
                        $this->_throwContentTypeHeader($mimeTypes[$fileExtension]);
                    }
                }
            }
        }
    }
    
    protected function _getSystemMimeTypes() {
        $mimeTypes = array();
        $mimeTypeFileName = '/etc/mime.types';
        if(file_exists($mimeTypeFileName)) {
            $file = fopen($mimeTypeFileName, 'r');
            while(($line = fgets($file)) !== false) {
                $line = trim(preg_replace('/#.*/', '', $line));
                if(strlen($line) > 0 ) {
                    $parts = preg_split('/\s+/', $line);
                    if(count($parts) > 1) {
                        $type = array_shift($parts);
                        foreach($parts as $part) {
                            $mimeTypes[$part] = $type;
                        }
                    }
                }
            }
            fclose($file);
        }
        return $mimeTypes;

    }
    
    protected function _throwContentTypeHeader($mimeType) {
        header("Content-type: '$mimeType'");
    }
    
}
?>