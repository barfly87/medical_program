<?php

class UserDisc extends Zend_Db_Table_Abstract {
    protected $_name = 'user_disc';
    protected $_primary = 'auto_id';

    public function getUserDisciplineIDForType($type='', $uid='') {
        $select = $this->select()
                                ->from($this, array('disc_id'))
                                ->where("uid = '".$uid."'")
                                ->where("type = '".$type."'")
                                ->limit('1'); 
        $row = parent::fetchRow($select);
        if($row != false) {
            $row = $row->toArray(); 
            if(isset($row['disc_id'])) {
                return $row['disc_id'];
            }
        }
        return false;
    }

}