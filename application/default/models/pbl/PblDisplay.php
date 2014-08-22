<?php
class PblDisplay {
    
    protected $pblDetails                       = array();
    protected $indexer                          = null;
    protected $blockFinder                      = null;
    protected $typeFinder                       = null;
    protected $req                              = array();
    protected $pblTas                           = array();
    private $listCounter                        = null;
    private $configSequences                    = null;
    private $configWeekviewItems                = array();
    private $taSeqs                             = array();
    private $taSeqsFound                        = array();
    private $taSeqResourcesTitleOrder           = array();
    private $taActivityTypeIdsFound             = array();
    protected $mediabankResourceService         = null;
    
    public function getPageDetails() {
        $content = $this->getResult();
        return array('content' => $content, 'req' => $this->req) + PblBlockConst::getModuleControllerAction();
    }
    
    public function __construct() {
        $this->indexer = Compass_Search_Lucene::open(SearchIndexer::getIndexDirectory());
        $this->blockFinder = new Blocks();
        $this->typeFinder = new ActivityTypes();
        $this->mediabankResourceService = new MediabankResourceService();
    }
    
    public function setPblDetails($pblDetails) {
        if(!empty($pblDetails)) {
            $this->pblDetails = $pblDetails;
        }
    }
    
    public function getPblDetails() {
        return $this->pblDetails;
    }
    
    public function setReq($req) {
        if(!empty($req)) {
            $this->req = $req;
        }            
    }
    
    public function getReq() {
        return  $this->req;
    }
    
    /**
     * Pass $type to look for specific teaching activity type
     * Pass level to either look for tas attached to pbl or to the whole block
     * @param $type
     * @param $level
     * @return string $queryStr;
     */
    protected function getReleaseDateQuery() {
        try {          
            $blockWeek = $this->pblDetails['pblBlockWeek'];
            $blockId = $this->pblDetails['pblBlockId'];
            $queryStr = '+doctype:Linkage';
            
            if (UserAcl::getRole() != 'student') {
                $domain = UserAcl::getDomainName();
            	$queryStr .= " +(ta_audience:{$domain} AND lo_audience:{$domain})";
            } else {
                //Add student info in the query
                $queryStr .= $this->createQueryForStudent();
            }
            
            //Add block info in the query
            $blockNames = $this->blockFinder->getAllNames();
            $blockName = $blockNames[$blockId];
            $queryStr .= " +(ta_block:\"{$blockName}\")";
            
            $queryStr .= " +(ta_block_week:{$blockWeek})";
            Zend_Registry::get('logger')->debug(__METHOD__ . " $queryStr");
            return $queryStr;
            
        } catch(Exception $ex) {
            Zend_Registry::get('logger')->debug("\n".$ex->getMessage()."\n".$ex->getTraceAsString()."\n\n");
            return '';            
        }
        
    }
    
