<?php
/** Include Every Page **/

/**header and footer style sheet must move to it's controller if have multi-template in one site*/
View::addFirstSignStyleSheet("main,header");
View::addStyleSheet("footer");

View::addJavascript("jquery-3.0.0.min,socket.io,dev_io");