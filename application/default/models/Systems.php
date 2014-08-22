<?php

class Systems extends Compass_Db_Table_LookupTable {
    protected $_name = 'lk_system';
    protected $_dependentTables = array('LearningObjectives');
}