<?php
class PblConst {
    
    public static $ref                      = 'ref';
    public static $resrcMsg                 = 'msg';
    public static $resrcSuccess             = '1';
    
    public static $errorPageNotFound            = '<p style="padding:4px;">None found</p>';
    public static $errorResourcesNotFound       = '<p class="red" style="padding:4px;">Resources could not be found.</p>';
    
    public static function printErrorOrNoneFound($error = null) {
        $message = PblConst::$errorPageNotFound;
        if ($error === true) {
            $message = '<span class="error">Error !!<span>';
        }
        print $message;
    }
    
}
?>