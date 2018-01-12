<?php
use MatthiasMullie\Minify;
class Resource {
    static function registerResourceHash($fileList,$type) {
        $lastModify = "";
        if ($type == "css") {
            $lastModify = self::getCssLastModifyDate($fileList);
        } elseif ($type == "js") {
            $lastModify = self::getJsLastModifyDate($fileList);
        }
        $fileImplodeName = implode($fileList,",");
        $hashName = md5($fileImplodeName.$lastModify);
        Cache::setResourceCache($hashName,$fileList);
        return $hashName;
    }
    private static function getCssLastModifyDate($fileList) {
        $lastMod = 0;
        if (is_array($fileList) && count($fileList) > 0) {
            foreach ($fileList as $val) {
                if (file_exists(BASE_DIR."/".CSS_PATH."/".$val.".css")) {
                    $mod_time = filemtime(BASE_DIR."/".CSS_PATH."/".$val.".css");
                    if ($mod_time > $lastMod) {
                        $lastMod = $mod_time;
                    }
                } else {
                    PGNUtil::showMsg("CSS File not found: ".$val);
                }
            }
        }
        return $lastMod;
    }
    private static function getJsLastModifyDate($fileList) {
        $lastMod = 0;
        if (is_array($fileList) && count($fileList) > 0) {
            foreach ($fileList as $val) {
                if (file_exists(BASE_DIR."/".JS_PATH."/".$val.".js")) {
                    $mod_time = filemtime(BASE_DIR."/".JS_PATH."/".$val.".js");
                    if ($mod_time > $lastMod) {
                        $lastMod = $mod_time;
                    }
                } else {
                    PGNUtil::showMsg("JS File not found: ".$val);
                }
            }
        }
        return $lastMod;
    }
    static function genCss($hash) {
        $cssData = Cache::getResourceCache($hash);
        if (is_array($cssData) && count($cssData) > 0) {
            $cssCombine = "";
            foreach ($cssData as  $val) {
                if (!file_exists(BASE_DIR."/".CSS_PATH."/".$val.".css")) {
                    header("HTTP/1.0 404 Not Found");
                    exit();
                }
                $cssCombine.= file_get_contents(BASE_DIR."/".CSS_PATH."/".$val.".css");
            }
            if (strlen($cssCombine) > 0) {
                $minifierCss = new Minify\CSS();
                $minifierCss->add($cssCombine);
                $css_data = $minifierCss->minify();
                if (ENV_MODE != "dev") {
                    self::createPublicCSSFolder();
                    file_put_contents(BASE_DIR."/public/css/".$hash.".css",$css_data);
                }
                header("Content-type: text/css");
                $timeExpires = gmdate("D, d M Y H:i:s", time() + 3600) . " GMT";
                header("Expires: ".$timeExpires);
                echo $css_data;
                exit();
            }
        }
        header("HTTP/1.0 404 Not Found");
        exit();
    }
    static function genCssFs($resource) {
        $cssData = explode(",",$resource);
        if (is_array($cssData) && count($cssData) > 0) {
            $cssCombine = "";
            $cssCombine = self::combineCSS($cssData, $cssCombine);
            if (strlen($cssCombine) > 0) {
                $minifierCss = new Minify\CSS();
                $minifierCss->add($cssCombine);
                $css_data = $minifierCss->minify();
                header("Content-type: text/css");
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
        $jsData = Cache::getResourceCache($hash);
        if (is_array($jsData) && count($jsData) > 0) {
            $jsCombine = "";
            $jsCombine = self::combineAndMinifyJS($jsData, $jsCombine);
            if (strlen($jsCombine) > 0) {
                if (ENV_MODE != "dev") {
                    if (!file_exists(BASE_DIR . "/public/js")) {
                        mkdir(BASE_DIR . "/public/js");
                    }
                    file_put_contents(BASE_DIR . "/public/js/" . $hash . ".js", $jsCombine);
                }
                header("Content-Type: application/javascript");
                $timeExpires = gmdate("D, d M Y H:i:s", time() + (3600*30)) . " GMT";
                header("Expires: ".$timeExpires);
                echo $jsCombine;
                exit();
            }
        }
        header("HTTP/1.0 404 Not Found");
        exit();
    }
    static function optimizeImage($resource,$type) {
        $rawFilePath = BASE_DIR."/".RAW_IMAGE_PATH."/".$resource.".".$type;
        $imgFilePath =  BASE_DIR."/public/images/".$resource.".".$type;
        $header = array('gif'=> 'image/gif',
            'png'=> 'image/png',
            'jpg'=> 'image/jpeg');
        header('Content-type: ' . $header[$type]);
        $timeExpires = gmdate("D, d M Y H:i:s", time() + (3600*30)) . " GMT";
        header("Expires: ".$timeExpires);
        if (file_exists($rawFilePath)) {
            if (ENV_MODE != "dev") {
                self::createDirectory($resource);
            }
            switch ($type) {
                case "jpg" :
                    self::optimizeJPG($rawFilePath, $imgFilePath);
                    break;
                case "png" :
                    self::optimizePNG($rawFilePath, $imgFilePath);
                    break;
                default :
                    if (ENV_MODE != "dev") {
                        echo file_get_contents($rawFilePath);
                    } else {
                        copy($rawFilePath,$imgFilePath);
                        echo file_get_contents($imgFilePath);
                    }
            }
        }
    }
    private static function createDirectory($resource) {
        $dirArray = explode("/",$resource);
        array_pop($dirArray);
        $dirCreate = BASE_DIR."/public/images";
        if (!file_exists($dirCreate)) {
            mkdir($dirCreate);
        }
        while (count($dirArray) > 0) {
            $dirCreate .= "/".array_shift($dirArray);
            if (!file_exists($dirCreate)) {
                mkdir($dirCreate);
            }
        }
    }

    private static function createPublicCSSFolder()
    {
        if (!file_exists(BASE_DIR . "/public/css")) {
            mkdir(BASE_DIR . "/public/css");
        }
    }

    /**
     * @param $rawFilePath
     * @param $imgFilePath
     */
    private static function optimizePNG($rawFilePath, $imgFilePath)
    {
        $img = imagecreatefrompng($rawFilePath);
        imagesavealpha($img, true);
        if (ENV_MODE != "dev") {
            imagepng($img, $imgFilePath, PNG_COMPRESS_LEVEL, PNG_NO_FILTER);
            echo file_get_contents($imgFilePath);
        } else {
            imagepng($img, null, PNG_COMPRESS_LEVEL, PNG_NO_FILTER);
        }
    }

    /**
     * @param $rawFilePath
     * @param $imgFilePath
     */
    private static function optimizeJPG($rawFilePath, $imgFilePath)
    {
        $img = imagecreatefromjpeg($rawFilePath);
        if (ENV_MODE != "dev") {
            imagejpeg($img, $imgFilePath, JPG_QUALITY);
            echo file_get_contents($imgFilePath);
        } else {
            imagejpeg($img, null, JPG_QUALITY);
        }
    }

    /**
     * @param $jsData
     * @param $jsCombine
     * @return string
     */
    private static function combineAndMinifyJS($jsData, $jsCombine): string {
        foreach ($jsData as $val) {
            if (!file_exists(BASE_DIR . "/" . JS_PATH . "/" . $val . ".js")) {
                header("HTTP/1.0 404 Not Found");
                exit();
            }
            $jsDataLoad = file_get_contents(BASE_DIR . "/" . JS_PATH . "/" . $val . ".js");
            if (!preg_match(".min.", $val)) {
                $minifierJs = new Minify\JS();
                $minifierJs->add($jsDataLoad);
                $jsCombine .= $minifierJs->minify() . ";\n";
            } else {
                $jsCombine .= $jsDataLoad;
            }
        }
        return $jsCombine;
    }

    /**
     * @param $cssData
     * @param $cssCombine
     * @return string
     */
    private static function combineCSS($cssData, $cssCombine): string {
        foreach ($cssData as $val) {
            if (file_exists(BASE_DIR . "/" . CSS_PATH . "/" . $val . ".css")) {
                $cssCombine .= file_get_contents(BASE_DIR . "/" . CSS_PATH . "/" . $val . ".css");
            } else {
                header("HTTP/1.0 404 Not Found");
                exit();
            }
        }
        return $cssCombine;
    }
}