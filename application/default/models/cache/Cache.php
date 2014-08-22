<?php

class Cache {
    
    private static $dbTableMetadata = null;
    /**
     * This function returns a Zend_Cache::factory object which 
     * can be used for caching
     * @param int $seconds Defaults to 86400 (24 Hours)
     * @return mixed 
     */
    public static function factory($lifetimeInSeconds=86400, $automaticSerialization=true, $cacheDir='' ) {
        if(!(int)$lifetimeInSeconds > 0) {
            $lifetimeInSeconds = 86400;
        }
        if(! is_bool($automaticSerialization)) {
            $automaticSerialization = true;
        }
        if(empty($cacheDir)) {
            $cacheDir = self::getCacheDirZend();
        }
        $frontendOptions = array(
            'lifetime' => (int)$lifetimeInSeconds,
            'automatic_serialization' => $automaticSerialization
        );
        $backendOptions = array(
            'cache_dir' => $cacheDir  //Directory where to put the cache files
        );
        return Zend_Cache::factory('Core', 'File', $frontendOptions, $backendOptions);
    }
    
    public static function getCacheFactoryForDbTableMetadata() {
        if(is_null(self::$dbTableMetadata)) {
            $cacheLifeTime = Compass::getConfig('cache.zend.db.table.metadata.lifetime');
            $cacheDir = Compass::getConfig('cache.zend.db.table.metadata.path');
            if(!empty($cacheDir)) {
                if(! file_exists($cacheDir)) {
                    mkdir($cacheDir, 0755, true);
                }
            }
            self::$dbTableMetadata = self::factory($cacheLifeTime, true, $cacheDir); 
        }
        return self::$dbTableMetadata; 
    }
    
    public static function getCacheDirZend() {
        $config = Zend_Registry::get('config');
        $cacheDir = $config->cache->zend->path;
        if(!empty($cacheDir)) {
            if(! file_exists($cacheDir)) {
                mkdir($cacheDir, 0755, true);
            }
            return $cacheDir;                
        }
        return '/tmp';
    }
    
    public static function createCacheIdFromFileNameAndMethod($filePath, $methodName) {
        if(!empty($filePath) &&  !empty($methodName)) {
            if(file_exists($filePath)) {
                $dirname = dirname($filePath);
                $cacheId = $dirname.'_'.$methodName;
                $cacheId = str_replace('/','_',$cacheId);
                $cacheId = str_replace('::','_',$cacheId);
                return strtolower($cacheId);
            }
        } 
        return null;
    }
    
    public static function dbTableMetadata() {
        if(true === (bool)Compass::getConfig('cache.zend.db.table.metadata.caching')){
            Zend_Db_Table_Abstract::setDefaultMetadataCache(Cache::getCacheFactoryForDbTableMetadata());
        } else {
            CacheService::clearZendCache();
        }
    }
    
}
?>