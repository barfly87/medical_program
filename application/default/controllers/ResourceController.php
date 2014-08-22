<?php

class ResourceController extends Zend_Controller_Action {

    /**
     * Set up ACL info
     */
    public function init() {
        $studentActions = array('view','download','viewordownload','pblblock','image','raw','json','gettypedetail','transcode','taview','originalimage','viewecho360lecture');
        $staffActions = array('history','link','linksearchinfo','add','edit','remove','upload','uploadedit','sort','manage','copy','loose','findlooseresourceurl','viewmediabankhtml','viewmediabanklink','repair');
        $this->_helper->_acl->allow('student',$studentActions);
        $this->_helper->_acl->allow('staff',$staffActions);
    }

    public function viewAction(){
        $request = $this->getRequest();
        $mid = MediabankResourceConstants::sanitizeMid($request->getParam('mid',''));
        $type = $request->getParam('type','');
        $id = $request->getParam('id','');
        $resourceTypeId = $request->getParam('resourcetypeid',0);
        $mediabankResourceService = new MediabankResourceService();
        
        if($id == 'new') {
            $this->_helper->layout()->setLayout('popup');  
            $this->view->tempResource = true;     
        } else {
            $id = (int)$id;
        }
        $typeController = '';
        if(strlen($type)> 0 && in_array($type,array('lo','ta')) && (int)$resourceTypeId > 0 ) {
            $typeController = ($type == 'lo') ? 'learningobjective' : 'teachingactivity';
            $id = (int)$id;
            if($id > 0) {
                $resource = new MediabankResource();
                $this->view->addUrl = $resource->resourceExist($type,$id,$resourceTypeId,$mid,ResourceConstants::$TYPE_MEDIABANK);
            }
        }
        $this->view->allowEdit = $mediabankResourceService->allowEdit($mid);
        $this->view->fileInfo = $mediabankResourceService->getFileInfo($mid);
        $this->view->type = $type;
        $this->view->resourceid = $request->getParam('resourceid','');
        $this->view->id = $id;
        $this->view->resourceTypeId = $resourceTypeId;
        $this->view->typeController = $typeController;
        $this->view->div = $request->getParam('div','');
        $this->view->layout = $request->getParam('layout','');
        if ('simple' == $this->view->layout) {
        	$this->_helper->layout()->setLayout('basic');
        }
        $this->view->result = '';
        $this->view->mid = MediabankResourceConstants::encode($mid);
        if (!empty($mid)) {
            try {
                $pageInfo = array ('type' => $typeController, 'id' => $id);
                $resourceService = new MediabankResourceService();
                $result = $resourceService->getMetaData($mid);
                $this->view->result = $resourceService->updatePageInfo($result, $pageInfo);
                PageTitle::setTitle($this->view, $this->_request, array($this->view->result['title']));
                $this->view->loTaAttached = $resourceService->getLoTaAttachedToResource($mid);
            } catch(Exception $ex) {
                $this->view->error = true;
            }
        }
    }
    
    public function viewordownloadAction() {
        //$mandatory params $mid, $type & $id
        //if the request does not have $fn(filename) we can use $type and $id for creating filename
        $mid    = MediabankResourceConstants::sanitizeMid($this->_getParam('mid',''));
        $type   = $this->_getParam('type','');
        $id     = (int)$this->_getParam('id',0);
        
        //$optional parameter. $fn stands for filename. It would be used if send in the url when
        //downloading the file instead of the one got from getMetaData function.
        $fn     = $this->_getParam('fn','');
        
        if(!empty($mid) && !empty($type) && $id > 0) {
            $mediabankResourceService = new MediabankResourceService();
            //$pageInfo helps creating the filename if the resource is downloaded.
            $pageInfo = array('type' => $type, 'id' => $id);
            $result = $mediabankResourceService->getMetaData($mid);
            $result = $mediabankResourceService->updatePageInfo($result, $pageInfo);
            
            $forward = $this->_getAllParams();
            switch($result['mimeTypeCategory']) {
                //If the resource is of type text or html we need to use viewordownload.phtml to display it
                case 'text':
                    $this->view->result = $result;
                break;
                case 'video':
                   $this->_forward('view','resource','default',$forward);
                break;
                case 'image':
                    $this->_forward('originalimage','resource','default',$forward);                     
                break;
                //Default: forward the request to the download action
                //If $fn was send then [decode -> add file type extension -> encode it again]. 
                //OR
                //use the one from $result                
                default:
                    $forward['fn']  = (empty($fn)) ? $result['fileName'] : base64_encode(base64_decode($fn).'.'.$result['fileTypeExtension']);
                    $forward['mt']  = $result['mimeType'];
                    $this->_forward('download','resource','default',$forward);
                break;                    
            }
        } else {
            Zend_Registry::get('logger')->warn(__METHOD__." - Error ! mid => '$mid', type => '$type' and id => '$id' ");
            $this->throwError();
        }
    }
    
