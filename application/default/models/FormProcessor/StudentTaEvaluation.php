<?php
class FormProcessor_StudentTaEvaluation extends FormProcessor {
    public $input = array();
    public function process(Zend_Controller_Request_Abstract $req) {
        $years      = $req->getParam('years','');
        $blocks     = $req->getParam('blocks','');
        $pbls       = $req->getParam('pbls','');
        $types      = $req->getParam('types','');
        
        //Used by evaluation form to pre select input already searched on.
        $this->input['years']   = $this->stripOutSlashes($years);
        $this->input['blocks']  = $this->stripOutSlashes($blocks);
        $this->input['pbls']    = $this->stripOutSlashes($pbls);
        $this->input['types']   = $this->stripOutSlashes($types);
        
        $params['years']    = $this->filterArrayAsInt($years);
        $params['blocks']   = $this->filterArrayAsInt($blocks);        
        $params['pbls']     = $this->filterArrayAsString($pbls);
        $params['types']    = $this->filterArrayAsInt($types);
        $studentEvaluateService = new StudentEvaluateService();
        return $studentEvaluateService->getStudentTaEvaluation($params);     
    }
    
    private function filterArrayAsInt($params) {
        $return = array();
        if(!empty($params) && is_array($params)) {
            foreach($params as $param) {
                $sanitizeParam = (int)$this->sanitize($param);
                if($sanitizeParam > 0) {
                    $return[] = $sanitizeParam;
                }
            }
        }
        return $return;
    }

    private function filterArrayAsString($params) {
        $return = array();
        if(!empty($params) && is_array($params)) {
            foreach($params as $param) {
                $sanitizeParam = $this->sanitize($param);
                if(! empty($sanitizeParam) ) {
                    $return[] = $sanitizeParam;
                }
            }
        }
        return $return;
    }

    private function stripOutSlashes($params) {
        if(!empty($params) && is_array($params)) {
            foreach($params as $paramKey => &$paramVal) {
                $paramVal = stripslashes($paramVal);
            }
        }
        return $params;
    }
    
}
?>