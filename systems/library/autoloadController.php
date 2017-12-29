<?php
class autoloadController {
    public static $_list;
    static function register() {
        $list = cacheController::getShareCache("autoload");
        if ($list == "") {
            $list = json_decode(file_get_contents(BASE_DIR."/resource/autoload.json"),true);
            if ($list == null) {
                throw new Exception('Json Return NULL value');
            }
            cacheController::setShareCache("autoload",$list);
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