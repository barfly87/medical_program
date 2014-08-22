<?php

/**
 * This class is programmed as a standalone CLASS so can be used else where
 * WARNING: So please make  
 */

class CmsMediabankCollectionCreator {
    
    private $config                     =   array('2010' => '19855','2009' => '18717','2008' => '16854','2007' => '15184');
    
    ## XPaths
    private $courseXPath                = '/course/Assignmentdocumentlink';
    private $blockProblemsXPath         = '/block/Assignmentdocumentlink';
    private $blockCommonnameXPath       = '/block/commonname';
    private $blockAcademicstageXPath    = '/block/academicstage';
    private $blockDocumentLinkPath      = '/block/documentlink';
    private $blockDocumentlinkNodes     = array('linkdocumentid','linkdocumenttypecode');
    private $problemSubtypeXPath        = '/problem/Assignmentdocumentlink';
    private $problemNameXPath           = '/problem/commonname';
    private $docId                      = '/%%TYPE%%/documentid';
    private $docTitle                   = '/%%TYPE%%/commonname';
    private $docDoctypefile             = '/%%TYPE%%/documenttypetitle';
    private $docPhase                   = '/%%TYPE%%/phasecode';
    
    private $assignmentdocumentlinkNodes= array('documentid','DocumentType');
    private $location                   = '/www/gmp/cds%%CDS_YEAR%%/xml/%%XML_ID%%.xml';
    private $mediabanklocation          = '/srv/imagebank/cmsdocs-%%CDS_YEAR%%/%%XML_ID%%.xml';
    private $urlFormat                  = 'http://www.gmp.usyd.edu.au/cds%%CDS_YEAR%%/x/%%XML_ID%%.html';                   
    
    ## Debugging vars
    private $DEBUG_BLOCK_ID             = '';
    private $DEBUG_PROBLEM_ID           = '';
    private $DEBUG_DOCUMENT_ID          = '';
    
    ## Vars for creating final xml
    private $currentCohort              = '';
    private $currentRotation            = ''; /* @TODO */
    private $currentBlockSeq            = '';
    private $currentBlock               = '';
    private $currentStage               = '';
    private $currentWeek                = '';
    private $currentDoctype             = '';
    private $currentSequence            = '';
    private $currentProblemName         = '';
    private $currentId                  = '';
    private $currentTitle               = '';
    private $currentDoctypefile         = '';
    private $currentPhase               = '';
    private $currentUrl                 = '';
    private $currentExportType          = '';
    
    private $xml = array(
        'cohort' => '',
        //elements found in COURSE document
        'rotation' => '',
        'block_sequence' => '',
        //elements found in BLOCK document
        'block' => '',
        'stage' => '',
        'week' => '',
        //elements found in PROBLEM document
        'doctype' => '',
        'sequence' => '',
        'problemname' => '',
        //elements found in last child (DOCUMENT)
        'id' => '',
        'title' => '',
        'doctypefile' => '',
        'phase' =>'',
        'url' => '',
        'export_type' => ''
        /* 
        elements used for DEBUGGING
        'DEBUG_BLOCK_ID' =>'',
        'DEBUG_PROBLEM_ID' => '',
        'DEBUG_DOCUMENT_ID' => ''
        */
    );
    
    public function run() {
        #ini_set("display_errors","1");
        #ERROR_REPORTING(E_ALL);
        print '<pre>';
        $this->process();
        print '</pre>';
    }
    
    ## PROCESS START
    public function process() {
        try {
            foreach($this->config as $cdsYear => $xmlId) {
                $this->currentCohort =  $cdsYear;
                $domxpath = $this->getDOMXPath($xmlId);
                $this->processCourse($domxpath);
                $this->currentCohort = '';  ## UNSET
            }
        } catch(Exception $ex) {
        }
    }
    
