<?php
class MovePblSessionResources {
    
    private $mediabankResource = null;
    private $mediabankResourceService = null;
    private $updateLkResourceFormat = "UPDATE lk_resource SET type_id = %d, type ='ta' WHERE auto_id = %d;";
    private $updateLkProblemResourceFormat = "UPDATE lk_resource SET type_id = %d,resource_type_id = %d, type ='ta' WHERE auto_id = %d;";
    private $deleteLkResourceFormat = 'DELETE FROM lk_resource WHERE auto_id = %d;';
    
    private $problem = array();
    private $sessions = array();
    private $db = null;
    
    public function __construct() {
        $this->mediabankResource = new MediabankResource();
        $this->mediabankResourceService = new MediabankResourceService();
        $this->db = Zend_Registry::get('db');
    }
    
    public function move() {
        $return             = array();
        $tas                = $this->mediabankResource->getPblSessionTas();
        $pbls               = $this->groupbyPbl($tas);
        $pbls               = $this->createMapping($pbls);
        $return['data']     = $this->processPbls($pbls);
        $return['problem']  = $this->problem;
        $return['sessions'] = $this->sessions;
        $finished = $this->execQueries();
        if($finished === true) {
            print '<h2 style="color:blue;">Queries updated</h2>';
        } else {
            print '<h2 style="color:red;">Error: Queries could not be executed.</h2>';
        }
        $this->changeTitleDescription();
        return $return;
    }
    
    private function changeTitleDescription() {
        $return = array();
        foreach($this->problem['mids'] as $mid => $rows) {
            foreach($rows as $row) {
                $params['title']      = (isset($row['newTitle']) && !empty($row['newTitle'])) ? $row['newTitle']: $row['metadata']['title'];
                $params['desc']       = (isset($row['newDescription']) && !empty($row['newDescription'])) ? $row['newDescription'] : $row['metadata']['description'];    
                $params['copyright']  = $row['metadata']['copyright'];  
                $params['mid']        = $mid;
                $return[]             = $params;
                if(stripos($mid,'compassresources') !== false) {
                    $mediabankFormService = new MediabankFormService($params);
                    $result = $mediabankFormService->process();
                    print '<pre>';
                    print_r($result);
                    print '</pre>';
                }
            }    
        }
    }
    
    private function execQueries() {
        $queries = '';
        if(!empty($this->problem['queries'])) {
            foreach($this->problem['queries'] as $query) {
                $queries .= $query."\n";
            }            
        }
        if(!empty($this->sessions['queries'])) {
            foreach($this->sessions['queries'] as $query) {
                $queries .= $query."\n";
            }            
        }
        if(!empty($queries)) {
            $this->db->beginTransaction();
            try {
                $this->db->getConnection()->exec($queries);
                $this->db->commit();
                return true;
            } catch (Exception $ex) {
                $this->db->rollBack();
                Zend_Registry::get('logger')->warn("\n".$ex->getMessage()."\n".$ex->getTraceAsString()."\n\n");
                return false;
            }
        }
    }
        
    private function processPbls(&$pbls) {
        foreach($pbls as $pblId => $pblsessions) {
            $pblSession1Found = false;
            foreach($pblsessions as $pblsession1 => $pblsessions2and3) {
                $pbls[$pblId][$pblsession1]['sessions']  = $this->processSessionResources($pblId, $pblsession1, $pblsessions2and3);
                if($pblSession1Found === false) {
                    $data                                   = $this->processProblemResources($pblId, $pblsession1);
                    $pbls[$pblId][$pblsession1]['problem']  = $data;
                    $pblSession1Found                       = true;     
                }
            }
            if($pblSession1Found === false) {
                $error = 'Could not find teaching activity (pbl session 1) for Pbl Id '. $pblId;
                Zend_Registry::get('logger')->warn(PHP_EOL."CLASS\t: ".__CLASS__.PHP_EOL."METHOD\t: ".__METHOD__.PHP_EOL."Error\t: ".$error.PHP_EOL);
            }
        }
        return $pbls;
    }
    
