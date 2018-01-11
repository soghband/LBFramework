<?php

class Route {
    private static $route_index;
    private static $param;
    private static $csrf;
    static function register($route_file){
        self::$route_index = Cache::getShareCache("route");
        if (self::$route_index == "") {
            $route_data = file_get_contents($route_file);
            $route = PGNUtil::jsonDecode($route_data);
            foreach ($route as $key => $value ) {
                $key_split = explode("/",$key);
                $file = $value["controller"];
                $current_level_shift = array_shift($key_split);
                $param_refine = "";
                $pattern_refine = "";
                if (isset($value["url_filter"])) {
                    $pattern_refine = $value["url_filter"];
                }
                if (isset($value["param_filter"])) {
                    $param_refine = $value["param_filter"];
                }
                if (count($key_split) > 0) {
                    if (empty(self::$route_index[$current_level_shift])) {
                        $parent = array();
                        self::$route_index[$current_level_shift] = self::make_index($parent, $key_split, $file, $pattern_refine,$param_refine);
                    } else {
                        $parent = self::$route_index[$current_level_shift];
                        self::$route_index[$current_level_shift] = self::make_index($parent, $key_split, $file, $pattern_refine,$param_refine);
                    }
                    if (isset($pattern_refine[$current_level_shift])) {
                        //self::$route_index[$current_level_shift]["_pattern"] = $pattern_refine[$current_level_shift];
                        self::$route_index["_pattern"][$pattern_refine[$current_level_shift]] = $current_level_shift;
                    }
                } else {
                    if (!is_array(self::$route_index)) {
                        self::$route_index = array();
                    }
                    self::$route_index[$current_level_shift]["_controller"] = $file;
                    if (isset($param_refine)) {
                        self::$route_index[$current_level_shift]["_param"] = $param_refine;
                    }
                    if (isset($pattern_refine[$current_level_shift])) {
                        self::$route_index["_pattern"][$pattern_refine[$current_level_shift]] = $current_level_shift;
                    }
                }
            }
        }
        Cache::setShareCache("route",self::$route_index);
    }
    static function show_index() {
        echo "<pre>";
        var_dump(self::$route_index);
        echo "</pre>";
    }
    static function make_index($parent,$key_array,$file,$pattern,$param_refine) {
        $current_key_shift = array_shift($key_array);
        if (count($key_array) > 0) {
            if (empty($parent[$current_key_shift])) {
                $parent_s = array();
                $parent[$current_key_shift] = self::make_index($parent_s,$key_array,$file,$pattern,$param_refine);
            } else {
                $parent_s = $parent[$current_key_shift];
                $parent[$current_key_shift] = self::make_index($parent_s,$key_array,$file,$pattern,$param_refine);
            }
            $parent[$current_key_shift] = self::make_index($parent_s,$key_array,$file,$pattern,$param_refine);
            if (isset($pattern[$current_key_shift])) {
                $parent["_pattern"][$pattern[$current_key_shift]] = $current_key_shift;
            }
            return $parent;
        }
        $parent[$current_key_shift]["_controller"] = $file;
        if (isset($param_refine)) {
            $parent[$current_key_shift]["_param"] = $param_refine;
        }
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
        if (preg_match("/\?/",$path)) {
            list($url_split) = explode("?",$path);
        } else {
            $url_split = $path;
        }
        $path_decode = urldecode($url_split);
        $path_array = explode("/",trim($path_decode,"/"));
        if (preg_match("/^\/public.*/",$url_split)) {
            array_shift($path_array);
        }
        $route_index = self::$route_index;
        $parameter = array();
        $route = array();
        if (count($path_array) == 0) {
            $path_array[] = "";
        }
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
                if (!$check_route) {
                    $route_index = 404;
                }
            } else {
                $route_index = 404;
            }
        }
        /** Create POST - GET Parameter */
        if (isset($_REQUEST)) {
            $get_check = true;
            foreach ($_REQUEST as $key => $value) {
                if (isset($route_index["_param"][$key])) {
                    if (preg_match("/".$route_index["_param"][$key]."/",$value)) {
                        $parameter[$key] = $value;
                    } else {
                        $get_check = false;
                    }
                } else {
                    $get_check = false;
                }
            }
            if (!$get_check) {
                $route_index = 404;
            }
        }
        if (isset($route_index["_controller"])) {
            $route["controller"] = $route_index["_controller"];
        } else {
            $route["controller"] = 404;
        }
        $route["param"] = $parameter;
        self::$param = $parameter;
        return $route;
    }
    static function getParam($name="") {
        if ($name == "") {
            return self::$param;
        } else {
            if (isset(self::$param[$name])) {
                return self::$param[$name];
            } else {
                return "";
            }
        }
    }
    static  function createCSRF() {
        $csrf = Session::get("csrf");
        if ($csrf == "") {
            $csrf_time = microtime(true);
            $csrf = md5($csrf_time.Session::id());
            Session::set("csrf",$csrf);
        }
        return $csrf;
    }
    static function getCSRF() {
        return Session::get("csrf");
    }
}
