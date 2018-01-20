<?php
View::addFirstSignCSS("document_fs");
View::addJS("document_menu");
if (!Route::getParam("class")) {
    View::addHtmlData("doc_page","document/home");
} else {
    View::addHtmlData("doc_page","document/".Route::getParam("class"));
}
$dataJson = file_get_contents(BASE_DIR."/resource/data/view.json");
Model::dataRegister("view_function_data", LBUtil::jsonDecode($dataJson));
$dataJsonMenu = file_get_contents(BASE_DIR."/resource/data/doc_menu.json");
Model::dataRegister("menu_data", LBUtil::jsonDecode($dataJsonMenu));
