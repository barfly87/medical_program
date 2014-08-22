<?php
class FormProcessor_MyDetails extends FormProcessor {

    public function process(Zend_Controller_Request_Abstract $request) {   

        $this->uid = Zend_Auth::getInstance()->getIdentity()->user_id;
        $table = new UserDisc();
        $uid_where  = "uid = '".$this->uid."'";

        //Loop through each of the types used in 'My Details' Page
        // e.g ('compass_1','compass_2','compass_3', 'org')
        foreach(DisciplineService::$typesForMyDetailsPage as $type) {

            //Sanitize 'type'
            //Few disciplines have got synonyms which would come up as a 'number' followed by 's' 
            //e.g 33s  which basically means this discipline is synonym of 33
            //We need to do type casting which removes 's' and 33 would be stored as their discipline
            $this->$type = (int)$this->sanitize($request->getPost($type)); 

            //Check if records exist for this 'type' and 'uid'
            $select = $table->select()
                                    ->where("type = '".$type."'")
                                    ->where($uid_where)
                                    ->limit('1'); 

            $this->disc = $table->fetchRow($select);
             
            //If record exist just update the row and if not create a new row
            if($this->disc) {                                   
                $this->disc->disc_id    = $this->$type;
                $this->disc->save();
            } else {                                                        
                $this->disc             = $table->createRow();    
                $this->disc->uid        = $this->uid;  
                $this->disc->type       = $type;
                $this->disc->disc_id    = $this->$type;
                $this->disc->save();
            }
        }
        return true;                           
    }

}
?>