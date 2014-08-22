<?php
class LdapService {
    
    private $_ds = null;
    
    public function __construct() {
        $this->_ds = Zend_Registry::get('ds');
    }
    
    public function getDS() {
        return $this->_ds;
    }
    
    public function getGroupsByCohort($cohort, $csv = false) {
        $return = array();
        if((int)$cohort > 0) {
            $allGroups = $this->_ds->getGroups(null);
            $match = '/cohort'.$cohort.'group([0-9]{2})/i';
            foreach($allGroups as $group) {
                preg_match($match, $group, $matches);
                if(count($matches) > 0) {
                    $cohortGroup = $this->_ds->getGroup($matches[0]);
                    $cohortGroupMembers = $cohortGroup['members'];
                    foreach($cohortGroupMembers as $cohortGroupMember) {
                        $ldap = LdapCache::getUserDetails($cohortGroupMember);
                        $student = array();
                        $student['Cohort']         = (int)$cohort;
                        $student['PBL Group']      = $group;
                        $student['UID']            = $ldap['uid'][0];
                        $student['Surname']        = $ldap['sn'][0];
                        $student['Given Name']     = $ldap['givenname'][0];
                        $student['Email']          = implode(', ', $ldap['mail']);
                        $student['All Groups']     = implode(', ', $ldap['groups']);
                        
                        $return[] = $student;
                    }
                }
            }
            if($csv === true) {
                $csvService = new CmsCsvService();
                $filename = 'Pbl Groups for Cohort '. $cohort; 
                $csvService->arrayToCsvDump($return, $filename);
            }
            
        } 
        return $return;
    }
    
}

