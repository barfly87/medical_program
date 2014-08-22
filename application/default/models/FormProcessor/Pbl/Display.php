<?php
class FormProcessor_Pbl_Display extends FormProcessor_Pbl_Init {
    
    private $subaction = 'list';
    private $renderPage = null;
    
    public function process(Zend_Controller_Request_Abstract $request) {
        //This function process pbl related parameters and create pbl info
        parent::process($request);
        
        //This function processes request pertaining to this page
        $this->processGet();
    }
    
    private function processGet() {
        //$type can be 'ta' or 'lo' at this stage
        $type = $this->sanitize($this->request->getParam('type',''));
        $this->req['type'] = $type;
        if(empty($type)) {
            $this->error = true;
            Compass::error('The request param "type" is not provided. eg. ta, lo and pbl',  __DIR__.'/'.__CLASS__, __LINE__);
            return;
        }
        
        //$typeid can be a TA ID or LO ID or PBL ID
        $typeId = (int)$this->request->getParam('typeid','');
        if($typeId > 0) {
            $this->subaction = 'typeid';
            $this->req['typeId'] = $typeId;
            $this->setTaTitle($typeId);
        }
        
        //$category can be pblresources, managepblresources, studentresources or sequence
        $category = $this->request->getParam('category','');
        if(!empty($category)) {
            $this->req['category'] = $category;
            if($category == 'managepblresources') {
                if(! UserAcl::isBlockchairOrAbove()) {
                    $this->error = true;
                    Compass::error('Only blockchair and above are allowed to access manage resources pages.',  __DIR__.'/'.__CLASS__, __LINE__);
                    return;
                }
                $this->subaction = 'managepblresources';
            }
            if($category == 'studentresources') {
                if(!StudentResourceService::showSocialTools()) {
                    $this->error = true;
                    Compass::error('Only students and certain staff can see student resources.',  __DIR__.'/'.__CLASS__, __LINE__);
                    return;
                }
                $this->subaction = 'studentresources';
            }
        }
        
        //If ta resource or pbl resources are requested $this->req['resources'] won't be empty.
        $this->processResource();
        if(!empty($this->req['resources'])) {
            $this->subaction = 'resources';
        }
        
        $reqData = array();
        if($type == 'ta') { //If request is of type 'ta' we are expecting activitytypeid
            $activityTypeId = (int)$this->request->getParam('activitytypeid','');
            if($activityTypeId <= 0) {
                $this->error = true;
                Compass::error('The request param "activitytypeid" is not provided.',  __DIR__.'/'.__CLASS__, __LINE__);
                return;
            } else {
                $activityTypes = new ActivityTypes();
                $activityTypeNames = $activityTypes->getAllNames();
                if(empty($activityTypeNames) || !isset($activityTypeNames[$activityTypeId])) {
                    Compass::error('Activity type id requested "'.$activityTypeId.'" does not exist in the database');
                    return;
                }
            }
            $this->req['activityTypeId'] = $activityTypeId;
        }
        
        $this->setRequestConfig();
        
        //The setRequestConfig should have been called before this block of statements are executed.
        if(in_array($type, array('lo','ta'))) {
            $this->req['columns'] = $this->_getColumns($type); 
        }
        
        //set column headings
        $columnHeadings = array();
        if(isset($this->req['columns'])) {
            $columnHeadings = $this->_getColumnHeadings($this->req['columns']);
        }
        $this->req['columnHeadings'] = $columnHeadings;
        
        $this->req['reqConfig']    = $this->reqConfig;
        if(! isset($this->reqConfig['view'])) {
            $this->reqConfig['view'] = 'display';
        }
        $this->req['subaction']    = $this->subaction;
    }
    
    private function _getColumnHeadings($columns) {
        $columnHeadings = array();
        foreach($columns as $column) {
            $columHeading = $column['title'];
            if(isset($column['translatetitle'])) {
                /* If a university would like to name columns different that should be defined in the folder
                   "compass/languages/en_AU.php". They can create a new one and add that language config in the
                   config.ini file  */
                $columHeading = Zend_Registry::get('Zend_Translate')->_($columHeading);;
            }
            $columnHeadings[] = $columHeading;
        }
        return $columnHeadings;
    }
    
