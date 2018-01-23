<?php
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
View::setCachePage(false);
echo microtime(true);
Time::setType("html");
Time::setDisplay(true);