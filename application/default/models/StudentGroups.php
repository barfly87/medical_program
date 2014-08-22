<?php

class StudentGroups extends Compass_Db_Table_LookupTable {
    protected $_name = 'lk_studentgroup';
    protected $_dependentTables = array('TeachingActivities');
}