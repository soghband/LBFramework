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
if ($_GET["t"] == "css") {
    resource::genCss($_GET["r"]);
}
if ($_GET["t"] == "js") {
    resource::genJs($_GET["r"]);
}