<?php
/** Include Every Page **/

/**header and footer style sheet must move to it's controller if have multi-template in one site*/
view::addFirstSignStyleSheet("main,header");
view::addStyleSheet("footer");

view::addJavascript("jquery-3.0.0.min,socket.io,dev_io");