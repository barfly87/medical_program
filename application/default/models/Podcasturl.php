<?php
class Podcasturl extends Zend_Db_Table_Abstract {
    
    protected $_name = 'podcasturl';
    
    public function insertUrl($url) {
        $uid = UserAcl::getUid();
        if($uid != 'unknown' && strlen(trim($url)) > 0) {
            try {
                $time = time();
                $data = array(
                    'uid'       =>      $uid,
                    'epoch'     =>      $time,
                    'url'       =>      trim($url),
                    'flag'      =>      0
                );
                $id = $this->insert($data);
                if($id > 0 ) {
                    return $id.PodcastUrlService::$idEpochSeparator.$time;
                }
                return false;
            } catch (Exception $ex) {
            	$error = $ex->getMessage().PHP_EOL.$ex->getTraceAsString().PHP_EOL;
            	Zend_Registry::get('logger')->warn(PHP_EOL."CLASS\t: ".__CLASS__.PHP_EOL."METHOD\t: ".__METHOD__.PHP_EOL."Error\t: ".$error.PHP_EOL);
                return false;
            }
        } else {
            die('User does not exist. Please login and try again. ');
        }
    }
    
    public function getRow($idEpoch, $updateFlag = false) {
        try {
            if(strstr($idEpoch,PodcastUrlService::$idEpochSeparator) === false) {
                return false;
            }
            list($id, $epoch) = explode(PodcastUrlService::$idEpochSeparator, $idEpoch);
            $row =  $this->fetchRow(
                        $this->select()
                            ->where('auto_id = ? ',$id)
                            ->where('epoch = ? ', $epoch)
                    );
            if(! empty($row)) {
                if($updateFlag === true) {
                    $row->flag = 1;
                    $row->save();
                }
                return $row->toArray();   
            }
            return false;
        } catch (Exception $ex) {
        	$error = $ex->getMessage().PHP_EOL.$ex->getTraceAsString().PHP_EOL;
        	Zend_Registry::get('logger')->warn(PHP_EOL."CLASS\t: ".__CLASS__.PHP_EOL."METHOD\t: ".__METHOD__.PHP_EOL."Error\t: ".$error.PHP_EOL);
            return false;
        }
    }
    
    public function urlExistForCurrentUser($url) {
        try {
            if(!empty($url)) {
                $uid = UserAcl::getUid();
                $row =  $this->fetchRow(
                            $this->select()
                                ->where('uid = ?', $uid)
                                ->where('url = ?', $url)
                        );
                if(!empty($row)) {
                    return $row->auto_id.PodcastUrlService::$idEpochSeparator.$row->epoch;
                }
                return false;
            }
            return $url;
        } catch (Exception $ex) {
        	$error = $ex->getMessage().PHP_EOL.$ex->getTraceAsString().PHP_EOL;
        	Zend_Registry::get('logger')->warn(PHP_EOL."CLASS\t: ".__CLASS__.PHP_EOL."METHOD\t: ".__METHOD__.PHP_EOL."Error\t: ".$error.PHP_EOL);
            return false;
        }   
    }
    
}
?>