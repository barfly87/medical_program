<?php
class FlowPlayer {
    
    private static $counter             = 1;
    private static $jsCssDisplayed      = false;
    private static $flowPlayerJsUrl     = '/js/flowplayer/flowplayer-3.2.6.min.js';
    private static $flowPlayerFlashUrl  = '/js/flowplayer/flowplayer-3.2.7.swf';
    private static $noVideoPlayer       = '<span class="error">Error !!</span> Video cannot be displayed.';
    
    
    public static function getFlowPlayerJsUrl() {
        return  self::_getBasePath().self::$flowPlayerJsUrl;
    }
    
    public static function getFlowPlayerFlashUrl() {
        return  self::_getBasePath().self::$flowPlayerFlashUrl;
    }
    
    public static function playAudio($audio, $width = 640) {
        $audioMp3Url = trim($audio['mp3Url']);
        if(strlen($audioMp3Url) <= 0) {
            echo '<span class="error">Error !!</span> Audio cannot be played.';
            return;
        }
        FlowPlayer::_displayJsCss();
        $flowPlayerId = 'flow-player-'. FlowPlayer::$counter;
        FlowPlayer::$counter++;
        $flowPlayerFlashUrl = self::getFlowPlayerFlashUrl();

        echo <<<FLOWPLAYER
<!-- setup player container  -->
<div id="{$flowPlayerId}" style="display:block;width:{$width}px;height:30px;"
    href="{$audioMp3Url}"></div>
<script>
\$f("{$flowPlayerId}", "{$flowPlayerFlashUrl}", {

    // fullscreen button not needed here
    plugins: {
        controls: {
            fullscreen: false,
            height: 30,
            autoHide: false
        }
    },
    clip: {
        autoPlay: false
    }
});
</script>
FLOWPLAYER;
    }

    public static function playVideo($video) {
        if(empty($video)) {
            echo self::$noVideoPlayer;
            return;
        }
        $videoFlvUrl = trim($video['flvUrl']);
        $videoMp4Url = trim($video['mp4Url']);
        if(strlen($videoFlvUrl) <= 0 || strlen($videoMp4Url) <= 0 ) {
            echo self::$noVideoPlayer;
            return;
        }
        FlowPlayer::_displayJsCss();
        $embedVideo = '';
        $imageUrl = $video['imageUrl'];
        if(! is_null($imageUrl) && strlen(trim($imageUrl)) > 0) {
            $imageUrl = trim($imageUrl);
            $embedVideo = FlowPlayer::_getFlowPlayerImageAndVideo($imageUrl, $videoFlvUrl, $videoMp4Url);
        } else {
            $embedVideo = FlowPlayer::_getFlowPlayerVideo($videoFlvUrl, $videoMp4Url);
        }
        
        $width = $video['width'];
        $width  = ((int)$width <= 0 ) ? 500 : (int)$width;
        
        $height = $video['height'];
        $height = ((int)$height <= 0) ? 376 : (int)$height;
        
        $flowPlayerId = 'flow-player-'. FlowPlayer::$counter;
        $flowPlayerFlashUrl = self::getFlowPlayerFlashUrl();
        
        echo <<<FLOWPLAYER
<div>        
<a style="display:block;width:{$width}px;height:{$height}px" id="{$flowPlayerId}"></a> 
<script>
    flowplayer(
        '{$flowPlayerId}',
        '{$flowPlayerFlashUrl}',
        { 
            {$embedVideo}
        }
    );
</script>
</div>        
FLOWPLAYER;
        FlowPlayer::$counter++;
    }
    
    private static function _displayJsCss() {
        if(FlowPlayer::$jsCssDisplayed === false) {
            FlowPlayer::$jsCssDisplayed = true;
            $flowPlayerJs = self::getFlowPlayerJsUrl();
            echo <<<JSCSS
<script type="text/javascript" src="{$flowPlayerJs}"></script>
JSCSS;
        }
    }
    
    private static function _getFlowPlayerImageAndVideo($imageUrl, $videoFlvUrl, $videoMp4Url) {
        $imageExtension = MediabankResourceConstants::$imageExtension;
        return <<<FLOWPLAYER
            playlist: [
                {
                    url             : '{$imageUrl}.{$imageExtension}',
                    autoPlay        : true 
                },
                {
                    //url           : flashembed.isSupported([9, 115]) ? '{$videoMp4Url}' : '{$videoFlvUrl}', 
                    url             : '{$videoFlvUrl}',
                    autoPlay        : false, 
                    autoBuffering   : true,
                    bufferLength    : 5,
                    scaling         : 'orig'
                }
            ]
FLOWPLAYER;
    }
    
    private static function _getFlowPlayerVideo($videoFlvUrl, $videoMp4Url) {
        return <<<FLOWPLAYER
            clip:  {
                url             : flashembed.isSupported([9, 115]) ? '{$videoMp4Url}' : '{$videoFlvUrl}', 
                autoPlay        : false,
                autoBuffering   : true,
                bufferLength    : 5,
                scaling         : 'orig'
            }
FLOWPLAYER;
    }
    
    private static function _getBasePath() {
        $basePath = 'http://smp.sydney.edu.au/' . Compass::baseUrl();
        if(isset($_SERVER['HTTP_HOST'])) {
            $basePath = 'http://'.$_SERVER['HTTP_HOST'] . Compass::baseUrl();
        }
        return $basePath;
    }
    
}
?>