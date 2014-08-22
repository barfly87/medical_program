<?php

class Domains extends Compass_Db_Table_LookupTable {
    protected $_name = 'lk_domain';
    protected $_dependentTables = array('TeachingActivities', 'LearningObjectives', 'LinkageTaDomains', 'LinkageLoDomains');
    
    /**
     * Get domain id based on domain name
     * @param $name
     * @return domain id
     */
    public function getDomainId($name) {
    	$row = $this->fetchRow($this->select()->where('name = ?', $name));
    	if (!$row) {
    		throw new Exception("Domain name '$name' does not exist");
    	}
    	return $row->auto_id;
    }
    
    /**
     * Get all domain names in the domain lookup table
     * @param $order
     * @return array with domain name as key and value
     */
    public function getDomainNames($order) {
    	$select = $this->select();
    	if ($order != NULL) {
    		$select->order($order);
    	}
    	$results = array();
    	$rows = $this->fetchAll($select);
    	foreach ($rows as $row) {
    		$results[$row->name] = $row->name;
    	}
    	return $results;
    }
    
   /** Get the name of the domain by auto_id*/
    public function getDomain($id) {
    	$select = $this->select()->where('auto_id = ?', $id);
    	$row = $this->fetchrow($select);
    	
 		return $row['name'];  
    	
    }
}