    public function viewmediabankhtmlAction() {
        $url = MediabankResourceConstants::decode($this->_getParam('url',''));
        if(!empty($url)) {
            ob_start();
            $mediabankUtility = new MediabankUtility();
            $mediabankUtility->curlUrlGet($url);
            $output = ob_get_clean();
            $doc = new DOMDocument();
            @$doc->loadHTML($output);
            $xpath = new DOMXpath($doc);
            $baseUrl = Compass::baseUrl().'/resource/viewmediabanklink?query=';
            $elements = $xpath->query("//img/@src | //a/@href");
            if (!is_null($elements)) {
                foreach ($elements as $element) {
                    $nodes = $element->childNodes;
                    foreach ($nodes as $node) {
                        $node->nodeValue = $baseUrl.base64_encode($node->nodeValue);
                    }
                }
            }
            $output =  $doc->saveHTML();
            $mediabankCss = $baseUrl.base64_encode("mediabank.css");
            $output = str_replace("mediabank.css", $mediabankCss, $output);
            echo $output;
            exit;
        }
        $this->throwError();
    }
    
    public function viewmediabanklinkAction() {
        ob_end_clean();
        $query =  MediabankResourceConstants::decode($this->_getParam('query',''));
        $query = preg_replace('/^\/mediabank\//', '', $query);
        $url = MediabankConstants::getMediabankBasePath().$query;
        $mediabankUtility = new MediabankUtility();
        $mediabankUtility->curlUrlGet($url, true);
        exit;
    }
    
    public function manageAction() {
        $resourceManageService = new ResourceManageService();
        $resourceManageService->processRequest($this->getRequest());
        if($resourceManageService->hasError() === true) {
            $this->_redirect('/index');
        }
        $this->view->results = $resourceManageService->getResults();
        $this->view->manageResourceData = $resourceManageService->getManageResourcesData();
        $this->view->pageType = $resourceManageService->getPageType();
        $this->view->pageTypeId = $resourceManageService->getPageTypeId();
        $this->_helper->layout()->setLayout('jquery_1_4_2');
    }
    
    public function copyAction() {
        $fromType   = trim($this->_getParam('from_type',''));
        $fromTypeId = (int)$this->_getParam('from_type_id', 0);
        $toType     = trim($this->_getParam('to_type',''));
        $toTypeId   = (int)$this->_getParam('to_type_id',0);
        
        $resourceCopy = new ResourceCopy();
        $result = $resourceCopy->process($fromType, $fromTypeId, $toType, $toTypeId);
        print Zend_Json_Encoder::encode($result);
        exit;
    }
    
    public function pblblockAction() {
        $request = $this->getRequest();
        $mid = trim($request->getParam('mid',''));
        $this->view->mid = $mid;
        $this->view->pblBlockFileName = trim($request->getParam('pblblockfn',''));
        $this->view->resourcesCount = (int)$request->getParam('resourcescount',0);
        
        if(!empty($mid)) {
            $mediabankResourceService = new MediabankResourceService();
            $result = $mediabankResourceService->getMetaData($mid);
            $this->view->result = $result;
        }
    }
    
    public function taviewAction() {
        $request        = $this->getRequest();
        $id             = (int)$request->getParam('id',0);
        $mid            = trim($request->getParam('mid',''));
        if($id == 0 || empty($mid)) {
            return;
        }
        $mediabankResourceService = new MediabankResourceService();
        $result = $mediabankResourceService->getMetaData($mid);
        $this->view->result = $result;
    }
    
