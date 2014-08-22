<?php
class BlockLearningObjective extends BlockAbstract{

    public function __construct() {
        parent::__construct();
    }
    /**
     * This function returns all the learning objectives for the current block. If no result is found it
     * tries to get learning objectives for the block 'OTHER' which are core blocks and flags $this->coreResults
     * to true so that this can be notified to the user.
     */
    public function getLearningObjectives() {
        $queryStr = $this->getReleaseDateQuery();
        $results = $this->indexer->find($queryStr);
        $return = null;
	/*
        if(empty($results)){
            $queryStr = $this->getOtherBlockQuery();
            $results = $this->indexer->find($queryStr);
            if(!empty($results)) {
                $this->coreResults = true;
            }
        }
	*/
        return  $this->processLearningObjectives($results);
    } 
    
    /**
     * Returns the below array from rows given
     * @param $rows
     * @return array
     */
    private function processLearningObjectives($rows) {
        $result = array();
        if($rows != null) {
            foreach($rows as $row) {
                $result[$row->lo_auto_id]['lo_auto_id'] = $row->lo_auto_id;
                $result[$row->lo_auto_id]['lo_title'] = $row->lo_title;
                $result[$row->lo_auto_id]['lo_discipline_names'] = $row->lo_discipline_names;
            }
        }
        return count($result) > 0 ? $result : array('error' => false);
    }

    public function getPageDetails() {
        $return  = array(
                    'learningObjectives' => $this->getLearningObjectives()
        );
        if($this->coreResults === true) {
            $return['coreResults'] = true;
        }
        return $return;
    }
    
}
?>
