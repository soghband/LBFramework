<?php
class Autoload {
    public static $_list;
    static function register() {
        $list = Cache::getShareCache("autoload");
        if ($list == "") {
            $list = json_decode(file_get_contents(BASE_DIR."/resource/autoload.json"),true);
            if ($list == null) {
                throw new InvalidArgumentException('Json Return NULL value');
            }
            Cache::setShareCache("autoload",$list);
        }
        self::$_list = $list;
        foreach (self::$_list as $val) {
            if (file_exists(BASE_DIR . "/" . $val)) {
                spl_autoload_register(function ($key) {
                    Autoload::loadFile($key);
                });
            }
        }
    }
    static function loadFile($class) {
        $file = self::$_list[$class];
        include BASE_DIR."/".$file;
    }
}
?>