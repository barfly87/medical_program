<?php
class MaintenanceConst {
    
    public static function getToken() {
        $config = Zend_Registry::get('config');
        if(isset($config) && isset($config->maintenance->token)) {
            return trim($config->maintenance->token);
        } else {
            $error = 'Getting maintenance token from config.ini';
            Zend_Registry::get('logger')->warn(PHP_EOL."CLASS\t: ".__CLASS__.PHP_EOL."METHOD\t: ".__METHOD__.PHP_EOL."Error\t: ".$error.PHP_EOL);
        }
        return false;
    }
    
    public static function authenticateToken($tokenGiven) {
        if(!empty($tokenGiven) && self::getToken() == $tokenGiven) {
            return true;
        } else {
            $error = "Invalid token('$tokenGiven') given for authentication ";
            Zend_Registry::get('logger')->warn(PHP_EOL."CLASS\t: ".__CLASS__.PHP_EOL."METHOD\t: ".__METHOD__.PHP_EOL."Error\t: ".$error.PHP_EOL);
        }
        return false;    
    }
    
    public static function getServicesAllowed() {
        $config = Zend_Registry::get('config');
        if(isset($config) && isset($config->maintenance->services->allowed)) {
            return $config->maintenance->services->allowed->toArray();
        } else {
            $error = 'Getting list of maintenance services from config.ini';
            Zend_Registry::get('logger')->warn(PHP_EOL."CLASS\t: ".__CLASS__.PHP_EOL."METHOD\t: ".__METHOD__.PHP_EOL."Error\t: ".$error.PHP_EOL);
        }
        return array();
    }
    
    public static function isServiceAllowed($serviceRequested) {
        $allowed = false;
        $services = self::getServicesAllowed();
        if(!empty($services)) {
            foreach($services as $service) {
                if(trim($service) == $serviceRequested) {
                    $allowed = true;
                    break;
                }   
            }
        }
        if($allowed === false) {
            $error = "Service requested '$serviceRequested' does not exist.";
            Zend_Registry::get('logger')->warn(PHP_EOL."CLASS\t: ".__CLASS__.PHP_EOL."METHOD\t: ".__METHOD__.PHP_EOL."Error\t: ".$error.PHP_EOL);
        }
        return $allowed;
    }
    
}
?>