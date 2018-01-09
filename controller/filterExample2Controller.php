<?php
view::setCachePage(false);
var_dump(route::getParam());
echo "<br>CSRF :".route::getCSRF();
