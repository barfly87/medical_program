<?php
class MediabankResourceConstants {
    
    public static $cid                              = 'compassresources';
    public static $tempDir                          = '/tmp';
    public static $cidForm                          = 'cid';
    public static $metadataFile                     = 'metadataFile';
    public static $dataFile0                        = 'dataFile0';
    public static $frontend                         = 'compass';
    
    public static $FORM_title                       = 'title';
    public static $FORM_titleError                  = 'Title is missing.';
    public static $FORM_copyright                   = 'copyright';
    public static $FORM_copyrightError               = 'Copyright is not selected.';
    public static $FORM_other                       = 'other';
    public static $FORM_otherError                  = 'Please specify text for \'Other\' copyright in textbox provided.';
    public static $FORM_description                 = 'description';
    public static $FORM_author                      = 'author';
    public static $FORM_resourceTypeIdPost          = 'resourceTypeIdPost';
    public static $FORM_resourceTypeIdPostError     = 'Resource type is not selected.';
    public static $FORM_htmlText                    = 'htmlText';
    public static $FORM_htmlTextError               = 'Please type some text and submit again.';
    public static $FORM_file                        = 'file';
    public static $FORM_fileError                   = 'Please select file to upload and submit again.';
    public static $FORM_URL                         = 'URL';
    public static $FORM_URLError                    = 'Please type the URL and submit again.';
    public static $FORM_URLInvalidError             = 'Please enter a valid URL.';
    public static $FORM_tempResource                = 'tempResource';
    public static $FORM_tempResourceVal             = 'yes';
    public static $FORM_uploadType                  = 'uploadtype';
    public static $FORM_uploadTypes                 = array('text','file','url');
    public static $FORM_actionName                  = 'actionName';
    public static $FORM_actionNameUpload            = 'upload';
    public static $FORM_actionNameEdit              = 'edit';
    public static $FORM_actionNameError             = 'System error. Required parameters not found.';
    public static $FORM_tabSelected                 = 'tabSelected';
    public static $FORM_tabSelectedError            = 'Error related to tab selection.';
    public static $FORM_mid                         = 'mid';
    public static $FORM_div                         = 'div';
    public static $FORM_mediabankError              = 'Problems uploading resource.';
    public static $FORM_MSG_addSuccess              = 'Successfully uploaded.';
    public static $FORM_MSG_editSuccess             = 'You have successfully edited the resource.';
    public static $FORM_REINDEX_collection          = 'collection';
    
    public static $FORM_looseResourceReferer        = 'loose_resource_referer';
    public static $FORM_looseResourceAddedOnce      = 'loose_resource_added_once';
    public static $FORM_looseResourceAddedOnceVal   = 'yes';
    
    public static $DB_updateResourceError           = 'Database error. Resource data could not be updated in the database.';
    public static $DB_addResourceError              = 'Database error. Resource data could not be added in the database.';
    public static $DB_updateResourceHistoryError    = 'Database error. Resource history could not be updated in the database.';
    
    public static $METADATA_xmlRoot                 = 'compassresource';   
    public static $METADATA_title                   = 'title';
    public static $METADATA_copyright               = 'copyright';
    public static $METADATA_description             = 'description';
    public static $METADATA_creator                 = 'creator';
    public static $METADATA_author                  = 'author';
    public static $METADATA_status                  = 'status';
    public static $METADATA_status_NEW              = 'NEW';
    public static $METADATA_status_LOOSE            = 'LOOSE';
    
    public static $LUCENEFIELD_collectionID         = 'collectionID';
    
    public static $SCHEMA_native                    = 'native';
    public static $SCHEMA_smp                       = 'Sydney Medical Program Structure';
    public static $SCHEMA_dublinCore                = 'Dublin Core';
    
