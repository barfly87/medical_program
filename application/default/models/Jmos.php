<?php

class Jmos extends Compass_Db_Table_LookupTable {
    protected $_name = 'lk_jmo';
    protected $_dependentTables = array('LearningObjectives');
}