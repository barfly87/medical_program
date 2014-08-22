<?php
abstract class FormProcessor_ResourceUploadEdit_Abstract extends FormProcessor {
    
    private $errors                         = array();
    
    protected $requestPost                  = null;
    protected $requestGet                   = null;
    
    private $requestTempResource            = null;
    private $requestResourceTypeIdPost      = null;
    private $requestTitle                   = null;
    private $requestDescription             = null;
    private $requestCopyright               = null;
    private $requestOther                   = null;
    private $requestAuthor                  = null;
    private $copyright                      = null;
    private $requestHtmlText                = null;
    private $requestFile                    = null;
    private $requestURL                     = null;
    private $requestTabSelected             = null;
    protected $requestActionName            = null;
    
    private $page                           = null;
    private $mediabankError                 = null;
    protected $form                         = null;
    
    protected $mediabankResource            = null;
    protected $mediabankResourceHistory     = null;
    
    protected $tempResourceSuccessMsg       = null;
    protected $tempResourceDetails          = null;
    
    public function initParent(Zend_Controller_Request_Abstract $requestPost) {
        $this->requestPost              = $requestPost;
        $this->mediabankResource        = new MediabankResource();
        $this->mediabankResourceHistory = new MediabankResourceHistory();
    }
    
    public function hasErrors() {
        if(empty($this->errors)) {
            return false;
        }
        return true;
    }
    
    public function getErrors() {
        return $this->errors;
    }
    
    protected function processTempResource() {
        $tempResource = trim($this->requestPost->getParam(
                            MediabankResourceConstants::$FORM_tempResource,''));
        if(! empty($tempResource) && 
            $tempResource == MediabankResourceConstants::$FORM_tempResourceVal) {
            $this->requestTempResource = $tempResource;
        }       
    }
    
    protected function processResourceTypeIdPost() {
        $resourceTypeIdPost = trim($this->requestPost->getParam(
                                MediabankResourceConstants::$FORM_resourceTypeIdPost,''));
        if(empty($resourceTypeIdPost)) {
            $this->errors[] = MediabankResourceConstants::$FORM_resourceTypeIdPostError;
            return;
        }       
        $this->requestResourceTypeIdPost = $resourceTypeIdPost;
    }
    
    protected function processTitle() {
        $requestTitle = trim($this->requestPost->getParam(
                                MediabankResourceConstants::$FORM_title,''));
        if(empty($requestTitle)) {
            $this->errors[] = MediabankResourceConstants::$FORM_titleError;
            return;
        }       
        $this->requestTitle = $requestTitle;
        $this->form['title'] = $requestTitle; 
    }
    
    protected function processDescription() {
        $this->requestDescription = trim($this->requestPost->getParam(
                                            MediabankResourceConstants::$FORM_description,''));
        $this->form['desc'] = $this->requestDescription;
    }
    
    protected function processAuthor() {
        $this->requestAuthor = trim($this->requestPost->getParam(
                                            MediabankResourceConstants::$FORM_author,''));
        $this->form['author'] = $this->requestAuthor;
    }
    
    
    
    protected function processCopyright() {
        $this->requestCopyright = trim($this->requestPost->getParam(
                                        MediabankResourceConstants::$FORM_copyright,''));  
        $this->requestOther     = trim($this->requestPost->getParam(
                                        MediabankResourceConstants::$FORM_other,''));    
        
        switch($this->requestCopyright) {
            case Utilities::getCopyrightUni():
                $this->copyright = $this->requestCopyright;
            break;
            case MediabankResourceConstants::$FORM_other:
                if(! empty($this->requestOther)) {
                    $this->copyright = $this->requestOther;
                } else {
                    $this->errors[] = MediabankResourceConstants::$FORM_otherError;
                }
            break;
            default:
                $this->errors[] = MediabankResourceConstants::$FORM_copyrightError;
                return;
            break;
        }
        $this->form['copyright'] = $this->copyright;
    }
    
