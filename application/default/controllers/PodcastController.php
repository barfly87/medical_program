<?php 
class PodcastController extends Zend_Controller_Action {
    
    public function init() {
        $student = array('search','resource','createurl');
        $this->_helper->_acl->allow('student', $student);

    }
    
    public function searchAction() {
        $idEpoch = $this->_getParam('pid', null);
        if(! is_null($idEpoch)) {
            $podcastUrlService = new PodcastUrlService();
            $url = $podcastUrlService->getUrl($idEpoch);
            if($url !== false) {
                parse_str($url, $params);
                if(count($params) > 0) {
                    $params['pid'] = $idEpoch;
                    $params['context'] = 'ta';
                    $params['podcast_title'] = base64_encode($podcastUrlService->createPodcastTitle($params));
                    $this->_forward('index', 'search', 'default', $params);
                    return;
                }
            }
        }
        $this->throwError();
    }
    
    public function resourceAction() {
        $podcastDownloadService = new PodcastDownloadService();
        $return = $podcastDownloadService->process($this->getRequest()->getParams());
        if(isset($return['forwardUrl'])) {
            $url = &$return['forwardUrl'];
            $this->_forward($url['action'], $url['controller'], $url['module'], $url['params']);
            return;
        }
        $this->throwError();
    }
    
    public function createurlAction() {
        $req = $this->getRequest();
        $msg = 'fail';
        if(isset($_SERVER['QUERY_STRING']) && !empty($_SERVER['QUERY_STRING'])) {
            $podcastUrlService = new PodcastUrlService();
            $urlId = $podcastUrlService->createUrlId($_SERVER['QUERY_STRING']);
            if($urlId !== false) {
                $msg = $urlId;
            }            
        }
        print $msg;
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
    }
    
    private function throwError() {
        throw new Zend_Controller_Action_Exception("Page not found.", 404);
    }
    
}

?>
