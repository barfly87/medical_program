<?php

/*
 * This class represents an SMS message.
 */

class SmsMessage {
	var	$phoneNumber;
	var	$message;
	var	$messageID;
	var	$delay;
	var	$validityPeriod;
	var	$deliveryReport;

		// Constructor.
	function SmsMessage (
		$phoneNumber,
		$message,
		$messageID,
		$delay,
		$validityPeriod,
		$deliveryReport
	) {
		$this->phoneNumber = $phoneNumber;
		$this->messageID = $messageID;
		$this->delay = $delay;
		$this->validityPeriod = $validityPeriod;
		$this->deliveryReport = $deliveryReport;

			// Escape newlines and backslashes.
		$this->message = "";
		for ($i = 0; $i < strlen ($message); $i++) {
		    $c = $message{$i};

		    if ($c == "\n")
			$this->message .= '\n';
		    elseif ($c == "\r")
			$this->message .= '\r';
		    elseif ($c == "\\")
			$this->message .= '\\\\';
		    else
			$this->message .= $c;
		}
	}
}