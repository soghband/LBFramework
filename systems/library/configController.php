<?php
class configController {
    private static $config;
    static function define($config_file) {
        $config = cacheController::getShareCache("config");
        if ($config == "") {
            include_once $config_file;
            cacheController::setShareCache("config",$config);
        }
        self::$config = $config;
        foreach (self::$config as $key => $value) {
            if (!defined($key)) {
                define($key, $value);
            }
        }
    }
}