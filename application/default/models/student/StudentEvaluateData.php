<?php 

class StudentEvaluateData extends Zend_Db_Table_Abstract {
    
    protected $_name = 'student_evaluate_data';
    
    public function insertData($student_evaluate_id = '', $key = '', $val = '') {
        try {
            if((int)$student_evaluate_id > 0 && !empty($key) && !empty($val)) {
                $data = array(
                            'student_evaluate_id' => $student_evaluate_id,
                            'key' => $key,
                            'val' => $val
                        );                
                $result = $this->insert($data);   
                if($result !== false) {
                    return $result;
                }     
                return false;                
            }
        } catch (Exception $ex) {
            Zend_Registry::get('logger')->warn($ex->getMessage());
            return false;
        }
    }
   
    public function fetchKeyVal($student_evaluate_id, $key = '') {
        try {
            if((int)$student_evaluate_id <= 0) {
                return false;
            }
            $select = $this->select()->from($this->_name, array('key','val'))->order(array('auto_id ASC'))->where('student_evaluate_id ='.(int)$student_evaluate_id);
            if(!empty($key)) {
            	$select->where("key = '$key'");
            }
            return $this->getAdapter()->fetchPairs($select);
            
        } catch (Exception $ex) {
            Zend_Registry::get('logger')->warn($ex->getMessage());
            return false;
        }
        
    }
    
    public function getUniqueKeys() {
        try {
            $return = array();
            $select = $this->select()->distinct()->from($this->_name,'key');
            $rows = $this->fetchAll($select);
            if($rows->count() > 0) {
                $rows = $rows->toArray();
                foreach ($rows as $row) {
                    $return[strtoupper($row['key'])] = $row['key'];
                }
            }
            return array_reverse($return, true);
        } catch (Exception $ex) {
        	$error = $ex->getMessage().PHP_EOL.$ex->getTraceAsString().PHP_EOL;
        	Zend_Registry::get('logger')->warn(PHP_EOL."CLASS\t: ".__CLASS__.PHP_EOL."METHOD\t: ".__METHOD__.PHP_EOL."Error\t: ".$error.PHP_EOL);
            return array();
        }
        
    }
    
}
?>