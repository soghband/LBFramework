<?php
class timeController {
    public static $start;
    public static $last_call;
    public static $type = "js";
    public static $time = 0;
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
        self::print_msg($display,true);
    }
    static function phase($msg) {
        $diff = microtime(true) - self::$last_call;
        self::$last_call = microtime(true);
        self::$time++;
        $display = self::$time." - Diff::[".number_format($diff,5)."] - Total::[".number_format(self::$last_call-self::$start,5)."] - ".$msg;
        self::print_msg($display);
    }
    static function print_msg($msg,$start=false) {
        if (TIME_CHECK == true) {
            if (self::$type == "js") {
                echo "<script language='JavaScript'>
                        ".($start == true ? "var transaction_code ='".TRANSACTION_CODE."'":"")."
                        if (transaction_code == '".TRANSACTION_CODE."') {
                            console.log('" . $msg . "')
                        }
                      </script>";
            } else {
                echo "<div>" . $msg . "</div>";
            }
        }
    }
}