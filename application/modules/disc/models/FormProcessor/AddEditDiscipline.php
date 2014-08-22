<?php
class FormProcessor_AddEditDiscipline extends FormProcessor {
    public $disc = null;
    private $error = false;
    private $errors = 'errors';

    public function __construct($disc_id = NULL) {
        parent::__construct();
        $table = new Discipline();
        if ((int)$disc_id > 1) {
            $this->disc = $table->fetchRow('auto_id='.((int)$disc_id));
        } else {
            $this->disc = $table->createRow();
        }
    }

    public function process(Zend_Controller_Request_Abstract $request)
    {
        
        //Sanitize name
        $this->name = $this->sanitize($request->getPost('name')); 
        //Check if name is empty
        if(strlen($this->name) == 0) {
            $this->errors .= '.nameEmpty';
            $this->error = true;
        }        
        
        //Sanitize types
        $typesPost = $request->getPost('types');
        foreach($typesPost as &$val) {                   
            $val = $this->sanitize($val);
        } 
        //Check if type is not selected
        if(count($typesPost) <= 0) {
            $this->errors .= '.typeEmpty';
            $this->error = true;    
        }
                
        //Sanitize Discipline
        //Few disciplines have got synonyms which would come through POST as number followed by 's' e.g 33s
        //Which means this discipline is synonym of discipline id 33
        //We need to do type casting which removes 's' and 33 would be stored as their discipline
        //As per the requirement this is what user wants
        $this->discipline_id = (int)$this->sanitize($request->getPost('discipline')); 
        
        //Check if discipline does has a parent or not.
        $this->parent_id = ((int)$this->discipline_id > 1) ? (int)$this->discipline_id : 0;  

        $this->original_name = $this->sanitize($request->getPost('original_name')); 
        $this->original_parent_id = $this->sanitize($request->getPost('original_parent_id'));
         
        //Check if any records already exist under this name and parent_id and throw error if it does
        if($this->original_name != $this->name || $this->original_parent_id != $this->parent_id) {
             $discipline = new Discipline();
             $result = $discipline->checkIfRowExist($this->name, $this->parent_id);
             if($result) {
                $this->errors .= '.nameExist';
                $this->error = true;   
             }
        }

        //Return if any errors
        if($this->error === true) {
            return array('error' => true,'error_msg' => $this->errors);
        }


        
        $this->synonym = $this->sanitize($request->getPost('synonym')); 

        //Set Flags for types
        $type = array();        
        $type['compass']   = (in_array('compass', $typesPost)) ? 1 : 0;
        $type['org']       = (in_array('org', $typesPost)) ? 1 : 0;

        //Create Row
        $this->disc->name       = $this->name;  
        $this->disc->synonym    = $this->synonym;
        $this->disc->compass    = $type['compass'];
        $this->disc->org        = $type['org'];
        $this->disc->parent_id  = $this->parent_id;
        
        return $this->disc->save();
    }

}


?>