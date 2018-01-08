<?php
error_reporting(E_ALL);
define("BASE_DIR",__DIR__);
define("TRANSACTION_CODE",crc32(microtime(true)));
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
include_once BASE_DIR . "/systems/library/cache.php";
include_once BASE_DIR."/vendor/autoload.php";
cache::initAutoload(BASE_DIR."/systems/library/autoload.php");
cache::loadShare();
autoload::register();
config::define(BASE_DIR."/resource/config.json");
cache::loadResource();
$type= $_GET["t"];
$resource = $_GET["r"];
switch ($type) {
    case "css" :
        resource::genCss($resource);
        break;
    case "cssfs" :
        resource::genCssFs($resource);
        break;
    case "js" :
        resource::genJs($resource);
        break;
    case "jpg" || "png" || "gif" :
        resource::optimizeImage($resource,$type);
        break;
    default :
        header("HTTP/1.0 404 Not Found");
        exit();
}
