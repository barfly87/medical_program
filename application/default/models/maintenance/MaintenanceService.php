<?php

class MaintenanceService {
    
    private $service    = null;
    private $token      = null;
    
    public function __construct($service, $token) {
        if(!empty($service) && !empty($token)) {
            $serviceExist   = MaintenanceConst::isServiceAllowed($service);
            $tokenValid     = MaintenanceConst::authenticateToken($token);
            if($tokenValid === true && $serviceExist === true) {
                $this->service = $service;
                $this->token = $token;
            }
        }    
    }    
    
    public function run() {
        $return = '';
        $toEmail = null;
        $toEmailName = null;
        $toEmailSubject = null;
        if(!empty($this->service) && !empty($this->token)) {
            switch($this->service) {
                case 'lectopia_medvid':
                    $toEmail = Zend_Registry::get('config')->maintenance->error->email;
                    $toEmailName = Zend_Registry::get('config')->maintenance->error->emailName;
                    $toEmailSubject = 'Maintenance - Lectopia Medvid Error';                     
                    $maintenanceLectopiaMedvid = new MaintenanceLectopiaMedvid();
                    $return = $maintenanceLectopiaMedvid->process();
                    Zend_Registry::get('logger')->warn('You have requested \'lectopia_medvid\' service.');                    
                break;
                case 'mediabank_collections':
                    $toEmail = Zend_Registry::get('config')->maintenance->error->email;
                    $toEmailName = Zend_Registry::get('config')->maintenance->error->emailName;
                    $toEmailSubject = 'Maintenance - Mediabank Collections Error';                     
                    $maintenanceMediabankCollections = new MaintenanceMediabankCollections();
                    $return = $maintenanceMediabankCollections->process();
                    Zend_Registry::get('logger')->warn('You have requested \'mediabank_collections\' service.');                    
                break;
                case 'optimize_lucene_index':
                    $toEmail = Zend_Registry::get('config')->maintenance->error->email;
                    $toEmailName = Zend_Registry::get('config')->maintenance->error->emailName;
                    $toEmailSubject = 'Maintenance - Optimize Index Error';                     
                    $maintenanceLuceneIndex = new MaintenanceLuceneIndex();
                    $return = $maintenanceLuceneIndex->process();
                    Zend_Registry::get('logger')->warn('You have requested \'optimize_lucene_index\' service.');                    
                break;
                default:
                    $error = "Functionality does not exist to process this service '$this->service'";
                    $return = PHP_EOL."CLASS\t: ".__CLASS__.PHP_EOL."METHOD\t: ".__METHOD__.PHP_EOL."Error\t: ".$error.PHP_EOL;
                    Zend_Registry::get('logger')->warn($return);
                break;
            }
        } else {
            $error = "Empty service requested '$this->service' OR empty token given '$this->token'";
            $return = PHP_EOL."CLASS\t: ".__CLASS__.PHP_EOL."METHOD\t: ".__METHOD__.PHP_EOL."Error\t: ".$error.PHP_EOL;
            Zend_Registry::get('logger')->warn($return);
        }
        if($return !== true && !is_null($toEmail) && !is_null($toEmailName) && !is_null($toEmailSubject)) {
            $fromEmail = Zend_Registry::get('config')->email->from->email;
            $fromEmailName  = Zend_Registry::get('config')->email->from->name;
            $this->sendMail($return, $fromEmail, $fromEmailName, $toEmail, $toEmailName, $toEmailSubject);
        }        
        return $return;
    }
    
    public function hasError() {
        if($this->service == null || $this->token == null) {
            return true;
        }
        return false;
    }
    
    private function sendMail($body, $fromEmail, $fromEmailName, $toEmail, $toEmailName, $toEmailSubject) {
        
        $mail = new Zend_Mail();
        $mail->setBodyText(strip_tags($body));
        $mail->setBodyHtml($body);
        $mail->setFrom($fromEmail, $fromEmailName);
        $mail->addTo($toEmail, $toEmailName);
        $mail->setSubject($toEmailSubject);
        $mail->send();
        
    }    
    
}

?>