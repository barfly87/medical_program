<?php

/*
 * This is the main class used to interface with the M4U SMS messaging
 * server.
 */

class SmsInterface {
	var	$allowSplitting;
	var	$allowLongMessages;
	var	$responseCode;
	var	$responseMessage;
	var	$username;
	var	$password;
	var	$useMessageID;
	var	$secure;
	var	$httpConnection;
	var	$serverList;
	var	$messageList;
	var	$httpProxy;
	var	$httpProxyPort;
	var	$httpProxyAuth;
	var	$httpsProxy;
	var	$httpsProxyPort;
	var	$httpsProxyAuth;
	var	$textBuffer;


	/*
	 * Constructor.
	 *
	 * The allowSplitting parameter determines whether messages over
	 * 160 characters will be split over multiple SMSes or truncated.
	 *
	 * The allowLongMessages parameter enables messages longer than 160
	 * characters to be sent as special concatenated messages. For this
	 * to take effect, the allowSplitting parameter must be set to false.
	 */
	function SmsInterface (
		$allowSplitting = false,
		$allowLongMessages = false
	) {
		$this->allowSplitting = $allowSplitting;
		$this->allowLongMessages = $allowLongMessages;
		$this->responseCode = -1;
		$this->responseMessage = NULL;
		$this->useMessageID = false;
		$this->messageList = array ();
		$this->httpConnection = NULL;
		$this->httpProxy = NULL;
		$this->httpProxyPort = 80;
		$this->httpProxyAuth = NULL;
		$this->httpsProxy = NULL;
		$this->httpsProxyPort = 443;
		$this->httpsProxyAuth = NULL;
		$this->textBuffer = NULL;

		$this->serverList = array ("smsmaster.m4u.com.au",
			"smsmaster1.m4u.com.au", "smsmaster2.m4u.com.au");
	}

		// Set the HTTP proxy server, if one is being used.
		// Also specify an optional proxy username and password.
	function setHttpProxy (
		$proxy,
		$port = 80,
		$username = NULL,
		$password = NULL
	) {
		$this->httpProxy = $proxy;
		$this->httpProxyPort = $port;

		if ($username != NULL && $password != NULL)
		    $this->httpProxyAuth = base64_encode (
						"$username:$password");
	}

		// Set the HTTPS proxy server, if one is being used.
		// Also specify an optional proxy username and password.
	function setHttpsProxy (
		$proxy,
		$port = 443,
		$username = NULL,
		$password = NULL
	) {
		$this->httpsProxy = $proxy;
		$this->httpsProxyPort = $port;

		if ($username != NULL && $password != NULL)
		    $this->httpsProxyAuth = base64_encode (
						"$username:$password");
	}

		// Return the response code received from calls to
		// changePassword, getCreditsRemaining, sendMessages, and
		// checkReplies.
	function getResponseCode ()
	{
		return $this->responseCode;
	}

		// Return the message that was returned with the response code.
	function getResponseMessage ()
	{
		return $this->responseMessage;
	}

		// Strip invalid characters from the phone number.
	function stripInvalid (
		$phone
	) {
		$ret = "";
		for ($i = 0; $i < strlen ($phone); $i++) {
		    $c = substr ($phone, $i, 1);
		    if ($c >= '0' && $c <= '9')
			$ret .= $c;
		    else if ($c == '+' && $ret == "")
			$ret = '+';
		}

		if (strlen ($ret) == 12 && substr ($ret, 0, 4) == "6104")
		    $ret = "614" . substr ($ret, 4, 8);

		return $ret;
	}

		// Add a message to be sent.
	function addMessage (
		$phone,
		$messageText,
		$messageID = 0,
		$delay = 0,
		$validityPeriod = 169,
		$deliveryReport = false
	) {
		$phone = $this->stripInvalid ($phone);
		if ($phone == "")
		    return;

		if (strlen ($messageText) <= 160) {
		    $this->messageList[] = new SmsMessage ($phone,
				$messageText, $messageID, $delay,
				$validityPeriod, $deliveryReport);
		    return;
		}

		if ($this->allowLongMessages) {		// Use concatenation.
		    $this->messageList[] = new SmsMessage ($phone,
				substr ($messageText, 0, 1071), $messageID,
				$delay, $validityPeriod, $deliveryReport);
		    return;
		}

		if (!$this->allowSplitting) {	// Truncate it.
		    $this->messageList[] = new SmsMessage ($phone,
				substr ($messageText, 0, 160), $messageID,
				$delay, $validityPeriod, $deliveryReport);
		    return;
		}

			// Break it into separate messages.
		$ml = array ();
		$maxlen = 152;
		while (strlen ($messageText) > $maxlen) {
		    if (($pos = strrpos (substr ($messageText, 0, $maxlen),
								" ")) == 0)
			$pos = $maxlen - 1;

		    $ml[] = substr ($messageText, 0, $pos + 1);
		    $messageText = substr ($messageText, $pos + 1);
		    $maxlen = 147;
		}
		$ml[] = $messageText;

		$n = count ($ml);
		for ($i = 0; $i < $n ; $i++) {
		    $ni = $i + 1;
		    if ($i == 0)
			$m = $ml[$i];
		    else
			$m = '(' . $ni . "/$n)$ml[$i]";

		    if ($ni != $n)
			$m .= '...(' . $ni . "/$n)";

		    $this->messageList[] = new SmsMessage ($phone, $m,
				$messageID, $delay + 30 * $i, $validityPeriod,
							$deliveryReport);
		}
	}

