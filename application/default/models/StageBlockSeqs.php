<?php

class StageBlockSeqs extends Zend_Db_Table_Abstract {
    protected $_name = 'stage_block_seq';
	protected $_referenceMap = array(
		'Stage' => array(
			'columns' => array('stage_id'),
			'refTableClass' => 'Stages',
			'refColumns' => array('auto_id')
		),
		'Block' => array(
			'columns' => array('block_id'),
			'refTableClass' => 'Blocks',
			'refColumns' => array('auto_id')
		),
		'Year' => array(
			'columns' => array('year_id'),
			'refTableClass' => 'Years',
			'refColumns' => array('auto_id')
		)
	);
    
	/** Get a list of block names for a stage id, used on teaching activity page as a drop down list
	 *  Will prepend empty block name to the list
	 */
	public function getBlocksForStage($stage_id = NULL) {
		$stageFinder = new Stages();
		$allStageNames = $stageFinder->getAllStages();
		
		$select = $this->select();
		$select->order("seq_no ASC");
		
		//Empty stage value, select all blocks
		if ($stage_id != NULL && !empty($allStageNames[$stage_id])) {
			$select->where("stage_id = ?", $stage_id);
		}
		$rows = $this->fetchAll($select);
		
		$blockFinder = new Blocks();
		$allBlockNames = $blockFinder->getAllNames();
		
		$result[$blockFinder->getBlockId('')] = '';
		//Do not put sequence no for stage 3 and 4 blocks
		foreach ($rows as $row) {
			if (strpos($allStageNames[$row['stage_id']], '3') !== FALSE) {
				$result[$row['block_id']] = $allBlockNames[$row['block_id']];
			} else {
				$result[$row['block_id']] = $row['seq_no'].'. '.$allBlockNames[$row['block_id']];
			}
		}
		return $result;
	}
	
	public function getBlocksForStageWithoutSeqNo($stage_id = NULL) {
		$stageFinder = new Stages();
		$allStageNames = $stageFinder->getAllStages();
		
		$select = $this->select();
		$select->order("seq_no ASC");
		
		//Empty stage value, select all blocks
		if ($stage_id != NULL && !empty($allStageNames[$stage_id])) {
			$select->where("stage_id = ?", $stage_id);
		}
		$rows = $this->fetchAll($select);
		
		$blockFinder = new Blocks();
		$allBlockNames = $blockFinder->getAllNames();
		foreach ($rows as $row) {
			$result[$row['block_id']] = array($allBlockNames[$row['block_id']], $row['seq_no'], $row['year_id']);
		}
		return $result;
	}
	
	public function getYearsForStage($stage_id = NULL) {
		$stageFinder = new Stages();
		$allStageNames = $stageFinder->getAllStages();
		
		$select = $this->select();
		$select->order("seq_no ASC");
		
		//Empty stage value, select all years
		if ($stage_id != NULL && !empty($allStageNames[$stage_id])) {
			$select->where("stage_id = ?", $stage_id);
		}
		$rows = $this->fetchAll($select);
		
		$yearFinder = new Years();
		$allYears = $yearFinder->getAllYears();
		
		$result[$yearFinder->getYearId(NULL)] = '';
		foreach ($rows as $row) {
			$result[$row['year_id']] = $allYears[$row['year_id']];
		}
		return $result;
	}
	
	public function getBlocksForYear($year_id) {
		$yearFinder = new Years();
		$allYears = $yearFinder->getAllYears();
		
		$select = $this->select();
		$select->order("seq_no ASC");
		
		if ($year_id != NULL && !empty($allYears[$year_id])) {
			$select->where("year_id = ?", $year_id);
		}
		
		$rows = $this->fetchAll($select);
		
		$blockFinder = new Blocks();
		$allBlockNames = $blockFinder->getAllNames();
		
		$result[$blockFinder->getBlockId('')] = '';
		foreach ($rows as $row) {
			$result[$row['block_id']] = $row['seq_no'].'. '.$allBlockNames[$row['block_id']];
		}
		return $result;
	}
	
	/**
	 * Get an array of block ids for a stage
	 * @param $stage_id
	 */
	public function getBlockIdsForStage($stage_id) {
		$select = $this->select()->where('stage_id = ?', $stage_id)->order(seq_no);
		$rows = $this->fetchAll($select);
		$results = array();
		
		foreach ($rows as $row) {
			$results[] = $row['block_id'];
		}
		return $results;
	}
	
	/** Get all block names
	 * This function is different from getAllNames from Blocks class, as it will add block sequence number in front of the block name.
	 * @throws Exception
	 */
	public function getAllBlocks() {
		return $this->getBlocksForStage();
	}
	
    public function getBlockId($block_no) {
    	$select = $this->select()->where('seq_no = ?', $block_no);
    	$row = $this->fetchRow($select);
    	if (!$row) {
    		throw new Exception("Block $block_no does not exist.");
    	}
    	return $row['block_id'];
    }
    
    public function getBlockNo($block_id) {
    	$select = $this->select()->where('block_id = ?', $block_id);
    	$row = $this->fetchRow($select);
    	if (!$row) {
    		throw new Exception("Block id $block_id does not exist.");
    	}
    	return $row['seq_no'];
    }
    
    /** Get block name based on block number */
    public function getBlockName($block_no) {
    	$block_id = $this->getBlockId($block_no);
    	$blockFinder = new Blocks();
    	return $blockFinder->getBlockName($block_id);
    }
    
    /** Get the current pbl for students if Compass is not connected to Events */
    public function getCurrentPbl($cohort) {
    	$config = Zend_Registry::get('config');
    	$taform_hidden_fields = $config->taform->hiddenfields->toArray();
    	if (!in_array('block_week', $taform_hidden_fields)) {
	    	$stage = date('Y') - $cohort + 1;
	    	$stage = ($stage > 2) ? 2 : $stage;
	    	$stageFinder = new Stages();
	    	$stageId = $stageFinder->getStageId("$stage");
	    	$select = $this->select()->where("stage_id = ?", $stageId)->order('seq_no');
	    	$row = $this->fetchRow($select);
	    	return $row['seq_no']. '.01';
    	} else {
    		return "1.01";
    	}
    }
    
    /** Get the last block number for a particular stage */
    public function getLastBlockNoForStage($stage_no) {
    	$stageFinder = new Stages();
    	$stage_id = $stageFinder->getStageId($stage_no);
    	$select = $this->select()->where("stage_id = ?", $stage_id)->order("seq_no DESC");
    	$row = $this->fetchRow($select);
    	
    	return $row['seq_no'];
    }
    
    public function getModulesForPhase($phaseName) {
    	$stageFinder = new Stages();
    	$phaseId = $stageFinder->getStageId($phaseName);
    	
    	$modules = $this->getBlocksForStageWithoutSeqNo($phaseId);
    	$modules = array_reverse($modules, true);
    	
    	$result = array();
    	$pblService = new PblService();
    	foreach ($modules as $moduleId => $moduleValue) {
    		$result[$moduleValue[0]]['resources'] = $pblService->getResourcesForBlock($moduleId);
    		$result[$moduleValue[0]]['blockId'] = $moduleId;
    		$result[$moduleValue[0]]['seq_no'] = $moduleValue[1];
    		$result[$moduleValue[0]]['year_id'] = $moduleValue[2];
    	}
    	return $result;
    }
    
}