    private function createQueryForStudent() {
        $queryStr = '';
        $identity = Zend_Auth::getInstance()->getIdentity();
        if ('student' == $identity->role) {
            $domain_qstr = array();
            foreach ($identity->all_domains as $domain) {
                $domain_qstr[] = "(ta_audience:{$domain} AND lo_audience:{$domain})";
            }
            $queryStr .= " +(". join(" OR ", $domain_qstr). ")";
            
            $stage3 = (int)(date('Y')) - 2;
            //stage 1 and 2 student
            if ($identity->cohort > $stage3) {
                $queryStr .= " +(";
                //stage 2 student can always see stage 1 material
                if ($identity->cohort == $stage3 + 1) {
                    $queryStr .= 'ta_stage:"1" ';
                }
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
        }
        Zend_Registry::get('logger')->debug(__METHOD__ . " $queryStr");
        return $queryStr;
    }
    
   
    protected function getResult() {
        try {       
            if(isset($this->req['type'])) {
                $this->setPblTas($this->req['type']);   
                $this->_setConfigWeekviewItems(); 
                $this->_setConfigSequences();
                return $this->_processType();
            }
            return array();
        } catch(Exception $ex) {
            Zend_Registry::get('logger')->debug("\n".$ex->getMessage()."\n".$ex->getTraceAsString()."\n\n");
            return array();            
        }
    }
    
    private function _setConfigWeekviewItems() {
        if(empty($this->configWeekviewItems)) {
            $configWeekviewItems = array();
            $config = $this->req['configWeekview'];
            if(isset($config['items']) && !empty($config['items'])
                            && isset($config['item']) && !empty($config['item'])) {
                foreach($config['items'] as $item) {
                    if(isset($config['item'][$item])) {
                        $configWeekviewItems[] = $config['item'][$item];
                    }
                }
            }
            $this->configWeekviewItems = $configWeekviewItems;
        }
    }
    
    private function _setConfigSequences() {
        $configSequences = array();
        if(!empty($this->configWeekviewItems)) {
            foreach($this->configWeekviewItems as $configWeekviewItem) {
                if(isset($configWeekviewItem['category']) && $configWeekviewItem['category'] == 'sequence' && isset($configWeekviewItem['activitytypeid'])) {
                    $configSequences[] = $configWeekviewItem;
                } 
            }
        }
        $this->configSequences = $configSequences;
    }

    /**
     * Process the search results of the lucene query and return information about teaching activites
     * @param $queryStr
     * @return mixed $rows
     */
    private function _processType() {
        try {
            $rows = array();
            
            $taAutoIdsFound = array();
            $loAutoIdsFound = array();
            $this->listCounter = 0;
            
            foreach($this->pblTas as $result) {
                $this->_setActivityTypeSequences($result);
                $this->_setTaActivityTypeIdsFound($result->ta_activitytype_id);
                if($this->req['type'] == 'ta') {
                    if( !in_array($result->ta_auto_id, $taAutoIdsFound) && $result->ta_activitytype_id == $this->req['activityTypeId']) {
                        $this->listCounter++;
                        $row = $this->_getLuceneResultRows($this->req['columns'], $result);
                        $rows[$result->ta_auto_id] = $row;
                        $taAutoIdsFound[] = $result->ta_auto_id;
                    }
                } else if ($this->req['type'] == 'lo' && !in_array($result->lo_auto_id, $loAutoIdsFound)) {
                    $this->listCounter++;
                    $row = $this->_getLuceneResultRows($this->req['columns'], $result);
                    $rows[$result->lo_auto_id] = $row;
                    $loAutoIdsFound[] = $result->lo_auto_id;
                } else if ($this->req['type'] == 'pbl') {
                    $this->listCounter++;
                }
            }
            
            $pblMenu = $this->_getPblMenu();
            $pblIconUrl = $this->_getPblIconUrl();
            
            $result = array(
                            'rows' => $rows,
                            'pblMenu' => $pblMenu,
                            'pblIconUrl' => $pblIconUrl
                            );
            return $result;
        } catch(Exception $ex) {
            Zend_Registry::get('logger')->debug("\n".$ex->getMessage()."\n".$ex->getTraceAsString()."\n\n");
            return array();
        }
    }
    
    private function _getPblIconUrl() {
        $url = '';
        try {
            $mediabankResource = new MediabankResource();
            $rows = $mediabankResource->getResourcesByType($this->pblDetails['pblId'], 'pbl', ResourceTypeConstants::$PBLICON_ID);
            if (is_array($rows)) {
                $url = MediabankResourceConstants::createCompassImageUrl($rows[0]['resource_id'], NULL, 160, 90);
            }
        } catch (Exception $ex) {
            $error = $ex->getMessage().PHP_EOL.$ex->getTraceAsString().PHP_EOL;
            Zend_Registry::get('logger')->warn(PHP_EOL."CLASS\t: ".__CLASS__.PHP_EOL."METHOD\t: ".__METHOD__.PHP_EOL."Error\t: ".$error.PHP_EOL);
        }
        return $url;
        
    }
    
    private function _setTaActivityTypeIdsFound($taActivityTypeId) {
        /**
         * We need to add the ta activity type ids as we were able to find records for.
         * This would allow us to display the menu link if records exist otherwise
         * no menu link would be displayed for that ta activity type id
         */
        if(!in_array($taActivityTypeId, $this->taActivityTypeIdsFound)) {
            $this->taActivityTypeIdsFound[] = $taActivityTypeId;
        }
    }
    
    
    private function _setActivityTypeSequences(&$result) {
        if(!empty($this->configSequences)) {
            foreach($this->configSequences as $seq) {
                $activityTypeId = $seq['activitytypeid'];
                if($result->ta_activitytype_id == $activityTypeId){
                    if(! in_array($result->ta_auto_id, $this->taSeqsFound)) {
                        $sequence = array();
                        $sequence['type'] = 'ta';
                        $sequence['typeId'] = $result->ta_auto_id;
                        $sequence['activityTypeId'] = $activityTypeId;
                        $resourceTypeIdsAllowed = (isset($seq['resourcetypeids']['allowed'])) ? $seq['resourcetypeids']['allowed'] : array();
                        $resourceTitleOrder = (isset($seq['resources']['order'])) ? $seq['resources']['order'] : array();
                        $sequence['resources'] = $this->_getSeqResources($result->ta_auto_id, 'ta', $resourceTypeIdsAllowed, $resourceTitleOrder);
                        $this->taSeqs[$activityTypeId]['sequences'][$result->ta_sequence_num][] = $sequence;
                        $this->taSeqsFound[] = $result->ta_auto_id;
                        ksort($this->taSeqs[$activityTypeId]['sequences']);
                    }
                }
            }
        }
    }
    
    private function _getSeqResources($typeId, $type, $resourceTypeIdsAllowed, $resourceTitleOrder) {
        $return = array();
        $resources = $this->mediabankResourceService->getResources($typeId, $type);
        $this->taSeqResourcesTitleOrder = $resourceTitleOrder;
        if(!empty($resources)) {
            foreach($resources as $resource) {
                if(empty($resourceTypeIdsAllowed) 
                       || (in_array($resource['resource_type_id'], $resourceTypeIdsAllowed) && $resource['allowUser'] !== false)   ){
                    $resourceInfo = array();
                    $resourceInfo['staffOnly'] = (isset($resource['staffOnly'])) ? $resource['staffOnly'] : false;
                    if(isset($resource['customViewUrl'])) {
                        $resourceInfo['customUrl'] = $resource['customViewUrl'];
                    } else {
                        $resourceInfo['typeId'] = $typeId;
                        $resourceInfo['resourceAutoId'] = $resource['auto_id'];
                    }
                    if(isset($resource['customOnclick'])) {
                        $resourceInfo['customOnclick'] = $resource['customOnclick'];
                    }
        
                    $resourceTitle =  $resource['title'];
                    if(strlen($resourceTitle) > 22) {
                        $resourceTitle = substr_replace($resourceTitle,' ... ', 10, -10);
                        $resourceInfo['titleHref'] = $resource['title'];
                    }
                    $resourceInfo['title'] = trim($resourceTitle);
                    $return[] = $resourceInfo;
                }
            }
            if(!empty($return)) {
                usort($return, array($this, 'sortResources'));
            }
        }
        return $return;
    }
    
    
    private function sortResources($a, $b) {
        $titleA = strtolower(trim($a['title']));
        $titleAOrder = 0;
        $titleAFound = false;
    
        $titleB = strtolower(trim($b['title']));
        $titleBOrder = 0;
        $titleBFound = false;
    
        if(!empty($this->taSeqResourcesTitleOrder)) {
            foreach($this->taSeqResourcesTitleOrder as $key => $resourceTitle) {
                $order = $key + 1;
                if($titleAFound == false && $titleA == strtolower(trim($resourceTitle))) {
                    $titleAFound = true;
                    $titleAOrder = $order;
                }
                if($titleBFound == false && $titleB == strtolower(trim($resourceTitle))) {
                    $titleBFound = true;
                    $titleBOrder = $order;
                }
            }
            if($titleAFound == true && $titleBFound == false) {
                return false;
            } else if ($titleAFound == false && $titleBFound == true) {
                return true;
            }
        }
        return $titleAOrder > $titleBOrder;
    }
    
    
    /**
     * All the menu links are set in config in the key 'configMenuLinks'
     * We need to loop through all the configs and see if we had found any records for
     * that ta activity type or lo type. If we find records we just add another key
     * to the 'configMenuLinks' which says 'foundrecords'
     */
    private function _getPblMenu(){
        if(!empty($this->configWeekviewItems)) {
            foreach($this->configWeekviewItems as $key => &$item) {
                if($item['type'] == 'ta') {
                    $this->_updateWeekviewItemOfTypeTa($item);
                } else if ($item['type'] == 'lo') {
                    $this->_updateWeekviewItemOfTypeLo($item);
                } else if ($item['type'] == 'pbl') {
                    if(isset($item['category'])) {
                        if($item['category'] == 'pblresources') {
                            $this->_updateWeekviewItemOfTypePblAndCategoryPblresources($item);
                        } else if($item['category'] == 'managepblresources') {
                            $this->_updateWeekviewItemOfTypePblAndCategoryManageresources($item);
                        } else if($item['category'] == 'studentresources') {
                            $this->_updateWeekviewItemOfTypePblAndCategoryStudentresources($item);
                        }
                    }
                }
            }
        }
        return $this->configWeekviewItems;
    }
    
    private function _updateWeekviewItemOfTypeTa(&$item) {
        if(!empty($this->taActivityTypeIdsFound)) {
            foreach($this->taActivityTypeIdsFound as $id) {
                if($item['activitytypeid'] == $id) {
                    $item['foundrecords'] = true;
                    if(isset($this->taSeqs[$id]['sequences'])) {
                        $item['sequences'] = $this->taSeqs[$id]['sequences'];
                    }
                    return $item;
                }
            }
        }
        return $item;
    }
    
    private function _updateWeekviewItemOfTypeLo(&$item) {
        $totalLuceneRows = count($this->pblTas);
        if($totalLuceneRows > 0) {
            $item['foundrecords'] = true;
        }
        return $item;
    }
    
    private function _updateWeekviewItemOfTypePblAndCategoryPblresources(&$item) {
        $excludeResourceTypeIds = array();
        if(isset($item['exclude']['resourcetypeids'])) {
            $configExcludeResourceTypeIds = explode(',', $item['exclude']['resourcetypeids']);
            foreach($configExcludeResourceTypeIds as $configExcludeResourceTypeId) {
                if((int)$configExcludeResourceTypeId > 0) {
                    $excludeResourceTypeIds[] = (int)$configExcludeResourceTypeId;
                }
            }
        }
        $foundRecords = false;
        $pblResources = PblBlockConst::createDynamicLinks(PblBlockConst::$pbl, $this->pblDetails['pblId'], $excludeResourceTypeIds);
        if(!empty($pblResources)) {
            $foundRecords = true;
            foreach($pblResources as $pblResource) {
                $resource = array();
                $resource['resourceTypeId'] = $pblResource['resource_type_id'];
                $resource['resourceTitle'] = $pblResource['resource_type_name'];
                $resource['staffOnly'] = ($pblResource['allow'] == 'staff') ? true : false;
                $item['pblResources'][] = $resource;
            }
        }
        $item['foundrecords'] = $foundRecords;
        return $item;
    }
    
    private function _updateWeekviewItemOfTypePblAndCategoryManageresources(&$item) {
        if(UserAcl::isBlockchairOrAbove()) {
            $item['managePblResources'] = true;
            $item['foundrecords'] = true;
        } else {
            $item['foundrecords'] = false;
        }
        return $item;
    }
    private function _updateWeekviewItemOfTypePblAndCategoryStudentresources(&$item) {
        if(StudentResourceService::showSocialTools()) {
            $item['studentResources'] = true;
            $item['foundrecords'] = true;
        } else {
            $item['foundrecords'] = false;
        }
        return $item;
    }
    
    private function _getLuceneResultRows(&$columns, &$result) {
        $values = array();
        $customSlidesRecOtherExist = false;
        $customSlidesRecOther = array();
        foreach($columns as $column) {
            $value = '';
            if(isset($column['lucenefield'])) {
                $luceneField = $column['lucenefield'];
                $value = $result->$luceneField;
                switch($luceneField) {
                    case 'ta_sequence_num':
                        $value = $this->_displayTaSequenceNumText($result->ta_owner, $value);
                    break;
                    case 'ta_title':
                        $value = $this->_displayTaTitle($result->ta_auto_id, $result->lo_loid, $value);
                    break;
                    case 'lo_title':
                        $value = $this->_displayLoTitle($result->lo_auto_id, $result->lo_loid, $value);
                    break;
                    case 'lo_numStudentResources':
                    	$nsrs = -1;
                    	try {
                    		$nsrs = $result->getDocument()->getFieldValue('lo_numStudentResourceSummaries');
                    	} catch (Exception $ex) {}
                    		
                        $value = $this->_displayLoNumStudentResources($result->lo_loid, $value, $nsrs);
                    break;
                }
            }
            if(isset($column['custom'])) {
                switch($column['custom']) {
                    case 'resourcelinks':
                        $value = (UserAcl::isStaffOrAbove()) ? $result->ta_resource_links_staff : $result->ta_resource_links_student;
                    break;
                    case 'numbering':
                        $value = $this->listCounter;
                    break;
                    case 'slides':
                    case 'recordings':
                    case 'otherresources':
                        if($customSlidesRecOtherExist == false) {
                            $customSlidesRecOther = $this->_displayCustomResources($result);
                            $customSlidesRecOtherExist = true;
                        }
                        if(isset($customSlidesRecOther[$column['custom']])) {
                            $value = implode('',$customSlidesRecOther[$column['custom']]);
                        }
                    break;
                }
            }
            $values[] = $value;
        }
        return $values;        
    }
    
    private function _displayLoNumStudentResources($loLoId, $loNumStudentResources, $loNumStudentResourceSummaries) {
        $html = '<span class="'.sprintf('%03dresources', $loNumStudentResources).'"><a href="javascript:showloresources('.$loLoId.')">';
        if($loNumStudentResources == 0) {
            $html .= '<span style="color:#888888;">0 Resources</span>';
        } else {
            $append = ($loNumStudentResources > 1) ? 's' : '';
            $html .= sprintf('<b>%s Resource%s</b>', $loNumStudentResources, $append);
        }
        if($loNumStudentResourceSummaries>0)
        	$html .= "<br>Summary";
        $html .= '</a></span>';
        return $html;
    }
    
    
    private function _displayCustomResources(&$result) {
        $return = array();
        $resourceLinks = (UserAcl::isStaffOrAbove()) ? $result->ta_resource_links_staff : $result->ta_resource_links_student;
        $parts = preg_split('~(</a[^>]*>)~', $resourceLinks, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
        
        foreach($parts as $part) {
            if (trim($part) == '') {
                continue;
            } 
            $fileType = '';
            if (preg_match('/rel="([^"]*)"/', $part, $matches)) {
                if(isset($matches[1])) {
                    $explode = explode('|', $matches[1]);
                    foreach($explode as $relConfig) {
                        if(strpos($relConfig, 'fileType:') !== false){
                            $fileType = str_replace('fileType:', '', $relConfig);
                            if(strpos($fileType,'powerpoint') !== false) {
                                $fileType = 'powerpoint';
                            }
                        }
                    }
                } 
            }
            if($fileType != '') {
                switch($fileType) {
                    case 'pdf':
                    case 'ppt':
                    case 'powerpoint':
                        $return['slides'][] = $part;
                    break;
                    
                    case 'mp3':
                    case 'richmedia':
                    case 'avi':
                    case 'mp4':
                    case 'm4v':
                    case 'mpeg':
                    case 'zip':
                        $return['recordings'][] = $part;
                    break;
                    
                    default:
                        $return['otherresources'][] = $part;
                    break;
                }
            }
        }
        return $return;
    }
    
    private function _displayTaSequenceNumText($owner, $taSequenceNum) {
        $taSequenceNumText = empty($taSequenceNum) ? '' : $taSequenceNum . '.';
        if($owner != 'Medicine') {
            $taSequenceNumText = substr($owner, 0, 1) . $taSequenceNumText;
        }
        return $taSequenceNumText;
    }
    
    private function _displayLoTitle($loAutoId, $loLoId, $title) {
        $loTitleUrl = Compass::baseUrl().'/learningobjective/view/id/'.$loAutoId;
        return sprintf('<a href="%s">%s</a><div id="studentresourcepanel_%s"></div>', $loTitleUrl, $title, $loLoId);
    }
    
    private function _displayTaTitle($taAutoId, $loLoId, $title) {
        $requestUri = Zend_Controller_Front::getInstance()->getRequest()->getRequestUri();
        $taTitleUrl = 'javascript:void(0);';
        if(stristr($requestUri, '/ref/')){
            $explode = explode('/ref/', $requestUri);
            $taTitleUrl = sprintf('%s/typeid/%d/ref/%s',$explode[0], $taAutoId, $explode[1]);
        }
        return sprintf('<a href="%s">%s</a><div id="studentresourcepanel_%s"></div>', $taTitleUrl, $title, $loLoId);
    }
    
    protected function setPblTas($type) {
        if(empty($this->pblTas)) {
            $queryStr = $this->getReleaseDateQuery();
            if($type == 'ta') {
                $this->pblTas = $this->indexer->find($queryStr, 'ta_sequence_num', SORT_NUMERIC, SORT_ASC);
            } else { //for the other types like 'lo' or 'pbl' it does not need sequence ordering
                $this->pblTas = $this->indexer->find($queryStr);
            }
        }
    }

}
?>