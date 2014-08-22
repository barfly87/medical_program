<?php 
class CmsCsvService {

    public function setHeaders($filename) {
        header("Content-type: text/x-comma-separated-values");
        header("Content-disposition: attachment; filename=\"$filename.csv\"");
    }
    
    public function arrayToCsvDump($rows,$filename) {
        $this->setHeaders($filename);
        $this->printRow($rows);
        exit;
    }
    
    private function printRow(&$rows){
        $rowsCount = count($rows); 
        if($rowsCount > 0){
            for($x = 0; $x < $rowsCount; $x++){
                if($x==0) {
                    $cols = array_keys($rows[$x]);
                    foreach($cols as $coltitle) {
                        echo "\"".$coltitle."\",";
                    }
                    echo "\n";
                }
                foreach($rows[$x] as $column) {
                    $columnVal = str_replace("\"", "'", $column);
                    //$columnVal = str_replace("\n", "<br>", $columnVal);
                    $columnVal = str_replace("\r", "", $columnVal);
                    echo "\"".$columnVal."\",";
                }
                echo "\n";
            }                
        }
    }
    
}

