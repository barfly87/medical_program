<?php

require_once 'Mepositoryid.php';
require_once 'MediabankUser.php';
require_once 'SearchResult.php';

class Mediabank {

    private $wsdlUrl;
    private $soapClient;
    private $repository; // e.g. http://mediabox.med.usyd.edu.au:8080/mepository

    function __construct($repository="http://localhost:8080/mepository/",$wsdlUrl="http://localhost:8080/mepository/cxfws/Core?wsdl") {
        ini_set('soap.wsdl_cache_enabled', '1');
        ini_set('soap.wsdl_cache_ttl', '3600');
        $this->wsdlUrl = $wsdlUrl;
        $this->repository = $repository;
        try {
            $this->soapClient = new SoapClient($this->wsdlUrl, array("trace" => true, "classmap" => array('MepositoryID' => 'MepositoryID','User' => 'User')));
        } catch(Exception $ex) {
            Zend_Registry::get('logger')->warn($ex->getMessage());
        }
    }

    function getRepositoryID() {
        try {
            $result = $this->soapClient->getRepositoryID()->return->repositoryID;
            return $result;
        } catch(Exception $ex) {
            $this->logErrors($ex);
        }
    }

    function getRepositoryMID() {
        try {
            return new MepositoryID($this->soapClient->getRepositoryID()->return->repositoryID,null,null);
        } catch(Exception $ex) {
            $this->logErrors($ex);
        }
    }

    function getMetadataSchema($collectionID,$user) {
        try {
            if (!$collectionID instanceof MepositoryID) {
                $collectionID = new MepositoryID($this->repository,$collectionID,null);
            }
            if (!$user instanceof MediabankUser) {
                $user = new MediabankUser($frontend="PHP Frontend",$username=$user,$roles="",$mepositoryID=$collectionID);
            }
            if(isset($this->soapClient)) {
                return $this->soapClient->getMetadataSchema(array("arg0" => $collectionID,"arg1" => $user))->return;
            }
        } catch(Exception $ex) {
            $this->logErrors($ex);
        }
        
    }

    function getMetadata($objectID,$schema,$user) {
        try {        
            if (!$user instanceof MediabankUser) {
            	$user = new MediabankUser($frontend="PHP Frontend",$username=$user,$roles="",$mepositoryID=$objectID);
            }
            if(is_string($objectID)) {
                $objectID = new MepositoryID($objectID);
            }
            
            if(isset($this->soapClient)) {
                return $this->soapClient->getMetadata(array("arg0" => $objectID,"arg1" => $schema,"arg2" => $user))->return;
            }
        } catch(Exception $ex) {
            $this->logErrors($ex);
        }
    }
    
    function getObject($objectID,$user) {
        try {        
            if (!$user instanceof MediabankUser) {
                $user = new MediabankUser($frontend="PHP Frontend",$username=$user,$roles="",$mepositoryID=$objectID);
            }
            if(isset($this->soapClient)) {
                return $this->soapClient->getObject(array("arg0" => $objectID,"arg1" => $user))->return;
            }
        } catch(Exception $ex) {
            $this->logErrors($ex);
        }
    }
    
    function getMetadataObject($objectID,$schema,$user) {
        try {        
            if (!$user instanceof MediabankUser) {
                $user = new MediabankUser($frontend="PHP Frontend",$username=$user,$roles="",$mepositoryID=$objectID);
            }
            if(isset($this->soapClient)) {
                return $this->soapClient->getMetadataObject(array("arg0" => $objectID,"arg1" => $schema,"arg2" => $user))->return;
            }
        } catch(Exception $ex) {
            $this->logErrors($ex);
        }
    }    

    function checkAccess($user, $right, $objectID) {
        try {
            if (!$user instanceof MediabankUser) {
                $user = new MediabankUser($frontend="PHP Frontend",$username=$user,$roles="",$mepositoryID=$objectID);
            }
            if(is_string($objectID)) {
                $objectID = new MepositoryID($objectID);
            }
            if(isset($this->soapClient)) {
                return $this->soapClient->checkAccess(array('arg0'=>$user, 'arg1'=>$right, 'arg2'=>$objectID))->return;
            }
        } catch(Exception $ex){
            if(is_string($objectID)) {
                $objectID = new MepositoryID($objectID);
            }
            $customErrors = array ('user' => $user,'right' => $right,'objectID' => $objectID);
            $this->logErrors($ex, $customErrors);
            return false;
        }
    } 
    
    function getCollection($collectionID) {
        try {
            if (!$collectionID instanceof MepositoryID) {
                $collectionID = new MepositoryID($this->repository,$collectionID,null);
            }
            if(isset($this->soapClient)) {
                $result = $this->soapClient->getCollection(array("arg0" => $collectionID))->return;
                return array("description" => $result->description, "objectPrimary" => $result->objectPrimary);
            }
        } catch(Exception $ex) {
            $this->logErrors($ex);
        }
    }

    function listCollection($collectionID,$user) {
        try {
            if (!$collectionID instanceof MepositoryID) {
                $collectionID = new MepositoryID($this->repository,$collectionID,null);
            }
            if (!$user instanceof MediabankUser) {
                $user = new MediabankUser($frontend="PHP Frontend",$username=$user,$roles="",$mepositoryID=$collectionID);
            }
            //Mediabank does not like objectID been send as empty or null when calling listCollection            
            unset($collectionID->objectID);
            return MepositoryID::CreateArrayFromSOAP($this->soapClient->listCollection(array("arg0" => $collectionID,"arg1" => $user)));
        } catch(Exception $ex) {
            $this->logErrors($ex);
        }
    }

