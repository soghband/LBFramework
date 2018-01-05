<?php

class cache {
    private static $_shareCache ;
    private static $_resourceCache;
    private static $_loaded = false;
    static function setCache($key,$data,$time) {

    }
    static function getCache($key) {

    }
    static function setShareCache($key,$data) {
        if (!is_array(self::$_shareCache)) {
            self::$_shareCache = array();
        }
        self::$_shareCache[$key] = $data;
    }
    static function getShareCache($key) {
        if (isset(self::$_shareCache[$key])) {
            return self::$_shareCache[$key];
        }
        return "";
    }
    static function setResourceCache($key,$data) {
        if (!is_array(self::$_resourceCache)) {
            self::$_resourceCache = array();
        }
        self::$_resourceCache[$key] = $data;
    }
    static function getResourceCache($key) {
        if (isset(self::$_resourceCache[$key])) {
            return self::$_resourceCache[$key];
        }
        return "";
    }
    static function saveShare() {
        if (self::$_loaded == false) {
            self::saveCache("share",self::$_shareCache);
        }
    }
    static function loadShare() {
        $_shareCache = self::loadCache("share");
        if ($_shareCache != "") {
            self::$_loaded = true;
        }
        self::$_shareCache = $_shareCache;
    }
    static function saveResource() {
        self::saveCache("resource",self::$_resourceCache);
    }
    static function loadResource() {
        $_resourceCache = self::loadCache("resource");
        self::$_resourceCache = $_resourceCache;
    }
    static function saveCache($name,$data) {
        if (function_exists("apcu_cache_info")) {
            apcu_add($name,$data);
        } else {
            self::file_cache_set($name,$data);
        }
    }
    static function loadCache($name) {
        if (function_exists("apcu_cache_info")) {
            $data = apcu_fetch($name);
        } else {
            $data = self::file_cache_get($name);
        }
        return $data;
    }
    private static function file_cache_get($name) {
        $md5 = md5($name);
        $data = "";
        if (file_exists(BASE_DIR."/cache_file/".$md5.".cache")) {
            $data_file = file_get_contents(BASE_DIR."/cache_file/".$md5.".cache");
            $data = unserialize($data_file);
        }
        return $data;
    }
    private static function file_cache_set($name,$data) {
        $md5 = md5($name);
        if (!is_dir(BASE_DIR."/cache_file")) {
            mkdir(BASE_DIR."/cache_file");
        }
        if (file_exists(BASE_DIR."/cache_file/".$md5.".cache")) {
            unlink(BASE_DIR."/cache_file/".$md5.".cache");
        }
        file_put_contents(BASE_DIR."/cache_file/".$md5.".cache",serialize($data));
    }
    static function initAutoload($autoload_file) {
        if (!class_exists("autoload")){
            include_once $autoload_file;
        }
    }
    static function clearCache() {
        if (function_exists("apcu_cache_info")) {
            if (apcu_clear_cache()) {
                echo "All Cache Cleared";
            }
        } else {
            $files = glob(BASE_DIR."/cache_file/*");
            foreach($files as $file){ // iterate files
                if(is_file($file)) {
                    $file_name_array = explode("/",$file);
                    $file_name = array_pop($file_name_array);
                    if (unlink($file)) {
                        echo "<div>Cache file deleted ".$file_name."</div>";
                    } else {
                        echo "<div>Cache file can't delete ".$file_name."</div>";
                    }
                }
            }
        }
    }
}