<?php
class SearchConstants {
    
    public static $config = array(
        'view' => array(
            'main' => array(
                'process' => 'simple'
            ),
            'activity' => array(
                'process' => 'activity',
                'layout' => 'basic'
            ),
            'objective' => array(
                'process' => 'objective',
                'layout' => 'basic'
            )
        ),
        'formprocessor' => array(
            'simple' => array(
                'methods' => array(
                    'm_1','m_2','m_10', 'm_12'
                )
            ),
            'advanced' => array( 
                'methods' => array(
                    'm_1','m_2','m_3','m_4','m_5','m_6','m_7','m_8','m_9','m_10','m_11', 'm_12', 'm_14'
                )
            ),
            'activity' => array(
                'methods' => array(
                    'm_1','m_2','m_4','m_5','m_6','m_7','m_8','m_9','m_10','m_13'
                )
            ),
            'objective' => array(
                'methods' => array(
                    'm_1','m_2','m_3','m_10','m_13'
                )
            )
        ),
        'columns' => array(
            'lo' => array(
                25,31,32,5,29,28,12
            ),
            'ta' => array(
                25,31,32,27,29,28,37         
            ),
            'activity' => array(
                27,30,31,32,29
            ),
            'objective' => array(
                5,22,25,19
            )
        ),
        'mandatoryColumns' => array(
            'lo' => array(4),
            'ta' => array(26),
            'activity' => array(26),
            'objective' => array(4)
        ),
        'configureSearch' => array(
            'lo' => array ( 
                'heading' => 'LEARNING OBJECTIVE COLUMNS',
                'title' => 'Configure LEARNING OBJECTIVE Search Results',
                'checkBoxes' => array (
                    4,5,6,7,8,9,10,12,13,14,15,16,17,18,19,20,21,22,23,24,25,46,47,48
                ) 
            ),
            'ta' => array(  
                'heading' => 'TEACHING ACTIVITY COLUMNS',
                'title' => 'Configure TEACHING ACTIVITY Search Results', 
                'checkBoxes' => array(
                    26,27,28,29,30,31,32,33,34,35,36,37,38,39,40,41,42,43,45,49,50,53,55,56,57
                )
            ),
            'activity' => array(
                'heading' => 'TEACHING ACTIVITY COLUMNS',
                'title' => 'Configure EDIT LINK / TEACHING ACTIVITY Search Results',
                'checkBoxes' => array(
                    26,27,28,29,30,31,32,33,34,35,36,37,38,39,40,41,42,43,45,49,50,53,55,56,57
                )
            ),
            'objective' => array(
                'heading' => 'LEARNING OBJECTIVE COLUMNS',
                'title' => 'Configure EDIT LINK / LEARNING OBJECTIVE Search Results',
                'checkBoxes' => array(
                    4,5,6,7,8,9,10,12,13,14,15,16,17,18,19,20,21,22,23,24,25,46,47,48
                )
            )
        )
    );
    
    public static function getTaResourceLuceneField() {
        $return = 53;
        if(UserAcl::isStaffOrAbove()) {
            $return = 55;
        }
        return $return;
    }
    
    public static $methods = array(
        'm_1'   =>  'processContext',
        'm_2'   =>  'processQstrQoption',
        'm_3'   =>  'processDiscipline',
        'm_4'   =>  'processStage',
        'm_5'   =>  'processBlock',
        'm_6'   =>  'processWeekOfBlock',
        'm_7'   =>  'processPbl',
        'm_8'   =>  'processTheme',
        'm_9'   =>  'processActivityType',
        'm_10'  =>  'processSearchHidden',
        'm_11'  =>  'processCurriculumArea',
    	'm_12'  =>  'processDomain',
    	'm_13'  =>  'processSessionDomain',
    	'm_14'  =>  'processSkills',
        'qq_s1'  =>  'processQuickQueryStudent1',
        'qq_s2'  =>  'processQuickQueryStudent2',
        'qq_staff1' => 'processQuickQueryStaff1',
        'qq_staff2' => 'processQuickQueryStaff2'
    );
    
