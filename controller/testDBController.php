<?php
$param = Route::getParam();
$type = $param["type"];
unset($param["type"]);
if ($type == "select") {
    $userData = Database::executeDb("mysql1","getUser",$param);
} elseif ($type == "insert") {
    $userData = Database::executeDb("mysql1","insertUser",$param);
}elseif ($type == "update") {
    $userData = Database::executeDb("mysql1","updateUser",$param);
}elseif ($type == "delete") {
    $userData = Database::executeDb("mysql1","deleteUser",$param);
} elseif ($type == "testTransaction") {
    Database::setAutoCommit(false);
    $paramInsert["usr"] = "bbb";
    $paramInsert["pwd"] = "1234";
    $paramInsert["name"] = "name_b";
    $paramInsert["l_name"] = "l_name_b";
    Database::executeDb("mysql1","insertUser",$paramInsert);
    Database::executeDb("mysql1","deleteUserFail",$param);
    $userData = Database::commit();
}
echo json_encode($userData);