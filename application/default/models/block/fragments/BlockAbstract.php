<?php
abstract class BlockAbstract {
    
    protected $blockDetails     = array();
    protected $indexer          = null;
    protected $blockFinder      = null;
    protected $typeFinder       = null;
    protected $requestParams    = array();
    protected $coreResults      = false;
    protected $coreBlocks       = array(14,15,16,19);
    
    public function __construct() {
        $this->indexer = Compass_Search_Lucene::open(SearchIndexer::getIndexDirectory());
        $this->blockFinder = new Blocks();
        $this->typeFinder = new ActivityTypes();
    }
    
    public function setBlockDetails($blockDetails) {
        if(!empty($blockDetails)) {
            $this->blockDetails = $blockDetails;
        }
    }
    
    public function getBlockDetails() {
        return $this->blockDetails;
    }
    
    public function setRequestParams($requestParams) {
        if(!empty($requestParams)) {
            $this->requestParams = $requestParams;
        }            
    }
    
    public function getOtherBlockQuery() {
        $queryStr = '+doctype:Linkage +(ta_block:"Other")';
        return $queryStr;
        
    }
    
    /**
     * This function returns default start of any query as per current block.
     * It would be used by other lucene queries which would basically prepend this $queryStr at the start.
     */
    protected function getReleaseDateQuery() {
        $blockName = $this->blockDetails['blockName'];
        $queryStr = '+doctype:Linkage';
        $queryStr .= " +ta_block:\"{$blockName}\"";
        
       $identity = Zend_Auth::getInstance()->getIdentity();
       if ('student' == $identity->role) {
            $domain_qstr = array();
            foreach ($identity->all_domains as $domain) {
        		$domain_qstr[] = "(ta_audience:{$domain} AND lo_audience:{$domain})";
        	}
            $queryStr .= " +(". join(" OR ", $domain_qstr). ")";
       } else {
            $queryStr .= " +(ta_audience:{$identity->domain} AND lo_audience:{$identity->domain})";
       }
       
        //Add student info in the query
        $queryStr .= $this->createQueryForStudent();
        Zend_Registry::get('logger')->debug(__METHOD__ . ': ' . $queryStr);
        return $queryStr;
    }
    
    /**
     *  This function returns lucene query string for block 'OTHER' as per the ta type if not null 
     */
    protected function getReleaseDateQueryForCoreBlock($type = null) {
        $queryStr = '+doctype:Linkage';
        $blockNames = $this->blockFinder->getAllNames();
        // Autoid for 'OTHER' in lk_activitytype table is 21
        $blockName = $blockNames[21];
        $queryStr .= " +(ta_block:\"{$blockName}\")";
        if(!empty($type)) {
            $queryStr .= " +(ta_type:\"{$type}\")";
        }
        
       $identity = Zend_Auth::getInstance()->getIdentity();
       if ('student' == $identity->role) {
            $domain_qstr = array();
            foreach ($identity->all_domains as $domain) {
        		$domain_qstr[] = "(ta_audience:{$domain} AND lo_audience:{$domain})";
        	}
            $queryStr .= " +(". join(" OR ", $domain_qstr). ")";
       } else {
            $queryStr .= " +(ta_audience:{$identity->domain} AND lo_audience:{$identity->domain})";
       }
            
        //Add student info in the query
        $queryStr .= $this->createQueryForStudent();
        Zend_Registry::get('logger')->debug(__METHOD__ . ': ' . $queryStr);
        return $queryStr;
    }

    private function createQueryForStudent() {
    	$queryStr = '';
    	$identity = Zend_Auth::getInstance()->getIdentity();
    	if (UserAcl::isStudent()) {
    		$queryStr .= " +(";
	        if (isset(Zend_Registry::get('config')->event_wsdl_uri)) {
	        	foreach ($identity->groups as $group) {
	        		$queryStr .= "releasedate_$group:[0 TO ".strtotime('now')."] ";
	        	}
	        } else {
	        	$stage = date('Y') - $identity->cohort + 1;
	        	$queryStr .= 'ta_stage:"'.$stage.'" ';
	        }
	        $queryStr .= ")";
    	}
    	return $queryStr;
    }
    
