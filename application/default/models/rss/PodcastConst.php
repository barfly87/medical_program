<?php 

class PodcastConst {
    
    CONST fileFormatAudio                       = 'audio';
    CONST fileFormatAudioText                   = '(Audio)';
    CONST fileFormatVideo                       = 'video';
    CONST fileFormatVideoText                   = '(Video)';
    CONST fileFormatImage                       = 'image';
    CONST fileFormatImageText                   = '(Image)';
    CONST fileFormatPdf                         = 'pdf';
    CONST fileFormatPdfText                     = '(PDF)';
    
    CONST formResourceType                      = 'podcast-resource-types';
    CONST formResourceTypeAllResources          = 'all-resources';
    CONST formResourceTypeRecordingsOnly        = 'recordings-only';
    CONST formYearsAll                          = 'All';
    CONST formPodcastYears                      = 'podcast-years';
    CONST formPodcasts                          = 'podcasts';

    private static $_podcastConfig               = null;
    private static $_podcastConfigLectopia       = null; 
    private static $_podcastConfigEcho360        = null;
    private static $_podcastConfigDefault        = null; 
    private static $_titleTextArr               = array();
    
    public static function setPodcastConfig() {
        if(is_null(self::$_podcastConfig)) {
            self::$_podcastConfig = $config = Zend_Registry::get('config')->podcast->toArray(); 
        }
    }
    
    public static function getPodcastConfigDefault() {
        if(is_null(self::$_podcastConfigDefault)) {
            self::setPodcastConfig();
            self::$_podcastConfigDefault = self::$_podcastConfig['default'];
        }
        return self::$_podcastConfigDefault;
    }
    
    public static function getPodcastConfigEcho360() {
        if(is_null(self::$_podcastConfigEcho360)) {
            self::setPodcastConfig();
            self::$_podcastConfigEcho360 = self::$_podcastConfig['echo360'];
        }
        return self::$_podcastConfigEcho360;
    }
    
    public static function getPodcastConfigLectopia() {
        if(is_null(self::$_podcastConfigLectopia)) {
            self::setPodcastConfig();
            self::$_podcastConfigLectopia = self::$_podcastConfig['lectopia'];            
        }
        return self::$_podcastConfigLectopia;
    }
    
    public static function _addNumberToTitleIfTitleAlreadyExist($titleText) {
        //If a title was already created similar to $titleText append a number at the end. like e.g title - 2, title - 3 and so on
        $appendTitleText = '';
        if(isset(self::$_titleTextArr[$titleText]) && isset(self::$_titleTextArr[$titleText]['counter']) && self::$_titleTextArr[$titleText]['counter'] > 0) {
            self::$_titleTextArr[$titleText]['counter'] = ++self::$_titleTextArr[$titleText]['counter'];
            $appendTitleText = ' - '.self::$_titleTextArr[$titleText]['counter'];
        } else {
            self::$_titleTextArr[$titleText]['counter'] = 1;
        }
        $titleText .= $appendTitleText;
        return $titleText;
    }
    
    
}