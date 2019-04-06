<?php
$userData = Database::getData("mysql1","getUser",Route::getParam());
echo json_encode($userData);