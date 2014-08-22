<?php

class LinkageLoAssessTypes extends Zend_Db_Table_Abstract {
    protected $_name = 'link_lo_assesstype';
    protected $_primary = 'auto_id';
    protected $_referenceMap = array(
        'LearningObjective' => array(
            'columns' => array('lo_id'),
            'refTableClass' => 'LearningObjectives',
            'refColumns' => array('auto_id')
        ),
        'Review' => array(
            'columns' => array('assesstype_id'),
            'refTableClass' => 'AssessTypes',
            'refColumns' => array('auto_id')
        )
    );
}