<?php
class Config {
    private static $config;
    static function define($config_file) {
        $config = Cache::getShareCache("config");
        if ($config == "") {
            $config_data = file_get_contents($config_file);
            $config = LBUtil::jsonDecode($config_data);
            Cache::setShareCache("config",$config);
        }
        self::$config = $config;
        foreach (self::$config as $key => $value) {
            if (!defined($key)) {
                define($key, $value);
            }
        }
    }
}