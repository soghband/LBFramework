<?php
define("PATTERN_ARRAY_STR","_pattern");
define("CONTROLLER_ARRAY_STR","_controller");
define("PARAM_ARRAY_STR","_param");
define("ROUTE_CONTROLLER_STR","controller");
class Route {
    private static $route_index;
    private static $param;
    static function register($route_file){
        self::$route_index = Cache::getShareCache("route");
        if (self::$route_index == "") {
            $route_data = file_get_contents($route_file);
            $route = LBUtil::jsonDecode($route_data);
            foreach ($route as $key => $value ) {
                self::processRouteRegister($key, $value);
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
                $parent[PATTERN_ARRAY_STR][$pattern[$current_key_shift]] = $current_key_shift;
            }
            return $parent;
        }
        $parent[$current_key_shift][CONTROLLER_ARRAY_STR] = $file;
        if (isset($param_refine)) {
            $parent[$current_key_shift][PARAM_ARRAY_STR] = $param_refine;
        }
        if (isset($pattern[$current_key_shift])) {
            $parent[PATTERN_ARRAY_STR][$pattern[$current_key_shift]] = $current_key_shift;
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
        list($route_index, $parameter) = self::processRouteIndex($path_array, $route_index, $parameter);
        /** Create POST - GET Parameter */
        list($route_index, $parameter) = self::processRequestParameter($route_index, $parameter);
        $route = self::assignController($route_index, $route);
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

    /**
     * @param $route_index
     * @param $route
     * @return mixed
     */
    private static function assignController($route_index, $route)
    {
        if (isset($route_index[CONTROLLER_ARRAY_STR])) {
            $route[ROUTE_CONTROLLER_STR] = $route_index[CONTROLLER_ARRAY_STR];
        } else {
            $route[ROUTE_CONTROLLER_STR] = 404;
        }
        return $route;
    }

    /**
     * @param $route_index
     * @param $parameter
     * @return array
     */
    private static function processRequestParameter($route_index, $parameter)
    {
        if (isset($_REQUEST)) {
            $get_check = true;
            foreach ($_REQUEST as $key => $value) {
                if (isset($route_index[PARAM_ARRAY_STR][$key])) {
                    if (preg_match("/" . $route_index[PARAM_ARRAY_STR][$key] . "/", $value)) {
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
        return array($route_index, $parameter);
    }

    /**
     * @param $path_array
     * @param $route_index
     * @param $parameter
     * @return array
     */
    private static function processRouteIndex($path_array, $route_index, $parameter) {
        while (count($path_array) > 0) {
            $shift_route = array_shift($path_array);
            if (isset($route_index[$shift_route])) {
                $route_index = $route_index[$shift_route];
            } else if (isset($route_index[PATTERN_ARRAY_STR])) {
                list($route_index, $parameter) = self::checkRoutePattern($route_index, $parameter, $shift_route);
            } else {
                $route_index = 404;
            }
        }
        return array($route_index, $parameter);
    }

    /**
     * @param $route_index
     * @param $parameter
     * @param $shift_route
     * @return array
     */
    private static function checkRoutePattern($route_index, $parameter, $shift_route)
    {
        $check_route = false;
        foreach ($route_index[PATTERN_ARRAY_STR] as $key => $val) {
            if (preg_match("/" . $key . "/", $shift_route)) {
                $route_index = $route_index[$val];
                $check_route = true;
                $parameter[trim($val, "{}")] = $shift_route;
            }
        }
        if (!$check_route) {
            $route_index = 404;
        }
        return array($route_index, $parameter);
    }

    /**
     * @param $key
     * @param $value
     */
    private static function processRouteRegister($key, $value) {
        $key_split = explode("/", $key);
        $file = $value[ROUTE_CONTROLLER_STR];
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
                self::$route_index[$current_level_shift] = self::make_index($parent, $key_split, $file, $pattern_refine, $param_refine);
            } else {
                $parent = self::$route_index[$current_level_shift];
                self::$route_index[$current_level_shift] = self::make_index($parent, $key_split, $file, $pattern_refine, $param_refine);
            }
            if (isset($pattern_refine[$current_level_shift])) {
                self::$route_index[PATTERN_ARRAY_STR][$pattern_refine[$current_level_shift]] = $current_level_shift;
            }
        } else {
            if (!is_array(self::$route_index)) {
                self::$route_index = array();
            }
            self::$route_index[$current_level_shift][CONTROLLER_ARRAY_STR] = $file;
            if (isset($param_refine)) {
                self::$route_index[$current_level_shift][PARAM_ARRAY_STR] = $param_refine;
            }
            if (isset($pattern_refine[$current_level_shift])) {
                self::$route_index[PATTERN_ARRAY_STR][$pattern_refine[$current_level_shift]] = $current_level_shift;
            }
        }
    }
}
