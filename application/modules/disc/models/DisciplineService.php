<?php

class DisciplineService {

    //Links for all Pages
    public static $listLink                     = '/disc/disc/list';
    public static $myDetailsLink                = '/disc/disc/mydetails';
    public static $myDetailsCompleteLink        = '/disc/disc/mydetailscomplete';
    public static $editLink                     = '/disc/disc/edit';
    public static $editCompleteLink             = '/disc/disc/editcomplete';
    public static $addLink                      = '/disc/disc/add';
    public static $addCompleteLink              = '/disc/disc/addcomplete';
    
    //Title text for all Pages
    public static $indexTitle                   = 'Discipline Index Page';
    public static $listTitle                    = 'List of Disciplines';
    public static $mydetailsTitle               = 'My Details Page';
    public static $mydetailsCompleteTitle       = 'My Details Stored Successfully';
    public static $addTitle                     = 'Add Discipline Page';
    public static $addCompleteTitle             = 'Added new discipline';
    public static $editTitle                    = 'Edit Discipline Page';
    public static $editCompleteTitle            = 'Edited discipline';

    //Types for different Pages
    public static $typesForMyDetailsPage        = array('compass_1','compass_2','compass_3', 'org');
    public static $types                        = array(    'Compass'               =>  'compass',
                                                            'Organisation Unit'     =>  'org'      );

    public static $error_msg                     = array(   'nameEmpty'             =>  'Please enter name.',
                                                            'typeEmpty'             =>  'You must select from Type.',
                                                            'nameExist'             =>  'Name already exists under that hierarchy.'  );

    public static $disciplineAdminRoles          = array(   'compass'               =>   'discipline_compass_admins',
                                                            'org'                   =>   'discipline_org_admins');
    public static $adminRoles                    = array('admin', 'blockchair','staff');
    
    //$tree stores tree structure for 'List' Page
    //e.g Surgery / Dermatology / Pharmacology
    private $tree = null;

    /**
     * @param  int $disciplineID
     * @return string $disciplineName
     * Returns discipline name for discipline id provided
     */
    public function getNameOfDiscipline($disciplineId = 1) {
        try{
            if(empty($disciplineId) || (int)$disciplineId == 1) {
                return false;
            }
            $discipline = new Discipline();
            $row= $discipline->getName((int)$disciplineId);
            if(isset($row['name'])) {
                return $row['name'];
            } else {
                return false;
            }
            
        } catch (Exception $e) {
            return false;
        }           
        
    }

