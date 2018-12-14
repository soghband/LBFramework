<?php
class Time {
    public static $start;
    public static $last_call;
    public static $type = "js";
    public static $time = 0;
    public static $static = array();
    private static $show_process = false;
    static function setType($type) {
        if (preg_match("/^(js|html)$/",$type)) {
            self::$type = $type;
        } else {
            self::$type = "js";
        }
    }
    static function setDisplay($bool) {
        self::$show_process = $bool;
    }
    static function start($msg,$first_init) {
        if (TIME_CHECK  && ENV_MODE == "dev") {
            self::$show_process = true;
        }
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
    static function showTime() {
        if (self::$show_process) {
            $return_data = "";
            if (self::$type == "js") {
                $return_data .= "<script language='JavaScript'>\n";
                foreach (self::$static as  $val) {
                    $return_data .= "console.log('".$val."');\n";
                }
                $return_data .= "</script>";
            } elseif (self::$type == "json") {
                $return_data = json_encode(self::$static );
            } else {
                foreach (self::$static as  $val) {
                    $return_data .= "<div>".$val."</div>";
                }
            }
            $pageView = ob_get_contents();
            if (preg_match("/^({|\[).*(\]|})$/",$pageView)) {
                $pageData = ob_get_clean();
                $data = json_decode($pageData,true);
                $data["time_process"] = self::$static;
                echo json_encode($data);
            } else {
                echo $return_data;
            }
        }
    }
}