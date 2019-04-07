<?php
$param = Route::getParam();
$type = $param["type"];
unset($param["type"]);
if ($type == "select") {
    $userData = Database::excuteDb("mysql1","getUser",$param);
} elseif ($type == "insert") {
    $userData = Database::excuteDb("mysql1","insertUser",$param);
}elseif ($type == "update") {
    $userData = Database::excuteDb("mysql1","updateUser",$param);
}elseif ($type == "delete") {
    $userData = Database::excuteDb("mysql1","deleteUser",$param);
}
echo json_encode($userData);