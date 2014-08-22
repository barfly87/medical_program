<?php

class Staff extends Zend_Db_Table_Abstract {
    protected $_name = 'staff';
    protected $_referenceMap = array(
        'Stafftype' => array(
            'columns' => array('stafftype'),
            'refTableClass' => 'Stafftype',
            'refColumns' => array('auto_id')
        ),
        'Staffpage' => array(
            'columns' => array('staffpage'),
            'refTableClass' => 'Staffpage',
            'refColumns' => array('auto_id')
        ),  
        'Domains' => array(
        	'columns' => array('domain_id'),
        	'refTableClass' => 'Domains',
        	'refColumn' => array('auto_id')
        )
    );
	
	/** Get all staff names 
	 * Stage 3 and 3 (year 4) blocks are the same, only one of them will be displayed
	 */
	public function getAllStaff($domainname = NULL) {
		$db = Zend_Registry::get("db");
		$select = $db->select()->from(array ('st'=>'staff' ), array ('auto_id', 'uid', 'domain_id', 'description','seq_no' ))->joinLeft(array ('s'=>'lk_stafftype' ), 's.auto_id = st.stafftype', array ('stafftype'=>'name' ))->join(array ('sp'=>'lk_staffpage' ), 'sp.auto_id = st.staffpage', array ('staffpage'=>'name' ))->order(array('staffpage','stafftype','seq_no'));
		$rows = $db->fetchAll($select);
		
		$domainFinder = new Domains();
		
		$results = array ();
		
		if ( $domainname ) {
			foreach($rows as $row) {
				$domain_id = $row['domain_id'];
				$domain = $domainFinder->getDomain($domain_id);
				if ( $domainname==$domain ) {
					$results[] = array ('id'=>$row['auto_id'], 'uid'=>$row['uid'], 'name'=>$row['name'], 'stafftype'=>$row['stafftype'], 'staffpage'=>$row['staffpage'],'description'=>$row['description'],'seq_no'=>$row['seq_no'],'domain'=>$domain );
				}
			}		
		} else {
			foreach($rows as $row) {
				$domain_id = $row['domain_id'];
				$domain = $domainFinder->getDomain($domain_id);
				$results[] = array ('id'=>$row['auto_id'], 'uid'=>$row['uid'], 'name'=>$row['name'], 'stafftype'=>$row['stafftype'], 'staffpage'=>$row['staffpage'], 'description'=>$row['description'],'seq_no'=>$row['seq_no'],'domain'=>$domain );
			}
		}
		return $results;
	}
    
    public function getAllStaffForPage($page,$domainname = NULL) {
		$db = Zend_Registry::get("db");
		$select = $db->select()
			->from(array ('st'=>'staff' ), array ('auto_id', 'uid', 'domain_id', 'description','seq_no' ))
			->joinLeft(array ('s'=>'lk_stafftype' ), 's.auto_id = st.stafftype', array ('stafftype'=>'name' ))
			->join(array ('sp'=>'lk_staffpage' ), 'sp.auto_id = st.staffpage', array ('staffpage'=>'name' ))
			->where("sp.name = ?", $page)
			->order(array('staffpage','stafftype','seq_no'));
		$rows = $db->fetchAll($select);
		
		$domainFinder = new Domains();
		
		$results = array ();
		
		if ( $domainname ) {
			foreach($rows as $row) {
				$domain_id = $row['domain_id'];
				$domain = $domainFinder->getDomain($domain_id);
				if ( $domainname==$domain ) {
					$results[] = array ('id'=>$row['auto_id'], 'uid'=>$row['uid'], 'stafftype'=>$row['stafftype'], 'staffpage'=>$row['staffpage'],'description'=>$row['description'],'seq_no'=>$row['seq_no'],'domain'=>$domain );
				}
			}		
		} else {
			foreach($rows as $row) {
				$domain_id = $row['domain_id'];
				$domain = $domainFinder->getDomain($domain_id);
				$results[] = array ('id'=>$row['auto_id'], 'uid'=>$row['uid'], 'stafftype'=>$row['stafftype'], 'staffpage'=>$row['staffpage'], 'description'=>$row['description'],'seq_no'=>$row['seq_no'],'domain'=>$domain );
			}
		}
		return $results;
	}
	
	public function getStaffMember ($staffid) {
		$db = Zend_Registry::get("db");
		$select = $db->select()
			->from(array ('st'=>'staff' ), array ('auto_id', 'uid', 'domain_id', 'description','seq_no','stafftype','staffpage' ))
			->where("st.auto_id = ?", $staffid);
		$rows = $db->fetchAll($select);
		foreach($rows as $row) { //should only be one, but eh....
			$result = array ('id'=>$row['auto_id'], 'uid'=>$row['uid'], 'stafftype'=>$row['stafftype'], 'staffpage'=>$row['staffpage'], 'description'=>$row['description'],'seq_no'=>$row['seq_no'],'domain_id'=>$row['domain_id']);
		}
		return($result);
	}
    /** Delete a block chair based on id */
    public function deleteStaff($id) {
    	$row = $this->find($id)->current();
    	if (!$row) {
    		throw new Exception("Could not find staff with id: $id");
    	}
    	$row->delete();
    }
    
    /** 
     * update the order field for a staff member
     */
    public function setStaffOrder($staffnum, $order) {
    	$data = array("seq_no" => $order);
    	$where = $this->getAdapter()->quoteInto('auto_id = ?', $staffnum);
    	$this->update($data, $where);
    	echo("<br>$staffnum => $order");
    }
    /** Add a staff member */
    public function addStaff($staffpage, $stafftype, $uid, $description, $domain_id) {
    	$row = $this->createRow();
    	if(((int)$stafftype)>=0 && $stafftype !="-1")
    		$row->stafftype = (int)$stafftype;
    	else
    		$row->stafftype =null;
    	$row->staffpage = (int)$staffpage;
    	$row->description = $description;
    	$row->uid = $uid;
    	$row->domain_id = (int)$domain_id;
    	$row->save();
    }    
    /** Update a staff member */
    public function updateStaff($staffid, $staffpage, $stafftype, $uid, $description, $domain_id, $seq_no) {
    	$data = array(
    		'staffpage' => $staffpage,
    		'description' => $description,
    		'uid' => $uid,
    		'domain_id' => $domain_id
    	);
    	if(isset($seq_no) && $seq_no > 0)
    		$data['seq_no'] = (int)$seq_no;
    	if(((int)$stafftype)>=0 && $stafftype !="-1")
    		$data['stafftype'] = (int)$stafftype;
    	$where = $this->getAdapter()->quoteInto('auto_id = ?', $staffid);
    	$this->update($data, $where);
    }    
}