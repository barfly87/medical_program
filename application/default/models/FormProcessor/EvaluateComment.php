<?php
class FormProcessor_EvaluateComment extends FormProcessor {

    public function process(Zend_Controller_Request_Abstract $req)  {
        $comment = $this->sanitize(trim($req->getParam('comment','')));
        $type = $this->sanitize($req->getParam('feedback_type',''));
        $type_id = $this->sanitize($req->getParam('feedback_type_id',''));
        
        $len = strlen($comment);
        if($len > 5000) {
            $comment = substr($comment,0,5000).' ..... (Comment length was stripped from '.$len.' to 5000)';
        }
        $studentEvaluate = new StudentEvaluate();
        $dataParams = StudentEvaluateConst::getDataFromRequestParams($req->getParams());
        $result = $studentEvaluate->insertComment($comment,$type,$type_id,'',$dataParams);
        $message = 'fail';
        if($result == 'duplicate_comment') {
            $message = 'success';
        } else if($result !== false) {
             if((int)$result > 0) {
                $this->_storeDataParams((int)$result, $dataParams);
                $message = 'success';
            }
        }
        print $message;
    }
    
    private function _storeDataParams($student_evaluate_id, $params) {
        if(count($params) > 0) {
            $studentEvaluateData = new StudentEvaluateData();            
            foreach($params as $key => $val) {
                $val = $this->sanitize($val);
                $studentEvaluateData->insertData($student_evaluate_id,$key,$val);
            }
        }
    }
    
}
