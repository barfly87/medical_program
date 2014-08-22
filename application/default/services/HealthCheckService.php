<?php 
class HealthCheckService {
    
    private $_data = null;
    
    public function __construct() {
        clearstatcache();
    }
    
    public function run() {
        $tests = array();
        $this->_data['ALL_TESTS']['start'] = microtime(true);
        $this->_startTime('Config');
        $this->_config($tests);
        $this->_stopTime('Config');
        
        $this->_startTime('PHP Modules');
        $this->_phpModules($tests);
        $this->_stopTime('PHP Modules');
        
        $this->_startTime('PHP Ofc Lib');
        $this->_phpOfcLib($tests);
        $this->_stopTime('PHP Ofc Lib');
        
        $this->_startTime('Zend Standard Analyzer');
        $this->_zendStandardAnalyzer($tests);
        $this->_stopTime('Zend Standard Analyzer');
        
        $this->_startTime('Apache Ownership');
        $this->_apacheOwnership($tests);
        $this->_stopTime('Apache Ownership');
        
        $this->_startTime('Lucene Index');
        $this->_luceneIndex($tests);
        $this->_stopTime('Lucene Index');
        
        $this->_startTime('Database');
        $this->_database($tests);
        $this->_stopTime('Database');
        
        $this->_startTime('Logging');
        $this->_logging($tests);
        $this->_stopTime('Logging');
        
        $this->_startTime('Events');
        $this->_events($tests);
        $this->_stopTime('Events');
        
        $this->_startTime('Exambank');
        $this->_exambank($tests);
        $this->_stopTime('Exambank');
        
        $this->_startTime('Echo360');
        $this->_echo360($tests);
        $this->_stopTime('Echo360');
        
        $this->_startTime('Mediabank');
        $this->_mediabank($tests);
        $this->_stopTime('Mediabank');
        
        $this->_startTime('LDAP');
        $this->_ldap($tests);
        $this->_stopTime('LDAP');
        
        $this->_data['ALL_TESTS']['finish'] = microtime(true);
        $this->_data['ALL_TESTS']['total'] = ($this->_data['ALL_TESTS']['finish'] - $this->_data['ALL_TESTS']['start']);
        $this->_data['ALL_TESTS']['seconds'] = (int)$this->_data['ALL_TESTS']['total'] - $hours*60*60 - $minutes*60;
        
        $emailIfMoreThanSeconds = Compass::getConfig('healthcheck.email.after.seconds');
        if(!empty($emailIfMoreThanSeconds) && (int)$emailIfMoreThanSeconds > 0 && $this->_data['ALL_TESTS']['seconds'] >= (int)$emailIfMoreThanSeconds) {
            $emailBody = '<table border="1" style="border-collapse: collapse;">';
            $counter = 1;
            foreach($this->_data as $task => $data) {
                $seconds = ($data['seconds'] > 0) ? '<b style="color:red;">'.$data['seconds'].' seconds</b>' : $data['seconds'] .'seconds';
                $emailBody .= '<tr><th style="text-align:left;padding:3px;">'.$task .'</th><td style="text-align:left;padding:3px;">'.$seconds.'</td></tr>';
                $counter++;
            }
            $emailBody .='</table>';
            $error = $this->_data['ALL_TESTS']['seconds'] . ' seconds to load healthcheck page';
            $this->sendMail($emailBody, $error);
            Zend_Registry::get('logger')->warn(PHP_EOL."CLASS\t: ".__CLASS__.PHP_EOL."METHOD\t: ".__METHOD__.PHP_EOL."ERROR\t: ".$error.PHP_EOL);
        }
        return $tests;
    }
    
    private function _startTime($task) {
        $this->_data[$task]['start'] = microtime(true);
    }
    
    private function _stopTime($task) {
        $this->_data[$task]['finish'] = (microtime(true));
        $this->_data[$task]['total'] = ($this->_data[$task]['finish'] - $this->_data[$task]['start']);
        $this->_data[$task]['seconds'] = (int)$this->_data[$task]['total'] - $hours*60*60 - $minutes*60;
    }
    
    public function monitor($quiet = true, $displayAllOk = true) {
        $tests = $this->run();
        $fail = false;
        $allOkFlag = true;
        $failedTests = '';
        $failedTestsError = '';
        foreach($tests as $test) {
            if($test['outcome'] == true) {
                if($quiet == false) {
                    echo $test['short_desc'].PHP_EOL.'<br />';
                }
            } else {
                $failedTests .= '<tr>';
                $failedTests .= '<td style="vertical-align:top; padding:3px;">'.$test['short_desc'].'</td>';
                $failedTests .= '<td style="vertical-align:top; padding:3px; width: 80px;">'.$test['desc'].'</td>';
                $failedTests  .= '</tr>';
                echo $test['short_desc'].PHP_EOL.'<br />';
                $failedTestsError .= $test['short_desc'].PHP_EOL;
                $allOkFlag = false;
            }
        }
        if($displayAllOk == true && $allOkFlag == true) {
            echo 'ALL OK';
        }
        if(!empty($failedTests)) {
            $error = 'Healthcheck FAILED';
            $failedTests = '<table border="1" style="border-collapse:collapse"><th style="text-align:left; padding:3px;">Short Description</th><th style="text-align:left; padding:3px;">Description</th>'.$failedTests.'</table>';
            $this->sendMail($failedTests, $error);
            Zend_Registry::get('logger')->warn(PHP_EOL."CLASS\t: ".__CLASS__.PHP_EOL."METHOD\t: ".__METHOD__.PHP_EOL."ERROR\t: ".$error.PHP_EOL.$failedTestsError);
        }
        exit;
    }
    
    
    
