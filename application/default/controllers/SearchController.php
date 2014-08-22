<?php
/** @see Zend_Controller_Action */

class SearchController extends Zend_Controller_Action {

	public function init() {
		$readActions = array('index', 'advanced', 'activity', 'objective','configuresearch');
		$this->_helper->_acl->allow('guest', array('indexer'));
		$this->_helper->_acl->allow('student', $readActions);
		$this->_helper->_acl->allow('domainadmin', array('status'));

		$ajaxContext = $this->_helper->getHelper('AjaxContext');
		$ajaxContext->addActionContext('status', 'html');
		$ajaxContext->initContext();
	}

	public function configuresearchAction(){
		$params = array(
            'url'                       =>  $this->_request->getParam('url'),
            'encryptedUrl'              =>  $this->_request->getParam('q'),
            'configureSearchColumns'    =>  $this->_request->getParam('configureSearchColumns'),
            'configureSearchType'       =>  $this->_request->getParam('configureSearchType'),
            'cookieSearchPage'          =>  $this->_request->getParam('cookieSearchPage')
		);
		$searchServiceConfigure = new SearchConfigureService($params);
		$url = $searchServiceConfigure->save();
		($url != false) ? $this->_redirect($url) : die();
	}

	public function indexAction() {
		$fp = new FormProcessor_AdvancedSearch();
		$searchtype = $this->_request->getParam('searchtype','');

		if(!empty($searchtype)){
			$searchViewService = new SearchViewService();
			$this->view->assign($searchViewService->getFormVariables());
			$this->view->searchType = $searchtype;
			if(isset(SearchConstants::$config['view'][$searchtype])) {
				$this->view->process = SearchConstants::$config['view'][$searchtype]['process'];
				if(isset(SearchConstants::$config['view'][$searchtype]['layout'])
				&& !empty(SearchConstants::$config['view'][$searchtype]['layout'])) {
					$this->_helper->layout()->setLayout(SearchConstants::$config['view'][$searchtype]['layout']);
				}
			}
			if($searchtype == 'qq') {
				PageTitle::setTitle($this->view, $this->_request, array('Quick Query'));
				$this->view->quickQueries = $searchViewService->processQuickQueries(SearchConstants::quickQueries());
			}
			PageTitle::setTitle($this->view, $this->_request, array('Main'));
			$process  = $this->_request->getParam('process');
			if(isset($process) && !empty($process)) {
				$fp->process($this->getRequest());
				$searchResultsService = new SearchResultsService();
				$searchResults = $searchResultsService->getSearchResults($searchtype, $process, $fp);

				if(!empty($fp->format)) {
					$searchResultsFormatService = new SearchResultsFormatService($fp->format);
					$searchResultsFormatService->processResults($searchResults, $this->getRequest());
				}
				$this->view->assign($searchResults);
				$this->view->lucenQueryString = $fp->queryStr;
			}
		}
		$this->view->fp = $fp;
	}

	public function indexerAction() {
		$request = $this->getRequest();
		#if (!$request->isPost()) {
		#$this->_redirect('/auth/privileges');
		#exit();
		#}
		set_time_limit(0);
		$this->view->title = 'Search indexer';
		$messages = array();
		 
		$statusFinder = new Status();
		$released_id = array_search(Status::$RELEASED, $statusFinder->getAllNames());
		if (file_exists(SearchIndexer::getIndexDirectory().'_tmp'))
		$messages[] = 'Indexer is already running, please wait until it finishes.';
		else {
			$index = Compass_Search_Lucene::create(SearchIndexer::getIndexDirectory().'_tmp');

			$lk_lo_tas = new LinkageLoTas();
			$all_lks = $lk_lo_tas->fetchAll("status=$released_id");
			$all_lks = $all_lks->toArray();

			$startmemused=memory_get_usage();
			$oldmemused=memory_get_usage();
			$doccount=0;
			foreach ($all_lks as $lks) {
				$lk = $lk_lo_tas->fetchRow('auto_id = '.$lks['auto_id']);
				$doc = $lk->getLuceneDoc();
				if(!empty($doc)) {
    				$index->addDocument($doc);
    				$doccount++;
    				$messages[] = 'Added Link: ' . $doc->auto_id;
				} else {
				    Zend_Registry::get('logger')->warn("LUCFAIL: Could not create lucene document for link_lo_ta auto_id :" . $lks['auto_id'] );
				}
				$memused = memory_get_usage();
				Zend_Registry::get('logger')->debug("leaked ".round(($memused - $oldmemused)/1024)."k of memory indexing ".$doc->auto_id." (total: ".round(($memused - $startmemused)/1024)."k; average: ".round(($memused - $startmemused)/1024/$doccount)."k per doc)");
				$oldmemused = $memused;

			}
			$index->commit();
			$index->optimize();

			if (file_exists(SearchIndexer::getIndexDirectory().'_old')) {
				$d = dir(SearchIndexer::getIndexDirectory().'_old');
				while ($entry = $d->read()) {
					if ($entry != '.' && $entry != '..')
					unlink(SearchIndexer::getIndexDirectory().'_old/'.$entry);
				}
				$d->close();
				rmdir(SearchIndexer::getIndexDirectory().'_old');
			}
			rename(SearchIndexer::getIndexDirectory(), SearchIndexer::getIndexDirectory().'_old');
			rename(SearchIndexer::getIndexDirectory().'_tmp', SearchIndexer::getIndexDirectory());
			$messages[] = 'Total documents in index: ' . $index->numDocs();
		}
		$this->view->messages = $messages;
}

public function statusAction() {
	$this->_helper->layout()->disableLayout();
	if (file_exists(SearchIndexer::getIndexDirectory().'_tmp')) {
		$index = Compass_Search_Lucene::open(SearchIndexer::getIndexDirectory().'_tmp');
		$this->view->msg = "Indexed " . $index->count() . ' documents so far.';
	} else {
		$this->view->msg = "Indexer is not running.";
	}
}
}
