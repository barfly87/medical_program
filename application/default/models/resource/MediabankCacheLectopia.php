<?php

class MediabankCacheLectopia {
    
    private static $mediabankCachePath = null;
    private static $lectopiaPath = null;
    private static $lectopiaMetadaPath = null;
    
    /**
     * This function returns metadata for native and smp schema. It first looks for cached 
     * copy of metadata which is not more than week old and returns it. If it does not find a 
     * recent cache copy it creates a new cache and returns the content.
     * @param $mid
     * @return $data
     */
    public static function getMetadata($mid) {
        try {
            $midFilePath = self::getLectopiaMetadataMidPath($mid);
            if(file_exists($midFilePath)) {
                $week = 60 * 60 * 24 * 7;
                $weekago = time() - $week;
                if(filemtime($midFilePath) < $weekago) {
                    return self::getMetadatForMid($mid, $midFilePath);
                } else {
                    return unserialize(file_get_contents($midFilePath));
                }
            } else {
                return self::getMetadatForMid($mid, $midFilePath);
            }
        } catch(Exception $ex) {
            Zend_Registry::get('logger')->warn($ex->getMessage());
            return array();            
        }
    }
    
    public function clearCache() {
        return Compass::removeDirectory(self::getLectopiaPath());
    }
    
    /**
     * Purge cache for mid
     * @param $mid
     * @return boolean
     */
    public static function purgeMetadataForMid($mid) {
        try {
            $midFilePath = self::getLectopiaMetadataMidPath($mid);
            self::getMetadatForMid($mid, $midFilePath);
            return true;
        } catch (Exception $ex) {
            Zend_Registry::get('logger')->warn($ex->getMessage());
            return false;
        }
    }
    
    /**
     * This function returns metadata for native and smp schema
     * @param $mid
     * @param $midFilePath
     * @return mixed $data
     */
    private static function getMetadatForMid($mid, $midFilePath) {
        try {
            $mediabankResourceService = new MediabankResourceService();
            $data = array();
            $data['metadata'] = $mediabankResourceService->getMetaData($mid);
            $data['smpMetadata'] = $mediabankResourceService->getSmpMetadata($mid);
            $serializedData = serialize($data);
            $fh = fopen($midFilePath, 'w+');
            fwrite($fh, $serializedData, strlen($serializedData)); 
            fclose($fh);
            return $data;
        } catch(Exception $ex) {
            return array();            
        }
    }
    
    /**
     * This function creates path where the lecture recordings mid needs to be stored.
     * If the mid is http://smp.sydney.edu.au/mediabank/|lectopia|25394
     * It uses first two characters of the objectID(25394) which is '25' to create a directory within
     * lectopia directory and it uses objectID(25394) to create unique filename.
     * So this function would create a directory structure for above mid as
     * ../compass/var/cache/mediabank/lectopia/metadata/25/25394
     * 
     * @param $mid
     * @return string lectopiaMidPath
     */
    private static function getLectopiaMetadataMidPath($mid) {
        $lectopiaMetadaPath = self::getLectopiaMetadataPath();
        $midObj = new MepositoryID($mid);
        $firstTwoChars = substr($midObj->objectID, 0, 2);
        $midPath = $lectopiaMetadaPath.'/'.$firstTwoChars;
        if(! file_exists($midPath)) {
            mkdir($midPath);            
        }
        return $midPath.'/'.$midObj->objectID;
    }

    /**
     * This function returns the dir path for storing lectopia metadata
     * @return unknown_type
     */
    private static function getLectopiaMetadataPath() {
        if(is_null(self::$lectopiaMetadaPath)) {
            $lectopiaMetadaPath = self::getLectopiaPath().'/metadata';
            if(! file_exists($lectopiaMetadaPath)) {
                mkdir($lectopiaMetadaPath);
            }
            self::$lectopiaMetadaPath = $lectopiaMetadaPath;
        }
        return self::$lectopiaMetadaPath;        
    }
    
    /**
     * This function returns the dir path for caching lectopia content
     * @return string lectopiaPath
     */
    private static function getLectopiaPath() {
        if(is_null(self::$lectopiaPath)) {
            $lectopiaPath = self::getMediabankCachePath().'/lectopia';
            if(! file_exists($lectopiaPath)) {
                mkdir($lectopiaPath);
            }
            self::$lectopiaPath = $lectopiaPath;
        }
        
        return self::$lectopiaPath;
    }
    
    /**
     * This function returns dir path where the mediabank cache should be stored.
     * If mediabank cache directory does not exist it creates one
     * @return string mediabankCachePath
     */
    private static function getMediabankCachePath() {
        if(is_null(self::$mediabankCachePath)) {
            $config = Zend_Registry::get('config');
            $mediabankCachePath = $config->cache->mediabank->path;
            if(! file_exists($mediabankCachePath)) {
                mkdir($mediabankCachePath);
            }
            self::$mediabankCachePath = $mediabankCachePath;
        }
        return self::$mediabankCachePath;
    }    
}

?>