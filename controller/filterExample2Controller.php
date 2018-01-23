<?php
View::setCachePage(false);
var_dump(Route::getParam());
echo "<br>CSRF :". Session::getCSRF();

