<?php

class DomainAdmins extends Zend_Db_Table_Abstract {
    protected $_name = 'domainadmin';
    protected $_referenceMap = array(
        'Domains' => array(
            'columns' => array('domain_id'),
            'refTableClass' => 'Domains',
            'refColumns' => array('auto_id')
        ),
    );
	
	/** Get the list of domain administrators */
	public function getAllDomainAdmins($domainname = NULL, $order = array('domain_id ASC')) {
		$domainFinder = new Domains();
		$domains = $domainFinder->getAllNames();
		
		$rows = $this->fetchAll(NULL, $order);
		$result = array ();
		if ( $domainname ) {
			foreach($rows as $row) {
				if ( $domainname==$domains[$row['domain_id']] ) {
					$result[] = array ('id'=>$row['auto_id'], 'name'=>$domains[$row['domain_id']], 'uid'=>$row['uid'] );
				}
			}
			return $result;
		
		} else {
			foreach($rows as $row) {
				$result[] = array ('id'=>$row['auto_id'], 'name'=>$domains[$row['domain_id']], 'uid'=>$row['uid'] );
			}
			return $result;
		}
	}
    
    /** Get domain info for user $uid */
    public function getDomainForUser($uid) {
    	$domainFinder = new Domains();
    	$domains = $domainFinder->getAllNames();
    	
    	$row = $this->fetchRow($this->select()->where("uid = ?", $uid));
    	return !$row ? NULL : $domains[$row['domain_id']];
    }
    
    /** Add a new domain administrator */
    public function addDomainAdmin($domain_id, $uid) {
    	$row = $this->createRow();
    	$row->domain_id = (int)$domain_id;
    	$row->uid = $uid;
    	$row->save();
    }
    
    /** Delete a domain administrator */
    public function deleteDomainAdmin($id) {
    	$row = $this->find($id)->current();
    	if (!$row) {
    		throw new Exception("Could not find domain administrator with id '$id'.");
    	}
    	$row->delete();
    }
}