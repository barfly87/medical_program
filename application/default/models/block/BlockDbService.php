<?php
class BlockDbService {
    
    private $db = null;
    
    public function __construct() {
        $this->db = Zend_Registry::get("db");
    }
    
    public function getListOfBlocks($stage = '3') {

        try {
            $select = $this->db->select()
                        ->from(array('sbs' => 'stage_block_seq'),array('block_id' => 'block_id'))
                        ->join(array('b' => 'lk_block'),'sbs.block_id = b.auto_id',array('b.name'))
                        ->join(array('s' => 'lk_stage'),'sbs.stage_id = s.auto_id',array())
                        ->where("s.stage in (?)", $stage)
                        ->order('sbs.seq_no');
            return $this->db->fetchPairs($select);
        } catch(Exception $ex) {
            Zend_Registry::get('logger')->debug("\n".$ex->getMessage()."\n".$ex->getTraceAsString()."\n\n");
            return array();           
        }
        
    }
    
    public function getBlockName($blockId) {
        try {
            $blockId = (int)$blockId;
            if($blockId > 0) {
            	$config = Zend_Registry::get('config');
				$taform_hidden_fields = $config->taform->hiddenfields->toArray();
				if (!in_array('block_week', $taform_hidden_fields)) {
					$stage = array('3');
				} else {
					$stage = array('II', 'III');
				}
                $blocks = $this->getListOfBlocks($stage);
                $blockKeys = array_keys($blocks);
                if(in_array((int)$blockId,$blockKeys)) {
                    return $blocks[$blockId];
                }
            }
            return false;
        } catch(Exception $ex) {
            Zend_Registry::get('logger')->debug("\n".$ex->getMessage()."\n".$ex->getTraceAsString()."\n\n");
            return false;           
        }
        
    }
}
?>