    /**
     * This function returns lucene results by querying current block + ta type.
     * If no results are found then it tries to query 'OTHER' block + ta type. If any results
     * are found for the block 'OTHER'  we need to notify user that the results are coming from core blocks
     * by setting the $this->coreResults to true.
     */
    protected function getTaForBlockAndType ($type) {
        $releaseDateQuery = $this->getReleaseDateQuery();
        $types = $this->typeFinder->getAllNames();
        $queryStr = $releaseDateQuery. " +(ta_type:\"{$types[$type]}\")";
        
        Zend_Registry::get('logger')->debug(__METHOD__ . ': ' . $queryStr);
        $results = $this->indexer->find($queryStr, 'ta_sequence_num', SORT_NUMERIC, SORT_ASC);
        
        if(empty($results) && in_array($this->blockDetails["blockId"], $this->coreBlocks)) {
            $queryStr = $this->getReleaseDateQueryForCoreBlock($types[$type]);
            $results = $this->indexer->find($queryStr, 'ta_sequence_num', SORT_NUMERIC, SORT_ASC);
            if(!empty($results)) {
            	$this->requestParams['coreResults'] = true;
                $this->coreResults = true;
            }

        }
        $rows = array();
        $terms = array();
        $blockWeeks = array();
        foreach($results as $result) {
            $row = array();
            $row['ta_auto_id']                  = $result->ta_auto_id;
            $row['name']                        = $result->ta_title;
            $row['sequence']                    = $result->ta_sequence_num;
            $row['disciplines']                 = $result->lo_discipline_names;
            $row['theme']                       = $result->lo_theme;
            $row['ta_resource_links_staff']     = $result->ta_resource_links_staff;
            $row['ta_resource_links_student']   = $result->ta_resource_links_student;
            $row['ta_block_week']               = $result->ta_block_week;
            $row['ta_term']                     = $result->ta_term;
            
            if(strlen(trim($result->ta_term)) > 0){
                $terms[] = $result->ta_term;
            }    
            if((int)$result->ta_block_week > 0 && !in_array($result->ta_block_week, $blockWeeks)){
                $blockWeeks[] = $result->ta_block_week;
            }    
            $rows[$result->ta_auto_id]  = $row;
        }
        //If terms exist then we should display term instead of sequence
        if(!empty($terms)) {
            uasort($rows,array($this, '_compareTerm'));
            $rows['display'] = 'term';
        //If block weeks exist and if there are at least two different types of block weeks display block weeks
        //instead of sequence
        } else if (count($blockWeeks) > 1) {
            uasort($rows,array($this, '_compareBlockWeek'));
            $rows['display'] = 'week';
        //Default display to Sequence
        } else if(!empty($rows)) {
            $rows['display'] = 'sequence';
        }
        return $rows;
    }
    
    private function _compareTerm($a, $b) {
        $aTerm = strtolower($a['ta_term']);
        $bTerm = strtolower($b['ta_term']);
        if ($aTerm == $bTerm) {
            return 0;
        }
        return ($aTerm > $bTerm) ? +1 : -1;
    }
    
    private function _compareBlockWeek($a, $b) {
        $aWeek = (int)$a['ta_block_week'];
        $bWeek = (int)$b['ta_block_week'];
        if($aWeek == $bWeek) {
            $aSeq = (int)$a['sequence'];
            $bSeq = (int)$b['sequence'];  
            if( $aSeq == $bSeq) {
                return 0;
            }
            return ($aSeq > $bSeq) ? +1 : -1;
        } 
        return ($aWeek > $bWeek) ? +1 : -1;
    }
    
    
    /**
     * Returns complete information on different types of resources attached to this Block.
     * It also contains links to different ta's for this Block 
     * @return array
     */
    public function getDynamicMenuLinks() {
        try {
            $return = new stdClass;
            $return->resources = PblBlockConst::createDynamicLinks(PblBlockConst::$block, $this->blockDetails['blockId']);
            $return->taTypes = $this->getDynamicTas();
            return $return;
        } catch(Exception $ex) {
            $error = $ex->getMessage().PHP_EOL.$ex->getTraceAsString().PHP_EOL;
            Zend_Registry::get('logger')->warn(PHP_EOL."CLASS\t: ".__CLASS__.PHP_EOL."METHOD\t: ".__METHOD__.PHP_EOL."Error\t: ".$error.PHP_EOL);
            return array('error' => true);            
        }
    }
    
