<?php

class Compass_Search_Lucene extends Zend_Search_Lucene {
    
    public function __construct($directory, $create){
        Zend_Search_Lucene_Analysis_Analyzer::setDefault(new StandardAnalyzer_Analyzer_Standard_English());        
        parent::__construct($directory, $create);
    }

    /**
     * Create index
     *
     * @param mixed $directory
     * @return Zend_Search_Lucene_Interface
     */
    public static function create($directory) {
        return new Zend_Search_Lucene_Proxy(new Compass_Search_Lucene($directory, true));
    }

    /**
     * Open index
     *
     * @param mixed $directory
     * @return Zend_Search_Lucene_Interface
     */
    public static function open($directory) {
        return new Zend_Search_Lucene_Proxy(new Compass_Search_Lucene($directory, false));
    }

    /**
     * Adds a document to this index.
     *
     * @param Zend_Search_Lucene_Document $document
     */
    /*public function addDocument(Zend_Search_Lucene_Document $document) {
        // check document doesn't already exist - docRef should be unique
        $docRef = $document->docRef;
        
        //Zend_Registry::get('logger')->info("Search/Lucene: ". $document->docRef);
        $term = new Zend_Search_Lucene_Index_Term($docRef, 'docRef');
        $query = new Zend_Search_Lucene_Search_Query_Term($term);
        $results = $this->find($query);

        if (count($results) > 0) {
            foreach($results as $result) {
                $this->delete($result->id);  
            }
        }
        if (strpos($document->docRef, 'LinkageLoTa') !== 0) {
            //Zend_Registry::get('logger')->info("Search/Lucene: Add ". $document->docRef);
            $docRefArr = explode(':', $document->docRef);

            $query = new Zend_Search_Lucene_Search_Query_MultiTerm();
            $query->addTerm(new Zend_Search_Lucene_Index_Term('Linkage', 'doctype'), true);
            //$query = 'doctype:Linkage AND ';
            if ($docRefArr[0] == 'LearningObjective') {
                $query->addTerm(new Zend_Search_Lucene_Index_Term($docRefArr[1], 'lo_auto_id'), true);
                //$query .= 'lo_auto_id:'.$docRefArr[1];
            } else if ($docRefArr[0] == 'TeachingActivity') {
                $query->addTerm(new Zend_Search_Lucene_Index_Term($docRefArr[1], 'ta_auto_id'), true);
                //$query .= 'ta_auto_id:'.$docRefArr[1];
            }
            $results = $this->find($query);
            $removedIds = array();
            Zend_Registry::get('logger')->info("Search/Lucene: ". $query. ' '. count($results));
            if (count($results) > 0) {
                $lk_lo_tas = new LinkageLoTas();
                foreach($results as $result) {
                    $this->delete($result->id);
                    Zend_Registry::get('logger')->info("Search/Lucene: ". $result->id. ' '.$result->auto_id);
                    $removedIds[] = $result->auto_id;
                }
                foreach ($removedIds as $id) {
                    $link_lo_ta = $lk_lo_tas->fetchRow('auto_id='.$id);
                    $doc = $link_lo_ta->getLuceneDoc();
                    parent::addDocument($doc);
                }
            } else {
                return parent::addDocument($document);
            }
        } else if (strpos($document->docRef, 'LinkageLoTa') === 0) {
            $term = new Zend_Search_Lucene_Index_Term('LearningObjective:'.$document->lo_auto_id, 'docRef');
            $query = new Zend_Search_Lucene_Search_Query_Term($term);

            //$query = 'doctype:LearningObjective AND auto_id:'.$document->lo_auto_id;
            $results = $this->find($query);
            Zend_Registry::get('logger')->info("Search/Lucene: FIND LO: ".count($results));
            if (count($results) > 0) {
                foreach($results as $result) {
                    $this->delete($result->id);
                     Zend_Registry::get('logger')->info("Search/Lucene: DELETE LO ". $result->lo_auto_id);
                }
            }
            $term = new Zend_Search_Lucene_Index_Term('TeachingActivity:'.$document->ta_auto_id, 'docRef');
            $query = new Zend_Search_Lucene_Search_Query_Term($term);
            //$query = 'doctype:TeachingActivity AND auto_id:'.$document->ta_auto_id;
            $results = $this->find($query);
            Zend_Registry::get('logger')->info("Search/Lucene: FIND TA: ".count($results));
            if (count($results) > 0) {
                foreach($results as $result) {
                    $this->delete($result->id);
                    Zend_Registry::get('logger')->info("Search/Lucene: DELETE TA ". $result->ta_auto_id);
                }
            }
            return parent::addDocument($document);
        }
    }*/
}