    public function linksearchinfoAction() {
        $request = $this->getRequest();
        $mid = MediabankResourceConstants::sanitizeMid($request->getParam('mid',''));
        $this->view->dc         = array();        
        $this->view->native     = array();
        $this->view->mid        = $mid;
        $this->view->infotype   = $request->getParam('infotype', '');
        $this->view->midCount   = $request->getParam('midcount', '');
        if(! empty($mid) && !empty($this->view->midCount)) {
            $mediabankService = new MediabankResourceService();
            $this->view->dc = $mediabankService->getMediabankMetaData($mid, MediabankResourceConstants::$SCHEMA_dublinCore);
            $this->view->native = $mediabankService->getMediabankMetaData($mid, MediabankResourceConstants::$SCHEMA_native);
        }        
        //$this->_helper->viewRenderer->setNoRender();
        $this->_helper->layout()->disableLayout();
    }
    
    public function linkAction(){
        $request        = $this->getRequest();
        $typeId         = $request->getParam('id','');
        $query          = $request->getParam('query','');
        $type           = $request->getParam('type','');
        $resourceTypeId = $request->getParam('resourcetypeid',''); 
        
        $allowAccess = true;
        if($typeId == 'new') {
            $this->_helper->layout()->setLayout('popup');  
            $this->view->tempResource = true;
        } else {
            $typeId = (int)$typeId;
        }

        if(empty($typeId) || empty($type) || ! in_array($type, ResourceConstants::$TYPES_allowed) || empty($resourceTypeId)) {
            $this->_redirect('/index');
        }

        // ACL START
        if($typeId != 'new') {
            $params = array('type'=>$type,'auto_id'=>$typeId,'action'=>'link');
            $errormsg = 'You are not authorized to view this page';
            $allowAccess = $this->allowAccess($params,$errormsg);
        }
        if($allowAccess === false) {
            $this->_redirect('/index');
        }
        //ACL END
        $resourceLinkService = new ResourceLinkService($typeId, $type);
        $this->view->data = $resourceLinkService->getViewData();
        
        $fp = null;
        if(!empty($query)) {
            try {
                $fp = new FormProcessor_LinkResources();
                $fp->process($request);
                $query = $fp->query;
                $this->view->addedResources = $fp->addedResources;
            } catch(Exception $ex) {
                $this->view->error = true;
            }
        }
                
        
        $this->view->type           = $type;
        $this->view->typeId         = $typeId;
        $this->view->resourceTypeId = $resourceTypeId;
        $this->view->query          = $query;
        $this->view->fp             = $fp;
        
        PageTitle::setTitle($this->view, $request, array($this->view->typeName, $typeId));
    }
    
    public function addAction() {
        $request = $this->getRequest();
        $id = $request->getParam('id','');
        $resourceTypeId = (int) $request->getParam('resourcetypeid',0);
        $mid = MediabankResourceConstants::sanitizeMid($request->getParam('mid',''));
        $type = $request->getParam('type','');
        if(empty($id) || empty($resourceTypeId) || empty($type) || empty($mid) || ! in_array($type, ResourceConstants::$TYPES_allowed)) {
            print 'fail';
            exit;
        }
       
        // ACL START
        $params = array('type'=>$type,'auto_id'=>$id,'action'=>'add');
        $allowAccess = $this->allowAccess($params);
        if(! $allowAccess) {
            echo 'fail';
            exit();
        }
        //ACL END
        
        $result = 'fail';
        try {
            $resource = new MediabankResource();
            $addResource = $resource->addResource($type, $id, $resourceTypeId, $mid);
            if($addResource == true) {
                $result = 'success';
            }
            $this->reindex($id, $type);
        } catch(Exception $ex) {

        }
        echo $result;
        exit();
    }
    
