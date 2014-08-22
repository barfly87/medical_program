<?php
class ResourceConstants {
    
    public static $TYPE_MEDIABANK   = 'mediabank';
    
    public static $TYPE_pbl         = 'pbl';
    public static $TYPE_block       = 'block';
    public static $TYPE_lo          = 'lo';
    public static $TYPE_ta          = 'ta';
    public static $TYPES_allowed    = array('lo','ta','pbl','block');
    
    public static function createMetadataTable($array) {
        $string = '';
        if(is_array($array) &&  !empty($array)) {
            $string .= '<table>';
            foreach($array as $key=>$val) {
                $tdVal = $val;
                if(is_array($val)) {
                    $tdVal = self::createMetadataTable($val);
                }
                $string .= "<tr><th>$key</th><td>$tdVal</td></tr>";
            }
            $string .= '</table>';
        }
        return $string;
    }
    
}
?>