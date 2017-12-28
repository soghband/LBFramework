<?php
class viewController {
    static $_fs_css;
    static $_css;
    static $_em_js;
    static $_js;
    static $_view;
    static $_catch_page;
    static function getPageView($controllerArray) {
        $page_string = $controllerArray["controller"];
        if (count($controllerArray["param"]) > 0) {
            foreach ($controllerArray["param"] as $key => $val) {
                $page_string.= "&".$key."=".$val;
            }
        }
        echo "<br>".$page_string;
        $page_hash = md5($page_string);
        $page_cache = cacheController::getCache($page_hash);
        if ($page_cache == "") {
            echo "no cache page";
            $page_cache = self::genPage($controllerArray);
        }
        self::$_view = $page_cache;

    }
    static function sessionView() {

    }
    static function genPage($controllerArray) {
        $controler_file = BASE_DIR."/controller/".$controllerArray["controller"].".php";
        echo $controler_file;
        if (file_exists($controler_file)) {
            include_once $controler_file;
        } else {
            echo "controller not found";
        }
    }
    static function setFirstSignStyleSheet($css_file_name) {
        if ($css_file_name != "") {
            $css_array = explode(",",$css_file_name);
            foreach ($css_array as $val) {
                $file_path = BASE_DIR."/css/".$val.".css";
                if (file_exists($file_path)) {
                    self::$_fs_css[] = $css_file_name;
                } else {
                    echo "CSS File not found: ".$css_file_name;
                }
            }
        }
    }
    static function setEmbedJavaScript($js_file_name) {

    }
}