    public function looseAction() {
        $this->_helper->layout()->setLayout('jquery_1_4_2');
        $subaction = $this->_getParam('subaction', '');
        $resourceAddedOnce = $this->_getParam(MediabankResourceConstants::$FORM_looseResourceAddedOnce, '');
        $resourceLooseService = new ResourceLooseService();
        $this->view->assign($resourceLooseService->getViewData($this->getRequest()));
        $this->view->resourceAddedMid = false;
        if($resourceLooseService->isSubactionAllowedByMediabank($subaction)) {
            $this->view->subaction = $subaction;
            //If user has already added one resource and is trying to add another we should not 
            //process the post params even though its a post request
            if($this->_request->isPost() 
                && $resourceAddedOnce != MediabankResourceConstants::$FORM_looseResourceAddedOnceVal) {
                $fp = new FormProcessor_ResourceUploadEdit_Loose();
                $resourceAddedMid = $fp->process($this->getRequest());
                if($fp->hasErrors()) {
                    $this->view->errors = $fp->getErrors();
                    $this->view->formdata = $fp->getFormData();
                } else {
                    $this->view->resourceAddedMid = $resourceAddedMid;
                }
            }
        } else {
            $this->throwError();
        }
    }
    
    public function findlooseresourceurlAction() {
        $mid = $this->_getParam('mid','');
        $url =  MediabankResourceConstants::createCompassLooseResourceUrl($mid);
        if(empty($url)) {
            echo 'fail';
        } else {
            echo $url;
        }
        exit;
        
    }    

    public function historyAction() {
        
        // Type ('lo', 'ta', 'block', 'pbl') and their respective Id
        $request    = $this->getRequest();
        $type       = $request->getParam('type','');
        $id         = (int)$request->getParam('id','');
        
        if(empty($type) || $id <= 0) {
            $this->throwError();
        }
        $mediabankResourceService = new MediabankResourceService();
        $this->view->result = $mediabankResourceService->getHistoryOfResources($type, $id);
        
        // Title
        $title  = base64_decode($request->getParam('title',''));
        $this->view->title = $title;
        
        // Return Url
        $returnUrl  = $request->getParam('returnurl','');
        $returnUrl  = (!empty($returnUrl)) ? base64_decode($returnUrl) : 'javascript:void(0);'; 
        $this->view->returnUrl = $returnUrl;
        PageTitle::setTitle($this->view, $request, array($title));
    	
    }
    
    public function sortAction() {
        $request = $this->getRequest();
        $data = $request->getParam('data','');
        $result = 'fail';
        if(!empty($data)) {
            $mediabankResource = new MediabankResource();
            $result = $mediabankResource->processSorting($data);
        }
        print $result;        
        die;
    }
    
    public function removeAction() {
        $request = $this->getRequest();
        $id = $request->getParam('id','');
        $mid = MediabankResourceConstants::sanitizeMid($request->getParam('mid',''));
        $type = $request->getParam('type','');
        $result = 'fail';
        if(empty($id) || empty($type) || empty($mid) || ! in_array($type, ResourceConstants::$TYPES_allowed)) {
            print 'fail';
            exit();
        }
        
        // ACL START
        $params = array('type'=>$type,'auto_id'=>$id,'action'=>'remove');
        $allowAccess = $this->allowAccess($params);
        if(! $allowAccess) {
            echo 'fail';
            exit();
        }
        //ACL END
        
        try {
            $resource = new MediabankResource();
            $removeResource = $resource->removeResource($id, $mid,$type);
            if($removeResource == true) {
                $result = 'success';
            }
            $this->reindex($id, $type);
        } catch(Exception $ex){

        }
        echo $result;
        exit();
    }
    
    public function originalimageAction() {
        $request = $this->getRequest();
        $mid = MediabankResourceConstants::sanitizeMid($request->getParam('mid',''));
        if(empty($mid)) {
            return false;
            exit();
        }
        $url = MediabankConstants::originalImageUrl() . $mid;

        $imageExist = @getimagesize($url);
        if($imageExist !== false) {
            ob_end_clean();
            header('Content-Type: '.MediabankResourceConstants::$imageMimeType);
            header("Content-Transfer-Encoding: binary");
            header('Accept-Ranges: bytes');
            
            $mediabankUtility = new MediabankUtility();
            $mediabankUtility->curlUrlGet($url);
            exit;
        } else {
            $this->throwError();
        }
    }

