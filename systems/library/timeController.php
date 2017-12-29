<?php
class timeController {
    public static $start;
    public static $last_call;
    public static $type = "js";
    public static $time = 0;
    public static $static = array();
    static function set_type($type) {
        if (preg_match("^(js|html)$",$type)) {
            self::$type = $type;
        } else {
            self::$type = "js";
        }
    }
    static function start($msg,$first_init) {
        self::$start = $first_init;
        self::$last_call = microtime(true);
        self::$time++;
        $diff = microtime(true) - $first_init;
        $display = self::$time." - First Init::[".number_format($diff,5)."] - ".$msg;
        if (!is_array(self::$static)) {
            self::$static = array();
        }
        self::$static[] =$display;
    }
    static function phase($msg) {
        $diff = microtime(true) - self::$last_call;
        self::$last_call = microtime(true);
        self::$time++;
        $display = self::$time." - Diff::[".number_format($diff,5)."] - Total::[".number_format(self::$last_call-self::$start,5)."] - ".$msg;
        self::$static[] =$display;
    }
    static function showProcessTime() {
        $return_data = "";
        if (TIME_CHECK == true) {
            if (self::$type == "js") {
                $return_data .= "<script language='JavaScript'>\n";
                foreach (self::$static as $key => $val) {
                    $return_data .= "console.log('".$val."');\n";
                }
                $return_data .= "</script>";
            } else {
                foreach (self::$static as $key => $val) {
                    $return_data .= "<div>".$val."</div>";
                }
            }
        }
        echo $return_data;
    }
}