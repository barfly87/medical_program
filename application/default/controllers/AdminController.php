<?php
class AdminController extends Zend_Controller_Action {

	/**
	 * Set up ACL info
	 */
	public function init() {
		
		$writeActions4da = array ('index', 'meshcrawler', 'indexerstatus', 'viewchair', 'deletechair', 'addchair',
		                          'viewcoordinator', 'deletecoordinator', 'addcoordinator', 'viewpblcoordinator', 
		                          'addpblcoordinator', 'deletepblcoordinator', 'clearldapcache', 'medvidconnector', 'clearmetadatacache',
		                          'learningtopicconnector', 'lectopiaconnector', 'clearlectopiacache','clearzendcache', 'reindexdocument', 
		                          'clearecho360cache','viewdomainadmin', 'audience','movepblsessionresources','incrementalreindexmediabankcollection',
		                          'blockhandbook', 'viewstaff', 'addstaff', 'deletestaff', 'sortstaff', 'editstaff','lectopiatoecho360migration','optimizeluceneindex');
		
		$writeActionsFull = array ('deletedomainadmin', 'adddomainadmin','medkeymigration', 'movecurriculumareas', 'viewlk', 'deletelk', 'addlk', 'editlk','adhoc','healthcheck');
		
		$this->_helper->_acl->allow('domainadmin', $writeActions4da);
		$this->_helper->_acl->allow('admin', $writeActionsFull);
		$this->_helper->_acl->allow('guest', array('maintenance'));
	}
	
	public function audienceAction() {
		$this->_helper->layout()->disableLayout();
		$this->_helper->viewRenderer->setNoRender();
		$action = $this->_getParam("useraction");
		$type = $this->_getParam("type");
		$type_id = $this->_getParam("type_id");
		$domain_id = $this->_getParam("domain_id");
		
		if ($type === 'ta') {
			$linkageTable = new LinkageTaDomains();
		} else {
			$linkageTable = new LinkageLoDomains();
		}
		
		if ($action === 'add') {
			$linkageTable->addAudience($type_id, $domain_id);
		} else {
			$linkageTable->removeAudience($type_id, $domain_id);
		}
		echo SearchIndexer::reindexDocument($type, $type_id);
	}
	
	public function reindexdocumentAction() {
		$this->_helper->layout()->disableLayout();
		$this->_helper->viewRenderer->setNoRender();
		$type = $this->_getParam("type");
		$idParam = $this->_getParam("id");
		if(stristr($idParam, ',')) {
            $ids = explode(',',  $idParam);
            $result = array();
            foreach($ids as $id) {
                $id = (int)$id;
                if($id > 0) {
                    $reindex = SearchIndexer::reindexDocument($type, $id);
                    $result[] = strtoupper($type).' -> '.$id .' -> '. $reindex;
                }
            }
            echo '<br /><b>Result</b><br />'.implode('<br /> ', $result); 
		} else {
    		echo SearchIndexer::reindexDocument($type, $idParam);
		}
	}
	
	/** This page is the admin page */
	public function indexAction() {
		PageTitle::setTitle($this->view, $this->_request);
		$sbs = new StageBlockSeqs();
		$this->view->allblocks = $sbs->getAllBlocks();
	}
	
	/** This page shows the list of stage coordinators */
	public function viewcoordinatorAction() {
		PageTitle::setTitle($this->view, $this->_request);
		$coordinatorFinder = new StageCoordinators();
		//$this->view->coordinators = $coordinatorFinder->getAllCoordinators();
		

		$identity = Zend_Auth::getInstance()->getIdentity();
		$domain = $identity->domain;
		$role = $identity->role;
		
		if ( $role=='admin' ) {
			$this->view->coordinators = $coordinatorFinder->getAllCoordinators();
		} else {
			$this->view->coordinators = $coordinatorFinder->getAllCoordinators($domain);
		}
	}
	
	/** Action to delete a stage coordinator */
	public function deletecoordinatorAction() {
		$id = $this->_getParam("id");
		$coordinatorFinder = new StageCoordinators();
		$coordinatorFinder->deleteCoordinator($id);
		
		//send a successful response to ajax request, does not need to display any html page
		echo "true";
		exit();
	}
	
