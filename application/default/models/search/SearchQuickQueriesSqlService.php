<?php
class SearchQuickQueriesSqlService {
    
    public function processStudent1($limit){
        $db = Zend_Registry::get("db");       
        $select = $db->select()->distinct()
                    ->from(array('l'=>'learningobjective'),array('auto_id'))
                    ->join(array('llt' => 'link_lo_ta'),'l.auto_id = llt.lo_id',array())
                    ->where("llt.status=4")
                    ->limit($limit)
                    ->order(array('l.auto_id DESC' ));
        $stmt = $db->query($select);                    
        $rows = $stmt->fetchAll();
        if(count($rows) > 0) {
            $tempResult = array();
            foreach($rows as $row) {
                $tempResult[] = $row['auto_id'];
            }
            return $tempResult;
        }
        return false;
    }

    public function processStudent2($limit){
        $db = Zend_Registry::get("db");       
        $select = $db->select()->distinct()
                    ->from(array('t'=>'teachingactivity'),array('auto_id'))
                    ->join(array('llt' => 'link_lo_ta'),'t.auto_id = llt.ta_id',array())
                    ->where("llt.status=4 and t.name not like ('Clinical Day - %')")
                    ->limit($limit)
                    ->order(array('t.auto_id DESC' ));
        $stmt = $db->query($select);                    
        $rows = $stmt->fetchAll();
        if(count($rows) > 0) {
            $tempResult = array();
            foreach($rows as $row) {
                $tempResult[] = $row['auto_id'];
            }
            return $tempResult;
        }
        return false;
    }

}
?>