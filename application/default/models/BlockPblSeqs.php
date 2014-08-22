<?php

class BlockPblSeqs extends Zend_Db_Table_Abstract {
    protected $_name = 'block_pbl_seq';
    protected $_referenceMap = array(
        'Blockseq' => array(
            'columns' => array('block_seq_id'),
            'refTableClass' => 'StageBlockSeqs',
            'refColumns' => array('auto_id')
        ),
        'Blockweeks' => array(
            'columns' => array('week_id'),
            'refTableClass' => 'BlockWeeks',
            'refColumns' => array('auto_id')
        ),
        'Pbls' => array(
            'columns' => array('pbl_id'),
            'refTableClass' => 'Pbls',
            'refColumns' => array('auto_id')
        )
    );
    
    /** Get all available pbls 
     * Format pbl_id => sequence_no pbl_name
     */
    public function getAllPbls() {
    	return $this->getPblList();
    }
    
    /**
     * Get list of PBLs based on stage id
     */
    public function getPblsForStage($stage_id) {
    	$stageFinder = new Stages();
    	$stage = $stageFinder->getStageName($stage_id);
    	if ($stage == '') {
    		return $this->getPblList();
    	} else {
    		return $this->getPblList($stage_id);
    	}
    }
    
    /** Get the list of PBLs beased on stage id and block id */
    public function getPblsForStageBlock($stage_id, $block_id) {
    	$stageFinder = new Stages();
    	$stage = $stageFinder->getStageName($stage_id);
    	
    	$blockFinder = new Blocks();
    	$block = $blockFinder->getBlockName($block_id);

    	if ($stage != '') {
    		//for stage 3 and 4, block doesn't matter
    		if ($stage != '1' && $stage != '2') {
    			return $this->getPblList($stage_id);
    		}
    		if ($block != '') {
    			return $this->getPblList($stage_id, $block_id);
    		} else {
    			return $this->getPblList($stage_id);
    		}
    	} else {
    		if ($block != '') {
    			return $this->getPblList(NULL, $block_id);
    		} else {
    			return $this->getPblList();
    		}
    	}
    }
    
    /** Get the name of the PBL based on stage, block, and block week id */
    public function getPblForStageBlockWeek($stage_id, $block_id, $week_id) {      
        $db = Zend_Registry::get("db");
        $select = $db->select()
                    ->from(array('p' => 'lk_pbl'), array('pbl_id' => 'auto_id', 'pbl_name' => 'name'))
                    ->join(array('bps' => 'block_pbl_seq'), 'bps.pbl_id = p.auto_id')
                    ->join(array('sbs' => 'stage_block_seq'), 'bps.block_seq_id = sbs.auto_id', array('seq_no' => 'seq_no'))
                    ->join(array('w' => 'lk_blockweek'), 'bps.week_id = w.auto_id', array('week_no' => 'weeknum'))
                    ->where("sbs.stage_id = $stage_id AND sbs.block_id = $block_id AND w.auto_id = $week_id");
        $rows = $db->fetchAll($select);
        if (count($rows) > 0) {
            if ($rows[0]['week_no'] < 10) {
                return "{$rows[0]['seq_no']}.0{$rows[0]['week_no']} {$rows[0]['pbl_name']}";
            } else {
                return "{$rows[0]['seq_no']}.{$rows[0]['week_no']} {$rows[0]['pbl_name']}";
            }
        } else {
            return '';
        }
    }
    
    /** Get the name of the PBL based on block, and block week value (not id)*/
    public function getPblNameForBlockWeek($block, $week) {      
        $db = Zend_Registry::get("db");
        $select = $db->select()
                    ->from(array('p' => 'lk_pbl'), array('pbl_id' => 'auto_id', 'pbl_name' => 'name'))
                    ->join(array('bps' => 'block_pbl_seq'), 'bps.pbl_id = p.auto_id')
                    ->join(array('sbs' => 'stage_block_seq'), 'bps.block_seq_id = sbs.auto_id', array('seq_no' => 'seq_no'))
                    ->join(array('w' => 'lk_blockweek'), 'bps.week_id = w.auto_id', array('week_no' => 'weeknum'))
                    ->where("sbs.seq_no = $block");
        if (!empty($week)) {
        	$select = $select->where("w.weeknum = $week");
        } else {
        	$select = $select->where("w.weeknum IS NULL");
        }
        $rows = $db->fetchAll($select);
        if (count($rows) > 0) {
            return $rows[0]['pbl_name'];
        } else {
            return '';
        }
    }
    
