<?php

class Skills extends Compass_Db_Table_LookupTable {
    protected $_name = 'lk_skill';
    protected $_dependentTables = array('LearningObjectives');
    
    /** Get skill id based on skill name */
    public function getSkillId($name) {
    	$select = $this->select()->where("name = ?", $name);
    	$row = $this->fetchRow($select);
    	if (!$row) {
    		throw new Exception("Skill $name does not exist.");
    	}
    	return $row['auto_id'];
    }
}