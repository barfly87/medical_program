<?php
class PodcastDownloadDefault extends PodcastDownloadAbstract {
    
    public function process($GET) {
        $url = $this->_processGetParams($GET);
        if(! empty($url) && parent::_isNotAHeadRequest()) {
            return array('forwardUrl' => $url);
        } else {
            $this->_throwHeader($GET);
            $error = 'Could not find URL for request params.' . PHP_EOL . print_r($GET, true);
            Zend_Registry::get('logger')->warn(PHP_EOL."CLASS\t: ".__CLASS__.PHP_EOL."METHOD\t: ".__METHOD__.PHP_EOL."Error\t: ".$error.PHP_EOL);
        }
        exit;
    }    
    
    private function _throwHeader(&$GET) {
        //$GET['mt'] = mimetype encoded
        if(isset($GET['mt']) && !empty($GET['mt'])) {
            parent::_throwContentTypeHeader(base64_decode($GET['mt']));
        } else {
            parent::_throwMimeTypeHeaderBasedOnRequestUri();
        }
    }
    
    private function _processGetParams(&$GET) {
        $url = null;
        if( isset($GET['resource_id']) && (int)$GET['resource_id'] > 0
        && isset($GET['typeid']) && (int)$GET['typeid'] > 0
        && isset($GET['mt']) && strlen(trim($GET['mt'])) > 0
        && isset($GET['mid']) && strlen(trim($GET['mid'])) > 0
        ) {
            $typeId             = (int)$GET['typeid'];
            $resourceAutoId     = (int)$GET['resource_id'];
            $mid                = MediabankResourceConstants::sanitizeMid(trim($GET['mid']));
            $where              = sprintf("auto_id = %d and type_id = %d and resource_id like '%%%s%%'", $resourceAutoId, $typeId, $mid);

            $mediabankResource = new MediabankResource();
            $result = $mediabankResource->fetchRow($where);

            if(! empty($result)) {
                $mt                 = trim($GET['mt']);
                $mid                = trim($GET['mid']);
                $url                = array();
                $url['module']      = 'default';
                $url['controller']  = 'resource';
                $url['action']      = 'download';
                $url['params']      = array(
                                        'mt' => $mt,
                                        'mid' => $mid
                                    );
            }
        }
        return $url;
    }
    
}
?>