    /**
     * Get the list of Pbls available with sequence number displayed in front of it.
     * If stage and block information is empty, then the whole list will be displayed
     */
    protected function getPblList($stage_id = NULL, $block_id = NULL) {
    	$pblFinder = new Pbls();
    	$result[$pblFinder->getPblId('')] = '';
    	
    	$db = Zend_Registry::get("db");
        $select = $db->select()
                    ->from(array('p' => 'lk_pbl'), array('pbl_id' => 'auto_id', 'pbl_name' => 'name'))
                    ->join(array('bps' => 'block_pbl_seq'), 'bps.pbl_id = p.auto_id', array())
                    ->join(array('sbs' => 'stage_block_seq'), 'bps.block_seq_id = sbs.auto_id', array('seq_no' => 'seq_no'))
                    ->join(array('s' => 'lk_stage'), 'sbs.stage_id = s.auto_id', array('stage' => 'stage'))
                    ->join(array('w' => 'lk_blockweek'), 'w.auto_id = bps.week_id', array('week_no' => 'weeknum'))
                    ->order("seq_no")
                    ->order("week_no");
                    
        if ($stage_id != NULL) {
        	$select->where("sbs.stage_id = ?", $stage_id);
        }
        if ($block_id != nULL) {
        	$select->where("sbs.block_id = ?", $block_id);
        }
        $rows = $db->fetchAll($select);
		foreach ($rows as $row) {
			if ($row['stage'] == 1 || $row['stage'] == 2) {
				$result[$row['pbl_id']] = sprintf("%d.%02d %s", $row['seq_no'], $row['week_no'], $row['pbl_name']);
			} else {
				$result[$row['pbl_id']] = sprintf("CRS.%02d %s", $row['week_no'], $row['pbl_name']);
			}
		}
		return $result;
    }
    
    /** Get the list weeks for a particular stage and block combination */
    public function getWeeksForStageBlock($stage_id, $block_id) {
    	$blockweekFinder = new BlockWeeks();
    	
    	$stageFinder = new Stages();
    	$stage = $stageFinder->getStageName($stage_id);
    	
    	$blockFinder = new Blocks();
    	$block = $blockFinder->getBlockName($block_id);
    	
    	//stage 3 and 4, stage 1 & 2 but no block info will get the full list
    	if (!($stage == '1' || $stage = '2') || $block == '') {
    		return $blockweekFinder->getAllWeeks();
    	}

        $result[$blockweekFinder->getEmptyWeekId()] = '';
        
        $db = Zend_Registry::get("db");
        $select = $db->select()
                    ->from(array('w' => 'lk_blockweek'), array('week_id' => 'auto_id', 'week_value' => 'weeknum'))
                    ->join(array('bps' => 'block_pbl_seq'), 'bps.week_id = w.auto_id')
                    ->join(array('sbs' => 'stage_block_seq'), 'bps.block_seq_id = sbs.auto_id')
                    ->where("sbs.stage_id = $stage_id AND sbs.block_id = $block_id");
        $rows = $db->fetchAll($select);
        if (count($rows) > 0) {   
            foreach ($rows as $row) {
                $result[$row['week_id']] = $row['week_value'];
            }
            return $result;
        } else {
            return $blockweekFinder->getAllWeeks();
        }
    }
    
    /** Get the week number based on stage id, block id, and problem id*/
    public function getWeekForStageBlockPbl($stage_id, $block_id, $pbl_id) {        
        $db = Zend_Registry::get("db");
        $select = $db->select()
                    ->from(array('w' => 'lk_blockweek'), array('week_id' => 'auto_id', 'week_value' => 'weeknum'))
                    ->join(array('bps' => 'block_pbl_seq'), 'bps.week_id = w.auto_id')
                    ->join(array('sbs' => 'stage_block_seq'), 'bps.block_seq_id = sbs.auto_id')
                    ->join(array('p' => 'lk_pbl'), 'bps.pbl_id = p.auto_id')
                    ->where("sbs.stage_id = $stage_id AND sbs.block_id = $block_id AND p.auto_id = $pbl_id");
        $rows = $db->fetchAll($select);
        if (count($rows) > 0)
            return $rows[0]['week_value'];
        else {
        	return '';
        }
    }
    
    /** Get block id, pbl id, pbl name based on block no and week no */
    public function fetchBlockAndBlockWeek($block, $block_week) {
    	$db = Zend_Registry::get("db");
		$select = $db->select()
					->from(array('bps' => 'block_pbl_seq'))
					->join(array('sbs' => 'stage_block_seq'),'bps.block_seq_id = sbs.auto_id')
					->join(array('pbl' => 'lk_pbl'), 'bps.pbl_id = pbl.auto_id')
					->join(array('lk_week' => 'lk_blockweek'), 'lk_week.auto_id = bps.week_id')
					->where("seq_no = ?", $block)
					->where('lk_week.weeknum = ?', $block_week);
		return $db->fetchRow($select);
    }
    
    /** Get all pbl seq no. for stage 1 and 2 only */
    public function getStage1And2PblSeqNo() {
    	$db = Zend_Registry::get("db");
        $select = $db->select()
                    ->from(array('w' => 'lk_blockweek'), array('week_id' => 'auto_id', 'week_no' => 'weeknum'))
                    ->join(array('bps' => 'block_pbl_seq'), 'bps.week_id = w.auto_id', array())
                    ->join(array('sbs' => 'stage_block_seq'), 'bps.block_seq_id = sbs.auto_id', array('seq_no' => 'seq_no'))
                    ->join(array('s' => 'lk_stage'), 'sbs.stage_id = s.auto_id', array())
                    ->where("stage = '1' OR stage = '2'")
                    ->order('seq_no')
                    ->order('week_no');
        $rows = $db->fetchAll($select);
        
        $result = array();
        foreach ($rows as $row) {
        	$result[] = sprintf("%d.%02d", $row['seq_no'], $row['week_no']);
        }
        return $result;
    }
}