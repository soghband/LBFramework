<?php

class routeController {
    private static $route_index;
    private static $param;
    static function register($route_file){
        self::$route_index = cacheController::getShareCache("route");
        if (self::$route_index == "") {
            $route = "";
            include_once $route_file;
            foreach ($route as $key => $value) {
                $key_split = explode("/",$key);
                $file = array_shift($value);
                $pattern_refine = $value;
                /*            echo "<pre>";
                            var_dump($pattern_refine);
                            echo "</pre>";*/
                $current_level_shift = array_shift($key_split);
                if (count($key_split) > 0) {
                    if (empty(self::$route_index[$current_level_shift])) {
                        $parent = array();
                        self::$route_index[$current_level_shift] = self::make_index($parent,$key_split,$file,$pattern_refine);
                    } else {
                        $parent = self::$route_index[$current_level_shift];
                        self::$route_index[$current_level_shift] = self::make_index($parent,$key_split,$file,$pattern_refine);
                    }
                    if (isset($pattern_refine[$current_level_shift])) {
                        //self::$route_index[$current_level_shift]["_pattern"] = $pattern_refine[$current_level_shift];
                        self::$route_index["_pattern"][$pattern_refine[$current_level_shift]] = $current_level_shift;
                    }
                } else {
                    self::$route_index[$current_level_shift]["_controller"] = $file;
                    if (isset($pattern_refine[$current_level_shift])) {
                        self::$route_index["_pattern"][$pattern_refine[$current_level_shift]] = $current_level_shift;
                    }
                }
            }
            cacheController::setShareCache("route",self::$route_index);
        }
    }
    static function show_index() {
        echo "<pre>";
        var_dump(self::$route_index);
        echo "</pre>";
    }
    static function make_index($parent,$key_array,$file,$pattern) {
        $current_key_shift = array_shift($key_array);
        if (count($key_array) > 0) {
            if (empty($parent[$current_key_shift])) {
                $parent_s = array();
                $parent[$current_key_shift] = self::make_index($parent_s,$key_array,$file,$pattern);

            } else {
                $parent_s = $parent[$current_key_shift];
                $parent[$current_key_shift] = self::make_index($parent_s,$key_array,$file,$pattern);
            }
            $parent[$current_key_shift] = self::make_index($parent_s,$key_array,$file,$pattern);
            if (isset($pattern[$current_key_shift])) {
                $parent["_pattern"][$pattern[$current_key_shift]] = $current_key_shift;
            }
            return $parent;
        }
        $parent[$current_key_shift]["_controller"] = $file;
        if (isset($pattern[$current_key_shift])) {
            $parent["_pattern"][$pattern[$current_key_shift]] = $current_key_shift;
        }
        return $parent;
    }
    static function refine_pattern($data) {
        $refine_data = array();
        foreach ($data as $val) {
            $data_split = explode('=', $val, 2);
            $refine_data["{".$data_split[0]."}"] = $data_split[1];
        }
        return $refine_data;
    }
    static function getRoute($path) {
        $path_decode = urldecode($path);
        $path_array = explode("/",trim($path_decode,"/"));
        $route_index = self::$route_index;
        $parameter = array();
        $route = "";
        while (count($path_array) > 0) {
            $shift_route = array_shift($path_array);
            if (isset($route_index[$shift_route])) {
                $route_index = $route_index[$shift_route];
            } else if (isset($route_index["_pattern"])) {
                $check_route = false;
                foreach ($route_index["_pattern"] as $key => $val) {
                    if (preg_match("/".$key."/",$shift_route)) {
                        $route_index = $route_index[$val];
                        $check_route = true;
                        $parameter[trim($val,"{}")] = $shift_route;
                    }
                }
                if ($check_route == false) {
                    $route_index = 404;
                }
            } else {
                $route_index = 404;
            }
        }
        if (isset($route_index["_controller"])) {
            $route["controller"] = $route_index["_controller"];
        } else {
            $route["controller"] = 404;
        }
        $route["param"] = $parameter;
        return $route;
    }
}
