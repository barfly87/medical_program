<?php

class StudentResourceCategories extends Compass_Db_Table_LookupTable {
    protected $_name = 'lk_studentresourcecategories';
    protected $_dependentTables = array('StudentResourceLink');
}