<?php
/**
 * Contains a list of rating catgeories for each user and student resources
 * @author daniel
 *
 */
class Ratings extends Compass_Db_Table_LookupTable {
    protected $_name = 'ratings';
    //protected $_dependentTables = array('StudentResourceLink');
    protected $_sequence = true;
}