<?php
define("PATTERN_ARRAY_STR", "_pattern");
define("CONTROLLER_ARRAY_STR", "_controller");
define("PARAM_ARRAY_STR", "_param");
define("ROUTE_CONTROLLER_STR", "controller");

class Route {
    private static $_routeIndex;
    private static $_param;
    private static $_reqBody;

    static function loadConfig($routeFile) {
        self::$_routeIndex = Cache::getShareCache("route");
        if (self::$_routeIndex == "") {
            $route_data = file_get_contents($routeFile);
            $route = LBUtil::jsonDecode($route_data);
            foreach ($route as $key => $value) {
                self::processRouteRegister($key, $value);
            }
        }
        Cache::setShareCache("route", self::$_routeIndex);
    }

    static function showIndex() {
        echo "<pre>";
        var_dump(self::$_routeIndex);
        echo "</pre>";
    }

    private static function makeIndex($parent, $key_array, $file, $pattern, $param_refine,$method_refined) {
        $current_key_shift = array_shift($key_array);
        if (count($key_array) > 0) {
            if (empty($parent[$current_key_shift])) {
                $parent_s = array();
                $parent[$current_key_shift] = self::makeIndex($parent_s, $key_array, $file, $pattern, $param_refine,$method_refined);
            } else {
                $parent_s = $parent[$current_key_shift];
                $parent[$current_key_shift] = self::makeIndex($parent_s, $key_array, $file, $pattern, $param_refine,$method_refined);
            }
            $parent[$current_key_shift] = self::makeIndex($parent_s, $key_array, $file, $pattern, $param_refine,$method_refined);
            if (isset($pattern[$current_key_shift])) {
                $parent[PATTERN_ARRAY_STR][$pattern[$current_key_shift]] = $current_key_shift;
            }
            return $parent;
        }
        $parent[$current_key_shift][CONTROLLER_ARRAY_STR] = $file;
        if (isset($method_refined)) {
            $parent[$current_key_shift]["_method"] = $method_refined;
        }
        if (isset($param_refine)) {
            $parent[$current_key_shift][PARAM_ARRAY_STR] = $param_refine;
        }
        if (isset($pattern[$current_key_shift])) {
            $parent[PATTERN_ARRAY_STR][$pattern[$current_key_shift]] = $current_key_shift;
        }
        return $parent;
    }

    static function getRoute($path) {
        if (preg_match("/\?/", $path)) {
            list($url_split) = explode("?", $path);
        } else {
            $url_split = $path;
        }
        $path_decode = urldecode($url_split);
        $path_array = explode("/", trim($path_decode, "/"));
        if (preg_match("/^\/public.*/", $url_split)) {
            array_shift($path_array);
        }
        $route_index = self::$_routeIndex;
        $parameter = array();
        $route = array();
        if (count($path_array) == 0) {
            $path_array[] = "";
        }
        list($route_index, $parameter) = self::processRouteIndex($path_array, $route_index, $parameter);
        /** Create POST - GET Parameter */
        list($route_index, $parameter) = self::processRequestParameter($route_index, $parameter);
        $route["param"] = $parameter;
        $route = self::assignController($route_index, $route);
        self::$_param = $parameter;
        self::setReqBody();
        return $route;
    }

    public static function getReqBody() {
        return self::$_reqBody;
    }

    static function getParam($name = "") {
        if ($name == "") {
            return self::$_param;
        } else {
            if (isset(self::$_param[$name])) {
                return self::$_param[$name];
            } else {
                return "";
            }
        }
    }

    /**
     * @param $route_index
     * @param $route
     * @return mixed
     */
    private static function assignController($route_index, $route) {
        if (isset($route_index[CONTROLLER_ARRAY_STR])) {
            $route[ROUTE_CONTROLLER_STR] = $route_index[CONTROLLER_ARRAY_STR];
        } else {
            if (!is_array($route_index) && is_int($route_index)) {
                $route[ROUTE_CONTROLLER_STR] = $route_index;
            } else {
                $route[ROUTE_CONTROLLER_STR] = 404;
            }
        }
        return $route;
    }

    /**
     * @param $route_index
     * @param $parameter
     * @return array
     */
    private static function processRequestParameter($route_index, $parameter) {
        if (isset($_REQUEST)) {
            $get_check = true;
            foreach ($_REQUEST as $key => $value) {
                list($route_index, $parameter, $get_check) = self::processParameter($route_index, $parameter, $key, $value, $get_check);
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
        if (isset($route_index["_method"]) && strtoupper( $route_index["_method"]) != strtoupper($_SERVER["REQUEST_METHOD"])) {
            $route_index = 405;
        }
        return array($route_index, $parameter);
    }

    /**
     * @param $route_index
     * @param $parameter
     * @param $shift_route
     * @return array
     */
    private static function checkRoutePattern($route_index, $parameter, $shift_route) {
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
        $param_refine = null;
        $pattern_refine = null;
        $method_refined = null;
        if (isset($value["url_filter"])) {
            $pattern_refine = $value["url_filter"];
        }
        if (isset($value["paramFilter"])) {
            $param_refine = $value["paramFilter"];
        }
        if (isset($value["method"])) {
            $method_refined = $value["method"];
        }
        if (count($key_split) > 0) {
            if (empty(self::$_routeIndex[$current_level_shift])) {
                $parent = array();
                self::$_routeIndex[$current_level_shift] = self::makeIndex($parent, $key_split, $file, $pattern_refine, $param_refine,$method_refined);
            } else {
                $parent = self::$_routeIndex[$current_level_shift];
                self::$_routeIndex[$current_level_shift] = self::makeIndex($parent, $key_split, $file, $pattern_refine, $param_refine,$method_refined);
            }
            if (isset($pattern_refine[$current_level_shift])) {
                self::$_routeIndex[PATTERN_ARRAY_STR][$pattern_refine[$current_level_shift]] = $current_level_shift;
            }
        } else {
            if (!is_array(self::$_routeIndex)) {
                self::$_routeIndex = array();
            }
            self::$_routeIndex[$current_level_shift][CONTROLLER_ARRAY_STR] = $file;
            if (isset($method_refined)) {
                self::$_routeIndex[$current_level_shift]["_method"] = $method_refined;
            }
            if (isset($param_refine)) {
                self::$_routeIndex[$current_level_shift][PARAM_ARRAY_STR] = $param_refine;
            }
            if (isset($pattern_refine[$current_level_shift])) {
                self::$_routeIndex[PATTERN_ARRAY_STR][$pattern_refine[$current_level_shift]] = $current_level_shift;
            }
        }
    }

    /**
     * @param $route_index
     * @param $parameter
     * @param $key
     * @param $value
     * @return array
     */
    private static function processParameter($route_index, $parameter, $key, $value, $get_check) {
        if (isset($route_index[PARAM_ARRAY_STR][$key])) {
            if (preg_match("/" . $route_index[PARAM_ARRAY_STR][$key] . "/", $value)) {
                $parameter[$key] = $value;
            } else {
                $get_check = false;
            }
        } else {
            if (!BY_PASS_UNSPECIFIED_PARAMETER) {
                $get_check = false;
            }
        }
        return array($route_index, $parameter, $get_check);
    }

    private static function setReqBody() {
        $reqBody = file_get_contents('php://input');
        $search = ["\n", "\t"];
        $replace = ["", ""];
        $reqBody = str_replace($search, $replace, $reqBody);
        if (preg_match("/^({|\[).*(\]|})$/", trim($reqBody))) {
            self::$_reqBody = LBUtil::jsonDecode($reqBody);
        } else {
            self::$_reqBody = $reqBody;
        }
    }
}
