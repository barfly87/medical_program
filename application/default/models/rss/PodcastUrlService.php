<?php
class PodcastUrlService {

    private $_params = array();
    public static $idEpochSeparator = '_';
    
    public function createUrlId($url) {
        $podcasturl = new Podcasturl();
        $idEpochExist = $podcasturl->urlExistForCurrentUser($url);
        if($idEpochExist === false) {
            return $podcasturl->insertUrl($url);
        }
        return $idEpochExist;
    }
    
    public function getUrl($idEpoch) {
        $podcasturl = new Podcasturl();
        $row = $podcasturl->getRow($idEpoch, true);
        if($row !== false && isset($row['url']) && !empty($row['url'])) {
            return $row['url'];
        }
        return false;
    }
    
    public function createPodcastTitle($params) {
        $this->_params = $params;
        $title = array();
        $title[] = 'Compass Podcast'; 
        $configFormElements = self::getConfigFormElementsForPodcastTitleCreation();
        $process = (isset($params['process']) && in_array($params['process'], array('advanced', 'simple'))) 
                        ? $params['process'] : 'advanced';
                        
        if(!empty($configFormElements) && isset($configFormElements[$process])) {
            $formElements = $configFormElements[$process];
            foreach($formElements as $formElement) {
                switch($formElement) {
                    case 'qstr':
                        $title[] = $this->_processQstr();
                    break;
                    case 'discipline':
                        $title[] = $this->_processDiscipline();
                    break;
                    case 'stage':
                        $title[] = $this->_processStage();
                    break;
                    case 'blockweek':
                        $title[] = $this->_processBlockWeek();
                    break;
                    default:
                        $title[] = $this->_createTitleFromParam($formElement);                       
                }
            }
        }
        $title = implode(' - ', array_filter($title));
        return $title;
    }
    
    public static function getNoOfYearsAllowedToCreateUrl () {
        $return = array();
        $noOfYrs = (int) Compass::getConfig('podcast.create.url.noofyears.allowed');
        if($noOfYrs > 0) {
            $currentYear = date('Y', time());
            $return[] = $currentYear;
            --$noOfYrs;//Since we added current year we need to minus $noOfYrs by 1
            if($noOfYrs > 0) { 
                for($i=$noOfYrs; $i>0; $i--) {
                    $return[] = --$currentYear;
                }
            }
            return $return;
        } else {
            $error = 'Config podcast.create.url.lastnoofyears does not exist or incorrect values was received.';
            Zend_Registry::get('logger')->warn(PHP_EOL."CLASS\t: ".__CLASS__.PHP_EOL."METHOD\t: ".__METHOD__.PHP_EOL."Error\t: ".$error.PHP_EOL);
        }
        return $return;
    }

    public static function getConfigFormElementsForPodcastTitleCreation() {
        $config = Zend_Registry::get('config');
        if(isset($config->podcast->title->form->elements)) {
            $return = $config->podcast->title->form->elements->toArray();
            if(isset($return['simple']) && strstr($return['simple'], ',') !== false) {
                $return['simple'] = explode(',', $return['simple']);
            }
            if(isset($return['advanced']) && strstr($return['advanced'], ',') !== false) {
                $return['advanced'] = explode(',', $return['advanced']);
            }
            return $return;
        }
        return array();
    }
    
    private function _processBlockWeek () {
        $str = $this->_createTitleFromParam('blockweek');
        if(!empty($str) && stristr($str,'Any') === false) {
           return "Block Week $str";
        }
        return '';
    }

    private function _processStage() {
    	$str = $this->_createTitleFromParam('stage');
    	if(!empty($str) && stristr($str,'Any') === false) {
    	   return "Stage ($str)";
    	}
    	return '';
    }
    
    private function _processQstr () {
    	$str = $this->_createTitleFromParam('qstr');
    	if(!empty($str)) {
    	   return "'$str'";
    	}
    	return '';
    }
    
    private function _processDiscipline() {
        if(isset($this->_params['discipline']) 
            && !empty($this->_params['discipline'])) {
            $disciplineNames = array();
            $disciplineService = new DisciplineService();
            foreach($this->_params['discipline'] as $disciplineId) {
                $disciplineNames[] = $disciplineService->getNameOfDiscipline($disciplineId); 
            }
            $this->_params['discipline'] = $disciplineNames;
            return $this->_createTitleFromParam('discipline');
        }
        return '';
    }
    
    private function _createTitleFromParam($key) {
        $return = '';
        if(isset($this->_params[$key])) {
            $param = $this->_params[$key];
            if(is_string($param) && strlen(trim($param)) > 0) {
                $return .= trim($param);
            } else if(is_array($param) && !empty($param)) {
                $return .= implode(', ', $param);
            } else if (is_int($param)) {
                $return .= $param;
            }
        }
        return $return;
    }
    
}