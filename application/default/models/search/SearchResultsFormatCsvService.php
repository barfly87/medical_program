<?php 
class SearchResultsFormatCsvService {
    
    public function process($searchResults) {
        $this->processHeaders();
        $this->getCsv($searchResults);
    }
    
    private function processHeaders(){
        $filename = 'Search Results '.date('Y_m_d_H_i ',time()).'.csv';
        $mimetype = 'application/vnd.ms-excel'; 
        header('Content-type: '.$mimetype);        
        header('Content-Disposition: attachment; filename="'.$filename.'"');
    }
    
    private function getCsv($searchResults) {
        $fp = fopen('php://output', 'w');
        $results = &$searchResults['results'];
        $context = &$searchResults['results']['context'];
        $subContext = &$searchResults['results']['subContext'];
        $columns = $this->createColumns(&$searchResults['columns']);
        fputcsv($fp, $columns); 
        ksort($context);
        foreach ($context as $autoid => $details) {
            foreach($details as $key => &$val) {
                $val = trim(strip_tags($val));
            }
            if(isset($subContext[$autoid])) {
                if(count($subContext[$autoid]) > 1) {
                    $subContext[$autoid] = $this->sortSubcontext($subContext[$autoid]);
                }
                foreach($subContext[$autoid] as $key => $subdetails) {
                    foreach($subdetails as $subkey => &$subval) {
                        $subval = trim(strip_tags($subval));
                    }
                    fputcsv($fp, array_merge($details,$subdetails));
                }
            } else {
                fputcsv($fp, $details);
            }
        }
        fclose($fp);
    }
    
    private function sortSubcontext($arr){
        $id = null;
        if(isset($arr[0]['ta_auto_id']) ){
            $id = 'ta_auto_id';
        } else if(isset($arr[0]['lo_auto_id'])) {
            $id = 'lo_auto_id';
        } else {
            return $arr;
        }
        $result = array();
        foreach($arr as $key=>$val) {
            $result[$val[$id]] = $val;
        }
        ksort($result);
        return $result;
    }
    
    private function createColumns($columns){
        if( isset($columns['column']) && isset($columns['subColumn']) ) {
            $last = count($columns['column']) - 1;
            unset($columns['column'][$last]);
            return array_merge($columns['column'], $columns['subColumn']);
        } else if( isset($columns['column']) ) {
            return $columns['column'];
        }
        return array();
    }
    
}
