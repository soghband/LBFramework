<?php
/** Include Every Page **/

/**header and footer style sheet must move to it's controller if have multi-template in one site*/
View::addFirstSignCSS("main,header");
View::addCSS("footer");

View::addJS("jquery-3.0.0.min");