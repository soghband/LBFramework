<?php
$first_init = microtime(true);
error_reporting(E_ALL);
define("BASE_DIR",__DIR__);
define("TRANSACTION_CODE",crc32(microtime(true)));
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
include_once BASE_DIR."/systems/library/cacheController.php";
include_once BASE_DIR."/vendor/autoload.php";
cacheController::initAutoload(BASE_DIR."/systems/library/autoloadController.php");
cacheController::loadShare();
autoloadController::register();
configController::define(BASE_DIR."/resource/config.json");
timeController::start("Start",$first_init);
timeController::phase("Config Register");
routeController::register(BASE_DIR."/resource/route.json");
//routeController::register(BASE_DIR."/systems/route.php");
timeController::phase("Route Register");
$route = routeController::getRoute($_SERVER["REQUEST_URI"]);
//routeController::show_index();
timeController::phase("Route Calculate");
viewController::getPageView($route);
//var_dump($route);
//routeController::show_index();
cacheController::saveShare();
timeController::phase("Stop");
timeController::showProcessTime();
//var_dump($_SERVER);

