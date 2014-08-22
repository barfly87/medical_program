<?php

class Pbls extends Compass_Db_Table_LookupTable {
    protected $_name = 'lk_pbl';
    protected $_dependentTables = array('TeachingActivities', 'BlockPblSeqs');
    
    /** Get problem id from problem name */
    public function getPblId($name) {
    	$select = $this->select()->where("name = ?", $name);
    	$row = $this->fetchRow($select);
    	if (!$row) {
    		throw new Exception("PBL $name does not exist");
    	}
    	return $row['auto_id'];
    }
    
    /** Get Pbl Ref from pblId */
    public function getPblRef($pblId) {
        $pblId = (int)$pblId;
        try {
            if($pblId > 0) {
                $db = Zend_Registry::get('db');
                $query = 'SELECT bs.block_seq_id AS block, bw.weeknum as week FROM block_pbl_seq as bs 
                          JOIN lk_blockweek as bw on bs.week_id = bw.auto_id 
                          WHERE pbl_id = ' . $pblId;
                $row = $db->query($query)->fetch();
                $ref = '';
                if(isset($row['block']) && isset($row['week'])) {
                    $week = ($row['week'] < 10) ? '0'.$row['week'] : $row['week'];
                    $ref = $row['block'].  '.'  .$week;
                }
                return $ref;
            }
        } catch (Exception $ex) {
        
        }
        return '';
    }
    
    /** Get Pbl Name from pblId */
    public function getPblName($pblId) {
        $pblId = (int)$pblId;
        try {
            if($pblId > 0) {
                $select = $this->select()->where("auto_id = ?", $pblId);
                $row = $this->fetchRow($select);
                if (!$row) {
                    throw new Exception("PBL $pblId does not exist");
                }
                return $row['name'];
            }
        } catch (Exception $ex) {
        
        }
    }
    
}