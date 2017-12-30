<?php
if (function_exists("apcu_cache_info")) {
    echo "11";
} else {
    echo "22";
}
apcu_store("aa","1");
echo apcu_fetch("aa");
phpinfo();