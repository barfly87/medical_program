<?php
class EvaluateEventsService {
    
    /**
     * Get the mapping between compass and events ta type ids.
     * For each events ta type id get the teaching activities between the start and the end date
     */
    public function getTaAutoIdsBetweenStartAndEndDate($taTypeIds, $startDate, $endDate) {
        $taAutoIds = array();
        if(!empty($taTypeIds)) {
            $compassToEventsTaTypeIdsMapping = ActivityTypes::$compass_events_mapping;
            foreach($taTypeIds as $taTypeId) {
                if(isset($compassToEventsTaTypeIdsMapping[$taTypeId])) {
                    $eventsTaTypeId = $compassToEventsTaTypeIdsMapping[$taTypeId];
                    $events = $this->_getEventsForDatesBetween($eventsTaTypeId, $startDate, $endDate);
                    $eventsTaAutoids = $this->_getTeachingActivityIds($events);
                    if(!empty($eventsTaAutoids)) {
                        $taAutoIds = array_merge($taAutoIds, $eventsTaAutoids);
                    }
                } else {
                    $error = 'Could not find compass ta activity id mapping to events. It seems that this ta activity type does not exist in Events or a mapping is not created in Compass';
                    Zend_Registry::get('logger')->warn(PHP_EOL."CLASS\t: ".__CLASS__.PHP_EOL."METHOD\t: ".__METHOD__.PHP_EOL."ERROR\t: ".$error.PHP_EOL);
                }
            }
        }
        return $taAutoIds;
    }
    
    /**
     * Make a web service call to Events and find teaching activities between given dates
     * @return array
     */
    private function _getEventsForDatesBetween($eventsTaTypeId, $startDate, $endDate) {
        try{
            //no Events application to control the release dates like Tabuk
            if (!Compass::isConnectedToEvents() || empty($startDate) || empty($endDate) || empty($eventsTaTypeId)) {
                return array();
            }
            $client = new Zend_Soap_Client(Compass::getConfig('event_wsdl_uri'));
            $result = $client->fetchEventsBetweenDates($startDate, $endDate, $eventsTaTypeId);
            if(isset($result['error'])) {
                Zend_Registry::get('logger')->warn(PHP_EOL."CLASS\t: ".__CLASS__.PHP_EOL."METHOD\t: ".__METHOD__.PHP_EOL."ERROR\t: ".$result['error'].PHP_EOL);
                return array();
            }
            return $result;
        } catch(Exception $ex) {
            $error = $ex->__toString();
            Zend_Registry::get('logger')->warn(PHP_EOL."CLASS\t: ".__CLASS__.PHP_EOL."METHOD\t: ".__METHOD__.PHP_EOL."ERROR\t: ".$error.PHP_EOL);
            return array();
        }
    }
    
    /**
     * Get teaching activity ids from the array generated from making the webservice call to Events
     * @param array $events
     * @return array $taIds
     */
    private function _getTeachingActivityIds($events) {
        $taIds = array();
        if(!empty($events) && is_array($events)) {
            foreach($events as $event) {
                $taId = (int)$event['linkedactivityid'];
                if($taId > 0) {
                    $taIds[] = $taId;
                }
            }
        }
        return $taIds;
    }
    
}