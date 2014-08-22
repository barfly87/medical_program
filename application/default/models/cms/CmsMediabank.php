<?php

class CmsMediabank {
    private $mediabank = null;

    public function __construct() {
        $this->mediabank = new Mediabank(MediabankConstants::getMepositoryId(), MediabankConstants::getMepositoryId()."cxfws/Core?wsdl");
    }
    
    public function search($query) {
        $index = "Lucene Index";
        $searchRemote = true;
        $mediabankResourceService = new MediabankResourceService();
        $user = MediabankResourceConstants::getMediabankuserObjForSearch();
        return $this->mediabank->search($index, $query, $user, $searchRemote);
    }
    
} 
