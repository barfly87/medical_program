<?php

/*
 * This class represents an SMS reply.
 */

class SmsReply {
	var	$phoneNumber;
	var	$message;
	var	$messageID;
	var	$when;
	var	$status;

		// Constructor.
	function SmsReply (
		$phoneNumber,
		$message,
		$messageID,
		$when,
		$status
	) {
		$this->phoneNumber = $phoneNumber;
		$this->message = $message;
		$this->messageID = $messageID;
		$this->when = $when;
		$this->status = $status;
	}

		// Unescape any escaped characters in the string.
	function unescape (
		$line
	) {
		if (strpos ($line, '\\') === false)
		    return $line;

		$res = "";
		$len = strlen ($line);

		for ($i = 0; $i < $len; $i++) {
		    $c = $line{$i};

		    if ($i + 1 < $len && $c == '\\') {
			$nc = $line{$i + 1};

			if ($nc == 'n') {
			    $c = "\n";
			    $i++;
			} elseif ($nc == 'r') {
			    $c = "\r";
			    $i++;
			} elseif ($nc == '\\')
			    $i++;
		    }

		    $res .= $c;
		}

		return $res;
	}

		// Parse a reply from a string.
	function parse (
		$line,
		$useMessageID
	) {
		$messageID = 0;
		$status = MessageStatus::none ();

			// Format is: messageID phone when message
			// Or if no message ID: phone when message
			// Or if delivery receipt: messageID messageStatus when
		$prevIdx = 0;
		if (($idx = strpos ($line, ' ')) === false)
		    return NULL;

			// Parse the message ID, if provided.
		if ($useMessageID) {
		    list ($messageID) = sscanf (substr ($line, 0, $idx), "%d");
		    if ($messageID === NULL)
			return NULL;

		    $prevIdx = $idx + 1;
		    if (($idx = strpos ($line, ' ', $idx + 1)) === false)
			return NULL;
		}

			// Parse the phone number.
		$phone = substr ($line, $prevIdx, $idx - $prevIdx);

			// If the phone is 1, 2, or 3, it's a message status.
		if ($phone == "1") {
		    $status = MessageStatus::pending ();
		    $phone = "";
		} elseif ($phone == "2") {
		    $status = MessageStatus::delivered ();
		    $phone = "";
		} elseif ($phone == "3") {
		    $status = MessageStatus::failed ();
		    $phone = "";
		}

		$prevIdx = $idx + 1;
		if (($idx = strpos ($line, ' ', $idx + 1)) === false)
		    $idx = strlen ($line);

			// Parse the time when the message was received.
		list ($when) = sscanf (substr ($line, $prevIdx,
						$idx - $prevIdx), "%d");
		if ($when === NULL)
		    return NULL;

			// The message is the remainder, unescaped.
		if ($status != MessageStatus::none ()
						|| strlen ($line) < $idx + 2)
		    $message = "";
		else
		    $message = SmsReply::unescape (substr ($line, $idx + 1));

		return new SmsReply ($phone, $message, $messageID, $when,
								$status);
	}
}