    private function processSessionResources($pblId, $pblsession1, $pblsessions2and3) {
        $return = array();
        if($pblsession1 != 0 && !empty($pblsessions2and3)) {
            foreach($pblsessions2and3 as $pblsession2and3) {
                $resources = $this->mediabankResource->getResources($pblsession2and3, 'ta');
                foreach($resources as $resource) {
                    $mid                = $resource['resource_id'];
                    $resourceAutoId     = $resource['auto_id'];
                    $return['auto_id']  = $resourceAutoId;
                    $mediabankTitle     = $this->mediabankResourceService->getTitleForMid($mid);
                    $metadata           = $this->mediabankResourceService->getMediabankMetaData($mid);

                    $changes = array();
                    
                    if(strpos($mid,'cmsdocs-smp') !== false) {
                        if(stripos($mediabankTitle, 'Problem Summary') === false 
                                && stripos($mediabankTitle, 'Mechanism') === false 
                                && stripos($mediabankTitle, 'Patient Data Sheet') === false) {
                            $changes['title'] = $mediabankTitle;
                            if(stripos($mediabankTitle, 'Results')) {
                                $changes['newTitle'] = 'Results';
                                $return['title'][] = array('title' => $mediabankTitle,'newTitle' =>$changes['newTitle']);
                            } else if (stripos($mediabankTitle, 'EBM Activity')) {
                                $changes['newTitle'] = 'EBM Activity';
                                $return['title'][] = array('title' => $mediabankTitle,'newTitle' =>$changes['newTitle']);
                            } else if(stripos($mediabankTitle, 'Trigger')) {
                                $changes['newTitle'] = stristr($mediabankTitle, 'Trigger');
                                $return['title'][] = array('title' => $mediabankTitle,'newTitle' =>$changes['newTitle']);
                            }
                            $this->sessions['queries'][] = sprintf($this->updateLkResourceFormat, $pblsession1, $resourceAutoId);
                        } else {
                            $this->sessions['delete'][] = array('title' => $mediabankTitle);
                            $this->sessions['queries'][] = sprintf($this->deleteLkResourceFormat, $resourceAutoId);
                        }        
                    } else {
                        if(stripos($mediabankTitle, 'Trigger')) {
                            $changes['title'] = $mediabankTitle;
                            $changes['newTitle'] = stristr($mediabankTitle, 'Trigger');
                            $return['title'][] = array('title' => $mediabankTitle,'newTitle' =>$changes['newTitle']);
                            //$this->sessions['other'][] = array('title' => $mediabankTitle, 'newTitle'=> $changes['newTitle']);
                        } else {
                            $return['other'][] = array('title' => $mediabankTitle); 
                            $this->sessions['other'][] = array('title' => $mediabankTitle);   
                        }                                
                        $this->sessions['queries'][] = sprintf($this->updateLkResourceFormat, $pblsession1, $resourceAutoId);
                    }
                    
                    $descriptionData = $this->getNewDescription($pblId, $metadata, $mediabankTitle, $changes);
                    if(!empty($descriptionData)) {
                        $return['desc'][]           = $descriptionData;
                        $changes['description']     = $descriptionData['description'];
                        $changes['newDescription']  = $descriptionData['newDescription'];
                    }
                    if(!empty($changes)) {
                        $changes['auto_id'] = $resourceAutoId;
                        if(strpos($mid,'cmsdocs-smp') === false) {
                            $changes['metadata'] = $metadata;
                            $this->sessions['mids'][$mid][] = $changes;   
                        }
                    }
                }
            }
         }
        return $return;
    }
    
