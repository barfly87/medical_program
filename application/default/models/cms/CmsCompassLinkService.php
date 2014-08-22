<?php
class CmsCompassLinkService {
    
    public function __construct() {
    }
    
    public function storeCmsResources($rows) {
        if(is_array($rows) && ! empty($rows)) {
            $resource = new MediabankResource();
            foreach($rows as $row) {
                if($row['count'] == 1) {
                    try {
                        $data['type'] = 'ta';
                        $data['type_id'] = $row['TA_id'];
                        $data['resource_type'] = ResourceConstants::$TYPE_MEDIABANK;
                        $data['resource_id'] = $row['MEDIABANK_mid'];
                        $data['order_by'] = $resource->getOrderBy('ta',$row['TA_id']);
                        $result = $resource->insert($data);
                    } catch (Exception $ex) {
                        Zend_Registry::get('logger')->warn($ex->getMessage());
                        echo 'Database Error:<br /><pre>';
                        print_r($row);
                        echo '</pre>'; 
                    }
                }
            }
            echo 'Successfully added'; exit;
        } else {
            echo 'Rows empty'; exit;
            return false;
        }
    }
    
    public function storeCmsPblResources($rows) {
        $insertRows = array();
        if(is_array($rows) && ! empty($rows)) {
            $resource = new MediabankResource();
            foreach($rows as $row) {
                if($row['count'] > 0) {
                    try {
                        if(! empty($row['MEDIABANK_mid']) ) {
                            foreach($row['MEDIABANK_mid'] as $mid) {
                                $data = array();
                                $data['type'] = 'ta';
                                $data['type_id'] = $row['TA_id'];
                                $data['resource_type'] = ResourceConstants::$TYPE_MEDIABANK;
                                $data['resource_id'] = $mid;
                                $data['order_by'] = $resource->getOrderBy('ta',$row['TA_id']);
                                $insertRows[] = $data;
                                $result = $resource->insert($data);
                            }
                        }
                    } catch (Exception $ex) {
                        Zend_Registry::get('logger')->warn($ex->getMessage());
                        echo 'Database Error:<br /><pre>';
                        print_r($row);
                        echo '</pre>'; 
                    }
                }
            }
            echo 'Successfully added'; 
            print '<pre>';
            print_r($insertRows);
            print '</pre>';
            exit;
        } else {
            echo 'Rows empty'; exit;
            return false;
        }
    }
    