    /*
     * This is the title which would be shown by the browser. So user can bookmark accordingly. Each Url request 
     * should have a different title
     */
    public function getPageTitle() {
        $pageTitle = $this->pageTitle[0];
        if(isset($this->subaction)) {
            $error = '';
            switch($this->req['subaction']) {
                case 'list':
                    if(!empty($this->reqConfig) && isset($this->reqConfig['title']['plural'])) {
                        $pageTitle .= ' - '.$this->reqConfig['title']['plural'];
                    } else {
                        Compass::error('Config file should have option like item.*.title.plural for your activity type. (* represents activity type config name)',  __DIR__.'/'.__CLASS__, __LINE__);
                    }
                break;
                case 'typeid':
                    if(!empty($this->reqConfig) && isset($this->reqConfig['title']['singular'])) {
                        $pageTitle .= ' - '.$this->reqConfig['title']['singular'];
                        if(isset($this->req['taTitle']) && !empty($this->req['taTitle'])) {
                            $pageTitle .= ' - '.$this->req['taTitle'];
                        }
                    } else {
                        Compass::error('Config file should have option like item.*.title.singular for your activity type. (* represents activity type)',  __DIR__.'/'.__CLASS__, __LINE__);
                        $error .= '';
                    }
                break;
                case 'resources':
                    if(!empty($this->req['resources'])) {
                        if(isset($this->req['resourceIdTitle'])) {
                            $pageTitle .= ' - '.$this->req['resourceIdTitle'];
                        } else if(isset($this->req['resourceTypeName'])) {
                            $pageTitle .= ' - '.$this->req['resourceTypeName'];
                        }
                    }
                break;
                case 'managepblresources':
                    if(isset($this->reqConfig['title']['plural'])) {
                        $pageTitle .= ' - '.$this->reqConfig['title']['plural'];
                    } else {
                        Compass::error('Config file should have option like weekview.item.managepblresources.title.plural',  __DIR__.'/'.__CLASS__, __LINE__);
                    }
                break;
                case 'studentresources':
                    if(isset($this->reqConfig['title']['plural'])) {
                        $pageTitle .= ' - '.$this->reqConfig['title']['plural'];
                    } else {
                        Compass::error('Config file should have option like weekview.item.studentresources.title.plural',  __DIR__.'/'.__CLASS__, __LINE__);
                    }
                break;
            }
            if(!empty($error)) {
                Zend_Registry::get('logger')->warn(PHP_EOL."CLASS\t: ".__CLASS__.PHP_EOL."METHOD\t: ".__METHOD__.PHP_EOL."ERROR\t: ".$error.PHP_EOL);
            }
        }
        return $pageTitle;
    }
    
    private function _getColumns($type){
        switch($type) {
            case 'ta':
                return $this->_getColumnsForTa();
            break;
            case 'lo':
                return $this->_getColumnsForLo();
            break;
            default:
                return array();
            break;
        }
    }
    
    private function _getColumnsForLo() {
        $error = '';
        $configColumns = array();
        if(isset($this->configWeekview['lo']['default']['columns'])){
            $configColumns = $this->configWeekview['lo']['default']['columns'];
        } else {
            Compass::error('Could not find key "weekview.lo.default.columns" in the config file',  __DIR__.'/'.__CLASS__, __LINE__);
        }
        return $this->_getConfigColumns($configColumns);
    }
    
    private function _getColumnsForTa() {
        $configColumns = array();
        if(isset($this->reqConfig['columns'])) {
            $configColumns = $this->reqConfig['columns'];
        } else if(isset($this->configWeekview['ta']['default']['columns'])){
            $configColumns = $this->configWeekview['ta']['default']['columns'];
        } else {
            Compass::error('Could not find key "weekview.ta.default.columns" in the config file or any custom columns (e.g weekview.item.lecture.columns[] either)',  __DIR__.'/'.__CLASS__, __LINE__);
        }
        return $this->_getConfigColumns($configColumns);
    }
    
    private function _getConfigColumns($configColumns) {
        $configListColumn = null;
        if(isset($this->configWeekview['list']['column'])) {
            $configListColumn = $this->configWeekview['list']['column'];
        } else {
            Compass::error('Could not find key "weekview.list.column" in the config file',  __DIR__.'/'.__CLASS__, __LINE__);
        }
    
        $columns = array();
        if(! empty($configColumns) && !empty($configListColumn)) {
            foreach($configColumns as $configColumn) {
                if(isset($configListColumn[$configColumn])) {
                    $columns[] = $configListColumn[$configColumn];
                } else {
                    Compass::error('Config option weekview.list.column.'.$configColumn.' does not seem to exist',  __DIR__.'/'.__CLASS__, __LINE__);
                }
            }
        }
        return $columns;
    }
} 
?>