    private function sendMail($body, $toEmailSubject) {
        $email = Compass::getConfig('healthcheck.email');
        if(isset($email['from']) && isset($email['to'])) {
            $from = $this->_getEmailAndName($email['from'],' Healthcheck FROM email is not set in the config file.');
            $tos = array();
            if(is_array($email['to'])) {
                foreach($email['to'] as $toString) {
                    $to = $this->_getEmailAndName($toString,' Healthcheck TO email is not set in the config file.');
                    if(!empty($to)) {
                        $tos[] = $to;
                    }
                }
            }
            if(!empty($from) && !empty($tos)) {
                $mail = new Zend_Mail();
                $mail->setBodyText(strip_tags($body));
                $mail->setBodyHtml($body);
                if(isset($from['email']) && isset($from['emailName'])) {
                    $mail->setFrom($from['email'], $from['emailName']);
                } else {
                    $mail->setFrom($from['email']);
                }
                foreach($tos as $to) {
                    if(isset($to['email']) && isset($to['emailName'])) {
                        $mail->addTo($to['email'],$to['emailName']);
                    } else {
                        $mail->addTo($to['email']);
                    }
                }
                $mail->setSubject($toEmailSubject);
                $mail->send();
            }
        } else {
            $error =  "Config option 'healthcheck.email' is not set.";
            Zend_Registry::get('logger')->warn(PHP_EOL."CLASS\t: ".__CLASS__.PHP_EOL."METHOD\t: ".__METHOD__.PHP_EOL."ERROR\t: ".$error.PHP_EOL);
        }
    }
    
    private function _getEmailAndName($string, $error = '') {
        $return = array();
        $email = null;
        $emailName = null;
        $string = trim($string);
        $validator = new Zend_Validate_EmailAddress();
        if(stristr($string, "|") !== false) {
            $explode = explode("|", $string);
            if($validator->isValid($explode[0])) {
                $email = $explode[0];
            }
            if(is_string($explode[1])) {
                $emailName = $explode[1];
            }
        } else if ($validator->isValid($string)) {
            $email = $string;
        }
        if(!is_null($email)) {
            $return['email'] = $email;
        } else if (!empty($error)){
            Zend_Registry::get('logger')->warn(PHP_EOL."CLASS\t: ".__CLASS__.PHP_EOL."METHOD\t: ".__METHOD__.PHP_EOL."ERROR\t: ".$error.PHP_EOL);
        }
        if(!is_null($emailName)) {
            $return['emailName'] = $emailName;
        }
        
        return $return;
    }
    
    private function _ldap(&$tests) {
        $this->_ldapConnection($tests);
        $this->_ldapGroupCompassAdmin($tests);
    }
    
    private function _ldapConnection(&$tests) {
        $test = array();
        $test['title'] = 'LDAP - Connection';
        try {
            $ds = Zend_Registry::get('ds');
            $ds->connect();
            $test['outcome'] = true;
            $test['desc'] = 'Successfully connected to LDAP';
            $test['short_desc'] = 'OK - LDAP - Connection';
       } catch (Exception $ex) {
            $test['outcome'] = false;
            $test['desc'] = 'Cannot access LDAP. Got an Exception:<br />'.$ex->getMessage().'<br />Check your application/config.ini file';
            $test['short_desc'] = 'FAIL - LDAP - Connection';
       }
        $tests[] = $test;
    }
    
