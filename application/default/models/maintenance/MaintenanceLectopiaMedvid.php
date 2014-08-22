<?php
class MaintenanceLectopiaMedvid extends MaintenanceAbstract {
    
    public function process() {
        $errorMsg = '';
        $error = false;
        
        //REINDEX LECTOPIA COLLECTION
        $mediabankCollectionService = new MediabankCollectionService();
        $lectopiaReindex = $mediabankCollectionService->reindex(MediabankResourceConstants::$COLLECTION_lectopia);
        if(! stristr($lectopiaReindex, MediabankResourceConstants::$REINDEX_mediabank_msg)) {
            $errorMsg .= 'Lectopia Reindex Error<br />';
            $errorMsg .= $lectopiaReindex.'<br />';
            $error = true;
        }
        
        //LINK LECTOPIA MIDS FROM MEDIABANK TO COMPASS
        $mediabankLectopiaConnector = new MediabankLectopiaConnector();
        $result = $mediabankLectopiaConnector->link();
        if(!isset($result) || (isset($result) && $result['error'] == true) ) {            
            $errorMsg .= 'Lectopia Connector error <br />';
            $errorMsg .= $result['error_string'].'<br />';
            $error = true;
        }
        
        //REINDEX ECHO360 COLLECTION
        $mediabankCollectionService = new MediabankCollectionService();
        $lectopiaReindex = $mediabankCollectionService->reindex(MediabankResourceConstants::$COLLECTION_echo360);
        if(! stristr($lectopiaReindex, MediabankResourceConstants::$REINDEX_mediabank_msg)) {
            $errorMsg .= 'Lectopia Reindex Error<br />';
            $errorMsg .= $lectopiaReindex.'<br />';
            $error = true;
        }
        
        //LINK ECHO360 MIDS FROM MEDIABANK TO COMPASS
        $mediabankLectopiaConnector = new MediabankEcho360Connector();
        $result = $mediabankLectopiaConnector->link();
        if(!isset($result) || (isset($result) && $result['error'] == true) ) {            
            $errorMsg .= 'Echo360 Connector error <br />';
            $errorMsg .= $result['error_string'].'<br />';
            $error = true;
        }
        
        //REINDEX MEDVID COLLECTION
        $medvidReindex = $mediabankCollectionService->reindex(MediabankResourceConstants::$COLLECTION_medvid);
        if(! stristr($medvidReindex, MediabankResourceConstants::$REINDEX_mediabank_msg)) {
            $errorMsg .= 'Med Vid Reindex Error<br />';
            $errorMsg .= $medvidReindex.'<br />';
            $error = true;
        }
        
        //LINK MEDVID MIDS FROM MEDIABANK TO COMPASS
        $mediabankMedvidConnector = new MediabankMedvidConnector();
        $result = $mediabankMedvidConnector->link();
        if(!isset($result) || (isset($result) && $result['error'] == true) ) {            
            $errorMsg .= 'Medvid Connector error <br />';
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