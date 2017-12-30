<?php
if (function_exists("apcu_cache_info")) {
    echo "APCu Enable<br>";
} else {
    echo "APCu Disable<br>";
}
if (function_exists("opcache_get_status" )) {
    echo "OPCache Enable<br>";
} else {
    echo "OPCache Disable<br>";
}