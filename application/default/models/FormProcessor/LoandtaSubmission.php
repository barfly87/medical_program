<?php
class FormProcessor_loandtaSubmission extends FormProcessor {
	public $link = null;
	public $lo = null;
	public $ta = null;

	public function __construct($link_id = NULL) {
		parent::__construct();
		$linkFinder = new LinkageLoTas();
		$loFinder = new LearningObjectives();
		$taFinder = new TeachingActivities();
		if ($link_id > 0) {
			//Get the existing linkage, lo, and ta information
			$this->link = $linkFinder->find((int)$link_id)->current();
			if (!empty($this->link->lo_id)) {
				$this->lo = $loFinder->find((int)$this->link->lo_id)->current();
				
				//Get many-to-many linkages to review and assessment type table as id arrays
				$this->reviewArr = $this->lo->review_ids_array;
				$this->assesstypeArr = $this->lo->assessment_type_ids_array;
			} else {
				$this->lo = $loFinder->createRow();
			}
			if (!empty($this->link->ta_id)) {
				$this->ta = $taFinder->find((int)$this->link->ta_id)->current();
			} else {
				$this->ta = $taFinder->createRow();
			}
		} else {
			$this->lo = $loFinder->createRow();
			$this->ta = $taFinder->createRow();
			$this->link = $linkFinder->createRow();
		}
	}

	/** Currently not being used */
	public function process(Zend_Controller_Request_Abstract $request) {
	    return true;
	}
}
?>
