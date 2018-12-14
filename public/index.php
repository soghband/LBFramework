<?php
$first_init = microtime(true);
/** Remove Error  Show if Core Finish */
error_reporting(E_ALL);
define("BASE_DIR",dirname(__DIR__));
define("TRANSACTION_CODE",crc32(microtime(true)));
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
/** End Error Show */
include_once BASE_DIR . "/systems/library/Cache.php";
include_once BASE_DIR . "/vendor/autoload.php";
Cache::initAutoload(BASE_DIR."/systems/library/Autoload.php");
Cache::loadShareCache();
Cache::loadResourceCache();
Autoload::register();
Config::define(BASE_DIR."/resource/config.json");
Time::start("Start",$first_init);
Time::phase("Config Register");
date_default_timezone_set(TIME_ZONE);
Session::start();
Route::register(BASE_DIR."/resource/route.json");
Time::phase("Route Register");
$route = Route::getRoute($_SERVER["REQUEST_URI"]);
Time::phase("Route Calculate");
View::getPageView($route);
if (ENV_MODE != "dev") {
    Cache::saveShareCache();
}
Time::phase("Stop");
Time::showTime();