    public function imageAction() {
        ob_end_clean();
        $request = $this->getRequest();
        $mid = MediabankResourceConstants::sanitizeMid($request->getParam('mid',''));
        $imageSize =  (int)$request->getParam('size','');
        if(empty($mid)) {
            return false;
            exit();
        }
        if(empty($imageSize)) {
            $imageSize = 500;
        }
        $width = $imageSize;
        $height = $imageSize;
        
        $displayDefaultImage = true;
        
        $reqNoDefaultImage = (int)$request->getParam('nodefaultimage',0);
        if($reqNoDefaultImage == 1) {
            $displayDefaultImage = false;
        }
        
        $reqWidth = (int)$request->getParam('width',0);
        $reqHeight = (int)$request->getParam('height',0);
        if($reqWidth > 0 && $reqHeight > 0) {
            $width = $reqWidth;
            $height = $reqHeight;
        }
        
        $url = MediabankConstants::imageUrl();
        $url = str_replace('%%%HEIGHT%%%',$height, $url);
        $url = str_replace('%%%WIDTH%%%',$width, $url);
        
        $imagetype = $request->getParam('type',0);
        if($imagetype==='crop')
        	$url = str_replace('resize','crop:top=0:left=0', $url);
        	
		$url .= $mid;
		$headers = getallheaders();
		$curlData = array();
		if($displayDefaultImage === true) {
    		$curlData['imageWidth'] = $width;
    		$curlData['imageHeight'] = $height;
		}
		
		if (isset($headers['If-Modified-Since'])) {
		    $curlData['ifModifiedSince'] = $headers['If-Modified-Since'];
		}
		try {
    		$mediabankUtility = new MediabankUtility($curlData);
    		$mediabankUtility->curlUrlGet($url, true);
		} catch(Exception $ex){
	        Zend_Registry::get('logger')->warn($ex->getMessage());
            throw new Zend_Controller_Action_Exception("Page not found.", 404);
        }
        exit();
    }
    
    public function transcodeAction() {
        ob_end_clean();
        $request = $this->getRequest();
        $mid = MediabankResourceConstants::sanitizeMid($request->getParam('mid',''));
        if(empty($mid)) {
            return false;
            exit();
        }
        
        $transcodeTo = $request->getParam('to','');
        $url = MediabankConstants::transcodeFlvUrl();
        $mimeType = MediabankResourceConstants::$videoFlvMimeType;  
        $processHeightWidth = true;
              
        switch($transcodeTo) {
            case MediabankResourceConstants::$audioMp3Extension:
                $url = MediabankConstants::transcodeMp3Url();
                $mimeType = MediabankResourceConstants::$audioMp3MimeType;
                $processHeightWidth = false;
            break;
            case MediabankResourceConstants::$videoMp4Extension;
                $url = MediabankConstants::transcodeMp4Url();
                $mimeType = MediabankResourceConstants::$videoMp4MimeType;
            break;
        }
        
        if($processHeightWidth === true) {
            $width = 500;
            $height = 376;

            $reqWidth = (int)$request->getParam('width',0);
            $reqHeight = (int)$request->getParam('height',0);
            if($reqWidth > 0 && $reqHeight > 0) {
                $reqWidth = 
                $width = (round($reqWidth/4)) * 4;
                $height = (round($reqHeight/4)) * 4;
            }
            
            $url = str_replace('%%%HEIGHT%%%',$height, $url);
            $url = str_replace('%%%WIDTH%%%',$width, $url);
        }
        
        $url .= $mid;
        try {
            header('Content-Type: '. $mimeType);
            $mediabankUtility = new MediabankUtility();
            $mediabankUtility->curlUrlGet($url);
        } catch(Exception $ex){
            Zend_Registry::get('logger')->warn($ex->getMessage());
        }
        exit();
    }
    
    public function downloadAction(){
        ob_end_clean();
        $request = $this->getRequest();
        $mid = MediabankResourceConstants::sanitizeMid($request->getParam('mid',''));
        if(empty($mid)) {
            $this->_redirect('/index');
        }

        if($request->getParam('count','')=='true')
        	StudentResourceService::incrementDownloadCount($mid);
        $fileName = trim($request->getParam('fn',''));
        $mimeType = trim($request->getParam('mt',''));
        if(empty($mimetype))
        	$headers=true;
        else
        	$headers=false;
        $fileName = (!empty($fileName)) ? MediabankResourceConstants::decode($fileName) : false;
        $mimeType = (!empty($mimeType)) ? MediabankResourceConstants::decode($mimeType) : false;
                
        $mediabankUtility = new MediabankUtility();

        // IE fix
        if(ini_get('zlib.output_compression')) {
            ini_set('zlib.output_compression', 'Off');
        }

        if($mimeType!==false)
        	header('Content-Type: ' . $mimeType);
        if($fileName!==false)
        	header('Content-Disposition: attachment; filename="'.$fileName.'"');
        header("Content-Transfer-Encoding: binary");
        header('Accept-Ranges: bytes');

        $mediabankUtility->curlDownloadResource($mid,$headers);
        exit();
    }