    public static $columnLink = array(
        'ta' => array(
            26 => 'link_1',
            27 => 'link_1',
            4  => 'link_3',
            29 => 'link_5'
        ),
        'activity' => array(
            26 => 'link_2',
            27 => 'link_2',
            4  => 'link_3',
            29 => 'link_5'
        ),
        'lo' => array(
            4 => 'link_3',
            31 => 'link_3',
            5 => 'link_3',
            26 => 'link_1'
        ),
        'objective' => array(
            4  => 'link_4',
            5  => 'link_4',
            26 => 'link_1',
        )
    );

    public static function quickQueries() {
     return array(
        'student' => array(
            'title' => 'Quick Queries',
            'queries'   => array(
                '1' => array(
                    'question' => 'Show me all ###ph_15###s for the ###ph_6### '.Zend_Registry::get('Zend_Translate')->_('Block').' ###ph_13###',
                    'lucene' => array(
                        'methods' => array('m_5','m_9','m_13'),
                        //Only columns from teaching activities should be selected
                        'columns' => array(26,27,29,28,32,33,34),
                        'columnLink' => array(
                            26   =>  'link_1',
                            29   =>  'link_5'
                        )
                    )
                ),
                '2' => array(
                    'question' => 'Show me all ###ph_15###s for ###ph_14### discipline ###ph_13###',
                    'lucene' => array(
                        'methods' => array('m_3','m_9','m_13'),
                        'columns' => array(26,27,29,32,28,25), 
                        'columnLink' => array(
                            26   =>  'link_1',
                            29   =>  'link_5'
                        )
                    )
                ),
                '3' => array(
                    'question' => 'Show me all ###ph_14### discipline ###ph_15###s for the ###ph_6### '.Zend_Registry::get('Zend_Translate')->_('Block').' ###ph_13###',
                    'lucene' => array(
                        'methods' => array('m_3','m_5','m_9','m_13'),
                        'columns' => array(26,27,29,32,28,25), 
                        'columnLink' => array(
                            26   =>  'link_1',
                            29   =>  'link_5'
                        )
                    )
                ),
                '4' => array(
                    'question' => 'Show me all ###ph_14### learning objectives for the ###ph_6### '.Zend_Registry::get('Zend_Translate')->_('Block').' ###ph_12###',
                    'lucene' => array(
                        'methods' => array('m_3','m_5','m_13'),
                        'columns' => array(4,5,25,6,7,26,27,29,32), 
                        'columnLink' => array(
                            4    =>  'link_3',
                            26   =>  'link_1',
                            29   =>  'link_5'
                        )
                    )
                ),
                '5' => array(
                    'question' => 'Show me all ###ph_15###s for ###ph_16### skills for the ###ph_6### '.Zend_Registry::get('Zend_Translate')->_('Block').' ###ph_13###',
                    'lucene' => array(
                        'methods' => array('m_5','m_9','m_13','m_14'),
                        //Only columns from teaching activities should be selected
                        'columns' => array(26,27,28,31,32,43), 
                        'columnLink' => array(
                            26   =>  'link_1'
                        )
                    )
                ),
                '6' => array(
                    'question' => 'List of ###ph_11### recently submitted Learning Objectives###ph_12###',
                    'lucene' => array(
                        'methods' => array('qq_s1','m_13'),
                        //Only columns from learning objectives should be selected
                        'columns' => array(4,5,25,7,12), 
                        'columnLink' => array(
                            4   =>  'link_3'
                        )
                    )
                ),
                '7' => array(
                    'question' => 'List of ###ph_11### recently submitted Teaching Activities###ph_13###',
                    'lucene' => array(
                        'methods' => array('qq_s2','m_13'),
                        //Only columns from teaching activities should be selected
                        'columns' => array(26,27,31,32,43), 
                        'columnLink' => array(
                            26   =>  'link_1'
                        )
                    )
                )
                
            )
        ),
        'staff' => array(
            'title' => 'Staff Quick Queries',
            'queries'   => array(
                '1' => array(
                    'question' => 'Search for Learning Objectives belonging to you.###ph_12###',
                    'lucene' => array(
                        'methods' => array('qq_staff1','m_13'),
                        //Only columns from learning objectives should be selected
                        'columns' => array(4,5,25,7,12), 
                        'columnLink' => array(
                            4   =>  'link_3'
                        )
                    )
                ),
                '2' => array(
                    'question' => 'Search for Teaching Activities belonging to you.###ph_13###',
                    'lucene' => array(
                        'methods' => array('qq_staff2','m_13'),
                        //Only columns from teaching activities should be selected
                        'columns' => array(26,27,31,32,43), 
                        'columnLink' => array(
                            26   =>  'link_1'
                        )
                    )
                )
            )
        )
    );
    }
    