    public static $imageMimeType                    = 'image/png';
    public static $imageExtension                   = 'png';
    public static $audioMp3MimeType                 = 'audio/mp3';
    public static $audioMp3Extension                = 'mp3';
    public static $videoFlvMimeType                 = 'video/x-flv';
    public static $videoFlvExtension                = 'flv';
    public static $videoMp4MimeType                 = 'video/mp4';
    public static $videoMp4Extension                = 'mp4';
    public static $noImageSmall                     = '/compass/img/noimage/noimage_150x100.gif';
    public static $noImageBig                       = '/compass/img/noimage/noimage_420x280.gif';
    
    public static $richmediaEndsWith                = '_richmedia';
    
    public static $REINDEX_collections              = array('lectopia', 'stage3medvid', 'medvid', 'echo360');
    public static $COLLECTION_lectopia              = 'lectopia';
    public static $COLLECTION_stage3medvid          = 'stage3medvid';
    public static $COLLECTION_medvid                = 'medvid';
    public static $COLLECTION_echo360               = 'echo360';
    public static $COLLECTION_compassresources      = 'compassresources';
    public static $REINDEX_mediabank_msg            = 'Done! Thanks for visiting';
    
    public static $compassDownloadUrlBasePath       = '/resource/download';
    public static $compassViewOrDownloadUrlBasePath = '/resource/viewordownload';
    public static $compassImageUrlBasePath          = '/resource/image';
    public static $compassTranscodeUrlBasePath      = '/resource/transcode';
    public static $compassTranscodeToUrlBasePath    = '/resource/transcode/to';
    public static $resourceEditUrlFmt               = '/resource/edit/type/%s/id/%d/resourceid/%d';
    public static $resourceTaEditUrlFmt             = '/resource/edit/type/ta/resourceid/%d/id/%d';
    
    private static $mediabankMimetypeToImgBasepath  = '';
    private static $mediabankMimetypeToImgDirpath   = '';
    
    public static function customUrlRichmedia($resourceUrl) {
        return <<<HEREDOCS
        window.open('{$resourceUrl}','_blank','width=980,height=640,resizable');
HEREDOCS;
    }
    
    public static function removeHtmlDoctype($html) {
        return str_replace(self::getHtmlDoctype(),'',$html);
    }
    
    public static function addHtmlDoctype($html) {
        return self::getHtmlDoctype().$html;
    }
    
    public static function getHtmlDoctype() {
        return '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"' . PHP_EOL .
               '    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">' . PHP_EOL ;        
    }
    
    public static function createCompassImageUrl($mid, $size=null, $width=null, $height=null, $resizetype = 'thumb') {
        if(!empty($mid)) {
            $mid = self::sanitizeMid($mid);
            $mid = self::encode($mid);
            $url = Compass::baseUrl().self::$compassImageUrlBasePath.'/mid/'.$mid;
            $widthExist = (!is_null($width)) && ((int)$width > 0 );
            $heightExist = (!is_null($height)) && ((int)$height > 0 );
            $sizeExist = (!is_null($size)) && ((int)$size > 0 );
            if($widthExist || $heightExist) {
                $url .= ($widthExist) ? '/width/'.(int)$width : ''; 
                $url .= ($heightExist) ? '/height/'.(int)$height : ''; 
            } else if($sizeExist) {
                $url .= '/size/'.$size;
            }
            $url .= '/type/'.$resizetype;
            return $url;
        }
        return '';
    }

    public static function createCompassLooseResourceUrl($mid) {
        $mid = self::sanitizeMid($mid);
        $objectId = MediabankResourceConstants::getObjectId($mid);
        if(trim($objectId) != '') {
            $compassUrl = 'http://'.$_SERVER['HTTP_HOST'].Compass::baseUrl().MediabankResourceConstants::$compassViewOrDownloadUrlBasePath;
            $compassUrl .= '/mid/'.base64_encode($mid).'/type/loose/id/'.$objectId;
            return $compassUrl;
        }
        return '';
    }
    
