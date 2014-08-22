<?php
class AdhocService {
    
    /**
     * OME would like to migrate CDS data systematically to Compass. This function would trawl throught database and
     * get all the TA's which have CDS links attached to it. It would also find all the information on the CDS data
     * through Mediabank and generate a list for OME to have a look
     */
    public function generatecdsreport() {
        set_time_limit(60 * 3);
        $mediabankResource = new MediabankResource();
        $linkLoTas = new LinkageLoTas();
        $linkLoTasRows = $linkLoTas->getLinkageWithStatus(Status::$RELEASED);
        $releasedTas = array();
        if(!empty($linkLoTasRows)) {
            foreach($linkLoTasRows as $linkLoTasRow) {
                $releasedTas[] = $linkLoTasRow->ta_id;
            }
        }
        if(!empty($releasedTas)) {
            $where = sprintf("type='ta' and type_id in (%s) and resource_id like '%%cms%%'",implode(',',$releasedTas));
            $rows = $mediabankResource->fetchAll($where);
            $csvRows = array();
            $teachingActivities = new TeachingActivities();
            $mediabankResourceService = new MediabankResourceService();
            if(!empty($rows)) {
                foreach($rows as $row) {
                    $csvRow         = array();
                    $ta             = $teachingActivities->fetchRow('auto_id = '.$row->type_id);
                    $metadata       = $mediabankResourceService->getMediabankMetaData($row->resource_id);
                    $csvRow['cds_doctype']       = $metadata['doctypefile'];
                    $csvRow['cds_id']            = $metadata['id'];
                    $csvRow['cds_title']         = $metadata['title'];
                    $csvRow['cds_url']           = 'http://smp.sydney.edu.au'.CmsConst::getCmsLink($row->resource_id,$row->type_id);
                    $csvRow['ta_id']             = $row->type_id;
                    $csvRow['ta_stage']          = $ta->stage ;
                    $csvRow['ta_block']          = $ta->block;
                    $csvRow['ta_block_week']     = $ta->block_week;
                    $csvRow['ta_pbl']            = $ta->pbl;
                    $csvRow['ta_sequence']       = $ta->sequence_num ;
                    $csvRow['ta_title']          = $ta->name;
                    $csvRow['mid']               = $row->resource_id;
                    $csvRow['resource_id']       = $row->auto_id;
                    $csvRows[]                   = $csvRow;
                }
            }
        }
        usort($csvRows,array($this, '_compareCdsReportRows'));
        $cmsCsvService = new CmsCsvService();
        $cmsCsvService->arrayToCsvDump($csvRows, 'CDS Report');
        exit;
    }
    
    private function _compareCdsReportRows($a, $b) {
        return strcmp($a['cds_doctype'], $b['cds_doctype']);
    }

    public function generatecdscontentreport() {
        set_time_limit(60 * 3);
        $midObjects = array(631,745,805,820,905,953,996,1083,1098,1423,1520,1535,1845,1846,1871,1884,1891,2391,2456,2462,2470,2497,
                            2525,2532,2554,2619,2645,2650,2664,2706,2709,2739,2764,2766,2775,2776,2780,2825,2830,2880,2886,2919,2927,
                            2934,2956,2959,2960,3026,3027,3052,3057,3185,3225,3226,3234,3237,3241,3242,3244,3247,3251,3259,3266,3268,
                            3276,3277,3279,3322,3340,3353,3362,3365,3412,3414,3515,3533,3535,3552,3556,3584,3729,4095,4156,4173,4200,
                            4290,4296,4297,4306,4307,4317,4332,4339,4341,4342,4359,4360,4362,4363,4364,4365,4367,4368,4369,4370,4371,
                            4372,4373,4374,4375,4376,4377,4378,4379,4380,4381,4382,4383,4384,4385,4386,4387,4388,4389,4390,4391,4406,
                            4407,4408,4450,4453,4486,4495,4496,4497,4505,4648,5045,8716,8717,8718,8720,8721,8723,8724,8727,8730,8731,
                            8732,8733,8734,8735,8736,8737,8738,8739,8740,8741,8742,8743,8744,8745,8746,8748,8749,8750,8751,8752,8753,
                            8754,8756,8757,8772,8775,8785,8790,8795,8798,9005,9259,9275,9713,10088);        
        $mids = array();
        foreach($midObjects as $midObject) {
            $mids[] = 'http://smp.sydney.edu.au/mediabank/|compassresources|'.$midObject;
        }
        $mediabankResource = new MediabankResource();
        $linkLoTas = new LinkageLoTas();
        $linkLoTasRows = $linkLoTas->getLinkageWithStatus(Status::$RELEASED);
        $releasedTas = array();
        if(!empty($linkLoTasRows)) {
            foreach($linkLoTasRows as $linkLoTasRow) {
                $releasedTas[] = $linkLoTasRow->ta_id;
            }
        }
        if(!empty($releasedTas)) {
            $where = sprintf("type='ta' and type_id in (%s) and resource_id in ('%s')",implode(',',$releasedTas), implode("','", $mids));
            $rows = $mediabankResource->fetchAll($where);
            $csvRows = array();
            $teachingActivities = new TeachingActivities();
            $mediabankResourceService = new MediabankResourceService();
            $mediabankResourceType = new MediabankResourceType();
            $resourceTypes = $mediabankResourceType->fetchAutoidResourceTypePair();
            if(!empty($rows)) {
                foreach($rows as $row) {
                    $csvRow         = array();
                    $ta             = $teachingActivities->fetchRow('auto_id = '.$row->type_id);
                    $metadata       = $mediabankResourceService->getMediabankMetaData($row->resource_id);
                    $csvRow['ta_type']                 = $ta->type;
                    $csvRow['ta_id']                   = $row->type_id;
                    $csvRow['ta_stage']                = $ta->stage ;
                    $csvRow['ta_block']                = $ta->block;
                    $csvRow['ta_block_week']           = $ta->block_week;
                    $csvRow['ta_pbl']                  = $ta->pbl;
                    $csvRow['ta_sequence']             = $ta->sequence_num ;
                    $csvRow['ta_title']                = $ta->name;
                    $csvRow['resource_id']             = $row->auto_id;
                    $csvRow['resource_type']           = $resourceTypes[$row->resource_type_id];
                    $csvRow['mid']                     = $row->resource_id;
                    $csvRow['mediabank_title']         = $metadata['title'];
                    $csvRow['mediabank_description']   = $metadata['description'];
                    $csvRows[]                         = $csvRow;
                }
            }
        }
        usort($csvRows,array($this, '_compareCdsContentReportRows'));
        $cmsCsvService = new CmsCsvService();
        $cmsCsvService->arrayToCsvDump($csvRows, 'CDS Content Report');
        exit;        
    }
    
