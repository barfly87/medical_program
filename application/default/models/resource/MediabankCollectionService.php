<?php
class MediabankCollectionService {
    
    public function reindex($mediabankCollection) {
        set_time_limit(0);
        $return = '';
        $mediabankCollection = trim($mediabankCollection);
        $startMsg = 'Start - Incremental reindexing Mediabank Collection : ' . $mediabankCollection;
        Zend_Registry::get('logger')->warn(PHP_EOL."CLASS\t: ".__CLASS__.PHP_EOL."METHOD\t: ".__METHOD__.PHP_EOL."Message\t: ".$startMsg.PHP_EOL);
        
        if(!empty($mediabankCollection)) {
            $startTime = time();
            if(in_array($mediabankCollection, MediabankResourceConstants::$REINDEX_collections)) {
                $mediabankUtility = new MediabankUtility();
                $reindexUrl = MediabankResourceConstants::getUrlForReindexingCollection($mediabankCollection);
                ob_start();
                $mediabankUtility->curlUrlGet($reindexUrl);
                $html = ob_get_contents();
                ob_end_clean();
                $return = str_replace('/mediabank','javascript:void(0)',$html);
            } else {
                $return = 'Collection not found. Only collections which can be reindexed are ('.implode(',', MediabankResourceConstants::$REINDEX_collections).')';
            }
            $endTime = time();
            $time = $endTime - $startTime;
            if($time > 60) {
                $mins = intval($time/60);
                $secs = $time%60;
                $time = "It took $mins minutes and $secs seconds to reindex $mediabankCollection collection.";
            } else {
                $time = "It took $time seconds to reindex $mediabankCollection collection.";
            }
            $return .= '<br />'.$time;
            if($mediabankCollection == MediabankResourceConstants::$COLLECTION_echo360) {
                //LINK ECHO360 MIDS FROM MEDIABANK TO COMPASS
                $mediabankEcho360Connector = new MediabankEcho360Connector();
                $result = $mediabankEcho360Connector->link();
                if(!isset($result) || (isset($result) && $result['error'] == true) ) {
                    $errorMsg = 'Echo360 Connector error <br />';
                    $errorMsg .= $result['error_string'].'<br />';
                    Zend_Registry::get('logger')->warn(PHP_EOL."CLASS\t: ".__CLASS__.PHP_EOL."METHOD\t: ".__METHOD__.PHP_EOL."Message\t: ".$errorMsg.PHP_EOL);
                } else {
                    echo "<br />Successfully linked any new lecture recordings that may have been created in Echo360 to Compass<br />";
                }
            }
        } else {
            $return = $mediabankCollection. ' collection not found.';
        }
        
        $endMsg = 'Result - : ' . strip_tags($return) .PHP_EOL;
        $endMsg .= 'End - Incremental reindexing Mediabank Collection : ' . $mediabankCollection;
        Zend_Registry::get('logger')->warn(PHP_EOL."CLASS\t: ".__CLASS__.PHP_EOL."METHOD\t: ".__METHOD__.PHP_EOL."Message\t: ".$endMsg.PHP_EOL);
        
        
        return $return;
    }
    
}
?>