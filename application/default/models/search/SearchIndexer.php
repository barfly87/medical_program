<?php

class SearchIndexer {
	/** Lucene index directory */
	protected static $_indexDirectory;

	public static function setIndexDirectory($directory) {
		if (!is_dir($directory)) {
			throw new Exception('Directory for SearchIndexer is invalid ('. $directory .')');
		}
		self::$_indexDirectory = $directory;
	}

	public static function getIndexDirectory() {
		return self::$_indexDirectory;
	}

	public static function observeTableRow($event, $row) {
		$doc = self::getDocument($row);
		if ($doc == NULL) {
			return;
		}
		
		switch ($event) {
			case 'post-insert':
				Zend_Registry::get('logger')->debug(__METHOD__. ': - post-insert');
			case 'post-update':
				Zend_Registry::get('logger')->debug(__METHOD__. ': - post-update');
				self::_addToIndex($doc);
				break;
			case 'post-delete':
				Zend_Registry::get('logger')->debug(__METHOD__. ': - post-delete');
				self::_deleteFromIndex($doc);
				break;
		}
	}

	public static function getDocument(Compass_Db_Table_Row_Observerable $row) {
		if (($row instanceof LearningObjective) || ($row instanceof TeachingActivity) ||
		($row instanceof LinkageLoTa)) {
			return $row->getLuceneDoc();
		}
		return NULL;
	}

    
    public static function updateDates($stage1, $blk1, $bw1, $type1id, $type1, $seq1, $stage2, $blk2, $bw2, $type2id, $type2, $seq2) {
    	set_time_limit(0);
    	Zend_Registry::get('logger')->debug("Request from events - Old value: $stage1, $blk1, $bw1, $type1id, $type1, $seq1");
    	Zend_Registry::get('logger')->debug("Request from events - New value: $stage2, $blk2, $bw2, $type2id, $type2, $seq2");
    	$lk_lo_tas = new LinkageLoTas();
    	
    	$index = Compass_Search_Lucene::open(self::$_indexDirectory);
    	$query = "+doctype:Linkage +ta_stage:\"$stage1\" +ta_block:\"$blk1\" +ta_block_week:\"$bw1\"";
    	if ($type1id != 5) { //event is not a problem milestone
    		$query .= " +ta_type:\"$type1\" +ta_sequence_num:\"$seq1\"";
    	};
    	$results = $index->find($query);
    	Zend_Registry::get('logger')->debug("Update dates linked to old event: ". $query . ' Found ' . count($results));
    	if (count($results) > 0) {
	    	foreach($results as $result) {
	    		$index->delete($result->id);
	    		//Zend_Registry::get('logger')->debug("Search/Lucene: update document " . $result->ta_auto_id);
	    		$link_lo_ta = $lk_lo_tas->fetchRow("auto_id={$result->auto_id}");
	    		$doc = $link_lo_ta->getLuceneDoc();
	    		$index->addDocument($doc);
	    	}
	    	$index->commit();
	    	$index->optimize();
	    	Zend_Registry::get('logger')->debug("FINISHED OPTIMIZATION");
    	}
    	
    	//only release time is changed, updated info is already added when adding documents
    	if (($stage1 == $stage2) && ($blk1 == $blk2) && ($bw1 == $bw2) && ($type1 == $type2) && ($seq1 == $seq2)) {
    		return;
    	}
        $query = "+doctype:Linkage +ta_stage:\"$stage2\" +ta_block:\"$blk2\" +ta_block_week:\"$bw2\"";
    	if ($type2id != 5) { //event is not a problem milestone
    		$query .= " +ta_type:\"$type2\" +ta_sequence_num:\"$seq2\"";
    	};
    	$results2 = $index->find($query);
    	Zend_Registry::get('logger')->debug("Update dates linked to new event: ". $query . ' Found ' . count($results2));
    	if (count($results2) > 0) {
	    	foreach($results2 as $result) {
	    		$index->delete($result->id);
	    		//Zend_Registry::get('logger')->debug("Search/Lucene: update document " . $result->ta_auto_id);
	    		$link_lo_ta = $lk_lo_tas->fetchRow("auto_id={$result->auto_id}");
	    		$doc = $link_lo_ta->getLuceneDoc();
	    		$index->addDocument($doc);
	    	}
	    	$index->commit();
	    	$index->optimize();
	    	Zend_Registry::get('logger')->debug("FINISHED OPTIMIZATION");
    	}
    }
    
