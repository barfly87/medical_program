<?php
class Pbl {
    
    private $pblRef = null;
    private $pblBlock = null;
    private $pblBlockWeek = null;
    private $allPbls = null;
    private $pblId = null;
    private $pblName = null;
    private $pblBlockId = null;
    private $pblBlockName = null;
    private $stage1pbl = null;
    private $stage2pbl = null;
    private $prevPbl = null;
    private $prevPblId = null;
    private $nextPbl = null;
    private $nextPblId = null;
    private $error = false;
    
    public function __construct($pblRef) {
        $this->setPblRef($pblRef);
        
        //Creates block and blockweek based on pbl ref
        $this->setBlockAndBlockWeek();
        
        //process Pbl
        $this->processPbl();
        
        //If no error is found then set other properties
        if($this->error == false) {
            $this->setAllPbls();
            $this->resetPblRef();
            $this->setStage1Pbl();
            $this->setStage2Pbl();
            $this->setPrevPbl();
            $this->setNextPbl();
        }
    }
    
    private function setPblRef($ref) {
        $this->pblRef = $ref;
    }

    // Creates block and blockweek based on pbl ref
    private function setBlockAndBlockWeek() {
        if(!empty($this->pblRef)) {   
            if(strstr($this->pblRef,'.') !== false) {
                $explode = explode(".",$this->pblRef);
                if(count($explode) == 2) {
                    $this->pblBlock = (int)$explode[0];
                    $this->pblBlockWeek = (int)$explode[1];
                }
            }
        }
    }

    
    private function processPbl() {
        if(!is_null($this->pblBlock) && !is_null($this->pblBlockWeek) && $this->pblBlock >= 0 && $this->pblBlockWeek > 0) {
            $bps = new BlockPblSeqs();
            $row = $bps->fetchBlockAndBlockWeek($this->pblBlock,$this->pblBlockWeek);
            if($row != null) {
                $this->pblId = $row['pbl_id'];
                $this->pblName = $row['name'];
                $this->pblBlockId = $row['block_id'];
                try {
                    $blocks = new Blocks();
                    $this->pblBlockName = $blocks->getBlockName($row['block_id']);
                } catch (Exception $ex) {
                    $this->pblBlockName = 'Unknown';
                }
            } else {
                $this->error = true;
            }
        } else {
            $this->error = true;
        }
    }
    
    private function resetPblRef() {
        $this->pblRef = ($this->pblBlockWeek < 10) ? 
                            $this->pblBlock. '.0'. $this->pblBlockWeek : 
                            $this->pblBlock. '.'. $this->pblBlockWeek;
    }
    
    private function setStage1Pbl() {
        preg_match('/^[0-9]{1,2}\.[0-9]{1,2}/', UserAcl::stage1Pbl(), $matches);
        if(isset($matches[0])) {
            $this->stage1pbl = $matches[0];
        }
    }
    
    private function setStage2Pbl() {
        preg_match('/^[0-9]{1,2}\.[0-9]{1,2}/', UserAcl::stage2Pbl(), $matches);
        if(isset($matches[0])) {
            $this->stage2pbl = $matches[0];
        }
    }
    
    private function setPrevPbl() {
        $index = array_search($this->pblRef,$this->allPbls);
        if (isset($this->allPbls[$index - 1])) {
            $this->prevPbl = $this->allPbls[$index - 1];
            $prevPblId = $this->pblId - 1;
            if($prevPblId >= 0) {
                $this->prevPblId = $prevPblId;
            }
        }
    }
    
    private function setNextPbl() {
        $index = array_search($this->pblRef, $this->allPbls);
        if (isset($this->allPbls[$index + 1])) {
            $this->nextPbl = $this->allPbls[$index + 1];
            $nextPblId = $this->pblId + 1;
            if($nextPblId >= 0) {
                $this->nextPblId = $nextPblId;
            }
        }
    }
 
    private function setAllPbls() {
        $bps = new BlockPblSeqs();
        $this->allPbls = $bps->getStage1And2PblSeqNo();
    }
    
    public function getPblDetails() {
        return array(
            'pblRef' => $this->pblRef,
            'pblBlock' => $this->pblBlock,
            'pblBlockWeek' => $this->pblBlockWeek,
            'allPbls' => $this->allPbls,
            'pblId' => $this->pblId,
            'pblName' => $this->pblName,
            'pblBlockId' => $this->pblBlockId,
            'pblBlockName' => $this->pblBlockName,
            'stage1pbl' => $this->stage1pbl,
            'stage2pbl' => $this->stage2pbl,
            'prevPbl' => $this->prevPbl,
            'nextPbl' => $this->nextPbl,
            'prevPblId' => $this->prevPblId,
            'nextPblId' => $this->nextPblId
        );    
    }
     
    public function hasError() {
        return $this->error;
    }
}
?>