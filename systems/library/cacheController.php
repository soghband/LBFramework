<?php

class cacheController {
    static $share_cache;
    static $cache;
    static $page_hash;
    static $cache_file_location;
    static $_loaded = false;
    static function setCache($key,$data,$time) {

    }
    static function getCache($key) {

    }
    static function setShareCache($key,$data) {
        self::$share_cache[$key] = $data;
    }
    static function getShareCache($key) {
        if (isset(self::$share_cache[$key])) {
            return self::$share_cache[$key];
        }
        return "";
    }
    static function saveShare() {
        if (self::$_loaded == false) {
            self::saveCache("share",self::$share_cache);
        }
    }
    static function loadShare() {
        $share_cache = "";
        $share_cache = self::loadCache("share");
        if ($share_cache != "") {
            self::$_loaded = true;
        }
        self::$share_cache = $share_cache;
    }
    static function saveCache($name,$data) {
        if (function_exists("apc_cache_info")) {
            apc_add($name,$data);
        } else {
            self::file_cache_set($name,$data);
        }
    }
    static function loadCache($name) {
        $data = "";
        if (function_exists("apc_cache_info")) {
            $data = apc_fetch($name);
        } else {
            $data = self::file_cache_get($name);
        }
        return $data;
    }
    static function file_cache_get($name) {
        $md5 = md5($name);
        $data = "";
        if (file_exists(BASE_DIR."/cache_file/".$md5.".cache")) {
            $data_file = file_get_contents(BASE_DIR."/cache_file/".$md5.".cache");
            //$data = json_decode($data_file,true);
            $data = unserialize($data_file);
        }
        return $data;
    }
    static function file_cache_set($name,$data) {
        $md5 = md5($name);
        if (!is_dir(BASE_DIR."/cache_file")) {
            mkdir(BASE_DIR."/cache_file");
        }
        //file_put_contents(BASE_DIR."/cache_file/".$md5.".cache",json_encode($data,true));
        file_put_contents(BASE_DIR."/cache_file/".$md5.".cache",serialize($data));
    }
    static function initAutoload($autoload_file) {
        if (!class_exists("autoloadController")){
            include_once $autoload_file;
        }
    }
    static function clearCache() {
        if (function_exists("apc_cache_info")) {
            if (apc_clear_cache()) {
                echo "All Cache Cleared";
            }
        } else {
            $files = glob(BASE_DIR."/cache_file/*");
            foreach($files as $file){ // iterate files
                if(is_file($file)) {
                    if (unlink($file)) {
                        $file_name_array = explode("/",$file);
                        $file_name = array_pop($file_name_array);
                        echo "<div>Cache file deleted ".$file_name."</div>";
                    } else {
                        echo "<div>Cache file can't delete ".$file_name."</div>";
                    }
                }
            }
        }
    }
}