<?php

class ActivityTypes extends Compass_Db_Table_LookupTable {
    protected $_name = 'lk_activitytype';
    protected $_dependentTables = array('TeachingActivities');
    public static $compass_events_mapping = array(
    	2 => 1,
    	3 => 2,
    	4 => 3,
    	25 => 6,
    	12 => 7,
    	30 => 8,
    	31 => 9
    );
    
    public static $compass_events_mapping_required_arr = array(1,2,3,8,9);
    
    public function getActivityId($activity_str) {
    	$row = $this->fetchRow($this->select()->where('name = ?', $activity_str));
    	if (!$row) {
    		throw new Exception("Teaching acitivity $activity_str does not exist.");
    	}
    	return $row->auto_id;
    }
    
    public function getCompassActivityTypeFromEventsActivityType($event_type_id) {
    	return (int)array_search($event_type_id, self::$compass_events_mapping);
    }
    
    public function getActivityName($id) {
    	return $this->find($id)->current()->name;
    }
}