    public function updateTACurrentTeacher() {
    	$db = Zend_Registry::get("db");
    	$select = $db->select()
    				->from(array('ta' => 'teachingactivity'), array('auto_id AS ta_id', 'principal_teacher AS pt'))
    				->join(array('act' => 'lk_activitytype'), 'ta.type = act.auto_id', array('name AS ta_type'))
    				->join(array('re' => 'lk_resource'),'ta.auto_id = re.type_id', array('resource_id as resource_id'))
    				->where("re.type='ta'");
    	$results = $db->fetchAll($select);
    	
    	$taFinder = new TeachingActivities();
    	$ldapconn = @ldap_connect("ldap.med.usyd.edu.au");
		$dn = "uid=vone,ou=People,o=CHS,o=chs";
		$ldapbind = ldap_bind($ldapconn, $dn, 'narcot');

		
    	echo '<pre>';
    	echo "-- Type\tCMS Doc ID\tTA id\tCompass Principal teacher\tCMS UID\tNames\tClean Names\tIDs\n";
    	foreach ($results as $row) {
    		if(preg_match("/^http:\/\/(.+)\/\|cmsdocs-(\d{4})\|(\d+)/", $row['resource_id'], $matches) > 0) {
    			$xmlurl = "/srv/www/gmp/htdocs/cds{$matches[2]}/xml/{$matches[3]}.xml";
    			$xml = simplexml_load_file($xmlurl);
    			if ($row['ta_type'] == 'Learning Topic') {
    				echo '-- ', $row['ta_type'], "\t", $matches[3], "\t", $row['ta_id'], "\t", $row['pt'], "\t";
    				$uids = array();
    				$all_names = array();
    				$uniq_names = array();
    				$uniq_ids = array();
    				foreach ($xml->author as $author) {					
    					if (!empty($author->uid->uid)) {
    						$uids[] = $author->uid->uid;
    					} else {
    						$names = explode(' ', $author->name);
    						$lname = array_pop($names);
    						if (substr($lname, 0, 1) == '(') {
    							$lname = array_pop($names);
    						}
    						$fname = array_pop($names);
    						$all_names[] =  $author->name;
    						$name = $fname. ' '. $lname;
    						if (!in_array($name, $uniq_names)) {
	    						$uniq_names[] = $name;
	    						$sr = ldap_search($ldapconn, 'ou=People,o=CHS,o=chs', "cn=$name", array('uid'));
								$info = ldap_get_entries($ldapconn, $sr);
								if (isset($info[0]['uid'][0])) {
									$uniq_ids[] = $info[0]['uid'][0];
								}
							}
    					}
    				}
	    			echo join(', ', $uids), "\t", join(', ', $all_names), "\t", join(', ', $uniq_names), "\t", join(', ', $uniq_ids), "\n";
	    			if (count($uids) != 0) {
	    				echo "UPDATE teachingactivity SET principal_teacher='", join(', ', $uids), "' WHERE auto_id={$row['ta_id']};\n\n";
	    			} else {
	    				if (count($uniq_names) != count($uniq_ids)) {
	    					echo "-- CHECK\n";
	    				}
	    				echo "UPDATE teachingactivity SET principal_teacher='", join(', ', $uniq_ids), "' WHERE auto_id={$row['ta_id']};\n\n";
	    			}
    			} else if ($row['ta_type'] == 'Lecture') {
    				echo '-- ', $row['ta_type'], "\t", $matches[3], "\t", $row['ta_id'], "\t", $row['pt'], "\t";
    				$uids = array();
    				$all_names = array();
    				$uniq_names = array();
    				$uniq_ids = array();
    				//try master lecturer first
    				foreach ($xml->masterlecturer as $masterlecturer) {
    					if (!empty($masterlecturer->uid->uid)) {
    						$uids[] = $masterlecturer->uid->uid;
    					} else {
    						$names = explode(' ', $masterlecturer->name);
    						$all_names[] =  $masterlecturer->name;
    						
    						$lname = array_pop($names);
    						$fname = array_pop($names);    						
    						$name = $fname. ' '. $lname;
    						if (!in_array($name, $uniq_names)) {
	    						$uniq_names[] = $name;
	    						$sr = ldap_search($ldapconn, 'ou=People,o=CHS,o=chs', "cn=$name", array('uid'));
								$info = ldap_get_entries($ldapconn, $sr);
								if (isset($info[0]['uid'][0]) && !in_array($info[0]['uid'][0], $uniq_ids)) {
									$uniq_ids[] = $info[0]['uid'][0];
								}
							}
    					}
    				}
    				//couldn't find master lecturer, try event details
    				if (count($uids) == 0 && count($all_names) == 0) {
    					$tmp_uid = null;
    					$tmp_name = null;
    					$tmp_time = 0;
						foreach ($xml->eventdetails as $event) {
							if ($event->lecturedatetime->UnixTime > $tmp_time) {
								$tmp_uid = (string)$event->uid->uid;
								$tmp_name = (string)$event->convenername;
								$tmp_time = (string)$event->lecturedatetime->UnixTime;
							}
						}
						if (!empty($tmp_uid)) {
							$uids[] = $tmp_uid;
						} else if (!empty($tmp_name)) {
							$names = explode(' ', $tmp_name);
							$all_names[] =  $tmp_name;
    						$lname = array_pop($names);
    						$fname = array_pop($names);
    						$name = $fname. ' '. $lname;
    						$uniq_names[] = $name;
    						$sr = ldap_search($ldapconn, 'ou=People,o=CHS,o=chs', "cn=$name", array('uid'));
    						$info = ldap_get_entries($ldapconn, $sr);
    						if (isset($info[0]['uid'][0])) {
    							$uniq_ids[] = $info[0]['uid'][0];
    						}		
						}
    				}
	    			echo join(', ', $uids), "\t", join(', ', $all_names), "\t", join(', ', $uniq_names), "\t", join(', ', $uniq_ids), "\n";
	    			if (empty($row['pt'])) {
	    				if (count($uids) != 0) {
		    				echo "UPDATE teachingactivity SET principal_teacher='", join(', ', $uids), "' WHERE auto_id={$row['ta_id']};\n\n";
		    			} else {
		    				if (count($uniq_names) != count($uniq_ids)) {
		    					echo "-- CHECK\n";
		    				}
		    				echo "UPDATE teachingactivity SET principal_teacher='", join(', ', $uniq_ids), "' WHERE auto_id={$row['ta_id']};\n\n";
		    			}
	    			} else {
	    				echo "-- SKIPPED\n";
	    				echo "-- UPDATE teachingactivity SET principal_teacher='", $row['pt'], "' WHERE auto_id={$row['ta_id']};\n\n";
	    			}
    			} else if ($row['ta_type'] == 'Theme session') {
    				echo '-- ', $row['ta_type'], "\t", $matches[3], "\t", $row['ta_id'], "\t", $row['pt'], "\t";
    				$uids = array();
    				$all_names = array();
    				$uniq_names = array();
    				$uniq_ids = array();
    				
    		    	$tmp_uid = null;
    				$tmp_name = null;
    				$tmp_time = 0;
					foreach ($xml->eventdetails as $event) {
						if (empty($event->lecturedatetime->UnixTime)) {
							$tmp_uid = (string)$event->uid->uid;
							$tmp_name = (string)$event->convenername;
						} else if ($event->lecturedatetime->UnixTime > $tmp_time) {
							$tmp_uid = (string)$event->uid->uid;
							$tmp_name = (string)$event->convenername;
							$tmp_time = (string)$event->lecturedatetime->UnixTime;
						}
					}
					if (!empty($tmp_uid)) {
						$uids[] = $tmp_uid;
					} else if (!empty($tmp_name)) {
						$names = explode(' ', $tmp_name);
						$all_names[] =  $tmp_name;
    					$lname = array_pop($names);
    					$fname = array_pop($names);
    					$name = $fname. ' '. $lname;
    					$uniq_names[] = $name;
    					$sr = ldap_search($ldapconn, 'ou=People,o=CHS,o=chs', "cn=$name", array('uid'));
    					$info = ldap_get_entries($ldapconn, $sr);
    					if (isset($info[0]['uid'][0])) {
    						$uniq_ids[] = $info[0]['uid'][0];
    					}		
					}
    				echo join(', ', $uids), "\t", join(', ', $all_names), "\t", join(', ', $uniq_names), "\t", join(', ', $uniq_ids), "\n";
	    			if (empty($row['pt'])) {
	    				if (count($uids) != 0) {
		    				echo "UPDATE teachingactivity SET principal_teacher='", join(', ', $uids), "' WHERE auto_id={$row['ta_id']};\n\n";
		    			} else {
		    				if (count($uniq_names) != count($uniq_ids)) {
		    					echo "-- CHECK\n";
		    				}
		    				echo "UPDATE teachingactivity SET principal_teacher='", join(', ', $uniq_ids), "' WHERE auto_id={$row['ta_id']};\n\n";
		    			}
	    			} else {
	    				echo "-- SKIPPED\n";
	    				echo "-- UPDATE teachingactivity SET principal_teacher='", $row['pt'], "' WHERE auto_id={$row['ta_id']};\n\n";
	    			}
    			}

    		}
    	}
    	echo "</pre>";
    	return $results;
    }
}
?>