<?php
use MatthiasMullie\Minify;
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
        $htmlFileCheck = false;
        $controllerFileCheck = false;
        $templateUsingCheck = false;
        $html_file = BASE_DIR."/view/".$controllerArray["controller"].".html";
        if (file_exists($html_file)) {
            $htmlFileCheck = true;
        }
        $controller_file = BASE_DIR."/controller/".$controllerArray["controller"]."Controller.php";
        if (file_exists($controller_file)) {
            $controllerFileCheck = true;
        }
        if ($htmlFileCheck == true) {
            self::addView($controllerArray["controller"]);
            if (!preg_match("/<html[ a-z='\"-_]*>/",self::$_data["<{view}>"])) {
                $templateUsingCheck = true;
            }
        }
        if ($controllerFileCheck == true) {
            if ($htmlFileCheck == false) {
                self::$_template  = "";
            }
            include_once $controller_file;
        }
        if ($templateUsingCheck == true || self::$_template != "") {
            $out = ob_get_clean();
            if (empty(self::$_data["<{view}>"])) {
                self::$_data["<{view}>"] = "";
            }
            self::$_data["<{view}>"].=$out;
            self::dataRegister("header",file_get_contents(BASE_DIR."/view/template/".self::$_template."/header.html"));
            self::dataRegister("footer", file_get_contents(BASE_DIR."/view/template/".self::$_template."/footer.html"));
            self::dataRegister("metaTag", file_get_contents(BASE_DIR."/view/template/".self::$_template."/meta.html"));
            if (empty(self::$_data["<{title}>"]) && defined("DEFAULT_TITLE")) {
                self::dataRegister("title",DEFAULT_TITLE);
            }
            $fs_css_data = "";
            foreach(self::$_fs_css as $key => $val) {
                $fs_css_data .= file_get_contents(BASE_DIR."/".CSS_PATH."/".$val.".css")."\r\n";
            }
            if (strlen($fs_css_data) > 0) {
                $minifier = new Minify\CSS();
                $minifierCss = $minifier->add($fs_css_data);
                $fs_css_data = "<style>".$minifierCss->minify()."</style>";
            }
            self::dataRegister("firstSignCss",$fs_css_data);
            self::$_rawView  =  file_get_contents(BASE_DIR."/view/template/".self::$_template."/master.html");
            self::dataReplace();
        } elseif ($htmlFileCheck == true) {
            self::$_rawView  =  file_get_contents($html_file);
        }
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
            if (isset(self::$_data["<{view}>"])) {
                $currentViewData = self::$_data["<{view}>"];
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
            && file_exists(BASE_DIR."/view/template/".$template."/footer.html")
            && file_exists(BASE_DIR."/view/template/".$template."/meta.html")) {
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
                    self::$_fs_css[] = $val;
                } else {
                    echo "CSS File not found: ".$val;
                }
            }
        }
    }
    static function setEmbedJavaScript($js_file_name) {

    }
}