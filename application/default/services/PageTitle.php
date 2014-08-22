<?php

class PageTitle {
	const TITLE_DELIMITER = '|';

	public static $page_prefix = 'Compass - ';
	public static $default_title = 'Welcome to Compass';

	public static $titles = array(
		'admin' =>
			array(
				'index'					=> 'Admin Page',
				'viewchair'				=> 'Admin - Block Chair List',
		   		'viewcoordinator'		=> 'Admin - Stage Coordinator List',
		   		'viewpblcoordinator'	=> 'Admin - PBL Coordinator List',
				'indexerstatus'			=> 'Admin - Lucene Indexer Status',
				'meshcrawler'			=> 'Admin - MeSH Crawler',
		    	'clearldapcache'		=> 'Admin - Refresh LDAP Cache',
				'blockhandbook'			=> '%d Block Handbook - %s'
	    	),
		'auth' => 
			array('login' => 'Login'),
		'chart' =>
			array(
				'pielo' 	=> 'Pie Chart - Learning Objective Vs. Stage',
				'barlodisc' => 'Bar Chart - Learning objective Vs. Discipline'
			),
		'error' =>
			array('error' => 'Error'),
		'evaluate' =>
			array('studentta' => 'View Student Teaching Activity Evaluation'),
		'guide' =>
			array(
				'handbook' => 'Block %d Essential Readings Handbook by Name',
				'handbookbypbl' => 'Block %d Essential Readings Handbook by PBL',
				'proceduralskills' => '%s %d Procedural Skills Handbook',
				'patientdoctor' => 'Block %d Patient Doctor Handbook',
			),
		'help'	=>
			array('search' => 'Available Search Fields and Syntax'),
		'index' =>
			array('index' => 'Home'),
		'learningobjective' =>
			array(
				'add'					=> 'Add New Learning Objective',
				'addcomplete'			=> 'New Learning Objective Added',
				'index'					=> 'Recently Submitted Learning Objectives',
				'view'					=> 'Learning Objective Details - %d',
				'edit'					=> 'Edit Learning Objective %d',
				'editcomplete'			=> 'Learning Objective Saved',
				'approvecomplete'		=> 'Learning Objective Approved',
				'archive'				=> 'Archive Learning Objective %d',
				'archivecomplete'		=> 'Learning Objective Archived',
				'archivelolink'			=> 'Archive Linkage Between Learning Objective %d and Teaching Activity %d',
				'archivelolinkcomplete'	=> 'Linkage Archived'
			),
		'lotalinkage' => 
			array(
				'delete'				=> 'Delete Linkage Between Learning Objective %d and Teaching Activity %d',
				'deletecomplete'		=> 'Linkage Deleted',
				'approvedelete'			=> 'Approve Linkage Deletion',
				'approvedeletecomplete'	=> 'Linkage Deletion Approved',
				'history'				=> 'History of linked %s for %s %d'
			),
		'mesh' =>
			array('index'	=> 'MeSH Browser'),
		'pbl' =>
			array(
				'display' => 'PBL %s',
				'top' 	=> 'All Problems and Blocks'
			),
        'block' =>
            array(
                'index' => 'Block',
                'list' => 'Block - List', 
                'learningobjectives' => 'Block %s - Learning Objectives',
                'fetch' => 'Block %s',     
                'get' => 'Block %s - %s',
                'manageresources' => 'Block - %s - Manage Resources',
            ),
		'query' =>
			array('index' => 'List of Quick Queries'),
		'resource' =>
			array(
				'history'	=> 'History of Resources for %s',
				'uploadedit'=> '%s Resource for %s %d',
				'view'		=> 'Resource - %s',
				'link'		=> 'Resource - Link Resource to %s %d'
			),
		'search'=> 
			array('index' => 'Search - %s'),
		'submission' => 
			array(
				'index' 		=> 'Submission',
				'editloandta'	=> 'Edit Submission %d',
				'viewloandta'	=> 'Submission %d Detail',
				'submitloandta'	=> 'Submission Completed',
				'deleteloandta'	=> 'Delete Submission %d',
				'approveloandta'=> 'Submission Approved'
			),
		'workflow' =>
			array(
				'index'				=> 'Workflow',
				'viewblock'			=> '"%s" Queue for Block "%s"',
				'viewunknownta'		=> '"In development" Queue with Unknown Teaching Activity',
				'viewownblock'		=> 'My "%s" Queue for Block "%s"',
				'viewownunknownta'	=> 'My "In development" Queue with Unknown Teaching Activity',
			),
		'teachingactivity' =>
			array(
				'add'					=> 'Add New Teaching Activity',
				'addcomplete'			=> 'New Teaching Activity Added',
				'index'					=> 'Recently Submitted Teaching Activities',
				'view'					=> 'Teaching Activity Details - %d',
				'edit'					=> 'Edit Teaching activity %d',
				'editcomplete'			=> 'Teaching Activity Saved',
				'approvecomplete'		=> 'Teaching Activity Approved',
				'archive'				=> 'Archive Teaching Activity %d',
				'archivecomplete'		=> 'Teaching Activity Archived',
				'archivetalink'			=> 'Archive Linkage Between Teaching Activity %d and Learning Objective %d',
				'archivetalinkcomplete'	=> 'Linkage Archived'
			),
		'disc'=> 
			array(
				'list'				=> 'Discipline List',
				'mydetails'			=> 'My Discipline Details',
				'mydetailscomplete'	=> 'My Discipline Details Saved',
				'edit'				=> 'Edit Discipline',
				'editcomplete'		=> 'Discipline Updated',
				'add'				=> 'Add New Discipline',
				'addcomplete'		=> 'New Discipline Added'
			),
		'curriculumareas' =>
			array(
				'list'		=> 'Curriculum Areas List',
				'add'		=> 'Add Curriculum Area'
			),
		'people'=>
			array(
				'students'	=> "Student List"
			)
		);

    public static function setTitle($view, $request, $params = NULL) {
    	$controller = $request->getControllerName();
    	$action = $request->getActionName();
    	$title = PageTitle::$default_title;
    	if (isset(PageTitle::$titles[$controller][$action])) {
    		if (isset($params)) {
    			$title = PageTitle::$page_prefix . vsprintf(PageTitle::$titles[$controller][$action], $params);
    		} else {
    			$title = PageTitle::$page_prefix . PageTitle::$titles[$controller][$action];
    		}
    	}
    	$view->headTitle($title . PageTitle::TITLE_DELIMITER);
    }
}