<?php
class PblCoordinatorService {
    
    public function getPblCoordinatorsDetails() {
        try {
            $pblCoordinator = new PblCoordinator();
            $where = null;
            if(! UserAcl::isAdmin()) {
                $where = PblCoordinator::$COL_domain_id .' = ' .UserAcl::getDomainId();
            }
            $rows = $pblCoordinator->fetchAll($where);
            $return = array();
            $pbls = new Pbls();
            if($rows->count() > 0) {
                foreach($rows as $rowObj) {
                    $rowArr = $rowObj->toArray();
                    $rowArr['domain_name']      = $rowObj->findParentDomains()->name;
                    $rowArr['pbl_name']         = $rowObj->findParentPbls()->name;
                    $rowArr['pbl_description']  = $rowObj->findParentPbls()->description;
                    $rowArr['pbl_ref']          = $pbls->getPblRef($rowObj->pbl_id);
                    $return[] = $rowArr;
                }
                return $this->_sort($return);
            }
            return $return;
        } catch(Exception $ex) {
            Zend_Registry::get('logger')->debug("\n".$ex->getMessage()."\n".$ex->getTraceAsString()."\n\n");
            return array();
        }
    }

    private function _sort(&$rows) {
        $domains = array();
        $return = array();
        $pbls = new Pbls();
        //Since we want to sort them by domain_id first we will 
        //have to make an array for each domain id and then sort by pbl ref 
        foreach($rows as $row) {
            $domains[$row['domain_id']][] = $row; 
        }
        //comparePblRef() will sort values found in each domain id array
        //using pbl_ref key.
        //after sorting is done we merge each array with $return.
        //Easy isn't it !!
        foreach($domains as $key=>$value) {
            uasort($domains[$key], array($this, 'comparePblRef'));
            $return = array_merge($return, $domains[$key]);
        }
        return $return;
    }
    
    public function comparePblRef($a, $b) {
        return (float)$a['pbl_ref'] > (float)$b['pbl_ref'];
    }
    
    public function deletePblCoordinator($auto_id) {
        try {
            $auto_id = (int) $auto_id;
            if($auto_id > 0) {
                $pblCoordinator =  new PblCoordinator();
                $row = $pblCoordinator->delete('auto_id = '.$auto_id);
                //$row contains no of rows deleted.
                return ($row > 0) ? true : false;
            }
            return false;
        } catch(Exception $ex) {
            Zend_Registry::get('logger')->debug("\n".$ex->getMessage()."\n".$ex->getTraceAsString()."\n\n");
            return false;           
        }
    }
    
    public function pblCoordinatorExist($pbl, $uid, $domain) {
        try {
            $pblCoordinator = new PblCoordinator();
            $data = array(
                PblCoordinator::$COL_pbl_id     .' = ? '    => $pbl,
                PblCoordinator::$COL_uid        .' = ? '    => $uid,
                PblCoordinator::$COL_domain_id  .' = ? '    => $domain
            );
            $result = $pblCoordinator->fetchAll($data);
            return ($result->count() > 0) ? true : false;
        } catch(Exception $ex) {
            Zend_Registry::get('logger')->debug("\n".$ex->getMessage()."\n".$ex->getTraceAsString()."\n\n");
            return true;           
        }
        
    }
    
    public function addPblCoordinator($pbl, $uid, $domain) {
        try {
            $pblCoordinator = new PblCoordinator();
            $data =  array(
                PblCoordinator::$COL_pbl_id         => $pbl,
                PblCoordinator::$COL_uid            => $uid,
                PblCoordinator::$COL_domain_id      => $domain
            );
            return $pblCoordinator->addPblCoordinator($data);                
        } catch(Exception $ex) {
            Zend_Registry::get('logger')->debug("\n".$ex->getMessage()."\n".$ex->getTraceAsString()."\n\n");
            return false;
        }
    }
    
    public function getUidsForPblId($pblId) {
        try {
            $return = array();
            $pblId = (int) $pblId;
            if($pblId > 0) {
                $domainId = UserAcl::getDomainId();
                $pblCoordinator =  new PblCoordinator();
                $select = $pblCoordinator->select()->from($pblCoordinator, PblCoordinator::$COL_uid)
                                                        ->where(PblCoordinator::$COL_pbl_id. ' = ?',$pblId)
                                                        ->where(PblCoordinator::$COL_domain_id.' = ?',$domainId);
                return $pblCoordinator->getAdapter()->fetchCol($select);                                                        
            }
            return $return;
        } catch(Exception $ex) {
            Zend_Registry::get('logger')->debug("\n".$ex->getMessage()."\n".$ex->getTraceAsString()."\n\n");
            return array();           
        }
    }

}
?>