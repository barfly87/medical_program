<?php

class MediabankResource extends Zend_Db_Table_Abstract {
    protected $_name = 'lk_resource';

    public function addResource($type, $typeId, $resourceTypeId, $mid, $resourceType = null) {
        try {        
            $typeId = (int)$typeId;
            if(is_null($resourceType)) {
                $resourceType = ResourceConstants::$TYPE_MEDIABANK;
            }
            $mid = MediabankResourceConstants::sanitizeMid($mid);
            
            $idExist = $this->idExist($typeId, $type);
            if($idExist == false) {
                $error = "Error when adding resource in lk_resource table. (id=>'$typeId' and type=>'$type' does not exist)";
                Zend_Registry::get('logger')->warn($error);
                return false;
            }  
            
            $resourceExist = $this->resourceExist($type, $typeId, $resourceTypeId, $mid, $resourceType);
            if($resourceExist == true) {
                $error = "Error when adding resource in lk_resource table. Row already exists in lk_resource table for ";
                $error .= "type=>'%s', type_id=>'%s',resource_type_id=>'%s', mid=>'%s' and resource_type=>'%s'";
                $error = sprintf($error,$type, $typeId, $resourceTypeId, $mid, $resourceType);
                Zend_Registry::get('logger')->warn($error);
                return false;
            }
            $select = $this->select()
                        ->from($this, 'order_by')
                        ->where("type = ?", $type)
                        ->where("type_id = ?", $typeId)
                        ->where("resource_type_id = ?", $resourceTypeId)
                        ->where("resource_type = ? ", $resourceType)
                        ->order("order_by DESC")
                        ->limit(1);
            $row = $this->fetchRow($select);
            $order_by = 0;
            if(!is_null($row)) {
                $order_by = $row->order_by;
            }
            $data = array (
                'type'              => $type,
                'type_id'           => $typeId,
                'resource_id'       => $mid,
                'resource_type'     => $resourceType,
                'order_by'		    => ++$order_by,
                'resource_type_id'  => $resourceTypeId
            );
            $result = $this->insert($data);
            
            if($result != false) {
                $history = new MediabankResourceHistory();
                $setHistory = $history->setHistory($result,'add');
                return true; 
            }
            return $result;
        } catch (Exception $e) {
            Zend_Registry::get('logger')->warn($e->getMessage());
            return false;
        }    
    }
    
    /**
     * 
     * @param $resourceAutoId
     * @param $update should be a key=>value pair of columnnames=>value to be updated
     * @return boolean
     */
    public function updateResource($resourceAutoId, $update = array()) {
        try {     
            $return = false;
            $resourceAutoId = (int)$resourceAutoId;   
            if($resourceAutoId > 0 && !empty($update)) {
                $where = $this->getAdapter()->quoteInto('auto_id = ?', $resourceAutoId);
                $val = $this->update($update, $where);
                $return = true;
            }
            return $return;
        } catch (Exception $e) {
            Zend_Registry::get('logger')->warn($e->getMessage());
            return false;
        }    
    }

    public function getOrderBy($type,$type_id) {
        try {
            $select = $this->select()
                        ->from($this, 'max(order_by)')
                        ->where("type_id = '".$type_id."' and type='".$type."' and resource_type='".ResourceConstants::$TYPE_MEDIABANK."'")
                        ->limit(1);
            $row = $this->fetchRow($select);
            if(is_null($row->max)) {
                return 1;
            } else {
                if((int)$row->max > 0) {
                    return (int)$row->max + 1;
                }
                return 100;
            }
        } catch (Exception $e) {
            Zend_Registry::get('logger')->warn($e->getMessage());
            return 100;
        }    
        
    }
    
    public function idExist($typeId, $type){
        try {
            $typeId = (int)$typeId;
            if($typeId > 0 && in_array($type, ResourceConstants::$TYPES_allowed)) {
                switch($type) {
                    case 'lo':
                        $table = new LearningObjectives();
                    break;
                    case 'ta':
                        $table =  new TeachingActivities();
                    break;
                    case 'pbl':
                        $table =  new Pbls();
                    break;
                    case 'block':
                        $table =  new Blocks();
                    break;
                    
                }
                $where = "auto_id = '".$typeId."'";
                $result = $table->fetchRow($where);
                if($result != false) {
                    return true;
                }
            }
            return false;
        } catch (Exception $e) {
            Zend_Registry::get('logger')->warn($e->getMessage());
            return false;
        }    
    }

    public function resourceExist($type, $typeId, $resourceTypeId, $mid, $resourceType){
        try{
            $select = $this->select()
                            ->where("type = ?", $type)
                            ->where("type_id = ?", (int)$typeId)
                            ->where("resource_type_id = ?", $resourceTypeId)
                            ->where("resource_id = ?", $mid)
                            ->where("resource_type = ? ", $resourceType);
            $result = $this->fetchRow($select);
            if($result != false) {
                return true;
            }
            return false;
        } catch (Exception $e) {
            Zend_Registry::get('logger')->warn($e->getMessage());
            return false;
        }    
    }

    public function getMid($typeId, $type,$auto_id){
        $resource = $this->getResource($typeId,$type,$auto_id);
        if($resource !== false) {
            return $resource[0]['resource_id'];
        }   
        return false; 
    }
    
