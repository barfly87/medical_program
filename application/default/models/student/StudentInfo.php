<?php
class StudentInfo extends Zend_Db_Table_Abstract {
    protected $_name = 'studentinfo';

    function getInfo($uid) {
    	    $select = $this->select()->where("uid = ?", $uid);
            $result = $this->fetchAll($select);
            return $result->toArray();
    }
    
    public function getMobileNumber($uid) {
    	$select = $this->select()->where("uid = ?", $uid);
    	$result = $this->fetchRow($select);
    	return trim($result['mobile_phone']);
    }
}
?>