    public static function getCollection($mid) {
        $mid = self::sanitizeMid($mid);
        if(strstr($mid, '|') !== false && count(explode('|', $mid)) == 3) {
            list($repositoryId, $collectionId, $objectId) = explode('|', $mid);
            return $collectionId;
        }
        return '';
    }
    
    public static function getObjectId($mid) {
        $mid = self::sanitizeMid($mid);
        if(strstr($mid, '|') !== false && count(explode('|', $mid)) == 3) {
            list($repositoryId, $collectionId, $objectId) = explode('|', $mid);
            return $objectId;
        }
        return '';
    }
    
    public static function sanitizeMid($mid) {
        if(empty($mid)) {
            return '';
        } 
        if(preg_match("/^http:\/\/(.)+\/\|(.)+\|(.)+/",$mid) > 0) {
            return $mid;         
        } else {
            $mid = self::decode($mid);
             if(preg_match("/^http:\/\/(.)+\/\|(.)+\|(.)+/",$mid) > 0) {
                return $mid;               
            }
        }
        return '';
    }
        
    public static function encode($mid) {
        return base64_encode($mid);
    }
    
    public static function decode($mid) {
        return base64_decode($mid);
    }
    
    public static function createCompassTranscodeUrl($mid, $basePath, $width=null, $height=null) {
        if(!empty($mid) && !empty($basePath)) {
            $mid = self::sanitizeMid($mid);
            $mid = self::encode($mid);
            if(!empty($mid)) {
                $url = Compass::baseUrl().$basePath.'/mid/'.$mid;
                $widthExist = (!is_null($width)) && ((int)$width > 0 );
                $heightExist = (!is_null($height)) && ((int)$height > 0 );
                if($widthExist || $heightExist) {
                    $url .= ($widthExist) ? '/width/'.(int)$width : '';
                    $url .= ($heightExist) ? '/height/'.(int)$height : '';
                }
                return $url;
            }
        }
        return '';
    }
    
    public static function createCompassTranscodeMp3Url($mid) {
        if(!empty($mid)) {
            return self::createCompassTranscodeUrl($mid, self::getTranscodeUrlMp3BasePath());
        }
        return '';
    }

    public static function createCompassTranscodeFlvUrl($mid, $width=null, $height=null) {
        if(!empty($mid)) {
            return self::createCompassTranscodeUrl($mid, self::$compassTranscodeUrlBasePath, $width, $height);
        }
        return '';
    }
        
    public static function createCompassTranscodeMp4Url($mid, $width=null, $height=null) {
        if(!empty($mid)) {
            return self::createCompassTranscodeUrl($mid, self::getTranscodeUrlMp4BasePath(), $width, $height);
        }
        return '';
    }    
    
    public static function createEcho360Url($presentationId) {
        return Compass::baseUrl().'/resource/viewecho360lecture/presentationid/'.$presentationId;
    }
    
    public static function getTranscodeUrlMp4BasePath() {
        return self::$compassTranscodeToUrlBasePath.'/'.self::$videoMp4Extension;
    }
    public static function getTranscodeUrlMp3BasePath() {
        return self::$compassTranscodeToUrlBasePath.'/'.self::$audioMp3Extension;
    }
    
    public static function createAuthorHtml($author) {
        $author = trim($author);
        $return = <<<HTML
<div style="display:inline;color:#772D2D;">
    Written/Prepared By:&nbsp;<span style="font-size: 95%;font-weight:bold;">{$author}</span>
</div>
HTML;
        return $return;
    }
    
    public static function createResourceEditUrl($type, $typeId, $resourceAutoId, $resourceTypeAutoId = null) {
        $return = '';
        $typeId = (int)$typeId;
        $resourceAutoId = (int)$resourceAutoId;
        if(!empty($type) && $typeId > 0 && $resourceAutoId > 0) {
            $url = Compass::baseUrl() . self::$resourceEditUrlFmt;
            $return .= sprintf($url, $type, $typeId, $resourceAutoId);
        }
        if(!empty($resourceTypeAutoId) && (int)$resourceTypeAutoId) {
            $return .= '/resourcetypeid/'.(int)$resourceTypeAutoId;
        }
        return $return;
    }
    
