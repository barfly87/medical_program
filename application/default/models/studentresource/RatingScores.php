<?php
/**
 * Contains a an aggregated score for the ratings stored in the Ratings table, linking uid to resource_id
 * @author daniel
 *
 */
class RatingScores extends Compass_Db_Table_LookupTable {
    protected $_name = 'ratingscores';
    //protected $_dependentTables = array('StudentResourceLink');
    protected $_sequence = true;
}