<?php
class viewController {
    static $_fs_css;
    static $_css;
    static $_em_js;
    static $_js;
    static $_view;
    static $_rawView;
    static $_catch_page;
    static $_template = "default";
    static $_data = array();
    static function getPageView($controllerArray) {
        $page_string = $controllerArray["controller"];
        if (count($controllerArray["param"]) > 0) {
            foreach ($controllerArray["param"] as $key => $val) {
                $page_string.= "&".$key."=".$val;
            }
        }
//        echo "<div>".$page_string."</div>";
        $page_hash = md5($page_string);
        $page_cache = cacheController::getCache($page_hash);
        if ($page_cache == "") {
//            echo "no cache page";
            $page_cache = self::genPage($controllerArray);
        }
        self::$_view = $page_cache;
        echo self::$_rawView;
    }
    static function sessionView() {

    }
    static function genPage($controllerArray) {
        $controller_file = BASE_DIR."/controller/".$controllerArray["controller"]."Controller.php";
        self::addView($controllerArray["controller"]);
        if (file_exists($controller_file)) {
            include_once $controller_file;
        }
        if (empty(self::$_data["<{view}>"])) {

        }
        self::dataRegister("header",file_get_contents(BASE_DIR."/view/template/".self::$_template."/header.html"));
        self::dataRegister("footer", file_get_contents(BASE_DIR."/view/template/".self::$_template."/footer.html"));
        self::dataRegister("metaTag", file_get_contents(BASE_DIR."/view/template/".self::$_template."/meta.html"));
        if (empty(self::$_data["<{title}>"]) && defined("DEFAULT_TITLE")) {
            self::dataRegister("title",DEFAULT_TITLE);
        }
        self::$_rawView  =  file_get_contents(BASE_DIR."/view/template/".self::$_template."/master.html");
        self::dataReplace();
    }
    static function dataReplace() {
        $search = array();
        $replace = array();
        foreach (self::$_data as $key => $val) {
            $search[] = $key;
            $replace[] = $val;
        }
        self::$_rawView = str_replace($search,$replace,self::$_rawView);
    }
    static function clearView() {
        if (isset(self::$_data["<{view}>"])) {
            unset(self::$_data["<{view}>"]);
        }
    }
    static function addView($fileName) {
        if (file_exists(BASE_DIR."/view/".$fileName.".html")) {
            if (!is_array(self::$_data)) {
                self::$_data = array();
            }
            $currentViewData = "";
            if (isset(self::$_data["view"])) {
                $currentViewData = self::$_data["view"];
            }
            $currentViewData.= file_get_contents(BASE_DIR."/view/".$fileName.".html");
            self::dataRegister("view",$currentViewData);
        } else {
            pgnUtil::showMsg("File Missing: view/".$fileName.".html");
        }
    }
    static function dataRegister($key,$data) {
        if (!is_array(self::$_data)) {
            self::$_data = array();
        }
        self::$_data["<{".$key."}>"] = $data;
    }
    static function setTemplate($template) {
        if (is_dir(BASE_DIR."/view/template/".$template)
            && file_exists(BASE_DIR."/view/template/".$template."/master.html")
            && file_exists(BASE_DIR."/view/template/".$template."/header.html")
            && file_exists(BASE_DIR."/view/template/".$template."/footer.html")) {
            self::$_template = $template;
        } else {
            pgnUtil::showMsg("Template Missing: ".$template);
        }
    }
    static function setFirstSignStyleSheet($css_file_name) {
        if ($css_file_name != "") {
            $css_array = explode(",",$css_file_name);
            foreach ($css_array as $val) {
                $file_path = BASE_DIR."/".CSS_PATH."/".$val.".css";
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