    public static function getAddUrl() {
    	return MediabankConstants::getMediabankBasePath() . '|'.self::$cid.'|';
    }
    
    public static function getCopyrightForm() {
        $uni = Utilities::getCopyrightUni();
        return array( $uni => $uni,'other' => 'Other');
    }
    
    public static function createInfoUrl($url, $params = array()) {
        $width          = '24';//Original size of the image
        $class          = 'resource-info';
        $paddingRight   = 18;
        
        if(isset($params['width']) && (int)$params['width'] > 0) {
            $width = (int)$params['width'];
        }
        if(isset($params['class']) && strlen(trim($params['class'])) > 0) {
            $class = trim($params['class']);
        }
        if(isset($params['padding-right']) && (int)$params['padding-right'] >= 0) {
            $paddingRight = (int)$params['padding-right'];
        }
        
        $baseUrl = Compass::baseUrl();
        $infoUrlHtml = <<<HTML
        <a href="{$url}">
            <img class="{$class}" width="{$width}" style="padding-right:{$paddingRight}px !important;" border="0" title="Click for more info" src="{$baseUrl}/img/info.png">
        </a>
HTML;
        return $infoUrlHtml;
    }
    
    public static function getImageUrlByMimeType($mimeType, $size = '', $width = '', $height = '') {
        $return = '';
        if(is_string($mimeType) && trim($mimeType) != '') {
            if(self::$mediabankMimetypeToImgBasepath == '') {
                self::$mediabankMimetypeToImgBasepath = Compass::getConfig('mediabank.mimetype.to.image.basepath');
            }
            if(self::$mediabankMimetypeToImgDirpath == '') {
                self::$mediabankMimetypeToImgDirpath = Compass::getConfig('mediabank.mimetype.to.image.basedir');
            }
            $append = '';
            if((int)$size > 0) {
                $append = '_'.(int)$size;
            } else if((int)$width > 0 && (int)$height > 0) {
                $append = '_'.(int)$width.'x'.(int)$height;
            }
            $fileBaseName = '/'.str_replace('/','-', trim($mimeType)).$append.'.png';
            $fileName = self::$mediabankMimetypeToImgDirpath.$fileBaseName;
            if(file_exists($fileName)) {
                return Compass::baseUrl().self::$mediabankMimetypeToImgBasepath.$fileBaseName;
            }
        }
        return $return;
    }
    
    public static function getListOfResourceTypes($type) {
        if(in_array($type, ResourceConstants::$TYPES_allowed)) {
            $pblResourceType = new PblResourceType();
            $results = $pblResourceType->fetchAll($type.' = 1');
            if($results->count() > 0) {
                $rows = $results->toArray();
                $return = array(''=>'');
                foreach($rows as $row) {
                    $return[$row['auto_id']] = $row['resource_type'];
                }
                return $return;
            }
        }
        return array();
    }

    public static function isResourceStaffOnly($param) {
        $return = false;
        //If the 'allow' column value in the lk_resourcetype is 'staff' it is a staff only resource
        if(in_array($param, array('staff'))) {
            $return = true;
        }
        return $return;
    }
    
    public static function staffOnlyTextImage($class=""){
        return '<img class="'.$class.'" src="'.Compass::baseUrl().'/img/staff_only.png" title=""/>';
    }
    
    public static function staffOnlyPeopleImage($class=""){
        return '<img class="'.$class.'" src="'.Compass::baseUrl().'/img/people.png" width="14" style="padding-left:2px;" border="0" title="Staff Only"/>';
    }
    
    public static function staffOnlyText(){
        return '(Staff Only)';
    }
    