    /**
     * @param $resourceRequired should be 'true' when you are adding a new resource. If the user is trying to 'ADD' a 
     * resource its mandatory that the resource does exist either in the form of html text, a file or a url.<br /><br />
     * 
     * If user is 'EDITING' the resource its not mandatory that they should re upload the resource. Only if the
     * resource is updated with a new resource it will be send to mediabank.
     */
    protected function processResource($resourceRequired = true) {
        $tabSelected = $this->requestPost->getParam(
                                            MediabankResourceConstants::$FORM_tabSelected, '');
        switch($tabSelected) {
            case 0:
                $this->processText($resourceRequired);
            break;
            case 1:
                $this->processFile($resourceRequired);
            break;
            case 2:
                $this->processUrl($resourceRequired);
            break;
            default:
                $this->errors[] = MediabankResourceConstants::$FORM_tabSelectedError;
                return;
            break;                
        }
        $this->requestTabSelected = $tabSelected;
    }
    
    private function processText($resourceRequired) {
        $requestHtmlText = trim($this->requestPost->getParam(
                                    MediabankResourceConstants::$FORM_htmlText),'');
        if(strlen(trim(strip_tags($requestHtmlText))) <= 0) {
            if($resourceRequired === true) {
                $this->errors[] = MediabankResourceConstants::$FORM_htmlTextError;
            }
            return;
        }
        $this->requestHtmlText          = $requestHtmlText;
        $this->form['html']             = $requestHtmlText;
        $this->form['processFile']      = 'html';
    }
    
    private function processFile($resourceRequired) {
        if(isset($_FILES[MediabankResourceConstants::$FORM_file]) 
            && $_FILES[MediabankResourceConstants::$FORM_file]['error'] > 0) {
            if($resourceRequired === true) {
                $this->errors[] = MediabankResourceConstants::$FORM_fileError;
            }
            return;
        } 
        $this->requestFile              = $_FILES[MediabankResourceConstants::$FORM_file]['tmp_name'];
        $this->form['fileLocation']     = $_FILES[MediabankResourceConstants::$FORM_file]['tmp_name'];
        $this->form['processFile']      = 'any';
    }
    
    private function processUrl($resourceRequired) {
        $requestURL = trim($this->requestPost->getParam(MediabankResourceConstants::$FORM_URL,''));
                                            
        $this->requestURL = $requestURL;                                    
        if(strlen($requestURL) <= 0) {
            if($resourceRequired === true) {
                $this->errors[] = MediabankResourceConstants::$FORM_URLError;
            }
            return;
        } 
        $isUrlValid =  false;
        try {
            $isUrlValid = Zend_Uri::check($requestURL);
            if(! $isUrlValid) {
                $this->errors[] = MediabankResourceConstants::$FORM_URLInvalidError;
                return;
            }
            $this->form['processFile']      = 'URL';
            $this->form['URL'] = $requestURL;
        } catch (Exception $ex) {
            $this->errors[] = $ex->getMessage();
        }
    }
    
    public function setRequestGet($requestGet) {
        $this->requestGet = $requestGet;
    }
    
    protected function uploadMediabankResource() {
        if(! $this->hasErrors()) {
            $return = $this->processForm();
            if(isset($return['mid'])) {
                if($this->requestGet->typeId > 0 
                   && strlen($this->requestGet->type) > 0 
                   && strlen($this->requestGet->resourceTypeId) > 0) {
                    $id = $this->mediabankResource->addResource(
                            $this->requestGet->type, $this->requestGet->typeId, 
                            $this->requestResourceTypeIdPost, $return['mid']
                          );
                    if($id === false) {
                        $this->errors[] = MediabankResourceConstants::$DB_addResourceError; 
                    }
                }
            } else {
                $this->setMediabankError($return['mediabankError']);
            }
        }
    }
    
    protected function looseUploadMediabankResource() {
        if(! $this->hasErrors()) {
            $this->form['status'] = MediabankResourceConstants::$METADATA_status_LOOSE;
            $return = $this->processForm();
            if(! isset($return['mid'])) {
                $this->setMediabankError($return['mediabankError']);
            } else {
                return $return['mid'];
            }
        }
        return false;
    }
    