    ## STEP 1 :: PROCESS COURSE
    public function processCourse($domxpath) {
        $blocks = $this->getXPathData($domxpath, $this->courseXPath,$this->assignmentdocumentlinkNodes);
        if(is_array($blocks) && count($blocks) > 0 ) {
            $blockSequence = 1;
            foreach($blocks as $block) {
                if($block['DocumentType'] == 'block') {
                    $this->currentBlockSeq = $blockSequence;
                    $this->DEBUG_BLOCK_ID = $block['documentid']; ## DEBUG
                    $this->processBlock($block['documentid']); 
                    $this->processBlockThemeSessions($block['documentid']);
                    $this->currentBlockSeq = ''; ## UNSET                   
                    $blockSequence++;
                }                   
            }
        }
    }
    
    ## STEP BLOCK-THEME_SESSION 2  :: PROCESS THEME SESSIONS
    public function processBlockThemeSessions($blockXmlId) {
        $domxpath           = $this->getDOMXPath($blockXmlId);
        $this->currentBlock = $this->getXPathData($domxpath,$this->blockCommonnameXPath);
        $this->currentStage = $this->getXPathData($domxpath,$this->blockAcademicstageXPath);
        $blockThemeSessions = $this->getXPathData($domxpath,$this->blockDocumentLinkPath,$this->blockDocumentlinkNodes);
        if(is_array($blockThemeSessions) && $blockThemeSessions > 0) {
            foreach($blockThemeSessions as $blockThemeSession) {
                if($blockThemeSession['linkdocumenttypecode'] == 'themesession') {
                    $this->currentDoctype = 'themesession'; 
                    $this->processThemeSessionDoctype($blockThemeSession['linkdocumentid']);
                    $this->currentDoctype = '';
                }
            }
        }
        $this->currentBlock = ''; ## UNSET
        $this->currentStage = ''; ## UNSET
    }
    
    ## STEP BLOCK-THEME_SESSION 3 :: PROCESS LAST CHILD DOCUMENT
    public function processThemeSessionDoctype($docXmlId) {
        $domxpath                   = $this->getDOMXPath($docXmlId);
        
        $this->currentId            = $this->getXPathData($domxpath, str_replace('%%TYPE%%',$this->currentDoctype, $this->docId));
        $this->currentTitle         = $this->getXPathData($domxpath, str_replace('%%TYPE%%',$this->currentDoctype, $this->docTitle));
        $this->currentDoctypefile   = $this->getXPathData($domxpath, str_replace('%%TYPE%%',$this->currentDoctype, $this->docDoctypefile));
        $this->currentPhase         = '';//$this->getXPathData($domxpath, str_replace('%%TYPE%%',$this->currentDoctype, $this->docPhase));
        $this->currentUrl           = $this->getUrl();
        $this->currentExportType    = 'BlockThemeSession';
        
        $this->processXml();
         
        $this->currentId            = ''; ## UNSET
        $this->currentTitle         = ''; ## UNSET
        $this->currentDoctypefile   = ''; ## UNSET
        $this->currentPhase         = ''; ## UNSET
        $this->currentUrl           = ''; ## UNSET
        $this->currentExportType    = ''; ## UNSET
         
    }
    
    ## STEP BLOCK-PROBLEM 2  :: PROCESS BLOCK
    public function processBlock ($blockXmlId) {
        $domxpath           = $this->getDOMXPath($blockXmlId);
        $this->currentBlock = $this->getXPathData($domxpath,$this->blockCommonnameXPath);
        $this->currentStage = $this->getXPathData($domxpath,$this->blockAcademicstageXPath);
        $problems           = $this->getXPathData($domxpath,$this->blockProblemsXPath,$this->assignmentdocumentlinkNodes);
        
        if(is_array($problems) && count($problems) > 0) {
            $problemSequence = 1;
            foreach($problems as $problem) {
                if($problem['DocumentType'] == 'problem') {
                    $this->currentWeek = $problemSequence;
                    $this->DEBUG_PROBLEM_ID = $problem['documentid']; ## DEBUG
                    $this->processProblem($problem['documentid']);
                    $this->currentWeek = ''; ## UNSET 
                    $problemSequence++; 
                }
            }
        }
        $this->currentBlock = ''; ## UNSET
        $this->currentStage = ''; ## UNSET
    }
    
