<?php
class MediabankConstants {
    public $mepositoryID = null;
    public static $mediabankBasePath = null;
    public $mediabank = null;
    public function __construct(){
        $this->mepositoryID = self::getMediabankBasePath();
        $this->mediabank = new Mediabank($this->mepositoryID, $this->mepositoryID."cxfws/Core?wsdl");
    }
    
    public static function getMediabankBasePath() {
        if(is_null(self::$mediabankBasePath)) {
            $config = Zend_Registry::get('config');
            self::$mediabankBasePath = $config->mediabank_basepath;
        }
        return self::$mediabankBasePath;
    }
    
    public static function getMepositoryId() {
        return self::getMediabankBasePath();
    }
    
    public static function addUrl() {
        return self::getMediabankBasePath().'REST/add';
    }
    
    public static function updateUrl() {
        return self::getMediabankBasePath().'REST/update';
    }
    
    public static function downloadUrl() {
        return self::getMediabankBasePath().'REST/getObject:file=0?mid=';
    }
    
    public static function imageUrl() {
        return self::getMediabankBasePath().'REST/getObject/transform:file=0/getimage/resize:width=%%%WIDTH%%%:height=%%%HEIGHT%%%:format=png?mid=';
    }    

    public static function originalImageUrl() {
        return self::getMediabankBasePath().'REST/getObject/transform:file=0/getimage:format=png?mid='; 
    }
    
    public static function transcodeFlvUrl() {
        return self::getMediabankBasePath().'REST/getObject/transform:file=0/transcode:width=%%%WIDTH%%%:height=%%%HEIGHT%%%?mid=';
    }

    public static function transcodeMp3Url() {
        return self::getMediabankBasePath().'REST/getObject/transform/transcode:format=mp3?mid=';
    }
        
    public static function transcodeMp4Url() {
    	//return self::getMediabankBasePath().'REST/getObject/transform:file=0/transcode:wrapper=mp4:vcodec=h264:acodec=mp3:width=%%%WIDTH%%%:height=%%%HEIGHT%%%?mid=';
        return self::getMediabankBasePath().'REST/getObject/transform:file=0/transcode:format=mp4:width=%%%WIDTH%%%:height=%%%HEIGHT%%%?mid=';
    }
    
    public static function fileInfoUrl() {
        return self::getMediabankBasePath().'REST/getObject/transform/file-info?mid=';
    }
    
}
?>