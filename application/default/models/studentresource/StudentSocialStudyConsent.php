<?php
/**
 * Tells whether a student has consented to Daniel Burn's PhD study
 * @author daniel
 *
 */
class StudentSocialStudyConsent extends Compass_Db_Table_LookupTable {
    protected $_name = 'studyconsent';
    //protected $_dependentTables = array('StudentResourceLink');
    protected $_sequence = true;
}