	/** Action to add a stage coordinator */
	public function addcoordinatorAction() {
		$request = $this->getRequest();
		$form = new StageCoordinatorForm();
		if ( $request->isPost() ) {
			if ( $form->isValid($_POST) ) {
				$coordinatorFinder = new StageCoordinators();
				$coordinatorFinder->addCoordinator($form->getValue('stage'), $form->getValue('uid'), $form->getValue('domain'));
				$this->_redirect('/admin/viewcoordinator');
			}
		}
		$this->view->form = $form;
	}
	
	/** Action to view the domain administrators */
	public function viewdomainadminAction() {
		PageTitle::setTitle($this->view, $this->_request);
		$domainadminFinder = new DomainAdmins();
		//$this->view->domainadmins = $domainadminFinder->getAllDomainAdmins();
		

		// view domain admins in the user's domain    	
		$identity = Zend_Auth::getInstance()->getIdentity();
		$domain = $identity->domain;
		$role = $identity->role;
		
		if ( $role=='admin' ) {
			$this->view->domainadmins = $domainadminFinder->getAllDomainAdmins();
		} else {
			$this->view->domainadmins = $domainadminFinder->getAllDomainAdmins($domain);
		}
	}
	
	/** Action to delete a domain administrator */
	public function deletedomainadminAction() {
		$id = $this->_getParam("id");
		$domainadminFinder = new DomainAdmins();
		$domainadminFinder->deleteDomainAdmin($id);
		
		//send a successful response to ajax request, does not need to display any html page
		echo "true";
		exit();
	}
	
	/** Action to add a domain administrator */
	public function adddomainadminAction() {
		$request = $this->getRequest();
		$form = new DomainAdminForm();
		
		if ( $request->isPost() ) {
			if ( $form->isValid($_POST) ) {
				$domainadminFinder = new DomainAdmins();
				$domainadminFinder->addDomainAdmin($form->getValue('domain'), $form->getValue('uid'));
				$this->_redirect('/admin/viewdomainadmin');
			}
		}
		$this->view->form = $form;
	}
	
	/** This page shows the list of block chairs */
	public function viewchairAction() {
		PageTitle::setTitle($this->view, $this->_request);
		$blockchairFinder = new Blockchairs();
		//$this->view->blockchairs = $blockchairFinder->getAllChairs();
		

		$identity = Zend_Auth::getInstance()->getIdentity();
		$domain = $identity->domain;
		$role = $identity->role;
		
		if ( $role=='admin' ) {
			$this->view->blockchairs = $blockchairFinder->getAllChairs();
		} else {
			$this->view->blockchairs = $blockchairFinder->getAllChairs($domain);
		}
	}
	
	/** Action to delete a block chair */
	public function deletechairAction() {
		$id = $this->_getParam("id");
		$blockchairFinder = new Blockchairs();
		$blockchairFinder->deleteBlockchair($id);
		
		//send a successful response to ajax request, does not need to display any html page
		echo "true";
		exit();
	}
	
	/** Action to add a block chair */
	public function addchairAction() {
		$request = $this->getRequest();
		$form = new BlockchairForm();
		if ( $request->isPost() ) {
			if ( $form->isValid($_POST) ) {
				$blockchairFinder = new Blockchairs();
				$blockchairFinder->addChair($form->getValue('block'), $form->getValue('uid'), $form->getValue('domain'));
				$this->_redirect('/admin/viewchair');
			}
		}
		$this->view->form = $form;
	}
	
	/** This page shows the list of staff */
	public function viewstaffAction() {
		PageTitle::setTitle($this->view, $this->_request);
		$staffFinder = new Staff();
		

		$identity = Zend_Auth::getInstance()->getIdentity();
		$domain = $identity->domain;
		$role = $identity->role;
		
		if ( $role=='admin' ) {
			$this->view->staff = $staffFinder->getAllStaff();
		} else {
			$this->view->staff = $staffFinder->getAllStaff($domain);
		}
	}
	
