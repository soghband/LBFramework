<?php

class Database {
    private static $_db = [];
    private static $_db_config;
    public static function loadConfig($configFile) {
        self::$_db_config = Cache::getShareCache("database");
        if (self::$_db_config == "") {
            self::$_db_config  = [];
            $database_config_data = file_get_contents($configFile);
            $database_config_data = LBUtil::jsonDecode($database_config_data);
            foreach ($database_config_data as  $value ) {
                $queryListByName = [];
                foreach($value["queryList"] as  $queryValue) {
                    $queryListByName[$queryValue["queryName"]] = $queryValue;
                }
                $dbName = $value["databaseName"];
                self::$_db_config [$dbName]["query"] = $queryListByName;
                self::$_db_config [$dbName]["connectionString"] = $value["connectionString"];
                self::$_db_config [$dbName]["username"] = $value["username"];
                self::$_db_config [$dbName]["password"] = $value["password"];
            }
        }
        Cache::setShareCache("database",self::$_db_config);
    }

    private static function processFetchMapData($statement,$response) {
        $responseArray = [];
        if ($statement != null) {
            foreach ($statement as $dataRow) {
                $responseRow = [];
                foreach ($response as $key => $val) {
                    if (array_key_exists($val,$dataRow)) {
                        $responseRow[$key] = $dataRow[$val];
                    } else {
                        $error[] =  "Column not found : ".$val;
                    }
                }
                $responseArray[] = $responseRow;
            }
        }
        return $responseArray;
    }


    public function query($query_string) {
        return $this->_db->query($query_string);
    }

    public function fetch() {

    }
    public static function excuteDb($dbName, $queryName, $param) {
        $dbData = self::$_db_config[$dbName];
        $conn = null;
        if (array_key_exists($dbName,self::$_db)) {
            $conn = self::$_db[$dbName];
        } else {
            $conn =  new PDO($dbData["connectionString"],$dbData["username"],$dbData["password"]);
            self::$_db[$dbName] =$conn;
        }
        $queryData = $dbData["query"][$queryName];
        $resp = null;
        $queryCheck = explode(" ",$queryData["queryStatement"]);
        $queryType = strtolower($queryCheck[0]);
        switch ($queryType) {
            case "select":
                $resp = self::processSelect($conn,$queryData,$queryType,$param);
                break;
            case ("insert" || "update" || "delete") :
                $resp = self::processExecute($conn,$queryData,$queryType,$param);
                break;
            default :
                $resp = "";
        }
        return $resp;
    }
    private static function processExecute($conn,$queryData,$queryType,$param) {
        $queryStr = self::processMakeQueryStr($queryData["queryStatement"],$queryData["param"],$param,$queryType);
        $checkError = false;
        $error = [];
        if (is_array($queryStr)) {
            $error = $queryStr;
            $checkError = true;
        }
        $statement = null;
        $responseArray = null;
        if (!$checkError) {
            $statement = $conn->prepare($queryStr);
            $result = $statement->execute($param);
            if ($result) {
                $responseArray["status"]  = "success";
            } else {
                $error[] = "Query fail:".$queryStr;
                $error[] = "Param:".json_encode($param);
            }
        }
        if (count($error) > 0) {
            $errorResp["error"] = $error;
            return $errorResp;
        }
        return $responseArray;
    }
    private static function processSelect($conn,$queryData,$queryType,$param) {
        $queryStr = self::processMakeQueryStr($queryData["queryStatement"],$queryData["param"],$param,$queryType);
        $checkError = false;
        $error = [];
        if (is_array($queryStr)) {
            $error = $queryStr;
            $checkError = true;
        }
        $statement = null;
        $responseArray = null;
        if (!$checkError) {
            $statement = $conn->prepare($queryStr);
            $statement->execute($param);
            $responseArray = self::processFetchMapData($statement,$queryData["response"]);
        }
        if (count($error) > 0) {
            $errorResp["error"] = $error;
            return $errorResp;
        }
        return $responseArray;
    }
    private static function processMakeQueryStr($rawQuery,$queryParam,$param,$queryType) {
        $error = [];
        $queryStr =  $rawQuery;
        foreach ($queryParam as $key => $val) {
            if (!array_key_exists($key, $param)) {
                list($error, $queryStr) = self::processMakeQueryNotFoundValue($queryType, $val, $key, $error, $queryStr);
            } else {
                $queryStr = self::processMakeQueryFoundValue($val, $key, $queryStr);
            }
        }
        if (count($error) > 0) {
            return $error;
        }
        return $queryStr;
    }
    private static function processMakeQueryNotFoundValue($queryType, $val, $key, $error, $queryStr) {
        if ($val["require"]) {
            $error[] = "Require parameter: " . $key;
        } else {
            if ($queryType == "insert" && !array_key_exists("field", $val)) {
                $queryStr = str_replace("#" . $key, "NULL", $queryStr);
            } else {
                $queryStr = str_replace("#" . $key, "1=1", $queryStr);
            }
        }
        return array($error, $queryStr);
    }
    private static function processMakeQueryFoundValue($val, $key, $queryStr) {
        $operator = "=";
        if (array_key_exists("operator", $val)) {
            $operator = $val["operator"];
        }
        if (array_key_exists("field", $val)) {
            $queryStr = str_replace("#" . $key, $val["field"] . $operator . ":" . $key, $queryStr);
        } else {
            $queryStr = str_replace("#" . $key, ":" . $key, $queryStr);
        }
        return $queryStr;
    }

}