    private function _ldapGroupCompassAdmin(&$tests) {
        $test = array();
        $test['title'] = 'LDAP - Groups';
        try {
            $ds = Zend_Registry::get('ds');
            $groups = array();
            $groups['Admin'] = 'compassadmin';
            try{
                $studentGroup = Compass::getConfig('ldapdirectory.studentgroup');
                if(!empty($studentGroup)) {
                    $groups['Student'] = $studentGroup;
                }
                $staffGroup = Compass::getConfig('ldapdirectory.staffgroup');
                if(!empty($studentGroup)) {
                    $groups['Staff'] = $staffGroup;
                }
            } catch (Exception $ex) {
            }
            
            $allGroupsExist = true;
            $groupsExist = array();
            $groupsDontExist = array();
            foreach($groups as $groupName => $group) {
                $ldapGroup = $ds->getGroup($group);
                if(empty($ldapGroup)) {
                    $allGroupsExist = false;
                    $groupsDontExist[$groupName] = $group;
                } else {
                    $groupsExist[$groupName] = $group;
                }
            }
            
            $desc = array();
            if(!empty($groupsDontExist)) {
                $groupStr = (count($groupsDontExist) > 1) ? 'groups' : 'group';
                $desc[] = sprintf("<span style='color:red;'>Ldap %s '%s' does NOT exist. Please check your LDAP.</span>", $groupStr, implode(', ', $groupsDontExist), $groupStr);
            }
            if(!empty($groupsExist)) {
                $groupStr = (count($groupsExist) > 1) ? 'groups' : 'group';
                $desc[] = sprintf("Ldap %s '%s' exists.", $groupStr, implode(', ', $groupsExist));
            }
            
            $test['desc'] = implode('&nbsp;&nbsp;',$desc);
            if($allGroupsExist == true) {
                $test['outcome'] = true;
                $test['short_desc'] = 'OK - LDAP - Groups - '.implode(', ', array_keys($groupsExist));
            } else {
                $test['outcome'] = false;
                $test['short_desc'] = 'FAIL - LDAP - Groups - '.implode(', ', array_keys($groupsDontExist));
            }
       } catch (Exception $ex) {
            $test['outcome'] = false;
            $test['desc'] = sprintf("While accessing LDAP group 'compassadmin' got an Exception:<br />%s<br />Make sure that 'compassadmin' group exists in LDAP", $ex->getMessage());
            $test['short_desc'] = 'FAIL - LDAP - Group - Admin';
       }
        $tests[] = $test;
    }
    
    private function  _mediabank(&$tests) {
        $test = array();
        $test['title'] = 'Mediabank - Connection';
        $rows = array();
        try {
            $mediabankResource = new MediabankResource();
            $collectionCompassResources = MediabankResourceConstants::$COLLECTION_compassresources;
            
            $table = $mediabankResource->getTableName();
            $select = $mediabankResource->select()->distinct()->from($table,'resource_id')->where("resource_id like '%|$collectionCompassResources|%'")->limit(10);
            $resourceIds = $mediabankResource->fetchAll($select);
            if($resourceIds->count() > 0) {
                $rows = $resourceIds->toArray();
            }
        } catch (Exception $ex) {
            $rows = array();
        }
        
        if(!empty($rows)) {
            try {
                $found = 0;
                $mediabankResourceService = new MediabankResourceService();
                foreach($rows as $row) {
                    $mid = $row['resource_id'];
                    $metadata = $mediabankResourceService->getMetadata($mid, false);
                    $objectId = $metadata['objectId'];
                    $info = array(
                                    'Title' => $metadata['title'],
                                    'File Type Extension' => $metadata['fileTypeExtension'],
                                    'Mime Type' =>$metadata['decodedMimeType']
                    );
                    $infoStr = ResourceConstants::createMetadataTable($info);
                    $baseUrl = Compass::baseUrl();
                    if(empty($metadata['data'])) {
                        $descMetadata = sprintf('<img src="%s/img/cross.png"  style="height:20px;"/>&nbsp;', $baseUrl);
                        $descMetadata .= sprintf('Could not get metadata from Mediabank for a randomly selected mid ("%s") from database for compassresources collection', $mid);
                        $descMetadata .= sprintf('<div class="innerTable">%s</div><br />', $infoStr);
                        $desc[] = $descMetadata;
                    } else {
                        $found++;
                        $imageDesc = '';
                        if(isset($metadata['image']) && isset($metadata['image']['src'])) {
                            $imageDesc = sprintf('<img alt="IMAGE NOT FOUND" src="%s" style="width:60px; height: 60px;"/><br />', $metadata['image']['src']);
                        }
                        unset($metadata['data']['title']);
                        $metadata = ResourceConstants::createMetadataTable($info + $metadata['data']);
                        $desc[] = <<<HTML
                <img src="$baseUrl/img/tick.svg" style="height:14px;"/>
                Successfully accessed <a style="color:blue;" href="javascript:void(0);" onclick="$('#metadata-$objectId').toggle();">METADATA</a> 
                    for a randomly selected mid ("$mid") from database for compassresources collection. <br />$imageDesc<br />
                <div class="innerTable">
                    <div id="metadata-$objectId" style="display: none;">
                        <pre>$metadata</pre>
                    </div>
                </div>
HTML;
                    }
                }
                
                $descList = '<a style="color:blue;" href="javascript:void(0);" onclick="$(\'#mediabank-request-list\').toggle();">DETAILED INFORMATION</a><ol id="mediabank-request-list" style="display:none"><li>'.implode('</li><li>', $desc).'</li></ol>';
                if($found > 0) {
                    $test['outcome'] = true;
                    if($found == count($rows)) {
                        $desc = sprintf('Successfully accessed %s of %s requests made to Mediabank. ', $found, count($rows));
                    } else {
                        $desc = sprintf('<span style="color:red;">Alert ! </span>Could only access %s of %s requests made to Mediabank. ', $found, count($rows));
                    }
                    $test['desc'] = $desc.$descList;
                    $test['short_desc'] = 'OK - Mediabank';
                } else {
                    $test['outcome'] = false;
                    $desc = sprintf('Could not access Mediabank. Total requests send  %s. ', count($rows));
                    $test['desc'] = $desc.$descList;
                    $test['short_desc'] = 'Fail - Mediabank';
                }
            } catch (Exception $ex) {
                $test['outcome'] = false;
                $test['desc'] = "Exception occurred while trying to connect to mediabank.<br />EXCEPTION: ".$ex->getMessage().'<br />';
                $test['short_desc'] = 'Fail - Mediabank';
            }
        } else {
            $test['outcome'] = true;
            $test['desc'] = '<span style="color:red;">Alert ! </span>Could not grab any compass mids from the database.';
            $test['short_desc'] = 'OK - Mediabank';
        }
        $tests[] = $test;
    }
    
