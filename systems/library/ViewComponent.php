<?php

use MatthiasMullie\Minify;
class ViewComponent {
    public static function checkHtml($html_file) {
        $htmlFileCheck = false;
        if (file_exists($html_file)) {
            $htmlFileCheck = true;
        }
        return $htmlFileCheck;
    }
    public static function checkController($controller_file) {
        $controllerFileCheck = false;
        if (file_exists($controller_file)) {
            $controllerFileCheck = true;
        }
        return $controllerFileCheck;
    }
    public static function checkTemplate($viewData) {
        $templateUsingCheck = false;
        if (!preg_match("/<html[ a-z='\"-_]*>/m", $viewData)) {
            $templateUsingCheck = true;
        }
        return $templateUsingCheck;
    }
    public static function firstSignCSSProcess($registeredFirstSignCss) {
        $fs_css_data = "";
        if (is_array($registeredFirstSignCss) && count($registeredFirstSignCss) > 0) {
            foreach ($registeredFirstSignCss as $firstSignVal) {
                $fs_css_data .= file_get_contents(BASE_DIR . "/" . CSS_PATH . "/" . $firstSignVal . ".css") . "\r\n";
            }
            $fs_css_data = self::processDisplayCss($registeredFirstSignCss, $fs_css_data);
        }
        return $fs_css_data;
    }
    public static function embedJSProcess($registeredEmbedJS) {
        $core_js = file_get_contents(BASE_DIR . "/systems/js/cssPreload.js");
        $core_js .= "\n" . file_get_contents(BASE_DIR . "/systems/js/jsPreload.js");
        if (JS_COMPRESS) {
            $minifierCoreJs = new  Minify\JS();
            $minifierCoreJs->add($core_js);
            $em_js_data_all = $minifierCoreJs->minify();
        } else {
            $em_js_data_all = $core_js;
        }
        if (is_array($registeredEmbedJS) && count($registeredEmbedJS) > 0) {
            foreach ($registeredEmbedJS as $val) {
                $em_js_data = file_get_contents(BASE_DIR . "/" . JS_PATH . "/" . $val . ".js");
                if (!preg_match("/\.min\./", $val)) {
                    $minifierJs = new Minify\JS();
                    $minifierJs->add($em_js_data);
                    $em_js_data_all .= $minifierJs->minify();
                }
            }
        }
        if (strlen($em_js_data_all) > 0) {
            $em_js_data_all = "<script language=JavaScript>" . $em_js_data_all . "</script>";
        }
        return $em_js_data_all;
    }

    public static function devIOProcess() {
        if (ENV_MODE == "dev" && ENABLE_DEV_IO) {
            if (!file_exists((BASE_DIR . "/" . JS_PATH . "/dev-tool.js"))) {
                $devToolContent = file_get_contents(BASE_DIR . "/systems/js/socket.io.js");
                $devToolContent .= "\n" . file_get_contents(BASE_DIR . "/systems/js/dev_io.js");
                file_put_contents(BASE_DIR . "/" . JS_PATH . "/dev-tool.js", $devToolContent);
            }
            View::addJS("dev-tool");
        }
    }
    public static function controllerProcess($controllerFileCheck, $controller_file) {
        if ($controllerFileCheck) {
            if (file_exists(BASE_DIR . "/controller/globalController.php")) {
                ob_start();
                include_once BASE_DIR . "/controller/globalController.php";
            }
            include_once $controller_file;
        }
    }
    private static function processDisplayCss($registeredFirstSignCss, $fs_css_data) {
        if (strlen($fs_css_data) > 0) {
            if (CSS_COMPRESS) {
                $minifierCss = new Minify\CSS();
                $minifierCss->add($fs_css_data);
                $css_printout = $minifierCss->minify();
            } else {
                $css_printout = $fs_css_data;
            }
            $fs_css_data = "<style " . (ENV_MODE == "dev" ? " class='devCss' fileList='" . implode(",", $registeredFirstSignCss) . "'" : "") . ">" . (ENABLE_DEV_IO && ENV_MODE == "dev" ? "" : $css_printout) . "</style>";
        }
        return $fs_css_data;
    }
}