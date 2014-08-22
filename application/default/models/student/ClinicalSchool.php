<?php
class ClinicalSchool extends Zend_Db_Table_Abstract {
    protected $_name = 'clinical_school';
    
    public function fetchPairs() {
       try {
            $return = array();
            $select = $this->select()->from('clinical_school', array('name'))->order(array('auto_id ASC'));
            $result = $this->fetchAll($select);
            
            if($result->count() > 0) {
                foreach($result->toArray() as $row) {
                    $return[$row['name']] = $row['name'];
                }
            }
            return $return;
       } catch(Exception $ex) {
            Zend_Registry::get('logger')->debug("\n".$ex->getMessage()."\n".$ex->getTraceAsString()."\n\n");
            return array();
       }
    }
}
?>