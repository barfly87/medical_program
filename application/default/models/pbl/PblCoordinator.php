<?php

class PblCoordinator extends Zend_Db_Table_Abstract {
    protected $_name = 'pblcoordinator';
    
    public static $COL_domain_id    = 'domain_id';
    public static $COL_pbl_id       = 'pbl_id';
    public static $COL_uid          = 'uid';

    protected $_referenceMap = array(
        'Pbls' => array(
            'columns' => array('pbl_id'),
            'refTableClass' => 'Pbls',
            'refColumns' => array('auto_id')
        ),
        'Domains' => array(
            'columns' => array('domain_id'),
            'refTableClass' => 'Domains',
            'refColumn' => array('auto_id')
        )
    );
    
    public function addPblCoordinator($data) {
        try {
            $row = $this->insert($data);
            return ($row != null) ? true: false;
        } catch(Exception $ex) {
            Zend_Registry::get('logger')->debug("\n".$ex->getMessage()."\n".$ex->getTraceAsString()."\n\n");
            return false;           
        }
    }
}