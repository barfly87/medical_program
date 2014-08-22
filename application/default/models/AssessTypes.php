<?php

class AssessTypes extends Compass_Db_Table_LookupTable {
    protected $_name = 'lk_assesstype';
    protected $_dependentTables = array('LinkageLoAssessTypes');
}