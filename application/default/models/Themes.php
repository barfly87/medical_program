<?php

class Themes extends Compass_Db_Table_LookupTable {
    protected $_name = 'lk_theme';
    protected $_dependentTables = array('LearningObjectives');
}