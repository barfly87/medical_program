<?php  
 
class ServiceController extends Zend_Controller_Action {
	public static $token = 'abc123';
	public static $error = array(                                
		'typeNotSpecified' => array('error' => 'Type is empty or not correctly supplied.'),
		'recordsNotFound' => array ('error' => 'Your query did not returned any results.'),
		'exception' => array('error' => 'Caught an Unknown Exception.'),
		'usernameEmpty' => array('error' => 'Username is empty.'),
		'cannotAuthenticate' => array('error' => 'Authentication failed.'),
		'invalidOwnerId' => array('error' => 'Invalid owner id'),
		'invalidTAId' => array('error' => 'Invalid teaching activity id'),
		'invalidCalendarYear' => array('error' => 'Invalid calendar year'),
		'invalidBlockNo' => array('error' => 'Invalid block number'),
		'invalidWeekNo' => array('error' => 'Invalid week number'),
		'invalidSeqNo' => array('error' => 'Invalid sequence number'),
		'invalidActivityType' => array('error' => 'Invalid activity type'),
		'invalidLuceneResultContext' => array('error' => "Invalid or missing context. Allowed values are 'lo' and 'ta'."),
		'invalidLuceneQuery' => array('error' => "Invalid query."),
	    'evaluationCommentEmpty' => array('error' => 'Comment is empty.'),
	    'evaluationTypeEmpty' => array('error' => 'Type is empty.'),
	    'evaluationTypeIdEmpty' => array('error' => 'Type Id is empty.'),
	    'evaluationUidEmpty' => array('error' => 'uid is empty.'),
		'luceneResultsException' => array('error' => "Caught an Unknown Exception. One of the reasons might be invalid lucene query.")
    );
	
	/**
	 * Set up ACL info
	 */
	public function init() {
		$serviceActions = array('all', 'userdetail', 'rest');
		$this->_helper->_acl->allow('guest', $serviceActions);
		$staffActions = array('allusers');
		$this->_helper->_acl->allow('staff', $staffActions);
	}
	
	public function allusersAction() {
        $this->_helper->viewRenderer->setNoRender();
        $this->getHelper('layout')->disableLayout();
        $config = Zend_Registry::get('config');
        $cacheFolder = dirname($config->index_folder);
        $fp = fopen($cacheFolder .'/cache/ldapusers.txt', 'r');
        fpassthru($fp);
        fclose($fp);
	}
	
	public function userdetailAction() {
		$request = $this->getRequest();
        $this->_helper->viewRenderer->setNoRender();
        $this->getHelper('layout')->disableLayout();
        $uid = $request->getParam("uid");
        if (!isset($uid))
    		echo "N/A";
        $result = "";
        $ds = Zend_Registry::get('ds');
        $detail = $ds->getUser($uid, array('chsedupersonsalutation', 'cn', 'mail', 'telephonenumber'));
        if (isset($detail['chsedupersonsalutation'][0]))
        	$result .= "{$detail['chsedupersonsalutation'][0]} ";
        $result .= "{$detail['cn'][0]}<br/>";
        if (isset($detail['mail'][0]))
        	$result .= "E: {$detail['mail'][0]}<br/>";
        if (isset($detail['telephonenumber'][0]))
        	$result .= "T: {$detail['telephonenumber'][0]}";
        echo $result;
	}
	
	public function allAction() {
		ini_set('soap.wsdl_cache_enabled', '0');
        $request = $this->getRequest();
        $wsdl = $request->getParam('wsdl', null); 
        if (isset($wsdl)) {
            $autodiscover = new Zend_Soap_AutoDiscover();
            $autodiscover->setClass('WebService');
            $autodiscover->handle();
        } else {
            $wsdlLink = 'http://' . $_SERVER['HTTP_HOST'] . Compass::baseUrl() .'/service/all?wsdl';
            $soap = new Zend_Soap_Server($wsdlLink);
            $soap->setClass('WebService');
            $soap->handle();
        }
        exit;
	}
	
	public function restAction() {
		$server = new Zend_Rest_Server();
		$server->setClass('RestService');
		$server->handle();
		exit;
	}
}