<?php
class PodcastDownloadEcho360 extends PodcastDownloadAbstract {
    
    public function process($GET) {
        if(parent::_isNotAHeadRequest()) {
            $this->_processGetParams($GET);
        } else {
            parent::_throwMimeTypeHeaderBasedOnRequestUri();
            $error = 'Could not find URL for request params.' . PHP_EOL . print_r($GET, true);
            Zend_Registry::get('logger')->warn(PHP_EOL."CLASS\t: ".__CLASS__.PHP_EOL."METHOD\t: ".__METHOD__.PHP_EOL."Error\t: ".$error.PHP_EOL);
        }
        exit;
    }    
    
    private function _processGetParams($GET) {
        if( isset($GET['resource_id']) && (int)$GET['resource_id'] > 0
        && isset($GET['typeid']) && (int)$GET['typeid'] > 0
        && isset($GET['media']) && strlen(trim($GET['media'])) > 0
        && isset($GET['echo360_id']) && strlen(trim($GET['echo360_id'])) > 0
        ) {
            $typeId         = (int)$GET['typeid'];
            $resourceAutoId = (int)$GET['resource_id'];
            $presentationId = trim($GET['echo360_id']);
            
            //$presentationId is last part of the mid 
            //mid = course:section:presentation
            //auto_id = 18753 and type_id = 1265 and resource_id like '%2152d779-585f-4643-bbe3-58a7ca78a4c8%'
            $where = sprintf("auto_id = %d and type_id = %d and resource_id like '%%%s%%'", $resourceAutoId , $typeId, $presentationId);
            
            $mediabankResource = new MediabankResource();
            $result = $mediabankResource->fetchRow($where);

            if(! empty($result)) {
                $media              = trim($GET['media']);
                $presentationId     = trim($GET['echo360_id']);
                /*
                //If OAuth is not working try to disable seamless login in ESS and uncomment this out and comment out OAuth enabled part
                $url = 'http://view.streaming.sydney.edu.au/ess/echo/presentation/'.$presentationId;
                if($media != null) {
                    $url .='/mediacontent.'.$media;
                }
                header("Location: ".$url);
                exit;
                */
                //Comment this out if OAuth is not enabled : START
                $echo360Service = new Echo360Service();
                $result = $echo360Service->generateUrl($presentationId, $media);
                $cookieFile = '';
                if(function_exists('sys_get_temp_dir') && function_exists('tempnam')) {
                    $cookieFile = tempnam(sys_get_temp_dir(), 'Cookie');
                }
                parent::_throwMimeTypeHeaderBasedOnRequestUri();
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $result['url']);
                curl_setopt($ch, CURLOPT_HEADER, false);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, false);
                curl_setopt($ch, CURLOPT_TIMEOUT, 30);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
                curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
                curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
                curl_setopt($ch, CURLOPT_VERBOSE, 1);
                curl_exec($ch);
                curl_close($ch);
                unlink($cookieFile);
                exit;
                //END
            }
        }
    }
}
?>