    private function processProblemResources($pblId, $pblsession1) {
        $return                 = array();
        $return['resources']    = array();
        $return['desc']         = array();
        $return['titles']       = array();
        $return['queries']      = array();
        
        $pblResources = $this->mediabankResource->getResourcesByType($pblId, 'pbl', array(1,3,4,5,14));
        
        if(!empty($pblResources)) {
            foreach($pblResources as $pblResource) {
                
                #problemQueries & resources
                $return['resources'][]      = $pblResource['auto_id'];
                $resourceTypeId             = ($pblResource['resource_type_id'] == 4) ? 10 : 7;
                $query                      = sprintf($this->updateLkProblemResourceFormat, $pblsession1, $resourceTypeId, $pblResource['auto_id']);
                $return['queries'][]        = $query;
                $this->problem['queries'][] = $query;
                
                #Title
                $mediabankTitle             = trim($this->mediabankResourceService->getTitleForMid($pblResource['resource_id']));
                $newMediabankTitle          = $this->getNewMediabankTitleForProblemResources($mediabankTitle, $pblResource);
                $changes                    = array();
                
                if($newMediabankTitle !== false) {
                    $title = array('auto_id' => $pblResource['auto_id'],'mid'=>$pblResource['resource_id'], 'title'=>$mediabankTitle, 'newTitle'=>$newMediabankTitle);
                    
                    $return['titles'][]     = $title;
                    $changes['title']       = $mediabankTitle; 
                    $changes['newTitle']    = $newMediabankTitle;
                }
                
                #problemDescription                
                $metadata       = $this->mediabankResourceService->getMediabankMetaData($pblResource['resource_id'], MediabankResourceConstants::$SCHEMA_native);
                $descriptionData = $this->getNewDescription($pblId, $metadata, $mediabankTitle, $changes);
                if(!empty($descriptionData)) {
                    $return['desc'][]           = $descriptionData;
                    $changes['description']     = $descriptionData['description'];
                    $changes['newDescription']  = $descriptionData['newDescription'];
                }
                if(!empty($changes)) {
                    $changes['auto_id'] = $pblResource['auto_id'];
                    $changes['metadata'] = $metadata;
                    $this->problem['mids'][$pblResource['resource_id']][] = $changes;   
                } else {
                    $this->problem['other'][] = $mediabankTitle;
                }
            }
        } else {
            $error = 'Could not find teaching activity (pbl session 1) for Pbl Id '. $pblId;
            Zend_Registry::get('logger')->warn(PHP_EOL."CLASS\t: ".__CLASS__.PHP_EOL."METHOD\t: ".__METHOD__.PHP_EOL."Error\t: ".$error.PHP_EOL);
        }
        return $return;
    }
    
    private function getNewDescription($pblId, $metadata, $mediabankTitle, $changes) {
        $return = array();
        if(isset($metadata) && isset($metadata['description']) && !empty($metadata['description'])) {
            if($metadata['description'] != $mediabankTitle) {
                $return['description']       = $metadata['description'];
                $return['newDescription']    = $metadata['description'];
                if(strlen(trim($mediabankTitle)) > 0) {
                    $return['newDescription'] .= ' - '. $mediabankTitle;
                } else if(isset($changes['newTitle']) &&  !empty($changes['newTitle'])) {
                    $return['newDescription'] .= ' - '. $changes['newTitle'];
                }
            } else {
                $this->problem['other'][] = array($mediabankTitle,$metadata['description']);
            }
        } else {
            $return['description']        = '';
            if(strlen(trim($mediabankTitle)) > 0) {
                $return['newDescription']    = $mediabankTitle;
            } else {
                $pbls = new Pbls();
                $return['newDescription']    = $pbls->getPblName($pblId);
                if(isset($changes['newTitle']) &&  !empty($changes['newTitle'])) {
                    $return['newDescription'] .= ' - '.$changes['newTitle'];
                }
            }
        }
        return $return;
    }
    
    private function getNewMediabankTitleForProblemResources($mediabankTitle, &$pblResource) {
        if(empty($mediabankTitle)) {
            if($pblResource['resource_type_id'] == 1) {
                return 'Problem Summary';
            }
        } else if(strpos($mediabankTitle,'Problem Summary') !== false) {
            return 'Problem Summary';
        } else if(strpos($mediabankTitle,'Tutor Guide') !== false) {
            return 'Tutor Guide';
        } else if(strpos($mediabankTitle,'Medical Humanities') !== false) {
            return 'Medical Humanities';
        } else if(strpos($mediabankTitle,'Mechanism') !== false) {
            return 'Mechanism';
        } else if(strpos($mediabankTitle,'Student Guide') !== false) {
            return 'Student Guide';
        }
        return false;
    }
    
    private function createMapping($pbls) {
        $return = array();
        foreach($pbls as $pblId => $tas) {
            $key = 0;
            $values = array();
            foreach($tas as $ta) {
                if($ta['sequence_num'] == 2) {
                   $key = $ta['auto_id'];
                } else if(in_array($ta['sequence_num'],array(3,4))) {
                   $values[] = $ta['auto_id']; 
                }
            }
            $return[$pblId][$key] = $values;
        }
        return $return;
    }
    
