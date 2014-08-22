<?php
class ExambankService {

    public function getNumberOfQuestionsByLO($loId) {
        ini_set('soap.wsdl_cache_enabled', '0');
        $return = false;
        $config = ExambankService::config();
        if(empty($config)) {
            $error = 'Exambank web service details seem to be missing from Compass Config';
            Zend_Registry::get('logger')->warn(PHP_EOL."CLASS\t: ".__CLASS__.PHP_EOL."METHOD\t: ".__METHOD__.PHP_EOL."Error\t: ".$error.PHP_EOL);            
            return $return;
        }
        try {
        	$soapClient = new SoapClient($config['wsdlUrl'],array('trace'=>true));
            $request = array(
                            'arg0' => $config['requestToken'], 
                            'arg1' => $loId
                        );
            $info = $soapClient->getNumberOfQuestionsByLO($request)->return;
            if(!empty($info) && is_array($info) && count($info) >= 2){
                $receivedResponseToken = $info[0];
                if(strcmp($config['responseToken'], $receivedResponseToken) == 0) {
                    $return = $info[1];
                }
            }
        } catch (SoapFault $fault) {
        	if(is_object($soapClient)) {
            $requestHeaders     = $soapClient->__getLastRequestHeaders();
            $responseHeaders    = $soapClient->__getLastResponseHeaders();
        	}
            $error              = "EXAMBANK WEB SERVICE\n";
            $error              .="FAULT CODE: ".$fault->faultcode."\nFAULT STRING: ".$fault->faultstring."\n";
            $error              .="REQUEST HEADERS: \n".$requestHeaders."\nRESPONSE HEADERS: \n".$responseHeaders;
            Zend_Registry::get('logger')->warn(PHP_EOL."CLASS\t: ".__CLASS__.PHP_EOL."METHOD\t: ".__METHOD__.PHP_EOL."Error\t: ".$error.PHP_EOL);            
        }
        return $return;
    }
    
    public static function config() {
        $wsdlUrl                = Compass::getConfig('exambank_wsdl_uri');
        $requestToken           = Compass::getConfig('exambank_request_token');
        $responseToken          = Compass::getConfig('exambank_response_token');
        $addNewQuestionUrl      = Compass::getConfig('exambank_add_new_question_url');
        $linkExistingQuestionUrl= Compass::getConfig('exambank_link_existing_question_url');
        
        if(!empty($wsdlUrl) && !empty($requestToken) && !empty($responseToken)) {
            return array(
                'wsdlUrl'                   => $wsdlUrl,
                'requestToken'              => $requestToken,
                'responseToken'             => $responseToken,
                'addNewQuestionUrl'         => $addNewQuestionUrl,
                'linkExistingQuestionUrl'   => $linkExistingQuestionUrl
            );
        }
        return array();
    }
    
}
?>