    ## STEP BLOCK-PROBLEM 3 :: PROCESS PROBLEM
    public function processProblem($problemXmlId) {
        $domxpath                   = $this->getDOMXPath($problemXmlId);
        $this->currentProblemName   = $this->getXPathData($domxpath, $this->problemNameXPath);
        $doctypes                   = $this->getXPathData($domxpath, $this->problemSubtypeXPath, $this->assignmentdocumentlinkNodes);
                
        if(is_array($doctypes) && count($doctypes) > 0) {
            $problemDocTypeSequence = array();
            foreach($doctypes as $doctype) {
                if(isset($problemDocTypeSequence[$doctype['DocumentType']])) {
                    $problemDocTypeSequence[$doctype['DocumentType']]++;    
                } else {
                    $problemDocTypeSequence[$doctype['DocumentType']] =  1;
                }
                $this->DEBUG_DOCUMENT_ID    = $doctype['documentid']; ## DEBUG
                $this->currentDoctype       = $doctype['DocumentType'];
                $this->currentSequence      = $problemDocTypeSequence[$doctype['DocumentType']];
                $this->processDoctype($doctype['documentid']);
                
                $this->DEBUG_DOCUMENT_ID    = ''; ## DEBUG
                $this->currentDoctype       = ''; ## UNSET
                $this->currentSequence      = ''; ## UNSET

            }
        }
        $this->currentProblemName = ''; ## UNSET
    }
    
    ## STEP BLOCK-PROBLEM 4 :: PROCESS LAST CHILD DOCUMENT
    public function processDoctype($docXmlId) {
        $domxpath                   = $this->getDOMXPath($docXmlId);
        
        $this->currentId            = $this->getXPathData($domxpath, str_replace('%%TYPE%%',$this->currentDoctype, $this->docId));
        $this->currentTitle         = $this->getXPathData($domxpath, str_replace('%%TYPE%%',$this->currentDoctype, $this->docTitle));
        $this->currentDoctypefile   = $this->getXPathData($domxpath, str_replace('%%TYPE%%',$this->currentDoctype, $this->docDoctypefile));
        $this->currentPhase         = $this->getXPathData($domxpath, str_replace('%%TYPE%%',$this->currentDoctype, $this->docPhase));
        $this->currentUrl           = $this->getUrl();
        $this->currentExportType    = 'BlockPbl';
        
        $this->processXml();
         
        $this->currentId            = ''; ## UNSET
        $this->currentTitle         = ''; ## UNSET
        $this->currentDoctypefile   = ''; ## UNSET
        $this->currentPhase         = ''; ## UNSET
        $this->currentUrl           = ''; ## UNSET
        $this->currentExportType    = ''; ## UNSET
         
    }
    /**
     * It creates xml file and returns DOMXPath of the xml
     */
    public function getDOMXPath($xmlId) {
        $xml = $this->getXml($xmlId);
        $dom = new DOMDocument;
        $dom->loadXML($xml);
        $domXpath = new DOMXPath($dom);
        return $domXpath;
    }
    
    /**
     * This function helps to get the xpath of xml.
     * Either directly access single node value and return as string.
     * OR
     * access values from more than one nodes and return as array
     */
    public function getXPathData($domxpath, $xpath, $tags = array()) {
        $domNodes = $domxpath->query($xpath);
        if(is_array($tags) && count($tags) > 0) {
            return $this->getValuesForMultiNodes($domNodes,$tags);
        } else {
            return $this->getValueForSingleNode($domNodes);
        }
    }

    public function getValueForSingleNode($domNode) {
        if($domNode->length > 0) {
            return $domNode->item(0)->nodeValue;
        }
        return '';
    }
    
    public function getValuesForMultiNodes($domNodes, $tags) {
        $domNodesArray = array();       
        $cnt = 0;
        foreach($domNodes as $domNode) {
            foreach($tags as $tag) {
                $nodeList = $domNode->getElementsByTagName($tag);
                for ($i = 0; $i < $nodeList->length; $i++) {
                    $domNodesArray[$cnt][$tag] = $nodeList->item($i)->nodeValue;
                }
            }
            $cnt++;
        }           
        return $domNodesArray;
    }