    public static function getUrlForReindexingCollection($mediabankCollection) {
        return MediabankConstants::getMediabankBasePath().'REST/reindex?mid='.MediabankConstants::getMediabankBasePath().'|'.$mediabankCollection.'&incremental=true';
    }

    public static function getMediabankuserObj($mid = '') {
        $username = UserAcl::getUid();
        $role = UserAcl::getRole();
        if( $username != 'unknown' && $role != 'unknown' && !empty($mid)) {
            return new MediabankUser(MediabankResourceConstants::$frontend, $username, $role, new Mepositoryid($mid));
        }
        return new MediabankUser('PHP Frontend','username','roles',new Mepositoryid('repositoryID','collectionID','objectID'));
    }
    
    public static function getMediabankuserObjForSearch() {
        $username = UserAcl::getUid();
        $role = UserAcl::getRole();
        if( $username != 'unknown' && $role != 'unknown') {
            return new MediabankUser(MediabankResourceConstants::$frontend, $username, $role, null);
        }
        return new MediabankUser('PHP Frontend','username','roles',new Mepositoryid('repositoryID','collectionID','objectID'));
        
    }
    
    public static function getEcho360PresentationId($mid) {
        $mid = self::sanitizeMid($mid);
        if(!empty($mid)) {
            list($repository, $collectionId, $objectId) = explode('|',$mid);
            if(!empty($objectId)) {
                $objectIdParts = explode(':', $objectId);
                if(isset($objectIdParts[2])) {
                    $presentationId = $objectIdParts[2];
                    return $presentationId;
                }
            }
        }
        return '';
    }
    
    public static function getCreationDateForEcho360Recording($mid) {
        $mid = self::sanitizeMid($mid);
        if(!empty($mid)) {
            $mediabankCacheEcho360 = new MediabankCacheEcho360();
            $cache = $mediabankCacheEcho360->getMetadata($mid);
            if(isset($cache['metadata']) && isset($cache['metadata']['data']) 
                && isset($cache['metadata']['data']['start-time'])) {
                $startTime = $cache['metadata']['data']['start-time'];
                $time = strtotime($startTime);
                if($time != false) {
                    $date = date('Y-m-d ', $time).'<i>'.date(' h:i a', $time).'</i>';
                    return $date;
                }
                return '';
            }
        }
        return '';
    }
    
    public static function getNoImageFoundURL($width, $height) {
        $url = '';
        if(isset($_SERVER['SERVER_NAME']) && ! empty($_SERVER['SERVER_NAME'])) {
            $width = (empty($width) || (int)$width<1) ? 100 : (int)$width;
            $height = (empty($height) || (int)$height<1) ? 100 : (int)$height;
            $documentRoot = Zend_Registry::get('config')->image->basepath;
            $originalImagePath =  $documentRoot.'/img/noimage/noimage.gif';
            $imagePath = '/noimage/noimage_'.$width.'x'.$height.'.gif';
            $httpImagePath = 'http://'.$_SERVER['SERVER_NAME'].Compass::baseUrl().'/img'.$imagePath;
            $newImagePath = $documentRoot.'/img'.$imagePath;
    
            if( !file_exists($newImagePath)) {
                if(file_exists($originalImagePath)) {
                    exec("convert -geometry {$width}x{$height} $originalImagePath $newImagePath");
                    if( file_exists($newImagePath))  {
                        $url = $httpImagePath;
                    }
                } else {
                    $error = "Image '$originalImagePath' not found for creating thumbnails";
                    Zend_Registry::get('logger')->warn(PHP_EOL."CLASS\t: ".__CLASS__." #METHOD\t: ".__METHOD__." #"."ERROR\t: ".$error.PHP_EOL);
                    throw new Zend_Controller_Action_Exception("Page not found.", 404);
                }
            } else {
                $url = $httpImagePath;
            }
        }
        if(empty($url)) {
            throw new Zend_Controller_Action_Exception("Page not found.", 404);
        }
        return $url;
    }

}
?>