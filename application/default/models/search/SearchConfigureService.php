<?php
class SearchConfigureService {
    
    private $url = '';
    private $configureSearchColumns = array();
    private $configureSearchType = '';
    private $encryptedUrl = '';  
    private $userId = null;  
    
    public function SearchConfigureService($params){
        foreach($params as $paramName => $paramValue) {
            $this->$paramName = $paramValue;
        }
        $this->userId = $this->getUserId();
        $this->deleteCookie();
    }

    public function save(){
        $encryptUrl = $this->encryptUrl($this->url);
        if($encryptUrl == $this->encryptedUrl) {
            $columnIds = array();
            foreach($this->configureSearchColumns as $columnId) {
                $id = (int)$columnId;
                if(strlen($id) > 0) {
                    array_push($columnIds,$id);
                }
            }
            $count = count($columnIds);
            if($count > 0 ) {
                $columnString = ($count == 1) ? $columnIds[0] : implode(',',$columnIds);
                $searchConfigure = new SearchConfigure();
                $result = $searchConfigure->saveColumns($this->userId, $columnString, $this->configureSearchType);
                return $this->url;
            }
        }
        return false;
    }
    
    private function deleteCookie(){
        if(isset($this->cookieSearchPage)) {
            setcookie($this->cookieSearchPage,'', time()-3600,'/');
        }
    }
        
    private function getUserId(){
        $auth = Zend_Auth::getInstance();
        if ($auth->hasIdentity()) {
            $user = $auth->getIdentity();
            return $user->user_id;
        } 
        return 'unknown';
    }    
    
    private function encryptUrl($url){
        $searchResultsService = new SearchResultsService();
        return $searchResultsService->encryptString($url);
    }
    
}
