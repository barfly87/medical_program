<?php

class MediabankCacheMetadata {

    private static $mediabankCachePath = null;
    private static $metadataPath = null;

    public static function setCache($mid, &$result) {
        try {
            $cacheFileName = self::getCacheFileName($mid);
            if($cacheFileName !== false) {
                //We only want to cache metadata when Mediabank is returning data as expected
                //and not otherwise. E.g $result['data'] should always exist if Mediabank is working fine.
                if(isset($result['data']) && !empty($result['data'])) {
                    $serializedData = serialize($result);
                    $fh = fopen($cacheFileName, 'w+');
                    fwrite($fh, $serializedData, strlen($serializedData));
                    fclose($fh);
                } else if(file_exists($cacheFileName)) {
                    return unserialize(file_get_contents($cacheFileName));
                }
            }
            return $result;
        } catch (Exception $ex) {
            
        }
    }
    
    private static function getCacheFileName($mid) {
        $cacheBaseDir = self::getCacheBaseDir();
        if(is_object($mid)) {
            $fileName = trim($mid->__toString());
        } else {
            $fileName = trim($mid);
        }
        
        if(strlen($fileName) < 0) {
            return false;
        }
        $fileName         = md5($fileName);
        $firstTwoChars    = substr($fileName, 0, 2);
        $thirdFourthChars = substr($fileName, 2, 2);
        $filePath         = $cacheBaseDir.DIRECTORY_SEPARATOR.$firstTwoChars.DIRECTORY_SEPARATOR.$thirdFourthChars;
        
        if(! file_exists($filePath)) {
            mkdir($filePath, 0755, true);
        }
        $file = $filePath.DIRECTORY_SEPARATOR.$fileName;
        return $file;
    }
    
    private static function getCacheBaseDir() {
        if(is_null(self::$metadataPath)) {
            $metadataPath = self::getMediabankCachePath().'/metadata';
            if(! file_exists($metadataPath)) {
                mkdir($metadataPath);
            }
            self::$metadataPath = $metadataPath;
        }
    
        return self::$metadataPath;
    }
    
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
    
    public static function getCache($mid) {
        try {
            $cacheFileName = self::getCacheFileName($mid);
            if(file_exists($cacheFileName)) {
                $day = 60 * 60 * 24;
                $dayago = time() - $day;
                if(! filemtime($cacheFileName) < $dayago) {
                    return unserialize(file_get_contents($cacheFileName));
                } 
            }
            return false;
        } catch(Exception $ex) {
            Zend_Registry::get('logger')->warn($ex->getMessage());
            return array();
        }
    }
    
    public static function removeCache($mid) {
        try {
            $cacheFileName = self::getCacheFileName($mid);
            if(file_exists($cacheFileName)) {
                return unlink($cacheFileName);
            }
            return false;
        } catch(Exception $ex) {
            Zend_Registry::get('logger')->warn($ex->getMessage());
            return array();
        }
    }
    
    private static $removeCacheCounter  = 0;
    public static function clearCache() {
        $return = true;
        $dir = MediabankCacheMetadata::getCacheBaseDir();
        if(file_exists($dir)) {
            while(! Compass::removeDirectory($dir)) {
                if(MediabankCacheMetadata::$removeCacheCounter > 1000) {
                    $return = false;
                    break;
                }
                MediabankCacheMetadata::$removeCacheCounter++;
            }
        }
        return $return;
    }
}

?>