<?php
class Bootstrap {
    public function __construct($configSection = 'production') {
        header('P3P:CP="IDC DSP COR ADM DEVi TAIi PSA PSD IVAi IVDi CONi HIS OUR IND CNT"');
        $rootDir = dirname(dirname(__FILE__));
        define('ROOT_DIR', $rootDir);

        // Add lib directory to the include path so that PHP can find the Zend Framework classes.
        set_include_path(get_include_path() 
            . PATH_SEPARATOR . ROOT_DIR . '/lib/'
            . PATH_SEPARATOR . ROOT_DIR . '/lib/standardanalyzer-1.0.0b'
            . PATH_SEPARATOR . ROOT_DIR . '/lib/sms'
            . PATH_SEPARATOR . ROOT_DIR . '/application/default/models/'
            . PATH_SEPARATOR . ROOT_DIR . '/application/default/models/search'
            . PATH_SEPARATOR . ROOT_DIR . '/application/default/models/mediabank'
            . PATH_SEPARATOR . ROOT_DIR . '/application/default/models/mediabankconnector'
            . PATH_SEPARATOR . ROOT_DIR . '/application/default/models/resource'
            . PATH_SEPARATOR . ROOT_DIR . '/application/default/models/resourcemanage'
            . PATH_SEPARATOR . ROOT_DIR . '/application/default/models/resourceuploadedit'
            . PATH_SEPARATOR . ROOT_DIR . '/application/default/models/resourcelink'
            . PATH_SEPARATOR . ROOT_DIR . '/application/default/models/resourceloose'
            . PATH_SEPARATOR . ROOT_DIR . '/application/default/models/help'
            . PATH_SEPARATOR . ROOT_DIR . '/application/default/models/pbl'
            . PATH_SEPARATOR . ROOT_DIR . '/application/default/models/block'
            . PATH_SEPARATOR . ROOT_DIR . '/application/default/models/block/fragments'
            . PATH_SEPARATOR . ROOT_DIR . '/application/default/models/pblblock'            
            . PATH_SEPARATOR . ROOT_DIR . '/application/default/models/acl'
            . PATH_SEPARATOR . ROOT_DIR . '/application/default/models/cms'
            . PATH_SEPARATOR . ROOT_DIR . '/application/default/models/student'
            . PATH_SEPARATOR . ROOT_DIR . '/application/default/models/ldap'
            . PATH_SEPARATOR . ROOT_DIR . '/application/default/models/ta'
            . PATH_SEPARATOR . ROOT_DIR . '/application/default/models/maintenance'
            . PATH_SEPARATOR . ROOT_DIR . '/application/default/models/rss'
            . PATH_SEPARATOR . ROOT_DIR . '/application/default/models/cache'            
            . PATH_SEPARATOR . ROOT_DIR . '/application/default/models/evaluate'
            . PATH_SEPARATOR . ROOT_DIR . '/application/default/models/formfilters'  
            . PATH_SEPARATOR . ROOT_DIR . '/application/default/models/studentresource'            
            . PATH_SEPARATOR . ROOT_DIR . '/application/default/models/echo360'            
            . PATH_SEPARATOR . ROOT_DIR . '/application/default/controllers/'
            . PATH_SEPARATOR . ROOT_DIR . '/application/default/forms/'
            . PATH_SEPARATOR . ROOT_DIR . '/application/default/services/'
            . PATH_SEPARATOR . ROOT_DIR . '/application/modules/disc/models/'  
        ); 
  
        require_once "Zend/Loader.php"; 
        Zend_Loader::registerAutoload();

        // Load configuration
        Zend_Registry::set('configSection', $configSection);
        $config = new Zend_Config_Ini(ROOT_DIR . '/application/config.ini', $configSection);
        Zend_Registry::set('config', $config);

        date_default_timezone_set($config->date_default_timezone);
        
        $locale = new Zend_Locale($config->locale);
        
        $translate = new Zend_Translate('array', ROOT_DIR . '/languages/en_AU.php', 'en');
        $translate->addTranslation(ROOT_DIR . '/languages/ar_SA.php', 'ar');
        $translate->setLocale($locale->getLanguage());
        Zend_Registry::set('Zend_Translate', $translate);

        // Configure database and store to the registery
        $db = Zend_Db::factory($config->database);
        Zend_Registry::set('db', $db);
        Zend_Db_Table_Abstract::setDefaultAdapter($db);
        Cache::dbTableMetadata();
        
        $ds = new DirectoryService();
        $ds->configure(
        	$config->ldap->server1->host, $config->ldap->server1->port, 
			$config->ldap->server1->username, $config->ldap->server1->password,
			$config->ldap->server1->baseDn, $config->ldapdirectory->userBase, $config->ldapdirectory->groupBase
		);
		Zend_Registry::set('ds', $ds);
    }


