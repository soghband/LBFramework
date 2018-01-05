<?php
use MatthiasMullie\Minify;
class resource {
    static function registerResourceHash($fileList,$type) {
        $lastModify = "";
        if ($type == "css") {
            $lastModify = self::getCssLastModifyDate($fileList);
        } elseif ($type == "js") {
            $lastModify = self::getJsLastModifyDate($fileList);
        }
        $fileImplodeName = implode($fileList,",");
        $hashName = md5($fileImplodeName.$lastModify);
        cache::setResourceCache($hashName,$fileList);
        return $hashName;
    }
    private static function getCssLastModifyDate($fileList) {
        $lastMod = 0;
        if (is_array($fileList) && count($fileList) > 0) {
            foreach ($fileList as $key=>$val) {
                if (file_exists(BASE_DIR."/".CSS_PATH."/".$val.".css")) {
                    $mod_time = filemtime(BASE_DIR."/".CSS_PATH."/".$val.".css");
                    if ($mod_time > $lastMod) {
                        $lastMod = $mod_time;
                    }
                } else {
                    pgnUtil::showMsg("CSS File not found: ".$val);
                }
            }
        }
        return $lastMod;
    }
    private static function getJsLastModifyDate($fileList) {
        $lastMod = 0;
        if (is_array($fileList) && count($fileList) > 0) {
            foreach ($fileList as $key=>$val) {
                if (file_exists(BASE_DIR."/".JS_PATH."/".$val.".js")) {
                    $mod_time = filemtime(BASE_DIR."/".JS_PATH."/".$val.".js");
                    if ($mod_time > $lastMod) {
                        $lastMod = $mod_time;
                    }
                } else {
                    pgnUtil::showMsg("JS File not found: ".$val);
                }
            }
        }
        return $lastMod;
    }
    static function genCss($hash) {
        $cssData = cache::getResourceCache($hash);
        if (is_array($cssData) && count($cssData) > 0) {
            $cssCombine = "";
            foreach ($cssData as $key => $val) {
                if (file_exists(BASE_DIR."/".CSS_PATH."/".$val.".css")) {
                    $cssCombine.= file_get_contents(BASE_DIR."/".CSS_PATH."/".$val.".css");
                } else{
                    header("HTTP/1.0 404 Not Found");
                    exit();
                }
            }
            if (strlen($cssCombine) > 0) {
                $minifierCss = new Minify\CSS();
                $minifierCss->add($cssCombine);
                $css_data = $minifierCss->minify();
                file_put_contents(BASE_DIR."/css/".$hash.".css",$css_data);
                echo $css_data;
            } else {
                header("HTTP/1.0 404 Not Found");
                exit();
            }
        } else {
            header("HTTP/1.0 404 Not Found");
            exit();
        }
    }
    static function genJs($hash) {
        $jsData = cache::getResourceCache($hash);
        if (is_array($jsData) && count($jsData) > 0) {
            $jsCombine = "";
            foreach ($jsData as $key => $val) {
                if (file_exists(BASE_DIR."/".JS_PATH."/".$val.".js")) {
                    $jsDataLoad = file_get_contents(BASE_DIR."/".JS_PATH."/".$val.".js");
                    if (!preg_match(".min.",$val)) {
                        $minifierJs = new Minify\JS();
                        $minifierJs->add($jsDataLoad);
                        $jsCombine .= $minifierJs->minify().";\n";
                    } else {
                        $jsCombine.= $jsDataLoad;
                    }
                } else{
                    header("HTTP/1.0 404 Not Found");
                    exit();
                }
            }
            if (strlen($jsCombine) > 0) {
                file_put_contents(BASE_DIR."/js/".$hash.".js",$jsCombine);
                echo $jsCombine;
            } else {
                header("HTTP/1.0 404 Not Found");
                exit();
            }
        } else {
            header("HTTP/1.0 404 Not Found");
            exit();
        }
    }
}