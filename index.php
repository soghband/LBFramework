<?php
$first_init = microtime(true);
error_reporting(E_ALL);
define("BASE_DIR",__DIR__);
define("TRANSACTION_CODE",crc32(microtime(true)));
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
include "systems/library/cacheController.php";
cacheController::initAutoload("systems/library/autoloadController.php");
cacheController::loadShare();
autoloadController::register();
configController::define("systems/config.php");
timeController::start("Start",$first_init);
timeController::phase("Config Register");
routeController::register("systems/route.php");
timeController::phase("Route Register");
$route = routeController::getRoute($_SERVER["REQUEST_URI"]);

timeController::phase("Route Calculate");
var_dump($route);

routeController::show_index();
cacheController::saveShare();
timeController::phase("Stop");
//var_dump($_SERVER);

