<?php
class SearchConfigure extends Zend_Db_Table_Abstract {
    protected $_name = 'search_configure';
    protected $_primary = 'auto_id';
    
    public function getColumns($user_id = null, $search_type = null){
        if($user_id == null || $search_type == null){
            return false;
        }
        $result = $this->getRow($user_id, $search_type); 
        if($result != false && isset($result[0]['column_ids'])) {
            return $result[0]['column_ids'];
        }
        return $result; 
    }
    
    public function getRow($user_id, $search_type){
        $select = $this->select()
                        ->from($this)
                        ->where("user_id = '".$user_id."'")
                        ->where("search_type = '".$search_type."'")
                        ->limit('1');
        $result = parent::fetchAll($select);
        if($result != false) {
            return $result->toArray();
        }
        return false;
    }
    
    
    public function saveColumns($user_id, $column_ids,$search_type){
        $recordExist = $this->getRow($user_id, $search_type);
        if($recordExist == false) {
            return $this->insertRow($user_id, $column_ids,$search_type);
        } else {
            return $this->updateRow($user_id, $column_ids,$search_type);
        }
    }
    
    public function insertRow($user_id, $column_ids,$search_type){
        $data = array(
                    'user_id'      => $user_id,
                    'column_ids' => $column_ids,
                    'search_type'      => $search_type
        );
        return $this->insert($data);
    }
    
    public function updateRow($user_id, $column_ids,$search_type){
        $data = array(
                    'column_ids' => $column_ids,
                );
        $where[] = "user_id = '".$user_id."'";
        $where[] = "search_type = '".$search_type."'";  
        return $this->update($data, $where);
    }
    
}
?>