<?php
error_reporting(E_ALL);
define("BASE_DIR",dirname(__DIR__));
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
include "../systems/library/Cache.php";
Cache::clearCache();
