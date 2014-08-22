<?php
class CacheService {
    
    public static function clearLectopiaCache() {
        $mediabankCacheLectopia = new MediabankCacheLectopia();
        return $mediabankCacheLectopia->clearCache();
    }
    
    public static function clearEcho360Cache() {
        $mediabankCacheEcho360 = new MediabankCacheEcho360();
        return $mediabankCacheEcho360->clearCache();
    }
    
    public static function clearLdapCache() {
        return LdapCache::clearCache();
    }
    
    public static function clearZendCache() {
        $cacheDir = Cache::getCacheDirZend();
        return Compass::removeDirectory($cacheDir);
    }
    
    public static function clearMetadataCache() {
        return MediabankCacheMetadata::clearCache();
    }
    
}
?>