<?php
class MaintenanceLuceneIndex extends MaintenanceAbstract {

    public function process() {
        $errorMsg = '';
        $error = false;

        /*********************
         OPTIMIZE LUCENE INDEX
         *********************/

        try {
            //Create necessary directories
            $searchIndexDir     = SearchIndexer::getIndexDirectory();
            $optimizeDir        = $searchIndexDir.'_optimize';
            $optimizeTempDir    = $searchIndexDir.'_optimize_tmp';
            $writeLockFile      = $searchIndexDir.'/write.lock.file';

            //Find the last modifed time for the file 'write.lock.file'
            $writeLockModifiedTime = 0;
            if(file_exists($writeLockFile)) {
                $writeLockModifiedTime = filemtime($writeLockFile);
            }

            //If no 'write.lock.file' is found $writeLockModifiedTime would be '0' and in that case
            //we are not suppose to optimize since we cannot know if the index was updated in 'search_index'
            //folder while we were optimizing in the 'search_index_optimize' folder
            if($writeLockModifiedTime != 0) {
                if(! is_dir($optimizeDir)) {
                    mkdir($optimizeDir);
                }
                //Delete all files from 'search_index_optimize' directory
                foreach(glob($optimizeDir.'/*') as $filename) {
                    unlink($filename);
                }
                //Copy files from 'search_index' directory to 'search_index_optimize' directory
                foreach(glob($searchIndexDir.'/*') as $filename) {
                    copy($filename, $optimizeDir.'/'.basename($filename));
                }
                $index = Compass_Search_Lucene::open($optimizeDir);
                $index->optimize();
                $currentWriteLockModifiedTime = filemtime($writeLockFile);
                if($currentWriteLockModifiedTime === $writeLockModifiedTime) {
                    rename($searchIndexDir, $optimizeTempDir);
                    rename($optimizeDir, $searchIndexDir);
                    rename($optimizeTempDir, $optimizeDir);
                    Zend_Registry::get('logger')->warn(PHP_EOL.'Successfully optimized lucene index'.PHP_EOL);
                } else {
                    $error = true;
                    $errorMsg = 'Could not optimize lucene index because the index is been modified in "search_index" folder while optimization was going on in "search_index_optimize" folder.';
                    $errorMsg .= 'It is not an error as such and most probably nothing should be broken but please check the "search_index" folder to make sure everything is alright';
                    Zend_Registry::get('logger')->warn(PHP_EOL."CLASS\t: ".__CLASS__.PHP_EOL."METHOD\t: ".__METHOD__.PHP_EOL."Error\t: ".$error.PHP_EOL);
                }
            } else {
                $error = true;
                $errorMsg = 'Could not find the file "write.lock.file" in the directory "search_index".';
            }

            //IF ERROR IS TRUE RETURN $errorMsg OR RETURN true
            if($error == true) {
                return $errorMsg;
            }
            return true;
        } catch(Exception $ex) {
            Zend_Registry::get('logger')->warn(PHP_EOL."CLASS\t: ".__CLASS__.PHP_EOL."METHOD\t: ".__METHOD__.PHP_EOL."Error\t: ".print_r($ex,true).PHP_EOL);
            return 'Error '. print_r($ex, true);
        }
    }
    
}
?>