    protected function getDynamicTas() {
        $taTypeNames        = array();
        $coreTaTypeNames    = array();
        $releaseDateQuery   = $this->getReleaseDateQuery();
        $results            = $this->indexer->find($releaseDateQuery);
        
        if(!empty($results)) {
            $taTypeNames = $this->processResultsForMenu($results);
        }
        if(in_array($this->blockDetails["blockId"], $this->coreBlocks)) {
            $queryStr = $this->getReleaseDateQueryForCoreBlock();
            $results = $this->indexer->find($queryStr);
            if(!empty($results)) {
                $coreTaTypeNames = $this->processResultsForMenu($results);
            }
        }
        $taTypes = ($taTypeNames + $coreTaTypeNames);
        if(!empty($taTypes)) {
            uksort($taTypes, array($this, 'sortTaTypeNamesByImportance'));
        } 
        return $taTypes;
    }
    
    private function sortTaTypeNamesByImportance($a, $b) {
        try {
            $importanceA = $this->typeFinder->find($a)->current()->importance;
            $importanceB = $this->typeFinder->find($b)->current()->importance;
            return $importanceA > $importanceB;
        } catch (Exception $ex) {
        	$error = $ex->getMessage().PHP_EOL.$ex->getTraceAsString().PHP_EOL;
        	Zend_Registry::get('logger')->warn(PHP_EOL."CLASS\t: ".__CLASS__.PHP_EOL."METHOD\t: ".__METHOD__.PHP_EOL."Error\t: ".$error.PHP_EOL);
            return false;
        }
    }
    
    private function processResultsForMenu ($results) {
        $return             = array();
        try {
            //List of Ta Type Names which are already added.
            $addedTaTypeNames   = array();
            //List of Ta Type Id to be excluded from menu. List coming from config.ini
            $excludedTaTypeIds  = $this->getTasForExclusionFromMenu();
            //List of Ta Type Names which needs to be renamed. List coming from config.ini
            $renameTaTypeNames  = $this->getListOfRenamedTaTypes();
            
            foreach($results as $result) {
                //If ta type name is not added then add it
                if(!in_array($result->ta_type, $addedTaTypeNames)) {
                    try {
                        $typeId = $this->typeFinder->getActivityId($result->ta_type);
                        $addedTaTypeNames[$typeId] = $result->ta_type;
                    } catch (Exception $ex) {
                    	Zend_Registry::get('logger')->debug("\n".$ex->getMessage()."\n".$ex->getTraceAsString()."\n\n");
                    }
                }
            }
            
            if(!empty($addedTaTypeNames)) {
                foreach($addedTaTypeNames as $taTypeId => $taTypeName) {
                    //If ta type name found in the $renamedTaTypeNames use that
                    if(isset($renameTaTypeNames[$taTypeName])) {
                        $taTypeName = $renameTaTypeNames[$taTypeName]; 
                    //otherwise add letter 's' if the ta type name does not end with 'ing', 's' or 'ive'
                    } else {
                        $taTypeName = BlockConst::renameTaTypeForBlockMenu($taTypeName);
                    }
                    //If this ta type id is not in $excludedTaTypeIds then add it.
                    if(!in_array($taTypeId, $excludedTaTypeIds)) {
                        $return[$taTypeId] = $taTypeName;
                    }
                }
            }
        } catch (Exception $ex) {
        	Zend_Registry::get('logger')->debug("\n".$ex->getMessage()."\n".$ex->getTraceAsString()."\n\n");
        }
        return $return;
    }
    
    private function getTasForExclusionFromMenu() {
        $config = Zend_Registry::get('config');
        if(isset($config->stage3->menu->tas->exclude)) {
            return $config->stage3->menu->tas->exclude->toArray();
        } else {
            $error = '"stage3.menu.tas.exlude" option does not exist in config.ini';
            Zend_Registry::get('logger')->warn(PHP_EOL."CLASS\t: ".__CLASS__.PHP_EOL."METHOD\t: ".__METHOD__.PHP_EOL."Error\t: ".$error.PHP_EOL);
        }
    }

    private function getListOfRenamedTaTypes() {
        $config = Zend_Registry::get('config');
        if(isset($config->stage3->menu->tas->rename)) {
            return $config->stage3->menu->tas->rename->toArray();
        } else {
            $error = '"stage3.menu.tas.rename" option does not exist in config.ini';
            Zend_Registry::get('logger')->warn(PHP_EOL."CLASS\t: ".__CLASS__.PHP_EOL."METHOD\t: ".__METHOD__.PHP_EOL."Error\t: ".$error.PHP_EOL);
        }
    }
}
?>