    public function rawAction() {
        ob_end_clean();
        $request = $this->getRequest();
        $mid = MediabankResourceConstants::sanitizeMid($request->getParam('mid',''));
        $mediabankUtility = new MediabankUtility();
        ob_start();
        $mediabankUtility->curlDownloadResource($mid,true);
        $text = ob_get_contents();
        ob_end_clean();
        echo htmlspecialchars_decode($text);
        exit();
    }
    
    public function editAction(){
        try {
            $this->_forward('uploadedit','resource','default',$this->_request->getParams());
        } catch(Exception $ex){

        }
    }
    
    public function uploadAction(){
        try {
            $this->_forward('uploadedit','resource','default',$this->_request->getParams());
        } catch(Exception $ex){

        }
    }    
 
    public function jsonAction() {
        $request = $this->getRequest();
        $mid = MediabankResourceConstants::sanitizeMid($request->getParam('mid',''));
        if(empty($mid)) {
            echo '{}';
        } else {
            $mediabankService = new MediabankResourceService();
            $result = $mediabankService->getMetaData($mid);
            echo  Zend_Json::encode($result);
        }        
        exit();
    }
    
    public function uploadeditAction() {
        $this->_helper->layout()->setLayout('jquery_1_4_2');
        
        $resourceUploadEditRequestFactory = new ResourceUploadEditRequestFactory();
        $resourceUploadEditRequest = $resourceUploadEditRequestFactory->getRequestObject($this->getRequest());
        if(! $resourceUploadEditRequest instanceof ResourceUploadEditRequest || 
            $resourceUploadEditRequest->hasError()){
            $this->_redirect('/index');
        }
        
        //$request object would contain all the processed request parameters + other dynamically created
        $request = $resourceUploadEditRequest->getRequestParams();
        if($request->typeId != 'new') {
            $params = array('type' => $request->type, 'auto_id' => $request->typeId, 'action' => $request->action);
            //If user is not authorized to access this page he/she would be redirected to error page.          
            $allowAccess = $this->allowAccess($params, 'You are not authorized to view this page');
            
            if($allowAccess === false) {
                //Log error and return
                $resourceUploadEditRequest->logUnauthorizedError();
                return;
            }
        } else { //If typeId is new then set the layout as popup
            $this->_helper->layout()->setLayout('jquery_1_4_2_popup');
        }
        //set request parameters in the view object
        $this->view->assign(array('request' => $request));
        
        $resourceUploadEditService = new ResourceUploadEditService($request);
        $page = $resourceUploadEditService->getViewData();
        $this->view->assign(array('page' => $page));
        if($page->actionAllowedByMediabank === false) {
            $this->_redirect('/index');
        }
        $this->view->resourceTypeIdSelected = $request->resourceTypeId;
        $this->view->resourceTypes  = MediabankResourceConstants::getListOfResourceTypes($request->type);
        PageTitle::setTitle($this->view, $this->_request, array($page->actionName, $page->typeName, $request->typeId));
        if ($this->_request->isPost()) {
            if($request->typeId == 'new') {
                $fp = new FormProcessor_ResourceUploadEdit_Temp();
            } else if($request->action == 'upload') {
                $fp = new FormProcessor_ResourceUploadEdit_Upload();
            } else if($request->action == 'edit') {
                $fp = new FormProcessor_ResourceUploadEdit_Edit();
            } else {
                $this->_redirect('/index');
            }
            
            //$request contains parameters received in GET url which are already PROCESSED
            $fp->setRequestGet($request);
            
            //$this->_request would be used for processing post parameters which are NOT PROCESSED yet
            $fp->process($this->_request);
            $formdata = $fp->getFormData();
            if($fp->hasErrors()) {
                $this->view->errors = $fp->getErrors();
                $this->view->formdata = $formdata;
            } else {
                $this->reindex($request->typeId, $request->type);
                if(empty($formdata->tempResourceDetails)) {
                    $this->_redirect(str_replace(Compass::baseUrl(), '', $page->returnUrl));
                } else {
                    $this->view->tempResourceDetails    = $formdata->tempResourceDetails;
                    $this->view->type                   = $request->type;
                    $this->view->success                = $formdata->tempResourceSuccessMsg;
                }
            }
        }        
    }
    
