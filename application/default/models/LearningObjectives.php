<?php

class LearningObjectives extends Zend_Db_Table_Abstract {
    protected $_name = 'learningobjective';
    protected $_primary = 'auto_id';
    protected $_rowClass = 'LearningObjective';
    protected $_dependentTables = array('LinkageLoReviews', 'LinkageLoAssessTypes');
    protected $_referenceMap = array(
        'Theme1' => array(
            'columns' => array('theme1'),
            'refTableClass' => 'Themes',
            'refColumns' => array('auto_id')
        ),
        'Theme2' => array(
            'columns' => array('theme2'),
            'refTableClass' => 'Themes',
            'refColumns' => array('auto_id')
        ),
        'Theme3' => array(
            'columns' => array('theme3'),
            'refTableClass' => 'Themes',
            'refColumns' => array('auto_id')
        ),    
        'Curriculumarea1' => array(
            'columns' => array('curriculumarea1'),
            'refTableClass' => 'CurriculumAreas',
            'refColumns' => array('auto_id')
        ),
        'Curriculumarea2' => array(
            'columns' => array('curriculumarea2'),
            'refTableClass' => 'CurriculumAreas',
            'refColumns' => array('auto_id')
        ),
        'Curriculumarea3' => array(
            'columns' => array('curriculumarea3'),
            'refTableClass' => 'CurriculumAreas',
            'refColumns' => array('auto_id')
        ),        
        'Skill' => array(
            'columns' => array('skill'),
            'refTableClass' => 'Skills',
            'refColumns' => array('auto_id')
        ),
        'Discipline1' => array(
            'columns' => array('discipline1'),
            'refTableClass' => 'Discipline',
            'refColumns' => array('auto_id')
        ),
        'Discipline2' => array(
            'columns' => array('discipline2'),
            'refTableClass' => 'Discipline',
            'refColumns' => array('auto_id')
        ),
        'Discipline3' => array(
            'columns' => array('discipline3'),
            'refTableClass' => 'Discipline',
            'refColumns' => array('auto_id')
        ),
        'Achievement' => array(
            'columns' => array('achievement'),
            'refTableClass' => 'Achievements',
            'refColumns' => array('auto_id')
        ),
        'System' => array(
            'columns' => array('system'),
            'refTableClass' => 'Systems',
            'refColumns' => array('auto_id')
        ),
        'JMO' => array(
            'columns' => array('jmo'),
            'refTableClass' => 'Jmos',
            'refColumns' => array('auto_id')
        ),
        'GradAttrib' => array(
            'columns' => array('gradattrib'),
            'refTableClass' => 'GradAttribs',
            'refColumns' => array('auto_id')
        ),
        'Domains' => array(
        	'columns' => array('owner'),
        	'refTableClass' => 'Domains',
        	'refColumns' => array('auto_id'),
        )
    );

    public function fetchLatest($count = 10) {
        return $this->fetchAll(null, 'date_created DESC', $count);
    }
    
    /** Get learning objective based on id */
    public function getLo($lo_id) {
    	try {
    		$row = $this->find($lo_id)->current();
    	} catch (Exception $e) {
    		throw new Exception("Could not find learning objective $lo_id.");
    	}
    	if (!$row) {
    		throw new Exception("Could not find learning objective $lo_id.");
    	}
    	return $row;
    }
    
    /** Get learning objective based on id */
    public function getLoByLOID($loid) {
    	try {
    		$row = $this->fetchRow('loid = '.$loid);
    	} catch (Exception $e) {
    		throw new Exception("Could not find learning objective for $lo_id.");
    	}
    	if (!$row) {
    		throw new Exception("Could not find learning objective for $lo_id.");
    	}
    	return $row;
    }
    
    
    /** Get the list of all learning objectives that's currently being used */
    public function getReleasedLos() {
    	$result = array();
    	$lkFinder = new LinkageLoTas();
    	$statusFinder = new Status();
    	
    	$allLos = $this->fetchAll();
    	foreach ($allLos as $lo) {
    		$linkage = $lo->findDependentRowset('LinkageLoTas', 'LearningObjective', $lkFinder->select()->where('status = ?', $statusFinder->getIdForStatus(Status::$RELEASED)));
    		if (count($linkage) > 0) {
    			$result[] = $lo;
    		}
    	}
    	return $result;
    }
}