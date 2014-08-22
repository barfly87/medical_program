<?php

class StageCoordinators extends Zend_Db_Table_Abstract {
    protected $_name = 'stagecoordinator';
    protected $_referenceMap = array(
        'Stages' => array(
            'columns' => array('stage_id'),
            'refTableClass' => 'Stages',
            'refColumns' => array('auto_id')
        ),
        'Domains' => array(
        	'columns' => array('domain_id'),
        	'refTableClass' => 'Domains',
        	'refColumn' => array('auto_id')
        )
    );
	
	/** Get the list of stage coordinators */
	public function getAllCoordinators($domainname = NULL, $order = array('stage_id ASC')) {
		$stageFinder = new Stages();
		$stages = $stageFinder->getAllStages();
		
		$domainFinder = new Domains();
		
		$rows = $this->fetchAll(NULL, $order);
		$result = array ();
		if ( $domainname ) {
			foreach($rows as $row) {
				$domain_id = $row['domain_id'];
				$domain = $domainFinder->getDomain($domain_id);
				if ( $domainname==$domain ) {
					$result[] = array ('id'=>$row['auto_id'], 'name'=>$stages[$row['stage_id']], 'uid'=>$row['uid'], 'domain'=>$domain );
				}
			}
		} else {
			foreach($rows as $row) {
				$domain_id = $row['domain_id'];
				$domain = $domainFinder->getDomain($domain_id);
				$result[] = array ('id'=>$row['auto_id'], 'name'=>$stages[$row['stage_id']], 'uid'=>$row['uid'], 'domain'=>$domain );
			}
		}
		return $result;
	}
    
    
    /** Add a new stage coordinator */
    public function addCoordinator($stage_id, $uid, $domain_id) {    	
    	$row = $this->createRow();
    	$row->stage_id = (int)$stage_id;
    	$row->uid = $uid;
    	$row->domain_id = (int)$domain_id;
    	$row->save();
    }
    
    /** Delete a stage coordinator */
    public function deleteCoordinator($id) {
    	$row = $this->find($id)->current();
    	if (!$row) {
    		throw new Exception("Could not find stage coordinator with id: $id");
    	}
    	$row->delete();
    }
}