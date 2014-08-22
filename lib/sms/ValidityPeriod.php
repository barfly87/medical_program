<?php

/*
 * This class defines constants used to specify the validity period
 * of messages injected into the SMS network.
 */

class ValidityPeriod {
	function minimum ()		{ return 0; }
	function oneHour ()		{ return 11; }
	function sixHours ()		{ return 71; }
	function oneDay ()		{ return 167; }
	function twoDays ()		{ return 168; }
	function threeDays ()		{ return 169; }
	function oneWeek ()		{ return 173; }
	function maximum ()		{ return 255; }
}
