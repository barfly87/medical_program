<?php

class GradAttribs extends Compass_Db_Table_LookupTable {
    protected $_name = 'lk_gradattrib';
    protected $_dependentTables = array('LearningObjectives');
}