    public function gettypedetailAction() {
        $req    = $this->getRequest();
        $los    = $req->getParam('lo','');
        $tas    = $req->getParam('ta','');
        $type  = array();

        $resrcService = new MediabankResourceService();
       
        if( !empty($los)) {
            foreach($los as $lo) {
                $type['lo'][] = $lo;
            }
        }
        if( !empty($tas)) {
            foreach($tas as $ta) {
                $type['ta'][] = $ta;
            }
        }
        if(count($type) > 0) {
            $return = $resrcService->getTypeDetail($type);
            echo Zend_Json::encode($return);
            exit;
        } 
        print '{}';
        exit;
    }

    private function allowAccess($params,$errormsg = null) {
        $allowAccess = true;
        $access = ResourceAcl::access($params);
        if(isset($access['allow'])) {
            if($access['allow'] !== true) {
                if(isset($access['err']['staff'])) {
                    $errormsg = $access['err']['staff'];
                }
                $allowAccess = false;
            }
        } else {
            $allowAccess = false;
        }
        if(! $allowAccess) {
            if(!is_null($errormsg) && is_string($errormsg) && strlen(trim($errormsg)) > 0) {
                $this->view->errormsg = trim($errormsg);
                $this->render('error');
            }
            return false;
        }
        return true;
    }
    
    private function reindex($id, $type){
        $linkFinder = new LinkageLoTas();
        
        //get the released status.
        $statusFinder = new Status();
        $released_status = $statusFinder->getIdForStatus(Status::$RELEASED);
        
        if ($id !== 'new') {
	        if ($type == 'ta') {
	        	$rows = $linkFinder->fetchAll("ta_id = $id AND status = $released_status");
	        } else {
	        	$rows = $linkFinder->fetchAll("lo_id = $id AND status = $released_status");
	        }
	        foreach ($rows as $row) {
	        	$row->notifyObservers('post-update');
	        }
        }
    }
    
    public function viewecho360lectureAction() {
        $presentationId = $this->_request->getParam('presentationid', null);
        if(!is_null($presentationId)) {
            $media = $this->_request->getParam('media', null);
            $echo360Service = new Echo360Service();
            $result = $echo360Service->generateUrl($presentationId, $media);
            if(!empty($result) && is_array($result) && isset($result['success']) 
                && $result['success'] === true && isset($result['url']) && strlen(trim($result['url'])) > 5) {
                header("Location: ".$result['url']);
                exit;
            }
            //If OAuth is not working for some reason try this and stop seamless login in ESS
            /*
            $url = 'http://view.streaming.sydney.edu.au/ess/echo/presentation/'.$presentationId;
            if($media != null) {
                $url .='/media.'.$media;
            }
            header("Location: ".$url);
            exit;
            */
        }
        $this->throwError();
    }
    
    public function repairAction() {
        $mid = $this->_request->getParam('mid', '');
        $mid = MediabankResourceConstants::sanitizeMid($mid);
        if(!empty($mid)) {
            MediabankCacheMetadata::removeCache($mid);
            if(strstr($mid, MediabankResourceConstants::$COLLECTION_echo360) !== false) {
                MediabankCacheEcho360::purgeMetadataForMid($mid);
            } else if (strstr($mid, MediabankResourceConstants::$COLLECTION_lectopia) !== false) {
                MediabankCacheLectopia::purgeMetadataForMid($mid);
            }
            echo 'Successfully repaired.';
        } else {
            echo 'Error ! No mid given';
        }
        exit;
    }
    
    private function throwError() {
        throw new Zend_Controller_Action_Exception("Page not found.", 404);
    }
}