    function getSchemas($collectionID) {
        try {
            if (!$collectionID instanceof MepositoryID) {
                $collectionID = new MepositoryID($this->repository,$collectionID,null);
            }
            if(isset($this->soapClient)) {
                return $this->soapClient->getSchemas(array("arg0" => $collectionID))->return;
            }
        } catch(Exception $ex) {
            $this->logErrors($ex);
        }
    }

    function toString() {
        try {
            if(isset($this->soapClient)) {
                return $this->soapClient->toString()->return;
            }
        } catch(Exception $ex) {
            $this->logErrors($ex);
        }
    }

    function getSchemaPrefix($schema) {
        try {
            if(isset($this->soapClient)) {
                return $this->soapClient->getSchemaPrefix(array("arg0" => $schema))->return;
            }
        } catch(Exception $ex) {
            $this->logErrors($ex);
        }
    }

    function getSearchFields() {
        try {
            if(isset($this->soapClient)) {            
                return $this->soapClient->getSearchFields()->return;
            }
        } catch(Exception $ex) {
            $this->logErrors($ex);
        }
    }

    function search($index, $query, $user, $searchRemote) {
        try {
            if(isset($this->soapClient)) {
                $results = $this->soapClient->search(array("arg0" => $index, "arg1" => $query, "arg2" => $user, "arg3" => $searchRemote));
                if(isset($results->return)) {
                    return SearchResult::CreateArrayFromSOAP($results->return);
                }
            }
            return false;
        } catch(Exception $ex) {
            $this->logErrors($ex);
        }
    }

    function getCollectionMIDs($repositoryID=null) {
        try {
            if(isset($this->soapClient)) {
                if ($repositoryID == null) {
                    $mIDs = MepositoryID::CreateArrayFromSOAP($this->soapClient->getCollectionIDs());
                }
                elseif ($repositoryID instanceof MepositoryID) {
                    $mIDs = MepositoryID::CreateArrayFromSOAP($this->soapClient->getCollectionIDs1(array("arg0" => $repositoryID)));
                }
                elseif (is_string($repositoryID)) {
                    $repositoryID = new MepositoryID($repositoryID);
                    $mIDs = MepositoryID::CreateArrayFromSOAP($this->soapClient->getCollectionIDs1(array("arg0" => $repositoryID)));
                }
            }
            // else I don't know what to do with it.
            return $mIDs;
        } catch(Exception $ex) {
            $this->logErrors($ex);
        }
    }

    private function getCollectionID($mID) {
        try {
            return $mID->collectionID;
        } catch(Exception $ex) {
            $this->logErrors($ex);
        }
    }

    function getCollectionIDs($repositoryID=null) {
        try {
            return array_map($this->getCollectionID, $this->getCollectionMIDs($repositoryID));
        } catch(Exception $ex) {
            $this->logErrors($ex);
        }
    }

    function getSoapFunctions() {
        try {
            if(isset($this->soapClient)) {
                return $this->soapClient->__getFunctions();
            }
        } catch(Exception $ex) {
            $this->logErrors($ex);
        }
    }

    function urlForItem($collection, $objectId, $fileNum) {
        try {
            return $this->repository."REST/getObject:file=".$fileNum."?mid=".$this->repository."|".$collection."|".$objectId;
        } catch(Exception $ex) {
            $this->logErrors($ex);
        }
    }

    function urlForTransform($collection, $objectId, $fileNum, $transform) {
        try {
            return $this->repository."REST/getObject/transform:file=".$fileNum.$transform."?mid=".$this->repository."|".$collection."|".$objectId;
            //    http://mediabox.med.usyd.edu.au:8080/mepository/REST/getObject/transform/getimage/resize:width=64:height=128:format=jpg?mid=http://mediabox.med.usyd.edu.au:8080/mepository/|pathology|9_54_1_Rgt
        } catch(Exception $ex) {
            $this->logErrors($ex);
        }
    }

    function logErrors(Exception $ex = null, $customErrors = array()){
        $error = '';
        if($ex != null) {
            $error .= PHP_EOL.PHP_EOL.$ex->getTraceAsString().PHP_EOL;
        }
        
        $logs = array (
            'PHP Class:'                    => get_class($this).PHP_EOL,
            'SOAP Last Request Headers:'    => $this->soapClient->__getLastRequestHeaders(),
            'SOAP Last Request:'			=> $this->soapClient->__getLastRequest(),
            'SOAP Response Headers:'		=> $this->soapClient->__getLastResponseHeaders(),
            'SOAP Response:'				=> $this->soapClient->__getLastResponse(),
        );
        
        if(is_array($customErrors) && count($customErrors) > 0) {
            $error .= PHP_EOL.'Custom Errors:'.PHP_EOL;
            foreach($customErrors as $customErrorName => $customErrorValue) {
                $error .= $customErrorName.' => '.$customErrorValue.PHP_EOL;
            }
            $error .= PHP_EOL;
        }
        
        foreach($logs as $key => $value ) {
            $error .= $key.PHP_EOL.$value.PHP_EOL;
        }
        Zend_Registry::get('logger')->warn($error);
    }
    
}

?>
