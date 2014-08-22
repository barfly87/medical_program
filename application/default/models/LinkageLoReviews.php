<?php

class LinkageLoReviews extends Zend_Db_Table_Abstract {
    protected $_name = 'link_lo_review';
    protected $_primary = 'auto_id';
    protected $_referenceMap = array(
        'LearningObjective' => array(
            'columns' => array('lo_id'),
            'refTableClass' => 'LearningObjectives',
            'refColumns' => array('auto_id')
        ),
        'Review' => array(
            'columns' => array('review_id'),
            'refTableClass' => 'Reviews',
            'refColumns' => array('auto_id')
        )
    );
}