<?php
class ResourceTypeConstants {
    
    public static $CASE_SUMMARY                     = 'Case Summary';
    public static $CASE_SUMMARY_ID                  = 1;
    
    public static $HAND_BOOK                        = 'Hand Book';
    public static $HAND_BOOK_ID                     = 6;
    
    public static $RESOURCES                        = 'Resources';
    public static $RESOURCES_ID                     = 7;
    
    public static $CONTENT                          = 'Content';
    public static $CONTENT_ID                       = 8;
    
    public static $REFERENCES                       = 'References';
    public static $REFERENCES_ID                    = 9;
    
    public static $STAFF_ONLY_ID                    = 10;
    public static $STAFF_ONLY                       = 'Staff Only';
    
    public static $ADMINISTRATIVE_RESOURCES_ID      = 12;
    public static $ADMINISTRATIVE_RESOURCES         = 'Administrative Resources';
    
    public static $RECORDINGS_ID                    = 13;
    public static $RECORDINGS                       = 'Recordings';
    
    public static $INTRODUCTION_ID                  = 15;
    public static $INTRODUCTION                     = 'Introduction';
    
    public static $PROLOGUE_ID                      = 16;
    public static $PROLOGUE                         = 'Prologue';
    
    public static $PBLICON_ID						= 17;
    public static $PBLICON							= 'PBL Icon';
    
    public static $INTERNATIONAL_ID					= 18;
    public static $INTERNATINOAL					= 'International';
    
    public static $EMPTY                            = 'empty';
    
    public static $ALLOW_STUDENT                    = 'student';
    public static $ALLOW_STAFF                      = 'staff';
    
    public static function staffOnlyResourceTypeIds() {
        return array(
                    self::$STAFF_ONLY_ID
        );
    }
    
    public static function staffOtherResources() {
        return array(
            self::$CASE_SUMMARY_ID,
            self::$HAND_BOOK_ID,
            self::$RESOURCES_ID,
            self::$ADMINISTRATIVE_RESOURCES_ID,
            self::$STAFF_ONLY_ID,
            self::$INTRODUCTION_ID
        );
    }
    
    public static function studentOtherResources() {
        return array(
            self::$CASE_SUMMARY_ID,
            self::$HAND_BOOK_ID,
            self::$RESOURCES_ID,
            self::$ADMINISTRATIVE_RESOURCES_ID,
            self::$INTRODUCTION_ID
        );
    }
    
}
?>