    public static $placeholder = array(
        'ph_1' => 'searchIn',
        'ph_2' => 'qstr',
        'ph_3' => 'qoption',
        'ph_4' => 'disciplines',
        'ph_5' => 'stage',
        'ph_6' => 'block',
        'ph_7' => 'blockweek',
        'ph_8' => 'pbl',
        'ph_9' => 'theme',
        'ph_10' => 'acttype',
        'ph_11' => 'limit',
        'ph_12' => 'hiddenLoContext',
        'ph_13' => 'hiddenTaContext',
        'ph_14' => 'qq_disciplines',
        'ph_15' => 'qq_acttype',
        'ph_16' => 'skill'
    );
    
    public static $links = array(
        'link_1' => '/compass/teachingactivity/view/id/%%%ta_auto_id%%%',
        'link_2' => 'javascript:addTaId(%%%ta_auto_id%%%)',
        'link_3' => '/compass/learningobjective/view/id/%%%lo_auto_id%%%',
        'link_4' => 'javascript:addLoId(%%%lo_auto_id%%%)',
        'link_5' => '/compass/pbl/index/ref/%%%ta_block_no%%%.%%%ta_block_week_zero_padded%%%'
    );

    /**
     * WARNING !! 
     * PLEASE READ CAREFULLY BEFORE MAKING ANY CHANGES TO BELOW ARRAY like EDIT/ADD/DELETE
     * 
     * ************************************** SUMMARY **************************************
     * Array keys like 1,2,3 are stored as cookies and also as you can see
     * their are numerous references made to this array from the above arrays and any change in current 
     * key/values would display incorrect search results and would break the system. 
     * 
     * ************************************** ADD ******************************************
     * You are allowed to add(APPEND) new key in ascending order when you add new lucene field 
     * Example would be 100 => array('luceneIndex' => 'luceneField',   'displayName' => 'luceneField DisplayName')
     * You are not allowed to put a new key in between and change the order. Nope !!
     * NOT Permissible
     * 2 => array('luceneIndex' => 'luceneField',   'displayName' => 'luceneField DisplayName')
     * Because key '2' already exists and you cannot shift it down like this
     * wrong 3  => array('luceneIndex' => 'doctype',               'displayName' => ''),
     * 
     * ************************************** EDIT *****************************************
     * You can change the 'displayName' value from the existing array
     * Example would be changing 'displayName' => 'LO ID' TO 'displayName' => 'Learning Objective Id'
     * You can also change the 'luceneIndex' value only if it just a name change rather then change of context
     * 
     * Permissible - 
     * 12 => array('luceneIndex' => 'lo_author_id','displayName' => 'Author') 
     * 	TO 
     * 12 => array('luceneIndex' => 'lo_created_by','displayName' => 'Author')
     * 
     * NOT Permissible -
     * 12 => array('luceneIndex' => 'lo_author_id','displayName' => 'Author') 
     * 	TO 
     * 12 => array('luceneIndex' => 'lo_discipline_names','displayName' => 'Author')
     * In this case you just need to delete this key and append new key at the end.
     * 
     * ************************************** DELETE **************************************
     * You need to uncomment the array key as done below eg. 11 and remove any references to this key from the
     * above arrays and viola it works !!
     * 
     * ********************************* WEB SERVICE DEPENDENCY ***************************
     * Please let Nick Miller <nmiller@med.usyd.edu.au> know about any changes in the below array since
     * he is using lucene query to get results back. Result set also contains lucene field names. So this
     * kind of changes need to be informed before hand before things start breaking  
     */
    
