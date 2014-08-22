<?php
class RestService {
	public function luceneUpdate($stage1, $blk1, $bw1, $type1, $seq1, $stage2, $blk2, $bw2, $type2, $seq2) {
		$sbs = new StageBlockSeqs();
		$blk1 = $sbs->getBlockName($blk1);
		$blk2 = $sbs->getBlockName($blk2);
		
		$typeFinder = new ActivityTypes();
		$typename1 = $typeFinder->getActivityName($typeFinder->getCompassActivityTypeFromEventsActivityType($type1));
		$typename2 = $typeFinder->getActivityName($typeFinder->getCompassActivityTypeFromEventsActivityType($type2));
		
		SearchIndexer::updateDates($stage1, $blk1, $bw1, $type1, $typename1, $seq1, 
								$stage2, $blk2, $bw2, $type2, $typename2, $seq2);
		return "Success";
	}
	
	public function luceneReindex($stage1, $blk1, $bw1, $type1, $seq1) {
		$sbs = new StageBlockSeqs();
		$blk1 = $sbs->getBlockName($blk1);
		
		$typeFinder = new ActivityTypes();
		$typename1 = $typeFinder->getActivityName($typeFinder->getCompassActivityTypeFromEventsActivityType($type1));
		
		SearchIndexer::AddOrDeleteDates($stage1, $blk1, $bw1, $type1, $typename1, $seq1);
		return "Success";
	}
	
	public function bulkLuceneUpdate($str) {
		$sbs = new StageBlockSeqs();
		$typeFinder = new ActivityTypes();
		
		Zend_Registry::get('logger')->info("Bulk update received: $str");
		$reqs = explode('|', $str);
		foreach ($reqs as $req) {
			$params = array();
			$name_val_pair = explode('&', $req);
			
			foreach ($name_val_pair as $pair) {
				$name_val = explode('=', $pair);
				$params[$name_val[0]] = $name_val[1];
			}
			
			//Zend_Registry::get('logger')->info(var_export($params, 1));
			$blk1 = $sbs->getBlockName($params['blk1']);
			$blk2 = $sbs->getBlockName($params['blk2']);
			$typename1 = $typeFinder->getActivityName($typeFinder->getCompassActivityTypeFromEventsActivityType($params['type1']));
			$typename2 = $typeFinder->getActivityName($typeFinder->getCompassActivityTypeFromEventsActivityType($params['type2']));
			SearchIndexer::updateDates($params['stage1'], $blk1, $params['bw1'], $params['type1'], $typename1, $params['seq1'],
										$params['stage2'], $blk2, $params['bw2'], $params['type2'], $typename2, $params['seq2']);
		}
		return "Success";
	}
	
	public function bulkLuceneReindex($str) {
		$sbs = new StageBlockSeqs();
		$typeFinder = new ActivityTypes();
		
		Zend_Registry::get('logger')->info("Bulk reindex received: $str");
		$reqs = explode('|', $str);
		foreach ($reqs as $req) {
			$params = array();
			$name_val_pair = explode('&', $req);
			
			foreach ($name_val_pair as $pair) {
				$name_val = explode('=', $pair);
				$params[$name_val[0]] = $name_val[1];
			}
			
			//Zend_Registry::get('logger')->info(var_export($params, 1));
			$blk1 = $sbs->getBlockName($params['blk1']);
			$typename1 = $typeFinder->getActivityName($typeFinder->getCompassActivityTypeFromEventsActivityType($params['type1']));
			SearchIndexer::AddOrDeleteDates($params['stage1'], $blk1, $params['bw1'], $params['type1'], $typename1, $params['seq1']);
		}
		return "Success";
	}
}