    protected function editMediabankResource() {
        if(! $this->hasErrors()) {
            $this->form['mid'] = $this->requestGet->mid;
            $return = $this->processForm();
            if(! empty($this->requestGet->resourceId)) {
                $historyUpdated = $this->mediabankResourceHistory->setHistory(
                                        $this->requestGet->resourceId,'edit');
                if($historyUpdated === false) {
                    $this->errors[] = MediabankResourceConstants::$DB_updateResourceHistoryError; 
                }
            }
            if(!empty($this->requestGet->resourceId) && ! empty($this->requestResourceTypeIdPost)) { 
                $resourceUpdated = $this->mediabankResource->updateResource(
                                        $this->requestGet->resourceId,
                                        array('resource_type_id' => $this->requestResourceTypeIdPost));
                if($resourceUpdated === false) {
                    $this->errors[] = MediabankResourceConstants::$DB_updateResourceError;    
                }
            }
            if(! isset($return['mid'])) {
                $this->setMediabankError($return['mediabankError']); 
            }
        }            
    }
    
    protected function processActionName() {
        $requestActionName = trim($this->requestPost->getParam(
                                    MediabankResourceConstants::$FORM_actionName,''));
        if(empty($requestActionName)) {
            $this->errors[] = MediabankResourceConstants::$FORM_actionNameError;
            return;
        }       
        $this->requestActionName = $requestActionName;
    }
    
    protected function tempMediabankResource() {
        if(! $this->hasErrors()) { 
            $success = '';       
            if($this->requestActionName == MediabankResourceConstants::$FORM_actionNameUpload) {
                $success = MediabankResourceConstants::$FORM_MSG_addSuccess;
            } else if($this->requestActionName == MediabankResourceConstants::$FORM_actionNameEdit){
                $this->form['mid'] = $this->requestGet->mid;
                $success = MediabankResourceConstants::$FORM_MSG_editSuccess;
            }
            $return = $this->processForm();
            if(isset($return['mid'])) {
                $this->tempResourceDetails      = $this->getTempResourceDetails($return['mid']);
                $this->tempResourceSuccessMsg   = $success;
            } else {
                $this->setMediabankError($return['mediabankError']);
            }
        }            
    }
    
    private function getTempResourceDetails($mid){
        $resourceService = new MediabankResourceService();
        $requestTitle = $resourceService->getTitleForMid($mid);
        $result = array(
                    'mid'               => $mid,
                    'title'             => $requestTitle,
                    'resourceTypeId'    => $this->requestResourceTypeIdPost,
                    'actionName'        => $this->requestActionName
        );
        if(! empty($this->requestGet->div)) {
            $result['div'] = $this->requestGet->div;
        }
        return $result;
    }
    
    private function setMediabankError($medibankError){
        $this->errors[] = MediabankResourceConstants::$FORM_mediabankError;
        $this->mediabankError = $medibankError;
    }
    
    protected function processForm() {
        $mediabankFormService = new MediabankFormService($this->form);
        return $mediabankFormService->process();
    }
    
    public function getFormData() {
        $return = new stdClass;
        $return->resourceTypeIdPost       = $this->requestResourceTypeIdPost;
		$return->title                    = $this->requestTitle;
		$return->description              = $this->requestDescription;
		$return->author                   = $this->requestAuthor;
		$return->copyright                = $this->requestCopyright;
		$return->other                    = $this->requestOther;
		$return->htmlText                 = $this->requestHtmlText;
		$return->file                     = $this->requestFile;
		$return->URL                      = $this->requestURL;
		$return->tabSelected              = $this->requestTabSelected;
	    $return->mediabankError           = $this->mediabankError;
	    ## Only used if the resource is for temp (lo or ta)
	    $return->tempResourceDetails      = $this->tempResourceDetails;
	    $return->tempResourceSuccessMsg   = $this->tempResourceSuccessMsg;
		return $return;
    }
    
}
?>