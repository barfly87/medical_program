<?php
class FormProcessor_LinkResources extends FormProcessor {
    
    public $query                   = '';
    public $results                 = array();
    public $collectionIds           = array();
    public $qoption                 = 'any';
    
    private $_luceneQuery           = '';
    private $_luceneCollectionIds   = array();
    private $_mediabank;
    private $_index                 = 'Lucene Index';
    private $_searchRemote          = true;
    
    public function __construct() {
        parent::__construct();
        $mediabankConstants = new MediabankConstants();
        $this->_mediabank = $mediabankConstants->mediabank;
        
    }
    
    public function process(Zend_Controller_Request_Abstract $request) {
        $action             = $request->getActionName();
        $type               = $request->getParam('type');
        $resourceTypeId     = $request->getParam('resourcetypeid',0);
        $collectionIds      = $request->getParam('collectionIds',array());
        $qoption            = $request->getParam('qoption', 'any');
        $query              = stripslashes(trim($request->getParam('query')));
        $id                 = $request->getParam('id');
        
        
        foreach($collectionIds as $collectionId) {
            if(strlen(trim($collectionId)) > 0 && stristr($collectionId,'Any') === false) {
                $this->_luceneCollectionIds[] = $this->sanitize($collectionId);
            }
        }
        $this->collectionIds    = $collectionIds;
        $this->query            = $query;
        $this->qoption          = $qoption;
        $this->_luceneQuery     = $this->query;
        
        $idExist = false;
        $columns = 6;
        if($id == 'new') {
            $idExist = true;
            $columns = 4;
        } else {
            $id = (int)$id;
            if($id > 0) {
                $idExist = true;
            }
        }
        
        if($idExist && !empty($query) && in_array($type, ResourceConstants::$TYPES_allowed)) {
            $resourcesMids = $this->_getResourcesAttachedToId($id, $type, $resourceTypeId);
            $resourceService = new MediabankResourceService();
            $user = MediabankResourceConstants::getMediabankuserObjForSearch();
            $this->_luceneQuery      = $this->_getQueryString();
            $results = $this->_mediabank->search($this->_index, $this->_luceneQuery, $user, $this->_searchRemote);
            if(is_array($results) && count($results) > 0) {
                $return = array();
                $addedResources = array();
                $resourceCount = 1;
                foreach($results as $key => $searchResultObject) {
                    $mepositoryId = $searchResultObject->mepositoryID;
                    $attributes = $searchResultObject->attributes;
                    $allowRead = $resourceService->allowRead($attributes['mid']);
                    if($allowRead == true) {
                        if(in_array($attributes['mid'], $resourcesMids)) {
                            $return[$key]['selected'] = 1;
                            array_push($addedResources,$resourceCount);
                        }
                        $return[$key]['mid']        = MediabankResourceConstants::encode($attributes['mid']);
                        $return[$key]['title']      = (trim(''.$attributes['title'].'') == '') ? 'Untitled' : trim($attributes['title']);
                        $return[$key]['collection'] = $mepositoryId->collectionID;
                        $return[$key]               = array_map('htmlspecialchars', $return[$key]);
                        $description                = $attributes['description'];
                        
                        if(strlen($description) > 30) {
                            $return[$key]['description30chars'] = substr($description, 0, 30) . ' ...';
                            if(strlen($description) > 300) {
                                $return[$key]['description300chars'] = substr($description, 0, 300);
                                $return[$key]['description300chars'] .= <<<ADD
                                    <br />.......................
                                    <br />.......................
                                    <br />
                                    <i>Click "more_info" link to see the remaining text of the description</i>
ADD;
                            } else {
                                $return[$key]['description300chars'] = $description;
                            }
                        } else {
                            $return[$key]['description30chars']     = $description;
                            $return[$key]['description300chars']    = $description;
                        }     
                        $return[$key]['description30chars'] = str_replace('"','&quot;',$return[$key]['description30chars']);
                        $return[$key]['description300chars'] = str_replace('"','&quot;',$return[$key]['description300chars']);
                        $resourceCount++;
                    }
                }
                if(count($return) > 0) {
                    $return = array_chunk($return, $columns);
                    $this->results = $return;
                }
                $this->addedResources = $addedResources;
                $this->resourceCount = $resourceCount - 1; //Because its not initialized as '0' but '1'
            }
        }
   }
   
   private function _getQueryString() {
       $return = '';
       if ($this->_luceneQuery != '') {
           if ($this->qoption == 'all') {
               $this->_escapeLuceneCharacters();
               $return .= ' +(';
               $strArr = explode(' ', $this->_luceneQuery);
               foreach ($strArr as $str) {
                   $return .= ' +'.$str;
               }
               $return .= ')';
           } else if ($this->qoption == 'any') {
               $this->_escapeLuceneCharacters();
               $return .= ' +('. str_replace(' ' , ' OR ' ,trim($this->_luceneQuery)) . ')';
           } else if ($this->qoption == 'lucene') {
               $return .= ' +('.$this->_luceneQuery. ')';
           } else {
               $this->_escapeLuceneCharacters();
               $return .= ' +"'.$this->_luceneQuery. '"';
           }
       }
       if(!empty($this->_luceneCollectionIds)) {
           // +collectionID:('compassresources' OR 'cmsdocs-smp')
           $return .= sprintf(" +%s:('%s')", MediabankResourceConstants::$LUCENEFIELD_collectionID,
                                implode("' OR '", $this->_luceneCollectionIds)
                     );
       }
       return $return;
   }
   
   private function _escapeLuceneCharacters() {
       //Note:: '\\' is actually '\' and it needs to be the first in the array list 
       //       otherwise already slashed elem \! would become \\! which is wrong
       $escapeChars = array('\\','+', '-', '&&', '||', '!', '(', ')', '{', '}', '[', ']', '^', '"', '~', '*', '?', ':');
       foreach($escapeChars as $escapeChar) {
           if(strstr($this->_luceneQuery, $escapeChar)) {
               $this->_luceneQuery = str_replace($escapeChar,'\\'.$escapeChar, $this->_luceneQuery);
           }
       }
       return $this->_luceneQuery;
   }
   
   private function _getResourcesAttachedToId($id, $type, $resourceTypeId){
        $resource = new MediabankResource();
        $resourcesAttached = $resource->getResourcesByType($id, $type, $resourceTypeId);
        if($resourcesAttached != false) {
            $result = array();
            foreach($resourcesAttached as $resourceAttached) {
                if(isset($resourceAttached['resource_id']) && (strlen(trim($resourceAttached['resource_id']))) > 0 ) {
                    array_push($result,trim($resourceAttached['resource_id']));
                }
            }
            return $result;
        }
        return array();
   }
}
?>