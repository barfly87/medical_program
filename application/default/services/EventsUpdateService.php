<?php
//Teaching activity type id is hard coded. Need to figure out how to relate activity types between Compass and Event
class EventsUpdateService {
	
	/**
	 * Refresh teaching activity id linked to an event.
	 * Events will try to update the event based on year, block, block week, event type, and sequence number info
	 * @param $ta - ta id or ta object
	 */
	public static function refreshLinkedTaId($ta) {
		if (!isset(Zend_Registry::get('config')->event_wsdl_uri)) {
    			return;
    	}
		if (!($ta instanceof TeachingActivity)) {
			$taFinder = new TeachingActivities();
			$ta = $taFinder->getTa($ta);
		}
		
		$types_mapping = ActivityTypes::$compass_events_mapping;
		$uid = Zend_Auth::getInstance()->getIdentity()->user_id;
		$stage = (int)($ta->stage);
		$year = date('Y') + $stage - 1;
		$block = $ta->block_no;
		$bw = $ta->block_week;
		$eventtype = isset($types_mapping[$ta->typeID]) ? $types_mapping[$ta->typeID] : NULL;
		$seq = $ta->sequence_num;
		if (!empty($bw) && isset($eventtype) && !empty($seq)) {
			try {
				$client = new Zend_Soap_Client(Zend_Registry::get('config')->event_wsdl_uri);
				Zend_Registry::get('logger')->info(__METHOD__."($ta->ownerID, $year, $block, $bw, $eventtype, $seq, $uid)");
				$result = $client->refreshLinkedTaId($ta->ownerID, $year, $block, $bw, $eventtype, $seq, $uid);
				Zend_Registry::get('logger')->info(__METHOD__." - RESULT: ".$result);
			} catch (Exception $exp) {
				Zend_Registry::get('logger')->error(__METHOD__."($ta->ownerID, $year, $block, $bw, $eventtype, $seq, $uid) failed");
				Zend_Registry::get('logger')->error(__METHOD__." - Error message: " . $exp->getMessage());
			}
		}
	}
}