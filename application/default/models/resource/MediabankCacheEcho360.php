<?php

class MediabankCacheEcho360 {
    
    private static $mediabankCachePath = null;
    private static $echoPath = null;
    private static $echo360MetadataPath = null;
    
    /**
     * This function returns metadata for native and smp schema. It first looks for cached 
     * copy of metadata which is not more than week old and returns it. If it does not find a 
     * recent cache copy it creates a new cache and returns the content.
     * @param $mid
     * @return $data
     */
    public static function getMetadata($mid) {
        try {
            $midFilePath = self::getEcho360MetadataMidPath($mid);
            if(file_exists($midFilePath)) {
                $week = 60 * 60 * 24;
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
        return Compass::removeDirectory(self::getEcho360Path());
    }
    
    /**
     * Purge cache for mid
     * @param $mid
     * @return boolean
     */
    public static function purgeMetadataForMid($mid) {
        try {
            $midFilePath = self::getEcho360MetadataMidPath($mid);
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
     * If the mid is http://smp.sydney.edu.au/mediabank/|echo360|4b3eebda-79a4-401a-91b1-cf17ba2e9061:7fef26b8-016e-40a9-8d35-6c8ba8b98dee:df8be73e-bd81-4f1c-807c-971dd7a69126
     * This function would create a directory structure for above mid as
     * ../compass/var/cache/mediabank/echo360/metadata/$courseId/$sectionId/$presentationId
     * 
     * @param $mid
     * @return string lectopiaMidPath
     */
    private static function getEcho360MetadataMidPath($mid) {
        $echo360MetadataPath = self::getEcho360MetadataPath();
        list($repositoryId, $collecitonId, $objectId) = explode('|', $mid);
        list($courseId, $sectionId, $presenationId) = explode(':', $objectId);
        $midPath = $echo360MetadataPath.'/'.$courseId.'/'.$sectionId;
        
        if(! file_exists($midPath)) {
            mkdir($midPath, 0755,true);            
        }
        return $midPath.'/'.$presenationId;
    }

    /**
     * This function returns the dir path for storing lectopia metadata
     * @return unknown_type
     */
    private static function getEcho360MetadataPath() {
        if(is_null(self::$echo360MetadataPath)) {
            $echo360MetadataPath = self::getEcho360Path().'/metadata';
            if(! file_exists($echo360MetadataPath)) {
                mkdir($echo360MetadataPath);
            }
            self::$echo360MetadataPath = $echo360MetadataPath;
        }
        return self::$echo360MetadataPath;        
    }
    
    /**
     * This function returns the dir path for caching lectopia content
     * @return string lectopiaPath
     */
    private static function getEcho360Path() {
        if(is_null(self::$echoPath)) {
            $echoPath = self::getMediabankCachePath().'/echo360';
            if(! file_exists($echoPath)) {
                mkdir($echoPath);
            }
            self::$echoPath = $echoPath;
        }
        
        return self::$echoPath;
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