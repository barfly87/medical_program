<?php
class SearchResultsFormatService {
    
    private $format = null;
    
    public function __construct($format){
        if(!empty($format) && in_array($format, SearchConstants::formatsAllowed())) {
            $this->format = $format;    
        }            
    }
    
    public function processResults($searchResults, $request){
        if(!is_null($this->format) && is_array($searchResults) && count($searchResults) > 0 ) {
            $formatServiceObj = null;
            switch($this->format) {
                case SearchConstants::$formatCsv:
                    $formatServiceObj = new SearchResultsFormatCsvService();
                break;
                case SearchConstants::$formatPodcast:
                    $formatServiceObj = new SearchResultsFormatPodcastService();
                break;
            }
            if($formatServiceObj !== null) {
                $formatServiceObj->process($searchResults, $request);   
            } else {
                die('Error ! Could not find the format.');
            }
            exit;
        }
    }
        
}