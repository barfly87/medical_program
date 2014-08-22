<?php
class BlockMenu {
    public static function Studentlinks() {
        $isStudentOrAbove =  UserAcl::isStudentOrAbove();
        $links = array( 
                    array(
                        'page'  => BlockConst::$pageLearningObjectives,
                        'desc'  => BlockConst::$pageLearningObjectivesTitle,
                        'allow' =>  $isStudentOrAbove)
                );
        return $links;            
    } 

    public static function StaffLinks() {
        $isStaffOrAbove = UserAcl::isBlockchairOrAbove();
        $links = array( 
                    array(
                        'page'  => BlockConst::$pageManageResources,
                        'desc'  => BlockConst::$pageManageResourcesTitle,
                        'allow' => $isStaffOrAbove
                    )
                );
        return $links;            
        
    }
                            
}
?>