	/** Action to delete a staff entry */
	public function deletestaffAction() {
		$id = $this->_getParam("id");
		$staffFinder = new Staff();
		$staffFinder->deleteStaff($id);
		
		//send a successful response to ajax request, does not need to display any html page
		echo "true";
		exit();
	}
	
	/** Action to sort staff */
	public function sortstaffAction() {
		$stafflist = $this->_getParam("stafflist");
		$staffFinder = new Staff();
		foreach($stafflist as $ind => $staffid) {
			if($staffid=="") continue;
			$staffidnum = substr($staffid, 6); //get the stuff after "userid"
			$staffFinder->setStaffOrder($staffidnum, $ind);
		}
		
		//send a successful response to ajax request, does not need to display any html page
		echo "true";
		exit();
	}
	
	/** Action to add a staff entry */
	public function addstaffAction() {
		$request = $this->getRequest();
		$form = new StaffForm();
		if ( $request->isPost() ) {
			if ( $form->isValid($_POST) ) {
				$staffFinder = new Staff();
				$staffFinder->addStaff($form->getValue('staffpage'),$form->getValue('stafftype'),$form->getValue('uid'),$form->getValue('description'),  $form->getValue('domain_id'));
				$this->_redirect('/admin/viewstaff');
			}
		}
		$this->view->form = $form;
	}

	/** Action to edit a staff entry */
	public function editstaffAction() {
		$request = $this->getRequest();
		$form = new StaffForm();
		$staffid = $this->_getParam("staffid");
		$staffFinder = new Staff();
		if ( $request->isPost() ) {
			if ( $form->isValid($_POST) ) {
				$staffFinder->updateStaff($this->_getParam("staffid"),$form->getValue('staffpage'),$form->getValue('stafftype'),$form->getValue('uid'),$form->getValue('description'),  $form->getValue('domain_id'));
				$this->_redirect('/admin/viewstaff');
			}
		} else {
			$this->view->staffmember = $staffFinder->getStaffMember($staffid);
			$form->populate($this->view->staffmember);
			$form->setAction(Compass::baseUrl().'/admin/editstaff');
			$this->view->staffid = $staffid;
			$this->view->form = $form;
		}
	}
	
	/** Display the total number of documents needs to be indexed and start the indexing process */
	public function indexerstatusAction() {
		PageTitle::setTitle($this->view, $this->_request);
		$lkFinder = new LinkageLoTas();
		$this->view->total = $lkFinder->getLinkageWithStatus(Status::$RELEASED)->count();
		
		$fp = fsockopen($_SERVER['HTTP_HOST'], 80, $errno, $errstr, 20);
		
		$header = "POST ".Compass::baseUrl()."/search/indexer HTTP/1.1\r\n";
		$header .= "Host: {$_SERVER['HTTP_HOST']}\r\n";
		$header .= "Connection: Close\r\n\r\n";
		fputs($fp, $header);
		stream_set_blocking($fp, 0);
	}
	
	/** Auto generate keywords for learning objectives in the database */
	public function meshcrawlerAction() {
		PageTitle::setTitle($this->view, $this->_request);
		set_time_limit(0);
		$this->view->result_no = MeshService::batchProcessAllLos();
	}
	
	public function clearzendcacheAction() {
	    $this->view->result = CacheService::clearZendCache();
    }
	
	public function clearldapcacheAction() {
        $this->view->result = CacheService::clearLdapCache();
	}
	
	public function clearlectopiacacheAction() {
        $this->view->result = CacheService::clearLectopiaCache();
	}
	
    public function clearecho360cacheAction() {
        $this->view->result = CacheService::clearEcho360Cache();
    }
    
    public function clearmetadatacacheAction() {
        $this->view->result = CacheService::clearMetadataCache();
    }
    
	public function viewpblcoordinatorAction() {
		PageTitle::setTitle($this->view, $this->_request);
        $pblCoordinatorService = new PblCoordinatorService();
        $this->view->pblCoordinators = $pblCoordinatorService->getPblCoordinatorsDetails();
	}
	