    private function groupbyPbl($tas) {
        $pbls = array();
        foreach($tas as $ta) {
            $row                    = array();
            $row['auto_id']         = $ta['auto_id'];
            $row['sequence_num']    = $ta['sequence_num']; 
            $row['pbl']             = $ta['pbl'];
            $pbls[$ta['pbl']][]     = $row;
        }
        return $pbls;                
    }

}


/*
    public function movePblSessionResources() {
        $return = array();
        $mediabankResourceService = new MediabankResourceService();
        $pbls = new Pbls();
        try {
            $query = <<<QUERY
select auto_id, sequence_num, pbl from teachingactivity as ta 
    where type = ? and sequence_num in (2,3,4)
order by pbl, sequence_num, auto_id;
QUERY;
            $rows = $this->getAdapter()->query($query, array(4))->fetchAll();
            $pblArr = array();
            foreach($rows as $row) {
                $pblArr[$row['pbl']][] = $row;
            }
            //
            foreach($pblArr as $pblId => $rows) {
                $key = 0;
                $values = array();
                $returnPbl =& $return[$pblId];
                foreach($rows as $row) {
                    if($row['sequence_num'] == 2) {
                        $key = $row['auto_id'];
                    } else if(in_array($row['sequence_num'],array(3,4))) {
                       $values[] = $row['auto_id']; 
                    }
                }
                $pblSession1Found = false;
                if( $key != 0 && !empty($values) ) {
                    foreach($values as $value) {
                        if(! isset($returnPbl[$key]['ta_resources'][$value])) {
                            $resources = $this->getResources($value,'ta');
                            foreach($resources as $resource) {
                                $mediabankTitle = $mediabankResourceService->getTitleForMid($resource['resource_id']);
                                $metadata = $mediabankResourceService->getMediabankMetaData($resource['resource_id'], MediabankResourceConstants::$SCHEMA_native);
                                $infoSession                = array();
                                if(isset($metadata) && isset($metadata['description']) && !empty($metadata['description'])) {
                                    if($metadata['description'] != $mediabankTitle) {
                                        $infoSession['description']        = $metadata['description'];
                                        $infoSession['new_description']    = $info['description'].' - '. $mediabankTitle;
                                    } else {
                                        $infoSession['description']        = $metadata['description'];
                                        $infoSession['new_description']    = $mediabankTitle;
                                    }
                                } else {
                                    $infoSession['description']        = $metadata;
                                    $infoSession['new_description']    = $mediabankTitle;
                                }
                                
                                $resource['mediabank_title'] = $mediabankTitle;
                                $returnPbl[$key]['ta_resources'][$value][] = $resource;
                                
                                if(strpos($resource['resource_id'],'cmsdocs-smp') !== false) {
                                    $infoSession['title']       = $mediabankTitle;
                                    $infoSession['resource_id'] = $resource['resource_id'];
                                    $infoSession['auto_id']     = $resource['auto_id'];
                                    if(stripos($mediabankTitle, 'Problem Summary') === false 
                                        && stripos($mediabankTitle, 'Mechanism') === false 
                                        && stripos($mediabankTitle, 'Patient Data Sheet') === false) {
                                            
                                        if(stripos($mediabankTitle, 'Results')) {
                                            $infoSession['new_title'] = 'Results';
                                            $return[10000]['session'][$resource['resource_type_id']]['cmsdocs']['Results'][] = $infoSession;
                                        } else if (stripos($mediabankTitle, 'EBM Activity')) {
                                            $infoSession['new_title'] = 'EBM Activity';
                                            $return[10000]['session'][$resource['resource_type_id']]['cmsdocs']['EBM Activity'][] = $infoSession;
                                        } else if(stripos($mediabankTitle, 'Trigger')) {
                                            $infoSession['new_title'] = stristr($mediabankTitle, 'Trigger');
                                            $return[10000]['session'][$resource['resource_type_id']]['cmsdocs']['Trigger'][] = $infoSession;
                                        }
                                                                        
                                    } else {
                                        $return[10000]['session'][$resource['resource_type_id']]['cmsdocs']['Other'][] = $infoSession;
                                    }
                                } else {
                                    $infoSession['title']       = $mediabankTitle;
                                    $infoSession['resource_id'] = $resource['resource_id'];
                                    $infoSession['auto_id']     = $resource['auto_id'];
                                    if(stripos($mediabankTitle, 'Trigger')) {
                                        $infoSession['new_title'] = stristr($mediabankTitle, 'Trigger');
                                        $return[10000]['session'][$resource['resource_type_id']]['cmsdocs']['Trigger'][] = $infoSession;
                                    } else {   
                                        $return[10000]['session'][$resource['resource_type_id']]['compassresources'][] = $infoSession;
                                    }                                
                                }
                                
                                if($pblSession1Found == false) {
                                    $pblSession1Found = true;
                                    $pblResources = $this->getResourcesByType($pblId,'pbl', array(1,3,4,5,14));
                                    foreach($pblResources as $pblResource) {
                                        $mediabankTitle = $mediabankResourceService->getTitleForMid($pblResource['resource_id']);
                                        $info                       = array();
                                        $info['resource_id']        = $pblResource['resource_id'];
                                        $info['auto_id']            = $pblResource['auto_id'];
                                        $metadata                   = $mediabankResourceService->getMediabankMetaData($pblResource['resource_id'], MediabankResourceConstants::$SCHEMA_native);
                                        if(isset($metadata) && isset($metadata['description']) && !empty($metadata['description'])) {
                                            if($metadata['description'] != $mediabankTitle) {
                                                $info['description']        = $metadata['description'];
                                                $info['new_description']    = $info['description'].' - '. $mediabankTitle;
                                            } else {
                                                $info['description']        = $metadata['description'];
                                                $info['new_description']    = $info['description'];
                                            }
                                        } else {
                                            $info['description']        = '';
                                            $info['new_description']    = '';
                                        }
                                        $info['title']              = $mediabankTitle;
                                                                                
                                        if(empty($mediabankTitle)) {
                                            if($pblResource['resource_type_id'] == 1) {
                                                $info['title']              = '';
                                                $info['new_title']          = 'Problem Summary';
                                                $info['description']        = '';
                                                $info['new_description']    = $pbls->getPblName($pblId) .' - Problem Summary';
                                                $return[10000]['problem'][$pblResource['resource_type_id']]['Problem Summary'][] = $info;
                                            }
                                        } else if(strpos($mediabankTitle,'Problem Summary') !== false) {
                                            $info['new_title'] = 'Problem Summary';
                                            $return[10000]['problem'][$pblResource['resource_type_id']]['Problem Summary'][] = $info;
                                        } else if(strpos($mediabankTitle,'Tutor Guide') !== false) {
                                            $info['new_title'] = 'Tutor Guide';
                                            $return[10000]['problem'][$pblResource['resource_type_id']]['Tutor Guide'][] = $info;
                                        } else if(strpos($mediabankTitle,'Medical Humanities') !== false) {
                                            $info['new_title'] = 'Medical Humanities';
                                            $return[10000]['problem'][$pblResource['resource_type_id']]['Medical Humanities'][] = $info;
                                        } else if(strpos($mediabankTitle,'Mechanism') !== false) {
                                            $info['new_title'] = 'Mechanism';
                                            $return[10000]['problem'][$pblResource['resource_type_id']]['Mechanism'][] = $info;
                                        } else if(strpos($mediabankTitle,'Student Guide') !== false) {
                                            $info['new_title'] = 'Student Guide';
                                            $return[10000]['problem'][$pblResource['resource_type_id']]['Student Guide'][] = $info;
                                        } else if(strpos($mediabankTitle,'Medical Humanities') !== false) {
                                            $info['new_title'] = 'Medical Humanities';
                                            $return[10000]['problem'][$pblResource['resource_type_id']]['Medical Humanities'][] = $info;
                                        }
                                        $pblResource['mediabank_title'] = $mediabankTitle;
                                        $returnPbl[$key]['pbl_resources'] = $pblResource; 
                                    }
                                }
                            }
                        }
                    }
                }    
                if($pblSession1Found === false) {
                    $error = 'Could not find teaching activity (pbl session 1) for Pbl Id '. $pblId;
                    Zend_Registry::get('logger')->warn(PHP_EOL."CLASS\t: ".__CLASS__.PHP_EOL."METHOD\t: ".__METHOD__.PHP_EOL."Error\t: ".$error.PHP_EOL);
                }
            }
            return $return;
        } catch(Exception $ex) {
            return array();
        }
    }
*/
?>