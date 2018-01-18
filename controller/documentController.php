<?php
View::addFirstSignStyleSheet("document_fs");
View::dataRegisterFromHtml("doc_menu","document/menu");
if (!Route::getParam("class")) {
    View::dataRegisterFromHtml("doc_page","document/home");
} else {
    View::dataRegisterFromHtml("doc_page","document/".Route::getParam("class"));
}