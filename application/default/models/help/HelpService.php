<?php

class HelpService {
    
    public function getMappingForLuceneFieldsToColumnNames() {
        $return = array();
        $columns = SearchConstants::columns();
        if(isset($columns) && !empty($columns)) {
            foreach($columns as $column) {
                if(isset($column['luceneIndex']) && isset($column['displayName'])) {
                    if(stristr($column['luceneIndex'],'lo_') !== false){
                        $return['lo'][$column['luceneIndex']] = $column['displayName'];
                    } else if(stristr($column['luceneIndex'],'ta_') !== false){
                        $return['ta'][$column['luceneIndex']] = $column['displayName'];
                    }
                }
            }
        }
        return $return;
    }
}