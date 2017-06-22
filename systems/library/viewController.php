<?php
class viewController {
    static $_fs_css;
    static $_css;
    static $_js;
    static $_view;
    static function getPageView($controller,$param) {
        $page_string = $controller;
        if (count($param) > 0) {
            foreach ($param as $key => $val) {
                $page_string.= "&".$key."=".$val;
            }
        }
        $page_hash = md5($page_string);
        $page_cache = cacheController::getCache($page_hash);
        if ($page_cache == "") {
            $page_cache = self::genPage($controller,$param);
        }
        self::$_view = $page_cache;
    }
    static function sessionView() {

    }
    static function genPage($controller,$param) {

    }
}