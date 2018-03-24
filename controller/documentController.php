<?php
View::addFirstSignCSS("document_fs");
View::addJS("document_menu");
if (!Route::getParam("class")) {
    View::addHtmlData("doc_page","document/home");
} else {
    View::addHtmlData("doc_page","document/".Route::getParam("class"));
    if (file_exists(BASE_DIR."/resource/data/".Route::getParam("class").".json")) {
        $dataJson = file_get_contents(BASE_DIR."/resource/data/".Route::getParam("class").".json");
        Model::addData("function_data", LBUtil::jsonDecode($dataJson));
    }
}
$dataJsonMenu = file_get_contents(BASE_DIR."/resource/data/doc_menu.json");
Model::addData("menu_data", LBUtil::jsonDecode($dataJsonMenu));
