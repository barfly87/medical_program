<?php

class CurriculumAreasStatus extends Zend_Db_Table_Abstract {
    protected $_name = 'lk_curriculumareas_status';
    protected $_primary = 'auto_id';

    CONST CURRENT       = 'Current';
    CONST CURRENT_ID    = 1;
    
    CONST ARCHIVED      = 'Archived';
    CONST ARCHIVED_ID   = 2;     
}    