	public function deletepblcoordinatorAction() {
        $msg = 'false';
		$auto_id = (int)$this->_getParam('id', '');
		if ($auto_id > 0) {
			$pblCoordinatorService = new PblCoordinatorService();
			$delete = $pblCoordinatorService->deletePblCoordinator($auto_id);
			if ( $delete === true ) {
                $msg = 'true';
			}
		}
		echo $msg;
		exit();
	}
	
    /** Action to add a Pbl Coordinator */
    public function addpblcoordinatorAction() {
        $request = $this->getRequest();
        $form = new PblcoordinatorForm();
        if ( $request->isPost() ) {
            if ( $form->isValid($_POST) ) {
                $pblId = $form->getValue('pbl');
                $uid = $form->getValue('uid');
                $domainId = $form->getValue('domain');
                
                $pblCoordinatorService = new PblCoordinatorService();
                $pblCoordinatorExist = $pblCoordinatorService->pblCoordinatorExist($pblId, $uid, $domainId);
                if(!$pblCoordinatorExist) {
                    $pblCoordinatorService->addPblCoordinator($pblId, $uid, $domainId);
                    $this->_redirect('/admin/viewpblcoordinator');
                } else {
                    $form->addErrorMessages(array("User ID '$uid' is already attached to the pbl you have selected."));
                }
            }
        }
        $this->view->form = $form;
    }
	
	public function medvidconnectorAction() {
		$mediabankMedvidConnector = new MediabankMedvidConnector();
		$result = $mediabankMedvidConnector->link();
		$this->view->result = $result;
	}
	
	public function learningtopicconnectorAction() {
		$mediabankLearningTopicConnector = new MediabankLearningTopicConnector();
		$result = $mediabankLearningTopicConnector->link();
		$this->view->result = $result;
	}
	
	public function lectopiaconnectorAction() {
		$mediabankLectopiaConnector = new MediabankLectopiaConnector();
		$result = $mediabankLectopiaConnector->link();
		$this->view->result = $result;
	}
	
	public function incrementalreindexmediabankcollectionAction() {
	    $mediabankCollection = $this->_getParam(MediabankResourceConstants::$FORM_REINDEX_collection, null);
	    $mediabankCollectionService = new MediabankCollectionService();
	    $html = $mediabankCollectionService->reindex($mediabankCollection);
	    print $html;
	    exit;
	}	

    public function movepblsessionresourcesAction() {
        $movePblSessionResources = new MovePblSessionResourcesFix();
        $results = $movePblSessionResources->move();    
        print '<pre>';print_r($results);print '<pre>';
        exit();                
    }
    
    public function lectopiatoecho360migrationAction() {
        $do = $this->_getParam('do', null);
        if(!is_null($do)) {
            $moveLectopiaRecordingsToEcho360 = new MoveLectopiaRecordingsToEcho360();
            $moveLectopiaRecordingsToEcho360->action($do);
        }
        
        
    }

    public function maintenanceAction() {
        try {
            set_time_limit(0);
            $service = $this->_getParam('service', null);
            $token = $this->_getParam('token', null);
            $maintenanceService = new MaintenanceService($service, $token);
            if(! $maintenanceService->hasError()) {
                $result = $maintenanceService->run();            
                if(!empty($result)) {
                    #This should return 'success' or error text on failure.
                    print $result; 
                }           
            } else {
                print "(Service : $service - Token : $token) given is incorrect";
            }
            exit();
        } catch (Exception $ex) {
            $error = $ex->getMessage().PHP_EOL.$ex->getTraceAsString().PHP_EOL;
            print $error;
            Zend_Registry::get('logger')->warn(PHP_EOL."CLASS\t: ".__CLASS__.PHP_EOL."METHOD\t: ".__METHOD__.PHP_EOL."Error\t: ".$error.PHP_EOL);
            exit();
        }    
    }
    
