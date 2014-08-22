<?php
/**
 * DentService class implements all Faculty of Dentistry related business logic including
 * relevant CRUD operations, which wrap the model layer and will serve for
 * the Controller layer.
 *
 * This software is the confidential and proprietary information of the
 * University of Sydney ("Confidential Information").  You shall not disclose
 * such such Confidential Information and shall use it only in accordance
 * with the terms of the license agreement you entered into with The University
 * of Sydney.
 *
 * @copyright  2009 The University of Sydney
 * @link
 * @since      2009-11-30
 * @version    2009-11-30
 * @author     Fang Xu
 */
/**
 * @author fang
 *
 */
class DentService {
	public function __construct() {
	}
	
	/*
	 * Check if a specific learning objective ID existed
	 * @param $id: integer type
	 * @return boolean type
	 */
	public function isExisted_Lo($id) {
		$t_los = new LearningObjectives();
		$r_lo = $t_los->fetchRow($t_los->select()->where('auto_id = ?', $id));
		if ($r_lo) {
			return true;
		} else {
			return false;
		}
	}
	
	public function isExisted_Ta($id) {
		$table = new TeachingActivities();
		$row = $table->fetchRow($table->select()->where('auto_id = ?', $id));
		return ($row) ? true : false;
	}
	
	public function isExisted_LinkLoTa($id) {
		$table = new LinkageLoTas();
		$row = $table->fetchRow($table->select()->where('auto_id = ?', $id));
		return ($row) ? true : false;
	}
	
	/*
	 * Remove a learning objective
	 * @param $id: integer type
	 * @return void
	 */
	public function removeLoById($id) {
		$db = Zend_Registry::get('db');
		$db->beginTransaction();
		try {
			$t_los = new LearningObjectives();
			$where = "auto_id = $id";
			$t_los->delete($where);
			$db->commit();
		} catch ( Exception $exception ) {
			$db->rollBack();
			throw $exception;
		}
	}
	
	/*
	 * Fetch all information of a Learning Objective based on its ID
	 * @param $id: integer type
	 * @return $a_lo: array type
	 */
	public function fetchLoById($id) {
		$t_los = new LearningObjectives();
		$select = $t_los->select();
		$select->distinct();
		$select->from('learningobjective');
		$select->where('auto_id = ?', $id);
		$r_lo = $t_los->fetchRow($select);
		if ($r_lo) {
			$a_lo = $r_lo->toArray();
			return $a_lo;
		} else {
			return null;
		}
	}
	
	public function fetchTaById($id) {
		$t_los = new TeachingActivities();
		$select = $t_tas->select();
		$select->distinct();
		$select->from('teachingactivity');
		$select->where('auto_id = ?', $id);
		$r_ta = $t_tas->fetchRow($select);
		if ($r_ta) {
			$a_ta = $r_ta->toArray();
			return $a_ta;
		} else {
			return null;
		}
	}
	
	public function fetchLinkLoTaById($id) {
		$table = new LinkageLoTas();
		$select = $table->select();
		$select->distinct();
		$select->from('link_lo_ta');
		$select->where('auto_id = ?', $id);
		$row = $table->fetchRow($select);
		
		return ($row) ? $row->toArray() : null;
	}
	
	/*
	 * Insert the learning objective into database
	 * if existed, delete it first and add it back as new
	 * @param $a_lo: array type
	 * @return void
	 */
	public function insertLoAsNew($a_lo) {
		$db = Zend_Registry::get('db');
		$db->beginTransaction();
		try {
			$id = $a_lo['auto_id'];
			$t_los = new LearningObjectives();
			$newrow = $t_los->createRow();
			if ($this->isExisted_Lo($id)) {
				$oldrow = $t_los->fetchRow('auto_id = $id');
				$oldrow->delete();
			}
			foreach ( $a_lo as $column => $value ) {
				$newrow->$column = $value;
			}
			$newrow->save();
		} catch ( Exception $exception ) {
			$db->rollBack();
			throw $exception;
		}
	}
	
	/*
	 * Insert the teaching activity into database
	 * if existed, delete it first and add it back as new
	 * @param $a_ta: array type
	 * @return void
	 */
	public function insertTaAsNew($a_ta) {
		$db = Zend_Registry::get('db');
		$db->beginTransaction();
		try {
			$id = $a_ta['auto_id'];
			$t_tas = new TeachingActivities();
			$newrow = $t_tas->createRow();
			if ($this->isExisted_Ta($id)) {
				$oldrow = $t_tas->fetchRow('auto_id = $id');
				$oldrow->delete();
			}
			foreach ( $a_ta as $column => $value ) {
				$newrow->$column = $value;
			}
			$newrow->save();
		} catch ( Exception $exception ) {
			$db->rollBack();
			throw $exception;
		}
	}
	
	public function insertLinkLoTaAsNew($a_data) {
		$db = Zend_Registry::get('db');
		$db->beginTransaction();
		try {
			$id = $a_data['auto_id'];
			$table = new LinkageLoTas();
			$newrow = $table->createRow();
			if ($this->isExisted_Table($id)) {
				$oldrow = $table->fetchRow('auto_id = $id');
				$oldrow->delete();
			}
			foreach ( $a_data as $column => $value ) {
				$newrow->$column = $value;
			}
			$newrow->save();
		} catch ( Exception $exception ) {
			$db->rollBack();
			throw $exception;
		}
	}
	//end
}