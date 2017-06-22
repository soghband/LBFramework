<?php
class autoloadController {
    public static $_list;
    static function register() {
        $list = cacheController::getShareCache("autoload");
        if ($list == "") {
            echo "no cache";
            include_once BASE_DIR."/systems/autoload.php";
            cacheController::setShareCache("autoload",$list);
        } else {
            echo "cache";
        }
        self::$_list = $list;
        foreach (self::$_list as $key => $val) {
            //echo BASE_DIR . "/" . $val;
            if (file_exists(BASE_DIR . "/" . $val)) {
                spl_autoload_register(function ($key) {
                    autoloadController::load_file($key);
                });
            }
        }
    }
    static function load_file($class) {
        $file = self::$_list[$class];
        include $file;
    }
}
?>