    public function getResource($typeId, $type,$auto_id){
        try{        
            $typeId = (int)$typeId;
            $auto_id = (int)$auto_id;
            if($typeId > 0 && $auto_id > 0 && in_array($type, ResourceConstants::$TYPES_allowed)) {
                $select = $this->select()
                            ->from($this)
                            ->where("auto_id = '".$auto_id."' and type_id = '".$typeId."' and type='".$type."'");
                $result = $this->fetchAll($select);
                if($result != false) {
                    return $result->toArray();
                }
            }        
            return false;
        } catch (Exception $e) {
            Zend_Registry::get('logger')->warn($e->getMessage());
            return false;
        }    
    }
    
    public function getResourcesByType($typeId, $type, $resource_type_id) {
    	Zend_Registry::get('logger')->info(__METHOD__." $typeId, $type, $resource_type_id");
    	try{        
            $typeId = (int)$typeId;
            if($typeId > 0 && in_array($type, ResourceConstants::$TYPES_allowed)) {
                $select = $this->select()
                            ->where("type_id = ?", $typeId)
                            ->where("type = ?", $type)
                            ->where("resource_type = ? ", ResourceConstants::$TYPE_MEDIABANK)
                            ->order("order_by");
                if(is_array($resource_type_id)) {
                    $expression = new Zend_Db_Expr('resource_type_id in ('.implode(',', $resource_type_id).')');
                    $select->where($expression->__toString());
                } else {
                    $select->where("resource_type_id = ?", $resource_type_id);
                }                            
                $result = $this->fetchAll($select);
                if($result->count() > 0) {
                    return $result->toArray();
                }
            }        
            return false;
        } catch (Exception $e) {
            Zend_Registry::get('logger')->warn($e->getMessage());
            return false;
        }
    }
    
    public function getResources($type_id, $type){
        try{        
            $type_id = (int)$type_id;
            if($type_id > 0 && in_array($type, ResourceConstants::$TYPES_allowed)) {
                $select = $this->select()
                            ->from($this)
                            ->where("type_id = '".$type_id."' and type='".$type."'")
                            ->order(array('order_by ASC','resource_type_id','order_by'));
                $result = $this->fetchAll($select);
                if($result != false) {
                    return $result->toArray();
                }
            }        
            return false;
        } catch (Exception $e) {
            Zend_Registry::get('logger')->warn($e->getMessage());
            return false;
        }    
    }
    
    public function removeResource($typeId, $mid, $type) {
        try{        
            /*
             * @TODO KAMAL SONI
            $resourceExist = $this->resourceExist($typeId,$mid,$type);
            if($resourceExist == false) {
                return false;
            }*/
            $where = "type_id='".$typeId."' and resource_id='".$mid."' and type='".$type."'";
            $select = $this->select()->from($this)->where($where);
            $row = $this->fetchRow($select);
            if(! is_null($row)) {
                $history = new MediabankResourceHistory();
                $setHistory = $history->setHistory($row->auto_id, 'delete');
                $deleted = $this->delete($where);
                if($deleted > 0) {
                    return true;
                }
            }
            return false;
        } catch (Exception $e) {
            Zend_Registry::get('logger')->warn($e->getMessage());
            return false;
        }    
    }
    
    public function getResourcesAttachedToMid($mid){
        try{        
            $select = $this->select()
                        ->from($this)
                        ->where("resource_id='".$mid."'");
            $result = $this->fetchAll($select);
            if($result != false) {
                return $result->toArray();
            }
            return false;
        } catch (Exception $e) {
            Zend_Registry::get('logger')->warn($e->getMessage());
            return false;
        }    
    }
   
    public function processSorting($data) {
        try {
            $result = 'fail';
            if(is_array($data) && count($data) > 0) {
                foreach($data as $elem) {
                    $split = explode('_',$elem);
                    if((int)$split[0] > 0 &&  (int)$split[1] > 0 ) {
                        $orderNo = (int)$split[0];
                        $autoId = (int)$split[1];
                        $this->updateResource($autoId, array('order_by' => new Zend_Db_Expr($orderNo))); 
                    }
                }
                $result = 'success';
            }
            return $result;
        } catch (Exception $e) {
            Zend_Registry::get('logger')->warn($e->getMessage());
            return 'fail';
        }    
        
    }
    
    public function getPblSessionTas() {
        try {
            $pblsessionTypeId = 4;
            $query = <<<QUERY
select * from teachingactivity 
    where type = ? and sequence_num in (2,3,4)
order by pbl, sequence_num, auto_id;
QUERY;
            $rows = $this->getAdapter()->query($query, array($pblsessionTypeId))->fetchAll();
            return $rows;
            
        } catch (Exception $ex) {
        	$error = $ex->getMessage().PHP_EOL.$ex->getTraceAsString().PHP_EOL;
        	Zend_Registry::get('logger')->warn(PHP_EOL."CLASS\t: ".__CLASS__.PHP_EOL."METHOD\t: ".__METHOD__.PHP_EOL."Error\t: ".$error.PHP_EOL);
            return array();
        }            
    }
    
    public function getTableName() {
        return $this->_name;
    }
 
}