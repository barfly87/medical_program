<?php

class LinkageLoDomains extends Zend_Db_Table_Abstract {
    protected $_name = 'link_lo_domain';
    protected $_primary = 'auto_id';
    protected $_referenceMap = array(
        'LearningObjective' => array(
            'columns' => array('lo_id'),
            'refTableClass' => 'LearningObjectives',
            'refColumns' => array('auto_id')
        ),
        'Domain' => array(
            'columns' => array('domain_id'),
            'refTableClass' => 'Domains',
            'refColumns' => array('auto_id')
        )
    );
    
    /**
     * Add domain with an id $domain_id as an audience to learning objective $lo_id
     * @param $lo_id
     * @param $domain_id
     */
    public function addAudience($lo_id, $domain_id) {
    	$row = $this->createRow();
    	$row->lo_id = $lo_id;
    	$row->domain_id = $domain_id;
    	$row->save();
    }
    
    /**
     * Remove domain with an id $domain_id as an audience from learning objective $lo_id
     * @param $lo_id
     * @param $domain_id
     */
    public function removeAudience($lo_id, $domain_id) {
    	$select = $this->select()->where("lo_id = ?", $lo_id)->where("domain_id = ?", $domain_id);
    	$row = $this->fetchRow($select);
    	if (!$row) {
    		throw new Exception("Audience id '$domain_id' is not valid for learning objective '$lo_id'.");
    	}
    	$row->delete();
    }
}