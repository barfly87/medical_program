<?php

class Descriptors extends Zend_Db_Table_Abstract {
    protected $_name = 'descriptor';
    
    /** Get the number of descriptors in the descriptor table */
    public function getNumberOfDescriptors() {
    	$select = $this->select();
    	$select->from($this->_name, 'count(*) AS num_rows');
    	$result = $this->fetchRow($select);
    	return $result->num_rows;
    }
    
    /** Get the children of the root category */
    public function getChildrenOfRootCategory($categoryLetter) {
    	$select = $this->select();
    	$select->where('length(treenumbers) = 3')
    		->where("treenumbers LIKE ?", $categoryLetter.'%')
    		->order('treenumbers');
    	return $this->fetchAll($select);
    }
    
    /** Get all children descriptors based on parent descriptor 
     *  Parent can appear at the end, or in the middle followed by ','
     */
    public function getDescriptorsByParent($parent, $order = NULL) {
    	$pattern1 = $parent .'.[[:digit:]]{3}$';
    	$pattern2 = $parent .'.[[:digit:]]{3},';
    	
    	$select = $this->select();
    	$select->where("treenumbers ~ '$pattern1' OR treenumbers ~ '$pattern2'");
    	if (NULL != $order) {
    		$select->order($order);
    	}
    	return $this->fetchAll($select);
    }
    
    /** Get the heading text based on uid */
    public function getHeadingTextFromUid($uid) {
    	$select = $this->select();
    	$select->where('uid=?', $uid);
    	return $this->fetchRow($select)->headingtext;
    }
    
    /** Determine whether to display clickable link for each of the descriptor in the row set
     *  An expandable link will only appear when the descriptor has at least one child. 
     */
    public function getLinkStatus($rowset, $parent) {
    	$showLinks = array();
    	
    	$db = Zend_Registry::get('db');
    	$stmt = $db->prepare("SELECT count(*) FROM descriptor WHERE treenumbers ~ :p1 OR treenumbers ~ :p2");
    	foreach ($rowset as $row) {
    		$tree_nums_arr = explode(',', $row->treenumbers);
    		$cur_child = '';
    		foreach ($tree_nums_arr as $number) {
    			if (strpos($number, $parent) === 0) {
    				$cur_child = $number;
    				break;
    			}
    		}
    		$child_pattern1 = $cur_child . '.[[:digit:]]{3}$';
    		$child_pattern2 = $cur_child . '.[[:digit:]]{3},';
    		
    		$stmt->bindParam('p1', $child_pattern1);
    		$stmt->bindParam('p2', $child_pattern2);
    		$stmt->execute();
    		$child_results = $stmt->fetchColumn(0);
    		
    		if ($child_results > 0) {
    			$showLinks[] = true;
    		} else {
    			$showLinks[] = false;
    		}
    	}
    	return $showLinks;
    }
    
    /** Get all descriptor headings as an associative array, lower case heading text as array index, heading text as value */
    public function getHeadingsAsAssociativeArray() {
    	$descriptor_arr = array();
    	$descriptors = $this->fetchAll();
    	foreach ($descriptors as $descriptor) {
    		$descriptor_arr[strtolower($descriptor->headingtext)] = $descriptor->headingtext;
    	}
    	return $descriptor_arr;
    }
}