    private function _template(&$tests) {
        $test = array();
        $test['title'] = '';
        $test['outcome'] = false;
        $test['desc'] = '';
        $test['short_desc'] = '';
        $tests[] = $test;
    }
    
    private function _echo360(&$tests) {
        $test = array();
        $test['title'] = 'Echo360 - Connection';
        $presentationId = null;
        $rows = array();
        try{
            $mediabankResource = new MediabankResource();
            $collectionEcho360 = MediabankResourceConstants::$COLLECTION_echo360;
            $table = $mediabankResource->getTableName();
            $select = $mediabankResource->select()->distinct()->from($table,'resource_id')->where("resource_id like '%|$collectionEcho360|%'")->limit(10);
            $resourceIds = $mediabankResource->fetchAll($select);
            if($resourceIds->count() > 0) {
                $rows = $resourceIds->toArray();
            }
        } catch (Exception $ex) {
            $rows = array();
            $test['outcome'] = true;
            $test['desc'] = '<span style="color:red;">Alert ! </span>Could not grab any presentation ids from the database.<br />Exception:'.$ex->getMessage();
            $test['short_desc'] = 'OK - Echo360';
        }
            
        if(!empty($rows)) {
            try {
                $presentationId = '';
                $echo360Service = new Echo360Service();
                $outcome = false;
                
                foreach($rows as $row) {
                    $mid = $row['resource_id'];
                    $presentationId = MediabankResourceConstants::getEcho360PresentationId($mid);
                    $url = $echo360Service->generateUrl($presentationId);
                    $headers = $echo360Service->getHeaders($url['url']);
                    if(!empty($headers)) {
                        $lastHeader = count($headers) - 1 ;
                        if(isset($headers[$lastHeader]['http']) && stristr($headers[$lastHeader]['http'], '200 OK')) {
                            $outcome = true;
                            break;
                        }
                    }
                }
                if($outcome == true) {
                    $url = $echo360Service->generateUrl($presentationId);
                    $test['outcome'] = true;
                    $test['desc'] = 'Successfully accessed <a href="'.$url['url'].'" style="color: blue;">ECHO 360 LINK</a> for presentation id '.$presentationId;
                    $test['short_desc'] = 'OK - Echo360';
                } else {
                    $url = $echo360Service->generateUrl($presentationId);
                    $test['outcome'] = false;
                    $test['desc'] = 'Could not access <a href="'.$url['url'].'" style="color: blue;">ECHO 360 LINK</a>';
                    $test['short_desc'] = 'Fail - Echo360';
                }
            } catch (Exception $ex) {
                $test['outcome'] = false;
                $test['desc'] = 'Could not generate echo360 URL.<br />Exception:'.$ex->getMessage();
                $test['short_desc'] = 'Fail - Echo360';
            }
        } else {
            $test['outcome'] = true;
            $test['desc'] = '<span style="color:red;">Alert ! </span>Could not grab any presentation ids from the database.';
            $test['short_desc'] = 'OK - Echo360';
        }
        $tests[] = $test;
    }
    
