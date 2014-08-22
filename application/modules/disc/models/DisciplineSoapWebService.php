<?php  
  
class DisciplineSoapWebService {  
  
    public static $error = array(                                
						       	'typeNotSpecified' => array('error' => 'Type is empty or not correctly supplied.'),
                                'recordsNotFound' => array ('error' => 'Your query did not returned any results.'),
                                'exception' => array('error' => 'Caught an Unknown Exception.'),
                                'usernameEmpty' => array('error' => 'Username is empty.'),
                           		'cannotAuthenticate' => array('error' => 'Authentication failed.')
    );
    
    public static $token = 'abc123';
     
    /** 
     * Get list of disciplines for a type.
     * Type can be 'compass' or 'org' 
     * 
     * @param string $type
     * @param string $token 
     * @return array $result
     */  
    public function getListOfDisciplines($type = null, $token = null) {  
        if($token == null || ($token != self::$token)) {
            return $error['cannotAuthenticate'];
        }
        try {  
             $listOfTypes = array_values(DisciplineService::$types);
             if(in_array($type, $listOfTypes)) {
                $discipline = new Discipline();
                $rows = $discipline->getListOfDisciplines($type);
                $result = (count($rows) > 0 ) ? $rows : self::$error['recordsNotFound'] ;
                return $result;
             }
             return self::$error['typeNotSpecified'] ;
        } catch (Exception $e) {  
            $error = self::$error['exception'];
            $error['exception'] = $e;
            return $error; 
        }  
    }  
    
    /** 
     * Get list of disciplines for a user.
     * Eg. of user can be 'ksoni'
     * 
     * @param string $user
     * @param string $token
     * @return array $result
     */  
    public function getListOfDisciplinesForUser($user = null, $token = null) {  
        if($token == null || ($token != self::$token)) {
            return $error['cannotAuthenticate'];
        }
        try {  
             $listOfTypesForUser = DisciplineService::$typesForMyDetailsPage;
             $user = (string) $user;
             if(strlen(trim($user)) <= 0) {
                 return self::$error['usernameEmpty']; 
             }
             $discipline = new Discipline(); 
             $userDisc = new UserDisc();
             $rows = array();
             $count = 0;
             foreach($listOfTypesForUser as $type){
                 $id = $userDisc->getUserDisciplineIDForType($type, $user);
                 if($id !== false) {
                     $row = $discipline->getRowForId($id);
                     $rows[$count]['type'] = $type; 
                     $rows[$count]['id'] = $id;
                     $rows[$count]['name'] = $row['name'];
                     $rows[$count]['synonym'] = $row['synonym'];
                     $rows[$count]['parent_id'] = $row['parent_id'];
                     $count++;
                 }
             }
             $result = ($count > 0 ) ? $rows : self::$error['recordsNotFound'] ;
             return $result;
        } catch (Exception $e) {  
            $error = self::$error['exception'];
            $error['exception'] = $e;
            return $error;  
        }  
    }    

}  