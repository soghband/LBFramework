<?php
error_reporting(E_ALL);
define("BASE_DIR",dirname(__DIR__));
define("TRANSACTION_CODE",crc32(microtime(true)));
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
include_once BASE_DIR . "/systems/library/Cache.php";
include_once BASE_DIR . "/vendor/autoload.php";
Cache::initAutoload(BASE_DIR."/systems/library/Autoload.php");
Cache::loadShareCache();
Autoload::register();
Config::define(BASE_DIR."/resource/config.json");
Cache::loadResourceCache();
$type= $_GET["t"];
$resource = $_GET["r"];
switch ($type) {
    case "css" :
        Resource::genCss($resource);
        break;
    case "cssfs" :
        Resource::genCssFs($resource);
        break;
    case "js" :
        Resource::genJs($resource);
        break;
    case "jpg" || "png" || "gif" || "svg" :
        Resource::optimizeImage($resource,$type);
        break;
    default :
        header("HTTP/1.0 404 Not Found");
        exit();
}