    public function blockhandbookAction() {
    	set_time_limit(0);
    	$this->view->block_id = $this->_getParam('blockid');
    	
    	$sbs = new StageBlockSeqs();
    	$this->view->block_no = $sbs->getBlockNo($this->view->block_id);
    	$this->view->block_name = $sbs->getBlockName($this->view->block_no);
    	
    	PageTitle::setTitle($this->view, $this->_request, array(date('Y'), $this->view->block_name));
    	
    	$guideService = new DynamicGuideService();
    	$this->view->tas = $guideService->getAllTasInBlock($this->view->block_no);
    }
    
    public function medkeymigrationAction() {
    	set_time_limit(0);
    	$this->_helper->layout()->disableLayout();
		$this->_helper->viewRenderer->setNoRender();
		
    	$med_uni_mapping = new MedkeyUnikeyMapping();
    	
   		$bcFinder = new Blockchairs();
    	$rows = $bcFinder->fetchAll();
    	foreach ($rows as $row) {
    		if (!empty($row['uid'])) {
	    		echo "Replacing {$row['uid']} with ", $med_uni_mapping->getUnikey($row['uid']), "<br />";
	    		$row->uid = $med_uni_mapping->getUnikey($row['uid']);
	    		$row->save();
	    	}
    	}
    	
    	$daFinder = new DomainAdmins();
    	$rows = $daFinder->fetchAll();
    	foreach ($rows as $row) {
    		if (!empty($row['uid'])) {
	    		echo "Replacing {$row['uid']} with ", $med_uni_mapping->getUnikey($row['uid']), "<br />";
	    		$row->uid = $med_uni_mapping->getUnikey($row['uid']);
	    		$row->save();
	    	}
    	}
    	
    	$loFinder = new LearningObjectives();
    	$rows = $loFinder->fetchAll();
    	foreach ($rows as $row) {
    		if (!empty($row['created_by'])) {
    			echo "Replacing created by {$row['created_by']} with ", $med_uni_mapping->getUnikey($row['created_by']), "<br />";
    			$row->created_by = $med_uni_mapping->getUnikey($row['created_by']);
    		}
    		if (!empty($row['approved_by'])) {
    			echo "Replacing approved by {$row['approved_by']} with ", $med_uni_mapping->getUnikey($row['approved_by']), "<br />";
    			$row->approved_by = $med_uni_mapping->getUnikey($row['approved_by']);
    		}
    		$row->save();
    	}
    	
    	$lkFinder = new LinkageLoTas();
    	$rows = $lkFinder->fetchAll();
    	foreach ($rows as $row) {
    		
    		if (!empty($row['created_by'])) {
    			echo "Replacing created by {$row['created_by']} with ", $med_uni_mapping->getUnikey($row['created_by']), "<br />";
    			$row->created_by = $med_uni_mapping->getUnikey($row['created_by']);
    		}
    		if (!empty($row['modified_by'])) {
    			echo "Replacing modified by {$row['modified_by']} with ", $med_uni_mapping->getUnikey($row['modified_by']), "<br />";
    			$row->modified_by = $med_uni_mapping->getUnikey($row['modified_by']);
    		}
    		if (!empty($row['approved_by'])) {
    			echo "Replacing approved by {$row['approved_by']} with ", $med_uni_mapping->getUnikey($row['approved_by']), "<br />";
    			$row->approved_by = $med_uni_mapping->getUnikey($row['approved_by']);
    		}
    		$row->save();
    	}
    	
    	$lkhistoryFinder = new LinkageHistories();
    	$rows = $lkhistoryFinder->fetchAll();
    	foreach ($rows as $row) {
    		if (!empty($row['created_by'])) {
    			echo "Replacing created by {$row['created_by']} with ", $med_uni_mapping->getUnikey($row['created_by']), "<br />";
    			$row->created_by = $med_uni_mapping->getUnikey($row['created_by']);
    		}
    		if (!empty($row['modified_by'])) {
    			echo "Replacing modified by {$row['modified_by']} with ", $med_uni_mapping->getUnikey($row['modified_by']), "<br />";
    			$row->modified_by = $med_uni_mapping->getUnikey($row['modified_by']);
    		}
    		if (!empty($row['approved_by'])) {
    			echo "Replacing approved by {$row['approved_by']} with ", $med_uni_mapping->getUnikey($row['approved_by']), "<br />";
    			$row->approved_by = $med_uni_mapping->getUnikey($row['approved_by']);
    		}
    		$row->save();
    	}
    	
    	$rhFinder = new MediabankResourceHistory();
    	$rows = $rhFinder->fetchAll();
    	foreach ($rows as $row) {
    		if (!empty($row['uid'])) {
	    		echo "Replacing {$row['uid']} with ", $med_uni_mapping->getUnikey($row['uid']), "<br />";
	    		$row->uid = $med_uni_mapping->getUnikey($row['uid']);
	    		$row->save();
	    	}
    	}
    	
    	$pblcFinder = new PblCoordinator();
    	$rows = $pblcFinder->fetchAll();
    	foreach ($rows as $row) {
    		if (!empty($row['uid'])) {
	    		echo "Replacing {$row['uid']} with ", $med_uni_mapping->getUnikey($row['uid']), "<br />";
	    		$row->uid = $med_uni_mapping->getUnikey($row['uid']);
	    		$row->save();
	    	}
    	}
    	
    	$podcFinder = new Podcasturl();
    	$rows = $podcFinder->fetchAll();
    	foreach ($rows as $row) {
    		if (!empty($row['uid'])) {
	    		echo "Replacing {$row['uid']} with ", $med_uni_mapping->getUnikey($row['uid']), "<br />";
	    		$row->uid = $med_uni_mapping->getUnikey($row['uid']);
	    		$row->save();
	    	}
    	}
    	
    	$scFinder = new SearchConfigure();
    	$rows = $scFinder->fetchAll();
    	foreach ($rows as $row) {
    		if (!empty($row['user_id'])) {
	    		echo "Replacing {$row['user_id']} with ", $med_uni_mapping->getUnikey($row['user_id']), "<br />";
	    		$row->user_id = $med_uni_mapping->getUnikey($row['user_id']);
	    		$row->save();
	    	}
    	}
    	
    	$stagecoordFinder = new StageCoordinators();
    	$rows = $stagecoordFinder->fetchAll();
    	foreach ($rows as $row) {
	    	if (!empty($row['uid'])) {
		    		echo "Replacing {$row['uid']} with ", $med_uni_mapping->getUnikey($row['uid']), "<br />";
		    		$row->uid = $med_uni_mapping->getUnikey($row['uid']);
		    		$row->save();
		    	}
    	}
    	
    	$sevalFinder = new StudentEvaluate();
    	$rows = $sevalFinder->fetchAll();
    	foreach ($rows as $row) {
	    	if (!empty($row['uid'])) {
		    		echo "Replacing {$row['uid']} with ", $med_uni_mapping->getUnikey($row['uid']), "<br />";
		    		$row->uid = $med_uni_mapping->getUnikey($row['uid']);
		    		$row->save();
		    	}
    	}
    	
    	$taFinder = new TeachingActivities();
    	$rows = $taFinder->fetchAll();
    	foreach ($rows as $row) {
	    	if (!empty($row['created_by'])) {
    			echo "Replacing created by {$row['created_by']} with ", $med_uni_mapping->getUnikey($row['created_by']), "<br />";
    			$row->created_by = $med_uni_mapping->getUnikey($row['created_by']);
    		}
    		if (!empty($row['approved_by'])) {
    			echo "Replacing approved by {$row['approved_by']} with ", $med_uni_mapping->getUnikey($row['approved_by']), "<br />";
    			$row->approved_by = $med_uni_mapping->getUnikey($row['approved_by']);
    		}
    	    if (!empty($row['reviewed_by'])) {
    			echo "Replacing reviewed by {$row['reviewed_by']} with ", $med_uni_mapping->getUnikey($row['reviewed_by']), "<br />";
    			$row->reviewed_by = $med_uni_mapping->getUnikey($row['reviewed_by']);
    		}
    		$pteachers = split(',' ,$row['principal_teacher']);
    		$new_pteacher = '';
    		foreach ($pteachers as $teacher) {
    			$teacher = trim($teacher);
    			if (!empty($teacher)) {
    				echo "Replacing principal teacher $teacher with ", $med_uni_mapping->getUnikey($teacher), "<br />";
    				$new_pteacher = $new_pteacher . $med_uni_mapping->getUnikey($teacher). ', ';
    			}
    		}
    		$row->principal_teacher = $new_pteacher;
    		
    		$cteachers = split(',' ,$row['current_teacher']);
    		$new_cteacher = '';
    		foreach ($cteachers as $teacher) {
    			$teacher = trim($teacher);
    			if (!empty($teacher)) {
    				echo "Replacing current teacher $teacher with ", $med_uni_mapping->getUnikey($teacher), "<br />";
    				$new_cteacher = $new_cteacher . $med_uni_mapping->getUnikey($teacher) . ', ';
    			}
    		}
    		$row->current_teacher = $new_cteacher;
    		$row->save();
	    }
	    
    	$udFinder = new UserDisc();
    	$rows = $udFinder->fetchAll();
    	foreach ($rows as $row) {
	    	if (!empty($row['uid'])) {
		    		echo "Replacing {$row['uid']} with ", $med_uni_mapping->getUnikey($row['uid']), "<br />";
		    		$row->uid = $med_uni_mapping->getUnikey($row['uid']);
		    		$row->save();
		    	}
    	}
    }
    
