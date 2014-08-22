<?php

class LinkageHistories extends Zend_Db_Table_Abstract {
    protected $_name = 'link_lo_ta_history';
    protected $_primary = 'auto_id';
    protected $_rowClass = 'LinkageHistory';
    protected $_referenceMap = array(
        'LearningObjective' => array(
            'columns' => array('lo_id'),
            'refTableClass' => 'LearningObjectives',
            'refColumns' => array('auto_id')
		),
        'TeachingActivity' => array(
            'columns' => array('ta_id'),
            'refTableClass' => 'TeachingActivities',
            'refColumns' => array('auto_id')
		),
        'Status' => array(
            'columns' => array('status'),
            'refTableClass' => 'Status',
            'refColumns' => array('auto_id')
		),
        'NewStatus' => array(
            'columns' => array('new_status'),
            'refTableClass' => 'Status',
            'refColumns' => array('auto_id')
		),
        'Strength' => array(
            'columns' => array('strength'),
            'refTableClass' => 'Strengths',
            'refColumns' => array('auto_id')
		)
	);
}