    /**
     * Return XML as string
     */
    public function getXml($xmlId) {
        $xmlLocation = $this->getLocation($xmlId);
        $handle = fopen($xmlLocation, "r");
        $xml = fread($handle, filesize($xmlLocation));
        fclose($handle);
        return $xml; 
    }
    
    /**
     * Return location for the $xmlId given.
     */
    public function getLocation($xmlId) {
        $xmlLocation = 'unknown location';
        if(! empty($this->currentCohort)) {
            $xmlLocation = str_replace('%%CDS_YEAR%%',$this->currentCohort,$this->location);
            return str_replace('%%XML_ID%%',$xmlId,$xmlLocation);
        }           
        return $xmlLocation;
    }
    
    /*
     * Return url for current cds and doc id
     */
    public function getUrl() {
        $url = $this->urlFormat;
        if(! empty($this->currentCohort) && !empty($this->currentId)) {
            $url = str_replace('%%CDS_YEAR%%',$this->currentCohort,$this->urlFormat);
            return str_replace('%%XML_ID%%',$this->currentId,$url);
        }           
        return $url;
    }
    
    /**
     * Return mediabank location
     */
    public function getMediabankLocation() {
        $xmlMediabankLocation = 'unknown location';
        if(! empty($this->currentCohort)) {
            $xmlMediabankLocation = str_replace('%%CDS_YEAR%%',$this->currentCohort,$this->mediabanklocation);
            return str_replace('%%XML_ID%%',$this->currentId,$xmlMediabankLocation);
        }           
        return $xmlMediabankLocation;
    }
     
    
    /**
     * The function creates tag/value information for the XML to be generated.
     */
    public function processXml() {
        if(!empty($this->currentId)) {
            $this->setXmlVars();
            $xml = $this->createXml();
            $this->storeXml($xml);
            print_r($this->xml);
        }
    }
    
    public function storeXml($xml) {
        $mediabankLocation = $this->getMediabankLocation();
        $handle = fopen($mediabankLocation,"w");    
        fwrite($handle, $xml);
        fclose($handle);
    }
    
    
    
    public function createXml() {
        $xml = '<?xml version="1.0" encoding="ISO-8859-1"?>';
        $xml .= '<doc>';
        foreach($this->xml as $tag => $val) {
            $xml .= '<'.trim($tag).'>'.htmlspecialchars(trim($val)).'</'.trim($tag).'>';                      
        }
        $xml .= '</doc>';
        return $xml;
    }
    
    public function setXmlVars() {
        $this->xml['cohort'] = $this->currentCohort;
        //THIS TAGS ARE FOUND IN COURSE DOCUMENT
        $this->xml['rotation'] = $this->currentRotation;
        $this->xml['block_sequence'] = $this->currentBlockSeq;
        //THIS TAGS ARE FOUND IN BLOCK DOCUMENT
        $this->xml['block'] = $this->currentBlock;
        $this->xml['stage'] = $this->currentStage;
        $this->xml['week'] = $this->currentWeek;
        //THIS TAGS ARE FOUND IN PROBLEM DOCUMENT
        $this->xml['doctype'] = $this->currentDoctype;
        $this->xml['sequence'] = $this->currentSequence;
        $this->xml['problemname'] = $this->currentProblemName;
        //THIS TAGS FOUND IN LAST CHILD DOCUMENT
        $this->xml['id'] = $this->currentId;
        $this->xml['title'] = $this->currentTitle;
        $this->xml['doctypefile'] = $this->currentDoctypefile;
        $this->xml['phase'] = $this->currentPhase;
        $this->xml['url'] = $this->currentUrl;
        $this->xml['export_type'] = $this->currentExportType;
        
        /*
        $this->xml['DEBUG_BLOCK_ID'] = $this->DEBUG_BLOCK_ID;
        $this->xml['DEBUG_PROBLEM_ID'] = $this->DEBUG_PROBLEM_ID;
        $this->xml['DEBUG_DOCUMENT_ID'] = $this->DEBUG_DOCUMENT_ID;
        */
    }
    
}