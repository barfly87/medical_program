<?php
class Compass_Controller_Plugin_AccessLogger extends Zend_Controller_Plugin_Abstract {
	public function routeShutdown(Zend_Controller_Request_Abstract $request) {
		$auth = Zend_Auth::getInstance();
		if (!$auth->hasIdentity()) {
			$uid = 'Guest';
		} else {
			$uid = $auth->getIdentity()->user_id;
		}
		$referer = (isset($_SERVER['HTTP_REFERER'])) ? $_SERVER['HTTP_REFERER'] : ' - ';
		Zend_Registry::get('access_logger')->info("{$_SERVER['REMOTE_ADDR']} - {$uid} - \"{$_SERVER['REQUEST_METHOD']} {$_SERVER['REQUEST_URI']} {$_SERVER['SERVER_PROTOCOL']}\" {$referer}");
	}
}