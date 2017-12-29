<?php
class configController {
    private static $config;
    static function define($config_file) {
        $config = cacheController::getShareCache("config");
        if ($config == "") {
            $config_data = file_get_contents($config_file);
            $config = pgnUtil::jsonDecode($config_data);
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