		// Clear all the messages from the list.
	function clearMessages ()
	{
		unset ($this->messageList);
	}

		// Open a connection to the specified server.
	function openServerConnection (
		$server,
		$secure
	) {
		if ($secure) {
		    if ($this->httpsProxy != NULL) {
			$url = "ssl://" . $this->httpsProxy;
			$port = $this->httpsProxyPort;
			$s = "POST https://$server/ HTTP/1.0\r\n";

			if ($this->httpsProxyAuth != NULL)
			    $s .= "Proxy-Authorization: Basic "
					. $this->httpsProxyAuth . "\r\n";
		    } else {
			$url = "ssl://" . $server;
			$port = 443;
			$s = "POST / HTTP/1.0\r\n";
		    }
		} elseif ($this->httpProxy != NULL) {
		    $url = $this->httpProxy;
		    $port = $this->httpProxyPort;
		    $s = "POST http://$server/ HTTP/1.0\r\n";

		    if ($this->httpProxyAuth != NULL)
			$s .= "Proxy-Authorization: Basic "
					. $this->httpProxyAuth . "\r\n";
		} else {
		    $url = $server;
		    $port = 80;
		    $s = "POST / HTTP/1.0\r\n";
		}

		if (($this->httpConnection = @fsockopen ($url, $port,
					$errno, $errstr, 10)) == NULL)
		    return false;

		$s .= "Host: $server\r\n";
		if (!fwrite ($this->httpConnection, $s))
		    return false;

		return true;
	}

		// Read the first two lines of HTML and make sure it's
		// an M4U server. Also skip HTTP header info.
	function openInputConnection ()
	{
		while (($s = fgets ($this->httpConnection, 4096)) != NULL)
		    if (rtrim ($s) == "")
			break;

		if (($s = fgets ($this->httpConnection, 4096)) == NULL)
		    return false;
		if (strncmp ($s, "<HTML><HEAD><TITLE>M4U", 22) != 0)
		    return false;

		if (($s = fgets ($this->httpConnection, 4096)) == NULL)
		    return false;
		if (strncmp ($s, "<BODY>", 6) != 0)
		    return false;

		return true;
	}

		// Close a connection to the server.
	function close ()
	{
		if ($this->httpConnection == NULL)
		    return;

		fclose ($this->httpConnection);
		$this->httpConnection = NULL;
	}

		// Connect to the M4U server and make sure it's responding
		// correctly.
	function connect (
		$username,
		$password,
		$useMessageID,
		$secure
	) {
		if ($this->httpConnection != NULL)
		    return false;

		$this->username = $username;
		$this->password = $password;
		$this->useMessageID = $useMessageID;
		$this->secure = $secure;

			// Try connecting to all the servers in the list.
		foreach ($this->serverList as $server)
		    if ($this->openServerConnection ($server, $secure))
			break;

		if ($this->httpConnection == NULL)
		    return false;

			// Successfully connected. Send username and password.
		$this->textBuffer = "m4u\r\nUSER=$username";
		if ($useMessageID)
		    $this->textBuffer .= "#";

		$this->textBuffer .= "\r\nPASSWORD=$password\r\nVER=PHP1.0d\r\n";

		return true;
	}

		// Flush the text buffer to the HTTP connection.
	function flushBuffer ()
	{
		$length = strlen ($this->textBuffer);
		$s = "Content-Length: $length\r\n\r\n" . $this->textBuffer;

		if (!fwrite ($this->httpConnection, $s))
		    return false;

		$this->textBuffer = NULL;

		return true;
	}

		// Read a response code back from the server.
		// It will look something like "100 OK".
	function readResponseCode ()
	{
		if (($s = fgets ($this->httpConnection, 4096)) == NULL)
		    return 600;

			// Parse the first three characters.
		list ($n) = sscanf (substr ($s, 0, 3), "%d");
		if ($n !== NULL) {
		    $this->responseCode = $n;
		    $this->responseMessage = substr ($s, 4);
		} else
		    $n = 700;

		return $n;
	}

