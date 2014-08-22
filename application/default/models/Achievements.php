<?php

class Achievements extends Compass_Db_Table_LookupTable {
    protected $_name = 'lk_achievement';
    protected $_dependentTables = array('LearningObjectives');
}