    public function runApp() {

        $config = Zend_Registry::get('config');

        SearchIndexer::setIndexDirectory($config->index_folder);
        Compass_Db_Table_Row_Observerable::attachObserver('SearchIndexer');

        // setup the layout
        Zend_Layout::startMvc($config->appearance);

        // acl action helper
        $acl = new Compass_Acl();
        $aclHelper = new Compass_Controller_Action_Helper_Acl(null, array('acl'=>$acl));
        Zend_Controller_Action_HelperBroker::addHelper($aclHelper);

        $ajaxContext = new Zend_Controller_Action_Helper_AjaxContext();
        Zend_Controller_Action_HelperBroker::addHelper($ajaxContext);

        // Create the application logger
        $logger = new Zend_Log(new Zend_Log_Writer_Stream($config->log_file));
        if ('production' == Zend_Registry::get('configSection')) {
        	//only log events that are notice and above
        	$filter = new Zend_Log_Filter_Priority(Zend_Log::NOTICE);
        	$logger->addFilter($filter);
        } else {
        	//$logger = new Zend_Log(new Zend_Log_Writer_Firebug());
        	
        	//enable db profiler
        	//$profiler = new Zend_Db_Profiler_Firebug('All DB Queries');
        	//$profiler->setEnabled(true);
        	//Zend_Registry::get('db')->setProfiler($profiler);
        }
        Zend_Registry::set('logger', $logger);
 
        //create access logger
        $access_logger = new Zend_Log(new Zend_Log_Writer_Stream($config->access_log_file));
        Zend_Registry::set('access_logger', $access_logger);
        
        // Set up the front controller. 
        $frontController = Zend_Controller_Front::getInstance();
        
        $alPlugin = new Compass_Controller_Plugin_AccessLogger();
        $frontController->registerPlugin($alPlugin);
 
        // Point the front controller to your action controller directory. 
        $frontController->setControllerDirectory(array(
            'default'       => ROOT_DIR . '/application/default/controllers'
        ));
        
        $frontController->addModuleDirectory(ROOT_DIR . '/application/modules');
        // Set the current environment 
        // Set a variable in the front controller indicating the current environment -- 
        // commonly one of development, staging, testing, production, but wholly dependent on the site's needs.
        $frontController->setParam('env', Zend_Registry::get('configSection'));
        $request = new Zend_Controller_Request_Http;
        $request->setBaseUrl('/compass/');
        $frontController->setRequest($request);
        Compass::addRouters($frontController->getRouter());
        
        //Add common helpers files for all the views
        $viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer');
        $viewRenderer->initView();
        $view = new Zend_View();
        $view->doctype('XHTML1_STRICT');
        $viewRenderer->setView($view);
        $viewRenderer->view->addHelperPath(ROOT_DIR . '/application/common/helpers');
        $viewRenderer->view->baseUrl = $frontController->getBaseUrl(); 
        $viewRenderer->view->zendEnv = Zend_Registry::get('configSection');
        
        try {
            $frontController->dispatch();
        } catch (Exception $exception) {
            if(Zend_Registry::get('config')->debug == 1) {
                $msg = $exception->getMessage(); 
                $trace = $exception->getTraceAsString();
                echo "<div>Error: $msg<p><pre>$trace</pre></p></div>"; 
            } else {
                try {
                    $logger->debug($exception->getMessage() . "\n" .  $exception->getTraceAsString() . "\n-----------------------------");
                } catch (Exception $e) {
                    // can't log it - display error message
                    die("<p>An error occurred with logging an error!");
                }
            }
        }
    }
}