    /**
     * @param  void
     * @return array $rows
     * Returns array('name','synonym','compass','org','parent_id','parent_name') for list page
     */
    public function getListOfDisciplinesForListPage() {
        try {
            $discipline = new Discipline();
            $rows= $discipline->listOfDisciplines($page='list');
            $rows = $this->_createList($rows, $page='list');
            array_shift($rows);
            return $rows;
            
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * @param  void
     * @return boolean $admin
     * Returns true if a user is under any of the compass admin groups
     */
    public function isUserCompassAdmin() {
        try {
                $rolesArr = $this->getUserRoles();
                $admin = false;
                if(isset($rolesArr)) {
                    foreach($rolesArr as $role) {
                        if(in_array($role, self::$disciplineAdminRoles)) {
                            $admin = true;
                        }
                    }             
                }   
                return $admin;
        } catch (Exception $e) {
            return false;
        }
    }     

    /**
     * @param  void
     * @return boolean $admin
     * Returns true if a user is in admin, blockchair and staff group
     */
    public function isUserAdmin() {
        try {
            $auth = Zend_Auth::getInstance();  
            if ($auth->hasIdentity()) {  
                $user = $auth->getIdentity();
                if(isset($user->role)) {
                    $admin = false;
                    if(in_array($user->role, self::$adminRoles)) {
                        $admin = true;
                    }
                    return $admin;
                } else {
                    return false;
                }
            }            
        } catch (Exception $e) {
            return false;
        }
    }
        
    /**
     * @param  void
     * @return array $rolesArr
     * Returns all the compass admin roles attached to the user
     * e.g array('discipline_compass_admins', 'discipline_org_admins ...)
     */
    public function getUserRoles(){
        try {
            $auth = Zend_Auth::getInstance();  
            if ($auth->hasIdentity()) {  
                $user = $auth->getIdentity();
                if(isset($user->disciplineRole)) {
                    $roles = $user->disciplineRole;
                    $rolesArr = explode('|',$roles);
                    return $rolesArr;
                } else {
                    return false;
                }
            } 
        } catch (Exception $e) {
            return false;
        }    
    }
    
        
    /**
     * @param  void
     * @return array $options
     * Returns discipline_id => discipline_name
     * eg. $options e.g ("1 => 'Medicine",......)
     */
    public function getListOfDisciplines() {
        try {
            $discipline = new Discipline();
            $rows = $discipline->listOfDisciplines();            
            $rows = $this->_createList($rows);    
            $options =  $this->_createSelectOptions($rows); 
            asort($options);
            return $options;
            
        } catch (Exception $e) {
            return false;
        }
    }    
    
    /**
     * @param  array $row
     * @return boolean $isAllowEdit
     * Returns true if user is allowed to edit the row
     */    
    public function allowEdit($row){
        try {
            if(!is_array($row) || count($row) < 1) {
                return false;
            }
            $userRoles = $this->getUserRoles();  
            $isAllowEdit = false;
            foreach(self::$disciplineAdminRoles as $k => $v) {
                if($row[$k] === 1 && in_array($v, $userRoles)){
                    $isAllowEdit = true;
                }                    
            }
            return $isAllowEdit;                

        } catch (Exception $e) {
            return false;
        }
        
    }
    
    /**
     * @param  int $id
     * @return array $options
     * Returns key=>value for discipline_id => discipline_name for edit page
     * e.g ("1 => 'Medicine",......)
     */
    public function getListOfDisciplinesForEditPage($id) {
        try {
            $discipline = new Discipline();
            $rows = $discipline->listOfDisciplines();
            if(isset($rows[$id])) {
                unset($rows[$id]);
            }
            $rows = $this->_createList($rows);
            $options =  $this->_createSelectOptions($rows); 
            asort($options);
            return $options;
            
        } catch (Exception $e) {
            return false;
        }        
    }

    /**
     * @param  string $errors
     * @return array $errors 
     * Returns error messages for errors found
     */
    public function createErrors($errors) {
        try {            
            $err = array();
            $explode = explode('.',$errors);
            array_shift($explode);
            foreach($explode as $val) {
                $err[$val] = DisciplineService::$error_msg[$val];
            }        
            return $err;
            
        } catch (Exception $e) {
            return false;
        } 
    }

    /**
     * @param  int $id
     * @return array $row
     * Returns row for id
     * e.g ( 'id' => '....', 'name' => '....', ....)
     */
    public function getRowForId($id = null) {
        try {                   
            if((int)$id > 0) {
                $discipline = new Discipline();          
                $row = $discipline->getRowForId($id);
                return $row;
            }
            return false;
            
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * @param  void
     * @return string $user_id
     * Returns fullname for the user_id
     * e.g for user_id 'johnsmith' it would return 'John Smith'
     */
    public function getUserName() { 
        try {   
            $auth = Zend_Auth::getInstance();  
            if ($auth->hasIdentity()) {  
                $user = $auth->getIdentity();
                return $user->firstname[0] .' '. $user->lastname[0];
            } 
            return false;
            
        } catch (Exception $e) {
            return false;
        }            
    }

    /**
     * @param  void
     * @return array $options
     * Returns key=>value for discipline_id => discipline_name for edit page
     * e.g ("1 => 'Medicine",......)
     */
    public function getListOfDisciplinesForMyDetailsPage() {
        try { 
            $options = array();
            $discipline = new Discipline();
            foreach(self::$types as $type) {
                $rows = $discipline->listOfDisciplines($page='mydetails', $type);
                $rows = $this->_createList($rows);
                $options[$type] = $this->_createSelectOptions($rows);  
                asort($options[$type]);    
                      
            } 
            return $options;
            
        } catch (Exception $e) {
            return false;
        }        
    }

    /**
     * @return array $return
     */
    public function getTreeOfDisciplineIds() {
        try {
            $discipline = new Discipline();
            $rows = $discipline->getRows();
            $return = array();
            foreach($rows as $row) {
                $return[$row['parent_id']][] = $row['auto_id'];
            }
            unset($return[0]);
            return $return;
        } catch(Exception $ex) {
            return array();
        }
    }
        
    /**
     * @param string $uid
     * @return array $user_disc
     * Returns discipline selected by user for each category 
     * compass1, compass2, compass3, org on my details page
     */ 
    public function getSelectedDisciplinesForUser($uid) {
        try {        
            $user_disc = array();
            $userDisc = new UserDisc();
            foreach(self::$typesForMyDetailsPage as $type) {
                $user_disc[$type] = $userDisc->getUserDisciplineIDForType($type,$uid);   
            }
            return $user_disc;
            
        } catch (Exception $e) {
            return false;
        }            
        
    }

    /**
     * @param array $rows
     * @param string $page
     * @return array $options 
     * e.g changes $row['name'] = 'Dermatology' to $row['name'] = 'Medicine / Derma / Dermatology'
     */  
    private function _createList($rows=null, $page=''){
        
        if($rows == null || !is_array($rows) || count($rows) <= 0) {
            return false;
        }
        
        if($page == 'list') {
            $rows = $this->_createListForListPage($rows);
        } else {
            $rows = $this->_createDefaultList($rows);
        }
        
        return $rows;
            
    }

    /**
     * @param array $rows
     * @return array $rows 
     * Creates $row['parent_name'] = 'Medicine / Derma ' using $row['parent_id'] 
     * Creates $row['allowEdit'] = true/false to give appropriate access for admin people
     * for each row depending whether the user belongs to compass or org
     */  
    private function _createListForListPage($rows){
        
        foreach($rows as &$row) {
            
            $this->tree = '';
            
            //this function changes the value of $this->tree till their are no
            //more parents left and creates tree structure
            $this->_getParentName($row['parent_id']);     
        
            $row['parent_name'] = trim($this->tree,' / ');
            $userRoles = $this->getUserRoles();  
            $isAllowEdit = false;
            foreach(self::$disciplineAdminRoles as $k => $v) {
                if($row[$k] === 1 && in_array($v, $userRoles)){
                    $isAllowEdit = true;
                }                    
            }
            $row['allowEdit'] = $isAllowEdit;
                
        }  
        
        return $rows;
    }
    
    /**
     * @param array $rows
     * @return array $rows 
     * Changes $row['name'] = 'Derma' to $row['name'] = 'Medicine / Derma ' using $row['parent_id'] 
     * Creates $row['name'] = 'Medicine / Derma2' for rows which has got synonym $row['synonym'] = 'Derma2' 
     */  
    private function _createDefaultList($rows = null){
 
        if($rows == null || !is_array($rows) || count($rows) <= 0) {
            return false;
        }
        $synonym = array(); // store synonyms
        foreach($rows as &$row){         
            $this->tree = $row['name'];
                
            //this function changes the value of $this->tree till their are no
            //more parents left and creates tree structure
            $this->_getParentName($row['parent_id']);     
               
            $row['name'] = $this->tree;
            if(strlen($row['synonym']) > 0) {
                $synonym[] = $row;   
            }
        } 

        if(count($synonym) > 0) {
            $synonym = $this->_createSynonyms($synonym);
            $rows = array_merge($rows, $synonym);
        }        
        return $rows;
    }

    /**
     * @param array $rows
     * @return array $synonymRows 
     * Creates $synonymRows['name'] = 'Medicine / Derma2' for $row['synonym'] = 'Derma2' 
     */  
    
    private function _createSynonyms($rows){
        $synonymRows = array();
        foreach($rows as $row) {            
            //append 's' to ids eg. 57s otherwise overrides 57 with new ones
            $row['auto_id'] = $row['auto_id'] . 's'; 
            if(strpos($row['name'],' / ')){ //check if heirarchy exists
                $explode = explode(' / ',$row['name']);
                $last = count($explode) - 1;
                $explode[$last] = $row['synonym']; //change last child with 'synonym' value
                $row['name'] = implode(' / ',  $explode); //create new hierarchy
            } else {
                $row['name'] = $row['synonym'];
            }
            
            array_push($synonymRows, $row);
        }

        return $synonymRows;        
    }
    
    /**
     * @param  array $rows
     * @return array $options
     * Creates select options by using $row
     * e.g ("1 => 'Medicine",......)
     */
    private function _createSelectOptions($rows = null) {
        $selectOptions = array('1' => '');
        if($rows == null || !is_array($rows) || count($rows) < 1) {
            return $selectOptions;
        }
        foreach($rows as $row) {
            $selectOptions[$row['auto_id']] = $row['name'];
        }  
        return $selectOptions;
    }

    /**
     * @param  int $parent_id
     * @return void
     * Creates heirarchy for each discipline by updating the 
     * $this->tree variable for each parent found
     */
    private function _getParentName($parent_id = 0){
        try{
            $discipline = new Discipline();
            $row = $discipline->getParent($parent_id);
            if( $parent_id != 0 && $row !== false ) {
                $this->tree = $row['name'] . ' / '.$this->tree;            
                $this->_getParentName($row['parent_id']);
            }
            
        } catch (Exception $e) {
            return false;
        }            
        
    }

}
