<?php

/*
 * This class defines constants used to specify a message delivery status.
 */

class MessageStatus {
	function none ()		{ return 0; }
	function pending ()		{ return 1; }
	function delivered ()		{ return 2; }
	function failed ()		{ return 3; }
}