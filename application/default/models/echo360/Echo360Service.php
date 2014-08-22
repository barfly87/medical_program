<?php
class Echo360Service {
    
    private $_echosystemRemoteApi = null;
    private $_user = null;
    
    public function __construct() {
        $config = Compass::getConfig('echo360.config');
        if(is_array($config) && !empty($config)) {
            $baseurl        = (isset($config['baseurl']))           ? $config['baseurl']        : null;
            $consumerkey    = (isset($config['consumerkey']))       ? $config['consumerkey']    : null;
            $consumersecret = (isset($config['consumersecret']))    ? $config['consumersecret'] : null;
            $realm          = (isset($config['realm']))             ? $config['realm']          : null;
            $this->_user    = (isset($config['user']))              ? $config['user']           : null;          
            
            if(!is_null($baseurl) && !is_null($consumerkey) && !is_null($consumersecret) && !is_null($realm)) {
                $this->_echosystemRemoteApi = new EchosystemRemoteApi(
                                                $baseurl, $consumerkey, $consumersecret, $realm
                                            );
            }
        }
    }
    
    public function generateUrl($presentationId, $media = null) {
        if($this->_user != null) {
            return $this->_echosystemRemoteApi->generate_presentation_url($this->_user, $presentationId, $media, true);    
        }
        return '';
    }
    
    public function debugGenerateUrl($presentationId, $media = null) {
        if($this->_user != null) {
            $u1 = '/ess/personapi/v1/soxu3266/session?redirecturl=http%3A%2F%2Fview.streaming.sydney.edu.au%3A8080%2Fess%2Fecho%2Fpresentation%2F26137b4b-141a-4f77-a0c5-ea2e46aaa534&instructor=false&security-realm=default&oauth_signature=108EJC2sEEKl9T6Q50ZzSJ%2Blk0Y%3D&oauth_consumer_key=bb-echo-trusted-key&oauth_version=1.0&oauth_signature_method=HMAC-SHA1&oauth_timestamp=1372683940&oauth_nonce=-8437312804143150459';
            $u2 = '/ess/personapi/v1/ksoni/session?redirecturl=http%3A%2F%2Fview.streaming.sydney.edu.au%3A8080%2Fess%2Fecho%2Fpresentation%2F4dda972e-4329-4733-aa4b-4a3d5124100e%2Fmediacontent.m4v&instructor=true&oauth_consumer_key=M3D1C%40L&oauth_nonce=4df4ad894a0543b4c3450697d2bfb8f2&oauth_signature=I%2ByooMgZ%2FTauLiU9WDs9Q1ruljU%3D&oauth_signature_method=HMAC-SHA1&oauth_timestamp=1372723004&oauth_token=&oauth_version=1.0&security-realm=';
            $presentationIdTemp = '53de338a-8d15-4c86-87ae-6cb668201e32';
            if($presentationIdTemp != $presentationId) {
                $presentationIdTemp = $presentationId;
            }
            $r = $this->_echosystemRemoteApi->generate_presentation_url($this->_user, $presentationIdTemp, $media, false);
            print '<pre>';
            var_dump($r);
            print '</pre>';            
            printf('<br><br><h1><a href="%s" target="_blank">URL</a></h1>', $r['url']);
            exit;
        }
        return '';
    }
    
    public function getHeaders($url) {
        if($this->_user != null) {
            $ch = $this->_echosystemRemoteApi->get_curl_with_defaults();
            return $this->_echosystemRemoteApi->get_headers($ch, $url);
        }
        return false;
    }
}
?>