    public static function columns() {
    	$arr = array(
	        1  => array('luceneIndex' => 'docRef',                      	'displayName' => ''),
	        2  => array('luceneIndex' => 'doctype',                     	'displayName' => ''),
	        3  => array('luceneIndex' => 'auto_id',                     	'displayName' => 'ID'),
	        4  => array('luceneIndex' => 'lo_auto_id',            			'displayName' => 'LO ID'),
	        5  => array('luceneIndex' => 'lo_title',              			'displayName' => 'Learning Objective Description'),
	        6  => array('luceneIndex' => 'lo_achievement',        			'displayName' => 'LO Achievement'),
	        7  => array('luceneIndex' => 'lo_theme',              			'displayName' => 'LO '.Zend_Registry::get('Zend_Translate')->_('Theme')),
	        8  => array('luceneIndex' => 'lo_skill',              			'displayName' => 'LO Skill'),
	        9  => array('luceneIndex' => 'lo_discipline_ids',     			'displayName' => 'LO Discipline Ids'),
	        10 => array('luceneIndex' => 'lo_system',             			'displayName' => 'LO System'),
	        //Removed lo_level
	        //11 => array('luceneIndex' => 'lo_level',                      'displayName' => 'LO Level'),
	        //Also removed any references made to this key from the above arrays
	        12 => array('luceneIndex' => 'lo_created_by_full_name',     	'displayName' => 'Author'),
	        13 => array('luceneIndex' => 'lo_date_created',       			'displayName' => 'LO Date Submitted'),
	        16 => array('luceneIndex' => 'lo_approved_by_full_name',        'displayName' => 'LO Approved By'),
	        17 => array('luceneIndex' => 'lo_date_approved',      			'displayName' => 'LO Date Approved'),
	        18 => array('luceneIndex' => 'lo_date_next_review',   			'displayName' => 'LO Date Next Review'),
	        20 => array('luceneIndex' => 'lo_jmo',                			'displayName' => 'LO JMO'),
	        21 => array('luceneIndex' => 'lo_gradattrib',         			'displayName' => 'LO Gradattrib'),
	        22 => array('luceneIndex' => 'lo_keywords',           			'displayName' => 'Keywords'),
	        23 => array('luceneIndex' => 'lo_review',             			'displayName' => 'LO Review'),
	        24 => array('luceneIndex' => 'lo_assessment_type',    			'displayName' => 'LO Assessment Type'),
	        25 => array('luceneIndex' => 'lo_discipline_names',   			'displayName' => 'Discipline'),
	        
	        26 => array('luceneIndex' => 'ta_auto_id',            			'displayName' => 'TA ID'),
	        27 => array('luceneIndex' => 'ta_title',              			'displayName' => 'Teaching Activity Description'),
	        28 => array('luceneIndex' => 'ta_type',               			'displayName' => 'Teaching Activity Type'),
	        29 => array('luceneIndex' => 'ta_pbl',                			'displayName' => 'Problem'),
	        //30 => array('luceneIndex' => 'ta_cohort',             			'displayName' => 'Cohort'),
	        31 => array('luceneIndex' => 'ta_stage',              			'displayName' => Zend_Registry::get('Zend_Translate')->_('Stage')),
	        32 => array('luceneIndex' => 'ta_block',              			'displayName' => Zend_Registry::get('Zend_Translate')->_('Block')),
	        33 => array('luceneIndex' => 'ta_block_week',         			'displayName' => Zend_Registry::get('Zend_Translate')->_('Week')),
	        34 => array('luceneIndex' => 'ta_sequence_num',       			'displayName' => 'TA Sequence Num'),
	        35 => array('luceneIndex' => 'ta_student_grp',        			'displayName' => 'TA Student Group'),
	        36 => array('luceneIndex' => 'ta_principal_teacher_full_name',  'displayName' => 'TA Principal Teacher'),
	        37 => array('luceneIndex' => 'ta_created_by_full_name',         'displayName' => 'Author'),
	        38 => array('luceneIndex' => 'ta_date_created',       			'displayName' => 'TA Date Created'),
	        41 => array('luceneIndex' => 'ta_approved_by_full_name',        'displayName' => 'TA Approved By'),
	        42 => array('luceneIndex' => 'ta_date_approved',      			'displayName' => 'TA Date Approved'),
	
	        //Removed lo_level
	        //44 => array('luceneIndex' => 'ta_cms_doc_id',         		'displayName' => 'TA CMS Doc Id'),
	        //Also removed any references made to this key from the above arrays
	        45 => array('luceneIndex' => 'ta_notes',              		    'displayName' => 'TA Notes'),
	        
	        //New fields Added
	        46 => array('luceneIndex' => 'lo_curriculumarea1',      		'displayName' => 'Learning Area 1'),
	        47 => array('luceneIndex' => 'lo_curriculumarea2',      		'displayName' => 'Learning Area 2'),
	        48 => array('luceneIndex' => 'lo_curriculumarea3',      		'displayName' => 'Learning Area 3'),
	        49 => array('luceneIndex' => 'ta_current_teacher_full_name',    'displayName' => 'TA Current Teacher'),
	        50 => array('luceneIndex' => 'ta_evaluate_count',       		'displayName' => 'TA Feedback Count'),
	        51 => array('luceneIndex' => 'ta_block_no',             		'displayName' => 'TA Block No'),
	        52 => array('luceneIndex' => 'ta_block_week_zero_padded',		'displayName' => 'TA Block Week Zero Padded'),
	        53 => array('luceneIndex' => 'ta_resource_links_student',  	    'displayName' => 'TA Resources'),
	        //Used in class 'SearchResultsFormatPodcastService'
	        54 => array('luceneIndex' => 'ta_resource_podcast',             'displayName' => 'TA Podcast'),
	        55 => array('luceneIndex' => 'ta_resource_links_staff',         'displayName' => 'TA Resources'),
	        56 => array('luceneIndex' => 'ta_reviewed_by_full_name',        'displayName' => 'TA Reviewed by'),
	        57 => array('luceneIndex' => 'ta_date_reviewed',                'displayName' => 'TA Date Reviewed'),
	        58 => array('luceneIndex' => 'ta_activitytype_id',              'displayName' => 'TA Activity Type Id')
    	);
    	return $arr;
    }

