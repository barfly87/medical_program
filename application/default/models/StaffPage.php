<?php

class StaffPage extends Compass_Db_Table_LookupTable {
    protected $_name = 'lk_staffpage';
    protected $_dependentTables = array('Staff');
}