    private function _compareCdsContentReportRows($a, $b) {
        return strcmp($a['ta_type'], $b['ta_type']);
    }
    
    
    /*
     * Basically this function would change UI and database
     * 
     * FROM
     *      Main Discipline         - Emergency Medicine
     *      Curriculum Area         - Drug and thera....
     *      Additional Discipline   - 
     *      Curriculum Area         - 
     * TO
     *      Main Discipline         - Emergency Medicine
     *      Curriculum Area         - 
     *      Additional Discipline   - Critical Care
     *      Curriculum Area         - Drug and thera....
     *      
     * Note Emergency Medicine is sub discipline of critical care
     * Same logic is applied for all the lo's which are subdiscipline of Critical Care or Surgery.
     */
    public static function moveCurriculumAreasUp1Discipline() {
        $db = Zend_Registry::get("db");
        //104 ===   select auto_id from lk_discipline where name = 'Critical Care';
        // 28 ===   select auto_id from lk_discipline where name = 'Surgery';
        $disciplineIds = array(104 => 'Critical Care', 28 => 'Surgery');
        $disciplineNos = array(1,2,3);
        $lo = new LearningObjectives();
        foreach($disciplineIds as $disciplineId => $disciplineName) {
            printf('####################<br />%s<br />####################<br />', $disciplineName);
            foreach($disciplineNos as $disciplineNo) {
                $disciplineNoPlus1 = $disciplineNo + 1;
                $query = <<< QUERY
                    select * from learningobjective 
                    where 
                        discipline{$disciplineNo} in (select auto_id from lk_discipline where parent_id = {$disciplineId}) 
                        and curriculumarea{$disciplineNo} != 0;
QUERY;
                $stmt = $db->query($query);
                $rows = $stmt->fetchAll();
                $tenspaces = str_repeat('&nbsp;',10);
                printf('%sFound %d learning objectives<br /><br />',str_repeat('&nbsp;',5), count($rows));
                if(! empty($rows)) {
                    $count = 0;
                    foreach($rows as $row) {
                        $loId = $row['auto_id'];
                        $loRow = $lo->fetchRow("auto_id = $loId ");
                        printf('%s%d) LO - %d  <br />',$tenspaces, ++$count,$loId);
                        if(! empty($loRow->discipline2Name)){
                            printf('%sIt seems we CANNOT move it to discipline 2 since discpline 2 is NOT empty<br />', $tenspaces);
                        }
                        $curr = new CurriculumAreas();
                        $result = $curr->fetchRow(sprintf("discipline_id = %d and curriculumarea='%s'", $disciplineId, $loRow->curriculumarea1Name));
                        if(!empty($result)) {
                            if($disciplineNo == 1) {
                                $loRow->discipline2      = $disciplineId;
                                $loRow->curriculumarea2  = $loRow->curriculumarea1;
                                $loRow->curriculumarea1  = 0;
                                $loRow->save();
                                SearchIndexer::reindexDocument('lo', $loId);
                                printf('%sSuccessfully moved<br />',$tenspaces.$tenspaces);
                            } else {
                                printf('%sFix it manually<br />', $tenspaces.$tenspaces);
                            }
                        } else {
                            printf('%s@@ ERROR ! Discipline = "%s" does NOT have Curriculum Area = "%s" attached to it @@<br />', $tenspaces.$tenspaces, $loRow->discipline1Name, $loRow->curriculumarea1Name);
                        }
                    }
                } 
                echo '<br /><br />';
            }
        }
        exit;
    }
    
}
?>