<?php
class SmsService {
	public static $smsaccounts = array(
			array("account" => "OME", "username" => "UniversityofS006", "password" => "668542","accessgroup" => "usydmpadmins"),
			array("account" => "Dentistry", "username" => "UniversityOfS011", "password" => "4bXcxDh8","accessgroup" => "dadmin"),
			array("account" => "IT", "username" => "UniversityofS027", "password" => "7136668","accessgroup" => array("cso")),
			array("account" => "Old IT", "username" => "usydmed001", "password" => "eng1arg0","accessgroup" => "daniel"),
			array("account" => "Northern Clinical School", "username" => "UniversityofS028", "password" => "6893151","accessgroup" => array("smstoolusers", "rnshusers"))
		);
	
	public static function sendsms($mobile, $message, $smsaccount, $proxyhost, $proxyport, $proxyaccount, $proxypassword) {
		$si = new SmsInterface (2, false);
		$si->addMessage($mobile, $message);

		if (!empty($proxyhost) && !empty($proxyport)) {
			$si->setHttpProxy($proxyhost, $proxyport, $proxyaccount, $proxypassword);
		}
		if (!$si->connect($smsaccount["username"], $smsaccount["password"], true, false)) {
	    	return("failed. Could not contact server (".$si->getResponseCode().": ".$si->getResponseMessage().").\n");
		} elseif (!$si->sendMessages ()) {
	    	return("failed. Could not send message to server(".$si->getResponseCode().": ".$si->getResponseMessage().").\n");
		}
		return $si->getResponseMessage();
	}
	
	
	public static function sendEmail($to, $subject, $body, $from) {
		$transport = new Zend_Mail_Transport_Smtp('localhost');
		Zend_Mail::setDefaultTransport($transport);
		$host = $_SERVER['HTTP_HOST'];
		$base_url = Compass::baseUrl();
		$new_body_text = <<<NEWBODYTXT
$body<br/><br/>=========================================<br/>
To get these messages via SMS, please add your mobile number to your profile page by going to http://{$host}{$base_url}/people/view.<br/>
Click Edit my details link, and add your mobile phone number into the "Mobile Phone" field.
It needs to be in the correct format: +61 XXX XXX XXX. Remember to remove the leading 0 from the number.<br/><br/>
If you have any problems, please contact Christiana Katalinic: christiana.katalinic@sydney.edu.au.
NEWBODYTXT;
		$mail = new Zend_Mail('utf-8');
		$mail->addHeader('Reply-To', $from['address']);
		$mail->addHeader('X-Mailer', "PHP/".phpversion());
		$mail->addTo($to['address'], $to['name']);
		$mail->setSubject($subject);
		$mail->setBodyHtml($new_body_text);
		$mail->setBodyText(strip_tags($body, '<a>'));
		$mail->setFrom($from['address'], $from['name']);
		$mail->send();
	}
	
	public function sendAlertEmail($to, $subject, $body, $from, $smsaccount, $groups) {
		$transport = new Zend_Mail_Transport_Smtp('localhost');
		Zend_Mail::setDefaultTransport($transport);
		$new_body_text = <<<NEWBODYTXT
Alert message sent<br/>
Sender: {$from['name']}<br/>
Sender Email: {$from['address']}<br/>
Account: {$smsaccount["account"]}<br/>
Groups: $groups<br/>
Message: $body<br/>
NEWBODYTXT;
		$mail = new Zend_Mail('utf-8');
		$mail->addHeader('Reply-To', $from['address']);
		$mail->addHeader('X-Mailer', "PHP/".phpversion());
		$mail->addTo($to['address'], $to['name']);
		$mail->setSubject($subject);
		$mail->setBodyHtml($new_body_text);
		$mail->setBodyText(strip_tags($body, '<a>'));
		$mail->setFrom($from['address'], $from['name']);
		$mail->send();
	}
}
?>