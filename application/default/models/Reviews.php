<?php

class Reviews extends Compass_Db_Table_LookupTable {
    protected $_name = 'lk_review';
    protected $_dependentTables = array('LinkageLoReviews');
}