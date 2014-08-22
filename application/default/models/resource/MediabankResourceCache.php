<?php 

class MediabankResourceCache {
    private static $cache = null;
    
    public static function Cache() {
        if(is_null(self::$cache)) {
            $frontendOptions = array(
             'lifetime' => 600, // cache lifetime of 10 mins
             'automatic_serialization' => true
            );
            $backendOptions = array(
              'cache_dir' => '/tmp/' // Directory where to put the cache files
            );
            
            // getting a Zend_Cache_Core object
            self::$cache = Zend_Cache::factory('Core','File',$frontendOptions,$backendOptions);
        }
        return self::$cache;
    }
    
}

?>