    public function optimizeluceneindexAction() {
        $maintenanceLuceneIndex = new MaintenanceLuceneIndex();
        $this->view->optimized = $maintenanceLuceneIndex->process();
    }
    
    public function adhocAction() {
    	$task = $this->_getParam('task','');
    	$adhocService = new AdhocService();
    	switch($task) {
    		case 'movecurriculumareas':
		        AdhocService::moveCurriculumAreasUp1Discipline();
    			break;
    		case 'generatecdsreport':
    			$adhocService->generatecdsreport();
    			break;
    		case 'generatecdscontentreport':
    		    $adhocService->generatecdscontentreport();
    		    break;	
    	}
    }
    
    public function healthcheckAction() {
        $healthCheckService = new HealthCheckService();
        $this->view->tests = $healthCheckService->run();
    }
    
    public function editlkAction() {
    	$lk = $this->_getParam("lk");
    	$lkFinder = new $lk();
    	
    	$updating = $this->_getParam("updating");
    	if($updating == "Update names") { //update the names
    		foreach($this->_getAllParams() as $key => $val) {
    			if(!strncmp("edit_", $key, 5)) {
    				$parts = explode('_', substr($key, 5));
    				$id = array_pop($parts);
    				$col = implode('_', $parts);
					//echo "$id => $val<br>";
    				$data = array($col => $val);
    				$where = $lkFinder->getAdapter()->quoteInto('auto_id = ?', $id);
    				$lkFinder->update($data, $where);
    			}
    		}
    	}
    	$lkrowset = $lkFinder->fetchAll($lkFinder->select()->order('auto_id'));
    	$lklist = array();
    	foreach($lkrowset as $lkrow)
    		$lklist[] = $lkrow->toArray();
    	$this->view->lklist = $lklist;
    	$this->view->cols = array_keys($lklist[0]);
    	$this->view->lkname = $lk;
    }
    public function deletelkAction() {
    	$lk = $this->_getParam("lk");
    	$lkFinder = new $lk();
    	$id = $this->_getParam("id");
    	$where = $lkFinder->getAdapter()->quoteInto('auto_id = ?', $id);
    	$lkFinder->delete($where);
    	$this->_redirect("/admin/editlk/lk/$lk");
    }
    public function addlkAction() {
    	$lk = $this->_getParam("lk");
    	$lkFinder = new $lk();
    	$lkitem = $this->_getParam("lkitem");
    	$data = array();
    	foreach($this->_getAllParams() as $key => $val) {
    		if(!strncmp("lkitem_", $key, 7)) {
    			$realkey = substr($key,7);
    			$data[$realkey] = $val;
    		}
    	}
    	//$data = array("name" => $lkitem);
    	$lkFinder->insert($data);
    	$this->_redirect("/admin/editlk/lk/$lk");
    }
    
}
	