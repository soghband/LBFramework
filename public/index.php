<?php
$first_init = microtime(true);
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
Time::start("Start",$first_init);
Time::phase("Config Register");
Session::start();
Route::register(BASE_DIR."/resource/route.json");
//route::register(BASE_DIR."/systems/route.php");
Time::phase("Route Register");
$route = Route::getRoute($_SERVER["REQUEST_URI"]);
//route::show_index();
//var_dump($route);
Time::phase("Route Calculate");
View::getPageView($route);
Cache::saveShareCache();
Time::phase("Stop");
Time::showProcessTime();
//var_dump($_SERVER);