    // Store/Retrieve format session for processing search results
    public static function getSearchFormatSession() {
        $session = new Zend_Session_Namespace('COMPASS_SEARCH');
        return $session->format;
    }

    public static function setSearchFormatSession($format) {
        $session = new Zend_Session_Namespace('COMPASS_SEARCH');
        $session->format = trim($format);
    }
    
    public static function processSearchConfigureCheckBoxes($ids){
        $columns = SearchConstants::columns();
        $result = array();
        foreach($ids as $id) {
            if(UserAcl::isStaffOrAbove() && $id == 53) {
                continue;
            }
            if(UserAcl::isStudent() && $id == 55) {
                continue;
            }
            if(isset($columns[$id]['displayName'])){
                $result[$id] = $columns[$id]['displayName'];
            }
        }
        return $result;
    }
    
    public static $formatPodcast = 'podcast';
    public static $formatCsv     = 'csv';
    public static function formatsAllowed() {
        return  array('csv','podcast');
    }
    
    //Used in SearchDefaultService.php
    public static $format = 
        array(
            //eg 'formatType' => array('luceneFieldIds' => "5,4,54")'
            //We only want to return 'ta_podcast_xml' lucene field when query is searched by specifying 
            //format is 'podcast'. Also the default mandatory fields would be returned.
            'podcast' => array('luceneFieldIds' => '26,54')
        );
    
}