		// Change the password on the local machine and server.
	function changePassword (
		$newPassword
	) {
		if ($this->httpConnection == NULL)
		    return false;

		$ok = true;
		$this->textBuffer .= "NEWPASSWORD=$newPassword\r\nMESSAGES\r\n.\r\n";
		if (!$this->flushBuffer () || !$this->openInputConnection ()
			|| (int) ($this->readResponseCode () / 100) != 1)
		    $ok = false;

		$this->close ();

		return $ok;
	}

		// Return the list of replies we have received.
	function checkReplies (
		$autoConfirm = true
	) {
		if ($this->httpConnection == NULL)
		    return NULL;

		$this->textBuffer .= "CHECKREPLY2.0\r\n.\r\n";

		if (!$this->flushBuffer () || !$this->openInputConnection ()
				|| $this->readResponseCode () != 150) {
		    $this->close ();
		    return NULL;
		}

		$srl = array ();
		while (!feof ($this->httpConnection)) {
		    $s = rtrim (fgets ($this->httpConnection, 4096));
		    if (strncmp ($s, ".", 1) == 0)
			break;

		    $sr = SmsReply::parse ($s, $this->useMessageID);
		    if ($sr != NULL)
			$srl[] = $sr;
		}

		$this->close ();

		if ($autoConfirm && count ($srl) > 0) {
		    $this->connect ($this->username, $this->password, true,
							$this->secure);
		    $this->confirmRepliesReceived ();
		}

		return $srl;
	}

	function confirmRepliesReceived ()
	{
		if ($this->httpConnection == NULL)
		    return false;

		$ok = true;
		$this->textBuffer .= "CONFIRM_RECEIVED\r\n.\r\n";
		if (!$this->flushBuffer ())
		    $ok = false;

		$this->close ();

		return $ok;
	}

		// Return the credits remaining (for prepaid users only).
	function getCreditsRemaining ()
	{
		if ($this->httpConnection == NULL)
		    return -2;

		$this->textBuffer .= "MESSAGES\r\n.\r\n";
		if (!$this->flushBuffer () || !$this->openInputConnection ()) {
		    $this->close ();
		    return -2;
		}

		$s = fgets ($this->httpConnection, 4096);
		$this->close ();

		if ($s == NULL)
		    return -2;

		list ($n) = sscanf (substr ($s, 0, 3), "%d");
		if ($n === NULL)
		    return -2;		// Invalid response.
		else
		    $this->responseCode = $n;

		if ($this->responseCode == 100)
		    return -1;

		if ($this->responseCode != 120)		// Invalid response.
		    return -2;

			// Parse the credits.
			// String of the form "120 OK 5000 credits remaining".
		$cred = substr ($s, 7);
		if (($idx = (strpos ($cred, ' '))) === false)
		    return -2;		// No space after the number.

		list ($n) = sscanf (substr ($cred, 0, $idx), "%d");
		if ($n === NULL)
		    return -2;
		else
		    return $n;
	}

		// Send all the messages that have been added with the
		// addMessage command.
	function sendMessages ()
	{
		if ($this->httpConnection == NULL)
		    return false;

		$this->textBuffer .= "MESSAGES2.0\r\n";

		foreach ($this->messageList as $sm) {
		    $s = "$sm->messageID $sm->phoneNumber $sm->delay "
						. "$sm->validityPeriod ";
		    $s .= $sm->deliveryReport ? "1 " : "0 ";
		    $s .= "$sm->message\r\n";
		    $this->textBuffer .= $s;
		}

		$ok = true;
		$this->textBuffer .= ".\r\n";
		if (!$this->flushBuffer () || !$this->openInputConnection ()
			|| (int) ($this->readResponseCode () / 100) != 1)
		    $ok = false;

		$this->close ();

		return $ok;
	}

    /**
     * Delete a scheduled message.
     *
     * @param int $messageId    The message id of the scheduled message to be deleted.
     *
     * @return boolean True if message deleted. False otherwise.
     */
    function deleteScheduledMessage(
        $messageId
    ) {
        $messageId = trim($messageId);
        if ($messageId == '' || !ctype_digit($messageId)) {
            return false;
        }

		if ($this->httpConnection == NULL)
		    return false;

		$this->textBuffer .= "DELETE $messageId\r\n";

		if (!$this->flushBuffer() || !$this->openInputConnection()
				|| $this->readResponseCode() != 100) {
		    $this->close ();
		    return false;
        }

        return true;
    }
}