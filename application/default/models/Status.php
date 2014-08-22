<?php

class Status extends Compass_Db_Table_LookupTable {
    protected $_name = 'lk_status';
    protected $_dependentTables = array('TeachingActivities', 'LearningObjectives');
    
    public static $IN_DEVELOPMENT = "In development";
	public static $AWAITING_APPROVAL = "Awaiting approval";
	public static $RELEASED = "Released";
	public static $REJECTED = "Rejected";
	public static $ARCHIVED = "Archived";
	public static $UNKNOWN = "";
	public static $NEW_VERSION = "New version";
	public static $OLD_VERSION = "Old version";
	
	public function getIdForStatus($status) {
		$select = $this->select();
		$select->where('name=?', $status);
		$row = $this->fetchRow($select);
		if (!$row) {
			throw new Exception("Could not find status: $status");
		}
		return $row['auto_id'];
	}
}