<?php
class EvaluateTa extends Zend_Db_Table_Abstract {

    protected $_name = 'evaluate_ta';

    public function getEvaluationForLoggedInUserForTaid($taAutoId) {
        $return = array();
        try {
            if((int)$taAutoId > 0) {
                $selectEvaluations = $this->select()
                ->where('ta_auto_id = ?',  $taAutoId)
                ->where('student_id = ?', UserAcl::getUid())
                ->order('auto_id DESC')
                ->limit(1);
                $row = $this->fetchAll($selectEvaluations);
                if($row->count() > 0) {
                    $rowArr = $row->toArray();
                    return $rowArr[0];
                }
                return false;
            }
            return false;
        } catch (Exception $ex) {
            $error = $ex->getMessage().PHP_EOL.$ex->getTraceAsString().PHP_EOL;
            Zend_Registry::get('logger')->warn(PHP_EOL."CLASS\t: ".__CLASS__.PHP_EOL."METHOD\t: ".__METHOD__.PHP_EOL."Error\t: ".$error.PHP_EOL);
            return false;
        }
    }

    public function getEvaluationForTaIds($taAutoids) {
        try {
            $selectEvaluations = $this->select()->where('ta_auto_id IN (?)', $taAutoids)->order('ta_auto_id ASC')->order('auto_id ASC');
            $rows = $this->fetchAll($selectEvaluations);
            if($rows->count() > 0) {
                return $rows->toArray();
            }
            return array();
        } catch (Exception $ex) {
            $error = $ex->getMessage().PHP_EOL.$ex->getTraceAsString().PHP_EOL;
            Zend_Registry::get('logger')->warn(PHP_EOL."CLASS\t: ".__CLASS__.PHP_EOL."METHOD\t: ".__METHOD__.PHP_EOL."Error\t: ".$error.PHP_EOL);
            return array();
        }
    }
    
    public function groupByColumn($taAutoids, $column) {
        $return = array();
        try {
            if(!empty($taAutoids)) {
                $selectEvaluations = $this->select()
                ->from($this->_name, array($column, 'count(*)'))
                ->group($column)
                ->where('ta_auto_id in (?)',  $taAutoids);
                $rows = $this->fetchAll($selectEvaluations);
                if($rows->count() > 0) {
                    return $rows->toArray();
                }
                return false;
            }
            return false;
        } catch (Exception $ex) {
            $error = $ex->getMessage().PHP_EOL.$ex->getTraceAsString().PHP_EOL;
            Zend_Registry::get('logger')->warn(PHP_EOL."CLASS\t: ".__CLASS__.PHP_EOL."METHOD\t: ".__METHOD__.PHP_EOL."Error\t: ".$error.PHP_EOL);
            return false;
        }
    }
    
    public function getUniqueTaTypesEvaluated() {
        $return = array();
        try {
            $selectTaType = $this->select()
                ->from($this->_name, array('ta_type_id', 'ta_type'))
                ->group(array('ta_type_id', 'ta_type'))
                ->order('ta_type');
                $rows = $this->fetchAll($selectTaType);
                if($rows->count() > 0) {
                    return $rows->toArray();
                }
                return array();
        } catch (Exception $ex) {
            $error = $ex->getMessage().PHP_EOL.$ex->getTraceAsString().PHP_EOL;
            Zend_Registry::get('logger')->warn(PHP_EOL."CLASS\t: ".__CLASS__.PHP_EOL."METHOD\t: ".__METHOD__.PHP_EOL."Error\t: ".$error.PHP_EOL);
            return false;
        }
    }
    
    public function getTableColumns() {
        $info = $this->info();
        if(isset($info['cols']) && !empty($info['cols'])) {
            return $info['cols'];
        }
        return array();
    }
}