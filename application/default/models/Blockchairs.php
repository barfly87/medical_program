<?php

class Blockchairs extends Zend_Db_Table_Abstract {
    protected $_name = 'blockchair';
    protected $_referenceMap = array(
        'Blocks' => array(
            'columns' => array('block_id'),
            'refTableClass' => 'Blocks',
            'refColumns' => array('auto_id')
        ),
        'Domains' => array(
        	'columns' => array('domain_id'),
        	'refTableClass' => 'Domains',
        	'refColumn' => array('auto_id')
        )
    );
	
	/** Get all blockchair names 
	 * Stage 3 and 3 (year 4) blocks are the same, only one of them will be displayed
	 */
	public function getAllChairs($domainname = NULL) {
		$db = Zend_Registry::get("db");
		$select = $db->select()->from(array ('bc'=>'blockchair' ), array ('auto_id', 'uid', 'domain_id' ))->join(array ('b'=>'lk_block' ), 'b.auto_id = bc.block_id', array ('name' ))->join(array ('sbs'=>'stage_block_seq' ), 'sbs.block_id = bc.block_id', array ('seq_no' ))->join(array ('s'=>'lk_stage' ), 'sbs.stage_id = s.auto_id', array ())->where("s.stage != ?", '3 (Year 4)')->order('seq_no');
		$rows = $db->fetchAll($select);
		
		$domainFinder = new Domains();
		
		$results = array ();
		
		if ( $domainname ) {
			foreach($rows as $row) {
				$domain_id = $row['domain_id'];
				$domain = $domainFinder->getDomain($domain_id);
				if ( $domainname==$domain ) {
					$results[] = array ('id'=>$row['auto_id'], 'uid'=>$row['uid'], 'name'=>$row['name'], 'block'=>$row['seq_no'], 'domain'=>$domain );
				}
			}		
		} else {
			foreach($rows as $row) {
				$domain_id = $row['domain_id'];
				$domain = $domainFinder->getDomain($domain_id);
				$results[] = array ('id'=>$row['auto_id'], 'uid'=>$row['uid'], 'name'=>$row['name'], 'block'=>$row['seq_no'], 'domain'=>$domain );
			}
		}
		return $results;
	}
    
    
    /** Delete a block chair based on id */
    public function deleteBlockchair($id) {
    	$row = $this->find($id)->current();
    	if (!$row) {
    		throw new Exception("Could not find block chair with id: $id");
    	}
    	$row->delete();
    }
    
    /** Add a block chair */
    public function addChair($block_id, $uid, $domain_id) {
    	$row = $this->createRow();
    	$row->block_id = (int)$block_id;
    	$row->uid = $uid;
    	$row->domain_id = (int)$domain_id;
    	$row->save();
    }
    
    public function getUidsOfAllBlocks() {
        $domainId = UserAcl::getDomainId();
        $rows = $this->fetchAll();
        $return = array();
        foreach($rows as $row) {
            if($row->domain_id == $domainId) {
                $return[$row->block_id][] = $row->uid;
            }    
        }
        return $return;        
    }
    
}