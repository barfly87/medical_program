<?php

class Stafftype extends Compass_Db_Table_LookupTable {
    protected $_name = 'lk_stafftype';
    protected $_dependentTables = array('Staff');
}