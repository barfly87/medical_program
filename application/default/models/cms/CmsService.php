<?php

class CmsService {

    public function run($service) {
        if(! empty($service)) {
            switch($service) {
                case 'create_mediabank_collection':
                    $cmsMediabankCollectionCreator = new CmsMediabankCollectionCreator();
                    $cmsMediabankCollectionCreator->run(); 
                break;
                case 'compare_name':
                    $cmsMediabankCompare = new CmsMediabankCompare('name');
                    $cmsMediabankCompare->getCsv();
                break;
                case 'compare_sequence':
                    $cmsMediabankCompare = new CmsMediabankCompare('sequence');
                    $cmsMediabankCompare->getCsv();
                break;
                case 'compare_sequence_name':
                    $cmsMediabankCompare = new CmsMediabankCompare('sequence_name');
                    $cmsMediabankCompare->getCsv();
                break;
                case 'compare_name_doctype':
                    $cmsMediabankCompare = new CmsMediabankCompare('name_doctype');
                    $cmsMediabankCompare->getCsv();
                break;
                case 'linkcmsdocs':
                    $cmsMediabankCompare = new CmsMediabankCompare('linkcmsdocs');
                    $cmsLinks = $cmsMediabankCompare->getCmsLinks();
                    $cmsCompassLinkService = new CmsCompassLinkService();
                    $cmsCompassLinkService->storeCmsResources($cmsLinks);
                break;
                case 'linkcmspbls' :
                    $cmsMediabankCompare = new CmsMediabankCompare('linkcmspbls');
                    $cmsPbls = $cmsMediabankCompare->getCmsPblDocs();
                    $cmsCompassLinkService = new CmsCompassLinkService();
                    $cmsCompassLinkService->storeCmsPblResources($cmsPbls);
                    
                break;
                case 'updatecurrentteacher' :
                	$cmsCompassLinkService = new CmsCompassLinkService();
                	$cmsCompassLinkService->updateTACurrentTeacher(); exit();
                default:
                    print 'Invalid service requested.';
                    exit;
                break;
            }
        }
    }
    
}