    private function _getHttpCode($url) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, TRUE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        $head = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return $httpCode;
    }
    
    private function _exambank(&$tests) {
        $test = array();
        $test['title'] = 'Exambank - Connection';
        $wsdl = Compass::getConfig('exambank_wsdl_uri');
        if(!empty($wsdl)) {
            try {
                $client = new Zend_Soap_Client($wsdl);
                $functions = print_r($client->getFunctions(), true);
                $noOfFunctions = count($client->getFunctions());
                $test['outcome'] = true;
                $noOfQuestionsStr = '';
                $exambankService = new ExambankService();
                $noOfQuestions = $exambankService->getNumberOfQuestionsByLO(1);
                if($noOfQuestions !== false) {
                    $noOfQuestionsStr = "Tried to call the function getNumberOfQuestionsByLO(\$loId = 1) and got no of questions attached as '$noOfQuestions'  ";
                } else {
                    $noOfQuestionsStr = '<span style="color:red;">Alert ! </span>Tried to call the function getNumberOfQuestionsByLO($loId = 1) got exception please check logs to find what exception occurred.';
                }
                $test['short_desc'] = 'OK - Exambank WS';
                $test['desc'] = <<<HTML
            Successfully connected to exambank wsdl. Found $noOfFunctions <a style="color:blue;" href="javascript:void(0);" onclick="$('#exambank_function').toggle();">FUNCTION(S)</a>. $noOfQuestionsStr
            <div id="exambank_function" style="display:none;">
                <pre>$functions</pre>
            </div>
HTML;
            } catch (Exception $ex) {
                $test['outcome'] = false;
                $desc = "Exception occurred while trying to connect to exambank wsdl '$wsdl'.<br />EXCEPTION: ".$ex->getMessage().'<br />';
                $test['desc'] = $desc;
                $test['short_desc'] = 'FAIL - Exambank WS';
            }
        } else {
            $test['outcome'] = true;
            $test['desc'] = '<span style="color:red;">Alert ! </span>Could not find the wsdl URL for Exambank. May be Exambank is not attached to Compass';
            $test['short_desc'] = 'OK - Exambank WS not set';
        }
        $tests[] = $test;
    }
    
    private function _events(&$tests) {
        $test = array();
        $test['title'] = 'Events - Connection';
        $wsdl = Compass::getConfig('event_wsdl_uri');
        if(!empty($wsdl)) {
            try {
                $client = new Zend_Soap_Client($wsdl);
                $functions = print_r($client->getFunctions(), true);
                $noOfFunctions = count($client->getFunctions());
                $test['outcome'] = true;
                $eventTypesStr = '';
                try {
                    $eventTypes = $client->fetchAllEventtypes();
                    $noOfEventTypes = count($eventTypes);
                    $eventTypesStr = "Tried to call the function fetchAllEventtypes() and got '$noOfEventTypes' event types returned ";
                } catch (Exception $ex) {
                    $eventTypesStr = '<span style="color:red;">Alert ! </span>Tried to call the function fetchAllEventtypes() but got<br />EXCEPTION: '. $ex->getMessage();
                }
                $test['short_desc'] = 'OK - Events WS';
                $test['desc'] = <<<HTML
            Successfully connected to events wsdl. Found $noOfFunctions <a style="color:blue;" href="javascript:void(0);" onclick="$('#events_function').toggle();">FUNCTION(S)</a>. $eventTypesStr
            <div id="events_function" style="display:none;">
                <pre>$functions</pre>
            </div>
HTML;
            } catch (Exception $ex) {
                $test['outcome'] = false;
                $desc = "Exception occurred while trying to connect to events wsdl '$wsdl'.<br />EXCEPTION: ".$ex->getMessage().'<br />';
                $test['desc'] = $desc;
                $test['short_desc'] = 'FAIL - Events WS';
            }
        } else {
            $test['outcome'] = true;
            $test['desc'] = '<span style="color:red;">Alert ! </span>Could not find the wsdl URL for Events. May be Events is not attached to Compass';
            $test['short_desc'] = 'OK - Events WS not set';
        }
        $tests[] = $test;
    }
    
    private function _logging(&$tests) {
        $test = array();
        $test['title'] = 'Logs - logged';
        $str = 'Compass Healthcheck Log Writeable By Zend'. date('Y-m-d H:i:s', time());
        try {
            Zend_Registry::get('logger')->warn(PHP_EOL.$str.PHP_EOL);
            $logFile = Compass::getConfig('log_file');
            $file = escapeshellarg($logFile);
            $tail = `tail -n 500 $file`;
            $foundTheLog = false;
            if($tail != null) {
                $lines = explode(PHP_EOL, $tail);
                $noOfLines = count($lines) - 1;
                for($i=$noOfLines; $i>0; $i--) {
                    if($str == $lines[$i]) {
                        $foundTheLog = true;
                        break;
                    }
                }
            }
            if($foundTheLog == false) {
                $test['outcome'] = false;
                $test['desc'] = 'Could not write to the log file or could not find the logged line used for testing';
                $test['short_desc'] = 'FAIL - Logging';
            } else {
                $test['outcome'] = true;
                $test['desc'] = 'Logged a test message successfully to the Compass log file';
                $test['short_desc'] = 'OK - Logging';
            }
        } catch (Exception $ex) {
            $test['outcome'] = false;
            $desc = "Exception occurred while trying to log using 'Zend_Registry::get(\"logger\")->warn(PHP_EOL.\$str.PHP_EOL);'.<br />EXCEPTION: ".$ex->getMessage().'<br />';
            $test['desc'] = $desc;
        }
        $tests[] = $test;
    }
    
    
    private function _database(&$tests) {
        $test = array();
        $test['title'] = 'Database - Connection';
        try {
            $teachingActivities = new TeachingActivities();
            $rows = $teachingActivities->fetchAll();
            $test['outcome'] = true;
            $test['desc'] = sprintf("Successfully connected to database and fetched '%s' rows from teachingactivity table", $rows->count());
            $test['short_desc'] ='OK - Database ';
        } catch (Exception $ex) {
            $test['outcome'] = false;
            $desc = "Exception occurred while trying to get total no of rows from teachingactivity table.<br />EXCEPTION: ".$ex->getMessage().'<br />';
            $desc .= "Check your 'compass/application/config.ini' and see all the database options are set correctly";
            $test['desc'] = $desc;
            $test['short_desc'] = 'FAIL - Database';
        }
        $tests[] = $test;
    }
    
    private function _luceneIndex(&$tests) {
        $this->_luceneIndexOpen($tests);  
        $this->_luceneIndexNoOfSegmentedFiles($tests);      
    }
    
    private function _luceneIndexOpen(&$tests) {
        $test = array();
        $test['title'] = 'Lucene Index - Open';
        try {
            $index = Compass_Search_Lucene::open(SearchIndexer::getIndexDirectory());
            $query = '+doctype:Linkage';
            $results = $index->find($query);
            $test['outcome'] = true;
            $test['desc'] = sprintf("Lucene index is accessible by Compass. Total no of documents found in the index are '%s'", count($results));
            $test['short_desc'] = 'OK - Index Access';
        } catch (Exception $ex) {
            $test['outcome'] = false;
            $desc = "Lucene index is NOT accessible by Compass.<br />Go to 'Admin' page and try to reindex lucene";
            $desc .= "<br />EXCEPTION: ".$ex->getMessage();
            $test['desc'] = $desc;
            $test['short_desc'] = 'FAIL - Index Access';
        }
        $tests[] = $test;
    }
    
    private function _luceneIndexNoOfSegmentedFiles (&$tests) {
        $test = array();
        $test['title'] = 'Lucene Index - No Of Files';
        $searchIndexDir = realpath(SearchIndexer::getIndexDirectory());
        $pass = 'OK - Index No Of Files';
        $fail = 'FAIL - Index No Of Files';
        if(is_dir($searchIndexDir)) {
            $dirs = scandir($searchIndexDir);
            if($dirs != false) {
                $countDirs = count($dirs);
                $noOfFilesAllowed = 50;
                if($countDirs > $noOfFilesAllowed) {
                    $test['outcome'] = false;
                    $desc = 'Search Index directory has got '.$countDirs .' files.<br />';
                    $desc .= 'The total number of files should never be above '.$noOfFilesAllowed.'.<br />';
                    $desc .= 'Try to click on "Optimize Lucene Index" from the Admin page and see if that fixes your problem';
                    $test['desc'] =  $desc;
                    $test['short_desc'] = $fail;
                } else {
                    $test['outcome'] = true;
                    $test['desc'] = 'Search Index directory has got '.$countDirs .' files';
                    $test['short_desc'] = $pass;
                }
            } else {
                $test['outcome'] = false;
                $test['desc'] = sprintf("Running the php command \"scandir('%s')\" returned false", $searchIndexDir);
                $test['short_desc'] = $fail;
            }
        } else {
            $test['outcome'] = false;
            $test['desc'] = sprintf('%s is not a directory. Directory path is received by calling \'SearchIndexer::getIndexDirectory();\'', $searchIndexDir);
            $test['short_desc'] = $fail;
        }
        $tests[] = $test;
    }
    
    private function _apacheOwnership(&$tests) {
        $scriptFileName = $_SERVER['SCRIPT_FILENAME'];
        $root = dirname(dirname($scriptFileName));
        $userInfo = posix_getpwuid(posix_getuid());
        $apacheUser = $userInfo['name'];
        $files = array(
                        array(
                            'path' =>$root.'/var',
                            'filetype' =>'directory',
                            'short_desc_pass' => 'OK - Folder - var',
                            'short_desc_fail'=>'FAIL - Folder - var'),
                        array(
                            'path' =>$root.'/var/search_index',
                            'filetype' =>'directory',
                            'short_desc_pass' => 'OK - Folder - var/search_index',
                            'short_desc_fail'=>'FAIL - Folder - var/search_index'),
                        array(
                            'path' =>$root.'/var/log',
                            'filetype' =>'directory',
                            'short_desc_pass' => 'OK - Folder - var/log',
                            'short_desc_fail'=>'FAIL - Folder - var/log'),
                        array(
                            'path' =>$root.'/var/log/log.txt',
                            'filetype' =>'file',
                            'short_desc_pass' => 'OK - File - var/log/log.txt',
                            'short_desc_fail'=>'FAIL - File - var/log/log.txt'),
                        array(
                            'path' =>$root.'/htdocs/img/noimage',
                            'filetype' =>'directory',
                            'short_desc_pass' => 'OK - Folder - img/noimage',
                            'short_desc_fail'=>'FAIL - Folder - img/noimage')
        );

        foreach($files as $file) {
            $test['title'] = 'Apache - ownership';
            if(file_exists($file['path'])) {
                if($file['filetype'] == 'directory') {
                    $fh = fopen($file['path'].'/.tmp-healthcheck', 'w');
                } else if($file['filetype'] == 'file'){
                    $fh = fopen($file['path'], 'a');
                }
                $writeable = true;
                if($fh == false) {
                    $writeable = false;
                } else {
                    $wrote = fwrite($fh, PHP_EOL.'Compass Healthcheck File Writeable '. date('Y-m-d H:i:s', time()).PHP_EOL);
                    if($wrote == false) {
                        $writeable = false;
                    }
                }
                fclose($fh);
                if($writeable != false && $file['filetype'] == 'directory') {
                    $removable = unlink($file['path'].'/.tmp-healthcheck');
                } else if($file['filetype'] == 'file'){
                    $removable = true;
                }
                
                if($writeable && $removable) {
                    $test['outcome'] = true;
                    $test['desc'] = sprintf("%s '%s' is writeable", ucwords($file['filetype']), $file['path']);
                    $test['short_desc'] = $file['short_desc_pass'];
                } else {
                    $test['outcome'] = false;
                    $test['short_desc'] = $file['short_desc_fail'];
                    $desc = sprintf("The group/owner of this %s '%s' should be '%s'. Cannot write to this %s.", 
                                    $file['filetype'], $file['path'], $apacheUser, $file['filetype']);
                    $desc .= sprintf("<br />Try 'sudo chown -R %s:%s %s'", $apacheUser, $apacheUser, $file['path']);
                    $test['desc'] = $desc;
                }
                $tests[] = $test;
            } else {
                $test['outcome'] = false;
                $desc =  sprintf("%s '%s' should exist and the group/owner should be '%s' or should be writable by '%s'.<br />", ucwords($file['filetype']), $file['path'], $apacheUser, $apacheUser);
                if($file['filetype'] == 'directory') {
                    $desc .= sprintf("Try 'sudo mkdir -p %s'",$file['path']);
                } else {
                    $desc .= sprintf("Try 'sudo touch %s'",$file['path']);
                }
                $test['desc'] = $desc;
                $test['short_desc'] = $file['short_desc_fail'];
                $tests[] = $test;
            }
        }
    }
    
    private function _apacheOwnershipx(&$tests) {
        $documentRoot = $_SERVER['DOCUMENT_ROOT'];
        $userInfo = posix_getpwuid(posix_getuid());
        $apacheUser = $userInfo['name'];
        $root = dirname($documentRoot);
        $files = array(
                        $root.'/var',
                        $root.'/var/search_index',
                        $root.'/var/log',
                        $root.'/var/log/log.txt',
                        $documentRoot.'/img/noimage'
        );

        foreach($files as $file) {
            if(file_exists($file)) {
                $owner = posix_getpwuid(fileowner($file));
                $group = posix_getpwuid(filegroup($file));
    
                if($owner['name'] == $apacheUser) {
                    $test['outcome'] = true;
                } else {
                    $test['outcome'] = false;
                }
                $test['title'] = 'Apache - ownership';
                $test['desc'] = sprintf("The group/owner of this file or dir '%s' should be 'apache' for linux or '_www' for mac. <br />Try 'sudo chown -R apache:apache %s'",
                                $file, $file);
                $tests[] = $test;
            } else {
                $test['outcome'] = false;
                $test['title'] = 'Directory or file does not exist';
                $test['desc'] = sprintf("Directory or file '%s' should exist and the group/owner should be 'apache' for linux or '_www' for mac.<br />Try 'sudo mkdir -p %s' if it's a directory or if it's a file create an empty file",
                                        $file , $file);
                $tests[] = $test;
            }
        }
    }
    
    private function _phpOfcLib(&$tests) {
        $test = array();
        if(! @include_once('php-ofc-library/open-flash-chart.php')) {
            $test['outcome'] = false;
            $folder = dirname($_SERVER['DOCUMENT_ROOT']) .'/lib/php-ofc-library';
            if(is_dir($folder)) {
                $desc .= sprintf("Folder '%s' seems to exist. May be its not in the include path<br />", $folder);
            } else {
                $desc .= sprintf("Folder '%s' does NOT exist.<br />", $folder);
                $desc .= "Try 'cvs update -d' when in lib folder.";
            }
            $test['desc'] = $desc;
            $test['short_desc'] = 'FAIL - POFC Lib';
        } else {
            $test['outcome'] = true;
            $test['desc'] = "Folder 'compass/lib/php-ofc-library' exists.";
            $test['short_desc'] = 'OK - POFC Lib';
        }
        $test['title'] = 'PHP Library - php-ofc-library';
        $tests[] = $test;        
    }
    
    private function _zendStandardAnalyzer(&$tests) {
        $test = array();
        $test['title'] = 'Zend Search - StandardAnalyzer';
        if(! @class_exists('Zend_Search_Lucene_Analysis_Analyzer')) {
            $test['outcome'] = false;
            $desc = "Class 'Zend_Search_Lucene_Analysis_Analyzer' could not be found.<br />";
            $folder = dirname($_SERVER['DOCUMENT_ROOT']) .'/lib/standardanalyzer-1.0.0b';
            if(is_dir($folder)) {
                $desc .= sprintf("Folder '%s' seems to exist. May be its not in the include path<br />", $folder);
            } else {
                $desc .= sprintf("Folder '%s' does NOT exist.<br />", $folder);
                $desc .= "Try 'cvs update -d' when in lib folder.";
            }
            $test['desc'] = $desc;
            $test['short_desc'] = 'FAIL - Standard Analyzer';
        } else {
            $test['outcome'] = true;
            $test['desc'] = "Found the class 'Zend_Search_Lucene_Analysis_Analyzer'.";
            $test['short_desc'] = 'OK - Standard Analyzer';
        }
        $tests[] = $test;
    }
    
    private function _config(&$tests) {

        $test = array();
        $test['title'] = 'Config - Access';
        try {
            $config = Zend_Registry::get('config');
            $test['outcome'] = true;
            $configOptions = ResourceConstants::createMetadataTable($config->toArray());
            $test['short_desc'] = 'OK - Config';
            $test['desc'] = <<<HTML
            Successfully accessed <a style="color:blue;" href="javascript:void(0);" onclick="$('#config').toggle();">CONFIG</a>
            <div id="config" class="innerTable" style="display:none;">
                <pre>$configOptions</pre>
            </div>
HTML;
    
        } catch (Exception $ex) {
            $test['outcome'] = false;
            $desc = "Exception occurred while trying to access config using '\$config = Zend_Registry::get('config');'.<br />EXCEPTION: ".$ex->getMessage().'<br />';
            $desc .= "Check 'compass/application/config.ini' file exist.";
            $test['desc'] = $desc;
            $test['short_desc'] = 'Fail - Config';
        }
        $tests[] = $test;
    }
    
    private function _phpModules(&$tests) {
        $exts = get_loaded_extensions();
        $extsRequired = array(
                            array(
                    			'extension' => 'ldap',
                    			'desc_pass' => "Extension 'php-ldap' is loaded.",
								'desc_fail' => "Extension 'php-ldap' is NOT loaded.<br />Try 'sudo yum install php-ldap'"),
                            array(
                    			'extension' => 'pgsql',
                    			'desc_pass' => "Extension 'php-pgsql' is loaded.",
								'desc_fail' => "Extension 'php-pgsql' is NOT loaded.<br />Try 'sudo yum install php-pgsql'"),
                            array(
                    			'extension' => 'dom',
                    			'desc_pass' => "Extension 'php-dom' is loaded.",
								'desc_fail' => "Extension 'php-dom' is NOT loaded.<br />Try 'sudo yum install php-dom'"),
                            array(
                    			'extension' => 'soap',
                    			'desc_pass' => "Extension 'php-soap' is loaded.",
								'desc_fail' => "Extension 'php-soap' is NOT loaded.<br />Try 'sudo yum install php-soap'"),
                            array(
                    			'extension' => 'mbstring',
                    			'desc_pass' => "Extension 'php-mbstring' is loaded.",
								'desc_fail' => "Extension 'php-mbstring' is NOT loaded.<br />Try 'sudo yum install php-mbstring'"),
                            array(
                    			'extension' => 'curl',
                    			'desc_pass' => "Extension 'php-curl' is loaded.",
								'desc_fail' => "Extension 'php-curl' is NOT loaded.<br />Try 'sudo yum install php-curl'"),
                            array(
                    			'extension' => 'iconv',
                    			'desc_pass' => "Extension 'php-iconv' is loaded.",
								'desc_fail' => "Extension 'php-iconv' is NOT loaded.<br />Try 'sudo yum install php-iconv'")
                        );
        foreach($extsRequired as $data) {
            $test['title'] = sprintf('PHP Extension - %s', $data['extension']);
            if(in_array($data['extension'], $exts)) {
                $test['outcome'] = true;
                $test['short_desc'] = 'OK - PHP Extension - '.$data['extension'];
                $test['desc'] = $data['desc_pass'];
            } else {
                $test['outcome'] = false;
                $test['short_desc'] = 'FAIL - PHP Extension - '.$data['extension'];
                $test['desc'] = $data['desc_fail'];
            }
            $tests[] = $test;
        }
    }
    
}
