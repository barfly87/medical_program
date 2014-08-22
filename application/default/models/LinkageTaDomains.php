<?php

class LinkageTaDomains extends Zend_Db_Table_Abstract {
    protected $_name = 'link_ta_domain';
    protected $_primary = 'auto_id';
    protected $_referenceMap = array(
        'TeachingActivity' => array(
            'columns' => array('ta_id'),
            'refTableClass' => 'TeachingActivities',
            'refColumns' => array('auto_id')
        ),
        'Domain' => array(
            'columns' => array('domain_id'),
            'refTableClass' => 'Domains',
            'refColumns' => array('auto_id')
        )
    );
    
    /**
     * Add domain with an id $domain_id as an audience to teaching activity $ta_id
     * @param $ta_id
     * @param $domain_id
     */
    public function addAudience($ta_id, $domain_id) {
    	$row = $this->createRow();
    	$row->ta_id = $ta_id;
    	$row->domain_id = $domain_id;
    	$row->save();
    }
    
    /**
     * Remove domain with an id $domain_id as an audience from teaching activity $ta_id
     * @param $ta_id
     * @param $domain_id
     */
    public function removeAudience($ta_id, $domain_id) {
    	$select = $this->select()->where("ta_id=?", $ta_id)->where("domain_id=?", $domain_id);
    	$row = $this->fetchRow($select);
    	if (!$row) {
    		throw new Exception("Audience id '$domain_id' is not valid for teaching activity '$ta_id'.");
    	}
    	$row->delete();
    }
}