    public static function AddOrDeleteDates($stage1, $blk1, $bw1, $type1id, $type1, $seq1) {
    	set_time_limit(0);
    	Zend_Registry::get('logger')->debug("Request from events - Value: $stage1, $blk1, $bw1, $type1id, $type1, $seq1");
    	$lk_lo_tas = new LinkageLoTas();
    	
        $index = Compass_Search_Lucene::open(self::$_indexDirectory);
    	$query = "+doctype:Linkage +ta_stage:\"$stage1\" +ta_block:\"$blk1\" +ta_block_week:\"$bw1\"";
    	if ($type1id != 5) { //event is not a problem milestone
    		$query .= " +ta_type:\"$type1\" +ta_sequence_num:\"$seq1\"";
    	};
    	$results = $index->find($query);
    	Zend_Registry::get('logger')->debug("Update dates linked to event: ". $query . ' Found ' . count($results));
    	if (count($results) > 0) {
	    	foreach($results as $result) {
	    		$index->delete($result->id);
	    		//Zend_Registry::get('logger')->debug("Search/Lucene: update document " . $result->ta_auto_id);
	    		$link_lo_ta = $lk_lo_tas->fetchRow("auto_id={$result->auto_id}");
	    		$doc = $link_lo_ta->getLuceneDoc();
	    		$index->addDocument($doc);
	    	}
	    	$index->commit();
	    	$index->optimize();
	    	Zend_Registry::get('logger')->debug("FINISHED OPTIMIZATION");
    	}
    }
    
	protected static function _addToIndex(Zend_Search_Lucene_Document $document) {
		$index = self::_deleteDocument($document);

		//TODO need to update linked lo and ta count
		$index->addDocument($document);
		$index->commit();
	}

	/** Deletes one document from the index */
	protected static function _deleteFromIndex(Zend_Search_Lucene_Document $document) {
		$index = self::_deleteDocument($document);

		//TODO need to update linked lo and ta count
		$index->commit();
	}
	
	/** Helper function used by _addToIndex and _deleteFromIndex */
	private static function _deleteDocument(Zend_Search_Lucene_Document $document) {
		try {
			$index = Compass_Search_Lucene::open(self::$_indexDirectory);
		} catch (Exception $e) {
			$index = Compass_Search_Lucene::create(self::$_indexDirectory);
		}
		$docRef = $document->docRef;

		Zend_Registry::get('logger')->debug(__METHOD__. ': - processing ' . $docRef);
		$term = new Zend_Search_Lucene_Index_Term($docRef, 'docRef');
		$query = new Zend_Search_Lucene_Search_Query_Term($term);
		$results = $index->find($query);
		Zend_Registry::get('logger')->debug(__METHOD__. " - Found existing document for ". $docRef . ' '. count($results));
		
		//delete existing entry first
		if (count($results) > 0) {
			foreach($results as $result) {
				$index->delete($result->id);
			}
		}
		return $index;
	}
	
    /** Reindex teaching activity or learning objective of a particular id */
    public static function reindexDocument($type, $typeid) {
    	set_time_limit(0);
    	if (!(($type == 'ta' || $type == 'lo') && $typeid > 0))
    		return '<span style="color:red">Error!</span>';
    	Zend_Registry::get('logger')->debug(__METHOD__. ": - reindex document $type $typeid");
    	$index = Compass_Search_Lucene::open(self::$_indexDirectory);
    	
    	$query = "+doctype:Linkage ";
    	if ($type == 'ta') {
    		$query .= "+ta_auto_id:\"$typeid\"";
    	} else if ($type == 'lo') {
    		$query .= "+lo_auto_id:\"$typeid\"";
    	}
    	$results = $index->find($query);
    	Zend_Registry::get('logger')->debug(__METHOD__.  " - Update $type $typeid (". $query . '), found ' . count($results));
    	
    	$lk_lo_tas = new LinkageLoTas();
    	if (count($results) > 0) {
    		foreach ($results as $result) {
    			$index->delete($result->id);
    			$link_lo_ta = $lk_lo_tas->fetchRow("auto_id={$result->auto_id}");
    			if ($link_lo_ta->status == Status::$RELEASED) {
    				Zend_Registry::get('logger')->debug(__METHOD__.  " - Adding {$result->auto_id}");
    				$doc = $link_lo_ta->getLuceneDoc();
    				$index->addDocument($doc);
    			}
    		}
    	} else {
    		if ($type == 'ta') {
    			$links = $lk_lo_tas->fetchAll("ta_id = $typeid");
    		} else {
    			$links = $lk_lo_tas->fetchAll("lo_id = $typeid");
    		}
    		foreach ($links as $link) {
    			if ($link->status == Status::$RELEASED) {
    				Zend_Registry::get('logger')->debug(__METHOD__.  " - Adding new {$link->auto_id}");
    				$doc = $link->getLuceneDoc();
    				$index->addDocument($doc);
    			}
    		}
    	}
    	$index->commit();
    	$index->optimize();
    	return '<span style="color:green">Success!</span>';
    }
}
