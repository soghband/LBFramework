<?php
define("DATA_VIEW","<{view}>");
define("CONTROLLER_STR","controller");
define("HTML_EXTENSION",".html");
define("TEMPLATE_FOLDER",BASE_DIR."/view/template/");
define("VIEW_FOLDER",BASE_DIR."/view/");
use MatthiasMullie\Minify;
class View {
    private static $_fs_css;
    private static $_css;
    private static $_css_index;
    private static $_em_js;
    private static $_js;
    private static $_js_index;
    private static $_view;
    private static $_rawView;
    private static $_template = "default";
    private static $_data = array();
    private static  $_sessionData = array();
    private static $_cachePage = true;
    private static $_sessionProcess = false;
    private static $_pageHash;
    static function getPageView($controllerArray) {
        $page_string = $controllerArray[CONTROLLER_STR];
        if (count($controllerArray["param"]) > 0) {
            foreach ($controllerArray["param"] as $key => $val) {
                $page_string.= "&".$key."=".$val;
            }
        }
        $page_hash = md5($page_string);
        self::$_pageHash = $page_hash;
        header("PageHash: ".$page_hash);
        Cache::setPageHash($page_hash);
        Cache::loadPageCache();
        $page_cache = Cache::getCache("pageData");
        $session_process =  Cache::getCache("sessionProcess");
        if ($session_process != "") {
            self::$_sessionProcess = $session_process;
        }
        if ($page_cache == "") {
            self::genPage($controllerArray);
            $page_cache = self::$_rawView;
            if (self::$_cachePage && ENV_MODE != "dev") {
                Cache::setCache("pageData",$page_cache);
                Cache::setCache("sessionProcess",self::$_sessionProcess);
                Cache::savePageCache();
            }
        }
        if (self::$_sessionProcess) {
            self::$_view  = self::sessionView($page_cache,$controllerArray);
        } else {
            self::$_view = $page_cache;
        }
        echo  self::$_view;
    }
    static function setCachePage($bool) {
        self::$_cachePage = $bool;
    }
    static  function getPageHash() {
        return self::$_pageHash;
    }
    static function setSessionProcess($bool) {
        self::$_sessionProcess = $bool;
    }
    static  function  getTemplateName() {
        return self::$_template;
    }
    static function sessionView($data,$controllerArray) {
        if (file_exists(BASE_DIR."/controller/session/globalSession.php")) {
            include_once  BASE_DIR."/controller/session/globalSession.php";
        }
        if (file_exists(BASE_DIR."/controller/session/".$controllerArray[CONTROLLER_STR]."Session.php")) {
            include_once  BASE_DIR."/controller/session/".$controllerArray[CONTROLLER_STR]."Session.php";
        }
        $search = array();
        $replace = array();
        foreach (self::$_sessionData as $key => $val) {
            $search[] = $key;
            $replace[] = $val;
        }
       return str_replace($search,$replace,$data);
    }
    private static function genPage($controllerArray) {
        $templateUsingCheck = false;
        $html_file = VIEW_FOLDER.$controllerArray[CONTROLLER_STR].HTML_EXTENSION;
        $htmlFileCheck = ViewComponent::checkHtml($html_file);
        $controller_file = BASE_DIR."/controller/".$controllerArray[CONTROLLER_STR]."Controller.php";
        $controllerFileCheck = ViewComponent::checkController($controller_file);
        if ($htmlFileCheck) {
            self::addView($controllerArray[CONTROLLER_STR]);
            $templateUsingCheck = ViewComponent::checkTemplate(self::$_data[DATA_VIEW]);
            if (!$templateUsingCheck) {
                self::$_template = "";
            }
        }
        if (!$htmlFileCheck && !$controllerFileCheck) {
            PGNUtil::showMsg("File not found: ".$controllerArray[CONTROLLER_STR].".html or ".$controllerArray[CONTROLLER_STR]."Controller.php");
        }
        self::$_template = ViewComponent::controllerProcess($controllerFileCheck, $htmlFileCheck, $controller_file, self::$_template);
        self::templateProcess($templateUsingCheck, $htmlFileCheck, $html_file);
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
        if (isset(self::$_data[DATA_VIEW])) {
            unset(self::$_data[DATA_VIEW]);
        }
    }
    static function addView($fileName) {
        if (file_exists(VIEW_FOLDER.$fileName.HTML_EXTENSION)) {
            if (!is_array(self::$_data)) {
                self::$_data = array();
            }
            $currentViewData = "";
            if (isset(self::$_data[DATA_VIEW])) {
                $currentViewData = self::$_data[DATA_VIEW];
            }
            $currentViewData.= file_get_contents(VIEW_FOLDER.$fileName.HTML_EXTENSION);
            self::dataRegister("view",$currentViewData);
        } else {
            PGNUtil::showMsg("File Missing: view/".$fileName.HTML_EXTENSION);
        }
    }
    static function dataRegister($key,$data) {
        if (!is_array(self::$_data)) {
            self::$_data = array();
        }
        self::$_data["<{".$key."}>"] = $data;
    }
    static function dataRegisterFromHtml($key,$htmlFileName) {
        if (file_exists(VIEW_FOLDER.$htmlFileName.HTML_EXTENSION)) {
            $htmlData = file_get_contents(VIEW_FOLDER.$htmlFileName.HTML_EXTENSION);
            self::dataRegister($key,$htmlData);
        } else {
            PGNUtil::showMsg("File Missing: view/".$htmlFileName.HTML_EXTENSION);
        }
    }
    static function sessionDataRegister($key,$data) {
        if (!is_array(self::$_sessionData)) {
            self::$_sessionData = array();
        }
        self::$_sessionData["<$".$key."$>"] = $data;
    }
    static function setTemplate($template) {
        if (is_dir(TEMPLATE_FOLDER.$template)
            && file_exists(TEMPLATE_FOLDER.$template."/master.html")
            && file_exists(TEMPLATE_FOLDER.$template."/header.html")
            && file_exists(TEMPLATE_FOLDER.$template."/footer.html")
            && file_exists(TEMPLATE_FOLDER.$template."/meta.html")) {
            self::$_template = $template;
        } else {
            PGNUtil::showMsg("Template Missing: ".$template);
        }
    }
    static function addFirstSignStyleSheet($css_file_name) {
        if ($css_file_name != "") {
            $css_array = explode(",",$css_file_name);
            foreach ($css_array as $val) {
                $file_path = BASE_DIR."/".CSS_PATH."/".$val.".css";
                if (file_exists($file_path) && empty(self::$_css_index[$val])) {
                    self::$_css_index[$val] = 1;
                    self::$_fs_css[] = $val;
                } else {
                    if (!file_exists($file_path)) {
                        PGNUtil::showMsg("CSS File not found: ".$val);
                    }
                }
            }
        }
    }
    static function addStyleSheet($css_file_name) {
        if ($css_file_name != "") {
            $css_array = explode(",",$css_file_name);
            foreach ($css_array as $val) {
                $file_path = BASE_DIR."/".CSS_PATH."/".$val.".css";
                if (file_exists($file_path) && empty(self::$_css_index[$val])) {
                    self::$_css_index[$val] = 1;
                    self::$_css[] = $val;
                } else {
                    if (!file_exists($file_path)) {
                        PGNUtil::showMsg("CSS File not found: ".$val);
                    }
                }
            }
        }
    }
    static function addEmbedJavascript($js_file_name) {
        if ($js_file_name != "") {
            $js_array  = explode(",",$js_file_name);
            foreach ($js_array as $val) {
                $file_path = BASE_DIR."/".JS_PATH."/".$val.".js";
                if (file_exists($file_path) && empty(self::$_js_index[$val])) {
                    self::$_js_index[$val] = 1;
                    self::$_em_js[] = $val;
                } else {
                    if (!file_exists($file_path)) {
                        PGNUtil::showMsg("JS File not found: ".$val);
                    }
                }
            }
        }
    }
    static function addJavascript($js_file_name) {
        if ($js_file_name != "") {
            $js_array  = explode(",",$js_file_name);
            foreach ($js_array as $val) {
                $file_path = BASE_DIR."/".JS_PATH."/".$val.".js";
                if (file_exists($file_path) && empty(self::$_js_index[$val])) {
                    self::$_js_index[$val] = 1;
                    self::$_js[] = $val;
                } else {
                    if (!file_exists($file_path)) {
                        PGNUtil::showMsg("JS File not found: ".$val);
                    }
                }
            }
        }
    }
    private static function templateProcess($templateUsingCheck, $htmlFileCheck, $html_file) {
        if ($templateUsingCheck && self::$_template != "") {
            $out = ob_get_clean();
            if (empty(self::$_data[DATA_VIEW])) {
                self::$_data[DATA_VIEW] = "";
            }
            self::$_data[DATA_VIEW] .= $out;
            self::dataRegister("header", file_get_contents(TEMPLATE_FOLDER . self::$_template . "/header.html"));
            self::dataRegister("footer", file_get_contents(TEMPLATE_FOLDER . self::$_template . "/footer.html"));
            self::dataRegister("metaTag", file_get_contents(TEMPLATE_FOLDER . self::$_template . "/meta.html"));
            if (empty(self::$_data["<{title}>"]) && defined("DEFAULT_TITLE")) {
                self::dataRegister("title", DEFAULT_TITLE);
            }
            $fs_css_data = ViewComponent::firstSignCSSProcess(self::$_fs_css);
            self::dataRegister("firstSignCss", $fs_css_data);
            $registeredEmbedJS = self::$_em_js;
            $em_js_data_all = ViewComponent::embedJSProcess($registeredEmbedJS);
            self::dataRegister("embedJS", $em_js_data_all);
            self::$_rawView = file_get_contents(TEMPLATE_FOLDER . self::$_template . "/master.html");
            $css_resource = Resource::registerResourceHash(self::$_css, "css");
            ViewComponent::devIOProcess();
            $js_resource = Resource::registerResourceHash(self::$_js, "js");
            Cache::saveResourceCache();
            $uxControlJs = self::resourceProcess($css_resource, $js_resource);
            self::dataRegister("systemUXControl", $uxControlJs);
            self::dataReplace();
        } elseif ($htmlFileCheck) {
            self::$_rawView = file_get_contents($html_file);
        } else {
            self::$_rawView = ob_get_clean();
        }
    }

    /**
     * @param $css_resource
     * @param $js_resource
     * @return string
     */
    private static function resourceProcess($css_resource, $js_resource) {
        if (strlen($css_resource) > 0) {
            if (ENV_MODE == "dev" && ENABLE_DEV_IO) {
                $uxControlJs = "<style class='devCss' fileList=" . implode(",", self::$_css) . "></style>";
                $uxControlJs .= " <script language=JavaScript>loadJs('/js/" . $js_resource . ".js');</script>";
            } else {
                $uxControlJs = " <script language=JavaScript>loadCss('/css/" . $css_resource . ".css'" . (strlen($js_resource) > 0 ? ",loadJs('/js/" . $js_resource . ".js')" : "") . ");</script>";
            }
        }
        return $uxControlJs;
    }
}