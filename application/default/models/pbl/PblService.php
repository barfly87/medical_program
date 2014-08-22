<?php
class PblService {
    
    public function getReleasedPBL() {
		$db = Zend_Registry::get("db");
		$select = $db->select()
			->from(array('bps' => 'block_pbl_seq'))
			->join(array('sbs' => 'stage_block_seq'), 'bps.block_seq_id = sbs.auto_id', array('block_num' => 'seq_no', 'block_id' => 'block_id'))
			->join(array('lk_week' => 'lk_blockweek'), 'lk_week.auto_id = bps.week_id', array('week_num' => 'weeknum'))
			->join(array('lk_pbl' => 'lk_pbl'), 'bps.pbl_id = lk_pbl.auto_id', array('pbl_name' => 'name', 'c_name' => 'description'))
			->where('seq_no <= 10')
			->order('seq_no')->order('weeknum');

		$blockFinder = new Blocks();
		$blockNames = $blockFinder->getAllNames();
		
		$pbl_nums = array();
		$pbl_ids = array();
		$pbl_names = array();
		$pbl_cnames = array();
		$blk_ids = array();
		$result = array();
		$rows =  $db->fetchAll($select);
		foreach ($rows as $row) {
			if ($row['week_num'] < 10) {
				$pbl_nums[] = $row['block_num']. '.0'. $row['week_num'];
			} else {
				$pbl_nums[] = $row['block_num']. '.'. $row['week_num'];
			}
			$pbl_ids[] = $row['pbl_id'];
			$pbl_names[] = $row['pbl_name'];
			$pbl_cnames[] = $row['c_name'];
			$blk_ids[] = $row['block_id'];
		}
		
    	$identity = Zend_Auth::getInstance()->getIdentity();
    	if ('student' == $identity->role) {
    		if (date('Y') - $identity->cohort + 1 >= 3) { //year 3 or 4 student
    			$index = count($rows) - 1;
    		} else {
    			if (!isset(Zend_Registry::get('config')->event_wsdl_uri)) {
    				preg_match('/^([0-9]{1,2})\.([0-9]{1,2})/', $this->getMaxPBL($identity->cohort), $matches);
    				$index = array_search($matches[0], $pbl_nums);
    			} else {
	    			$next_block = (int)$identity->releasedpbl + 1;
	    			$next_pbl = "{$next_block}.01";
		    		preg_match('/^([0-9]{1,2})\.([0-9]{1,2})/', $next_pbl, $matches);
		    		$index = array_search($matches[0], $pbl_nums);
		    		if ($index !== FALSE) {
		    			--$index;
		    		} else {
		    			$index = count($rows) - 1;
		    		}
    			}
    		}
    	} else {
    		$index = count($rows) - 1;
    	}

    	$blockchairs = new Blockchairs();
    	//This would get the domain from the session and only return blockchairs for that domain
        $blockchairsArr = $blockchairs->getUidsOfAllBlocks();
        
        $pblCoordinatorService = new PblCoordinatorService();
        
        for($i = $index; $i >= 0; $i--) {
            $blockId = $blk_ids[$i];
            $blockName = $blockNames[$blockId];
            $pblId = $pbl_ids[$i];
            
    		$result[$blockName]['pbls'][] = array($pbl_nums[$i], $pbl_names[$i], $pbl_cnames[$i], 'pblId' => $pblId, 'pblUids' => $pblCoordinatorService->getUidsForPblId($pblId));
    		//Add block id info for this block
    		if(!isset($result[$blockName]['blockId'])) {
                $result[$blockName]['blockId'] = $blockId;
    		}
    		//We need to add uids for each block separated 
            if(!isset($result[$blockName]['blockUids'])) {
            	if (isset($blockchairsArr[$blockId])) {
	                foreach($blockchairsArr[$blockId] as $uid) {
	                    $result[$blockName]['blockUids'][] = $uid;
	                }
            	}
            }
            //Add resources which belong to this block
    		if(!isset($result[$blockName]['resources'])) {
                $result[$blockName]['resources'] = $this->getResourcesForBlock($blockId);     	    
    		}
    	}
    	return $result;
    }
    
    public function getResourcesForBlock($blockId) {
        $return = array();
        try {
            $mediabankResourceService = new MediabankResourceService();
            $return = $mediabankResourceService->getResources($blockId, ResourceConstants::$TYPE_block);
        } catch (Exception $ex) {
        	Zend_Registry::get('logger')->debug("\n".$ex->getMessage()."\n".$ex->getTraceAsString()."\n\n");
            return array();
        }
        return $return;
    }
    
    /** Get the max pbl number the user is allowed to see */
    private function getMaxPBL($cohort) {
        $stage = date('Y') - $cohort + 1;
    	if ($stage > 2) $stage = 2;
    	$stageFinder = new Stages();
    	$stageId = $stageFinder->getStageId("$stage");
    	
        $db = Zend_Registry::get("db");
        $select = $db->select()
                    ->from(array('p' => 'lk_pbl'), array('pbl_id' => 'auto_id', 'pbl_name' => 'name'))
                    ->join(array('bps' => 'block_pbl_seq'), 'bps.pbl_id = p.auto_id')
                    ->join(array('sbs' => 'stage_block_seq'), 'bps.block_seq_id = sbs.auto_id', array('seq_no' => 'seq_no'))
                    ->join(array('w' => 'lk_blockweek'), 'bps.week_id = w.auto_id', array('week_no' => 'weeknum'))
                    ->where("sbs.stage_id = ?", $stageId)
                    ->order('seq_no DESC')
                    ->order('week_no DESC');
		
        $row = $db->fetchRow($select);
        if ($row['week_no'] < 10) {
            return "{$row['seq_no']}.0{$row['week_no']}";
        } else {
            return "{$row['seq_no']}.{$row['week_no']}";
        }
    }
}
?>
