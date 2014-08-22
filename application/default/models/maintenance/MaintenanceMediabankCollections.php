<?php
class MaintenanceMediabankCollections extends MaintenanceAbstract {
    
    public function process() {
        $errorMsg = '';
        $error = false;
        
        //REINDEX ECHO360 COLLECTION
        $mediabankCollectionService = new MediabankCollectionService();
        $echo360Reindex = $mediabankCollectionService->reindex(MediabankResourceConstants::$COLLECTION_echo360);
        if(! stristr($echo360Reindex, MediabankResourceConstants::$REINDEX_mediabank_msg)) {
        	//Just getting too many emails about errors from Mediabank so commented it out 
        	/*
            $errorMsg .= 'Echo360 Reindex Error<br />';
            $errorMsg .= $echo360Reindex.'<br />';
            $error = true;
            */
            $errorMsg .= 'Echo360 Reindex Error'.PHP_EOL;
            $errorMsg .= $echo360Reindex.PHP_EOL;
        	$return = PHP_EOL."CLASS\t: ".__CLASS__.PHP_EOL."METHOD\t: ".__METHOD__.PHP_EOL."Error\t: ".$errorMsg.PHP_EOL;
        	Zend_Registry::get('logger')->warn($return);
        }
        
        //LINK ECHO360 MIDS FROM MEDIABANK TO COMPASS
        $mediabankEcho360Connector = new MediabankEcho360Connector();
        $result = $mediabankEcho360Connector->link();
        if(!isset($result) || (isset($result) && $result['error'] == true) ) {            
            $errorMsg .= 'Echo360 Connector error <br />';
            $errorMsg .= $result['error_string'].'<br />';
            $error = true;
        }
        
        //IF ERROR IS TRUE RETURN $errorMsg OR RETURN 'success'               
        if($error == true) {
            return $errorMsg;
        }        
        return true;        
    }
    
}
?>