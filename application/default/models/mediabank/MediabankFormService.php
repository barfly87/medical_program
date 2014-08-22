<?php
class MediabankFormService {
    
    public $title = null;
    public $desc = null;
    public $copyright = null;
    public $author = null;
    public $html = null;
    public $action = null;
    public $cid = null;
    public $mid = null;
    public $fileLocation = null;
    public $processFile = null;
    public $URL = null;
    public $status = null;
    
    /**
     * 
     * @param mixed $params
     */
    public function __construct($params) {
        $this->title        = isset($params['title'])           ? $params['title']                  : null ;
        $this->desc         = isset($params['desc'])            ? $params['desc']                   : null ;
        $this->copyright    = isset($params['copyright'])       ? $params['copyright']              : null ;
        $this->author       = isset($params['author'])          ? $params['author']                 : null ;
        $this->action       = isset($params['action'])          ? $params['action']                 : null ;
        $this->mid          = isset($params['mid'])             ? $params['mid']                    : null ;
        $this->fileLocation = isset($params['fileLocation'])    ? $params['fileLocation']           : null ;
        $this->processFile  = isset($params['processFile'])     ? $params['processFile']            : null ;
        $this->URL          = isset($params['URL'])             ? $params['URL']                    : null ;
        $this->status       = isset($params['status'])          ? $params['status']                 : null ;
        $this->cid          = MediabankResourceConstants::$cid;
        
        if(isset($params['html'])){
            if(get_magic_quotes_gpc()){
                $this->html = stripslashes($params['html']) ;
            } else {
                $this->html = $params['html'] ;
            }
            $this->html = MediabankResourceConstants::addHtmlDoctype($this->html);
        }
    }

    public function process() {
        $metadataXml = $this->getMetadataXml();
        $fileLocation = $this->getFileLocation(); 
        $postData = $this->createPostData($metadataXml,$fileLocation);
        $return = null;
        if(is_null($this->mid)) {
            $return = $this->addResource($postData);
        } else {
            $return = $this->updateResource($postData);
        }
        if(strlen($return) > 0 && stristr($return, '|'.MediabankResourceConstants::$cid.'|')) {
            return array( 'mid' => $return);
        } else {
            return array('mediabankError' => $return);
        }
    }
    
    private function getMetadataXml() {
        if(!is_null($this->mid)) {
            return $this->getMetadataXmlForEditAction();
        } else {
            return $this->getMetadataXmlForAddAction();
        }
    }
    
    private function getMetadataXmlForAddAction() {
        $xmlArray = $this->createArray();
        $xmlString = $this->createXml($xmlArray);
        return $this->createXmlFile($xmlString);
    }
    
    private function getMetadataXmlForEditAction() {
        $mediabankResourceService = new MediabankResourceService();
        $metadata = $mediabankResourceService->getMetadataXmlObjForMid($this->mid);
        $xml = simplexml_load_string($metadata);
        $tags = $this->createArray();
        foreach($tags[MediabankResourceConstants::$METADATA_xmlRoot] as $tagName => $tagValue) {
            if(isset($xml->$tagName)) {
                $xml->$tagName = $tagValue;
            }
        }
        $xmlString =  $xml->asXml();
        return $this->createXmlFile($xmlString);
    }

    private function createArray(){
        $status = (!empty($this->status)) ? $this->status : MediabankResourceConstants::$METADATA_status_NEW;
        return array (
            MediabankResourceConstants::$METADATA_xmlRoot => array(
                MediabankResourceConstants::$METADATA_title         => $this->title,
                MediabankResourceConstants::$METADATA_copyright     => $this->copyright,
                MediabankResourceConstants::$METADATA_description   => $this->desc,
                MediabankResourceConstants::$METADATA_creator       => UserAcl::getUid(),
                MediabankResourceConstants::$METADATA_status        => $status,
                MediabankResourceConstants::$METADATA_author        => $this->author
            )
        );        
    }
    
    private function createXml($array){
        $xmlService = new XMLService();
        return $xmlService->createXMLfromArray($array);
    }

    private function createXmlFile($xmlString){
        $dir = MediabankResourceConstants::$tempDir;
        $file =  $dir.'/'.UserAcl::getUid().time();
        if(is_dir($dir)){
            file_put_contents($file, $xmlString);
            return $file;
        }
        return '';
    }
    
    private function getFileLocation() {
        switch($this->processFile){
            case 'html':
                return $this->getHtmlFileLocation();
            break;
            case 'any':
                 return $this->fileLocation;
            break;
            case 'URL':
                return $this->getUrlFileLocation();
            break;
        }
    }
    
    private function getUrlFileLocation () {
    	if(!empty($this->URL)) {
            $dir = MediabankResourceConstants::$tempDir;
            $file =  $dir.'/'.UserAcl::getUid().time().'.url';
            if(is_dir($dir)){
                file_put_contents($file, $this->URL);
                return $file;
            }
            return '';
    	}
    }
    
    private function getHtmlFileLocation() {
        if(!is_null($this->html) && !empty($this->html) && is_string($this->html)) {
            $dir = MediabankResourceConstants::$tempDir;
            $file =  $dir.'/'.UserAcl::getUid().time().'.html';
            if(is_dir($dir)){
                file_put_contents($file, $this->html);
                return $file;
            }
            return '';
        }
    }
    
    private function addResource ($postData){
        $mediabankUtility = new MediabankUtility();
        return $mediabankUtility->addResource($postData);
    }
    
    private function updateResource ($postData) {
        $mediabankUtility = new MediabankUtility();
        return $mediabankUtility->updateResource($postData);
    }
    
    private function createPostData($xmlFileLocation, $fileLocation){
        $postData = array();
        if(!is_null($this->mid)) {
            $postData['mid'] = $this->mid;
        }
        $postData[MediabankResourceConstants::$cidForm] = $this->cid;
        $postData[MediabankResourceConstants::$metadataFile] = '@'.$xmlFileLocation;
        if(!empty($fileLocation)) {
            $postData[MediabankResourceConstants::$dataFile0] = '@'.$fileLocation;
        }
        return $postData; 
    }
}
?>