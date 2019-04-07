<?php

class Database {
    private static $_db = [];
    private static $_db_config;
    private static $_auto_commit = true;
    private static $_allError = [];
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
                self::$_db_config [$dbName]["option"] = $value["option"];
            }
        }
        Cache::setShareCache("database",self::$_db_config);
    }
    public static function setAutoCommit($autocommit) {
        self::$_auto_commit = $autocommit;
    }
    public static function commit() {
        if (count(self::$_allError) == 0) {
            foreach (self::$_db as $conn) {
                $conn->commit();
            }
            return array("status"=>"success");
        } else {
            foreach (self::$_db as $conn) {
                $conn->rollback();
            }
            return array("status"=>"error","error"=>self::$_allError);
        }
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

    private static function convertOption($option) {
        $returnOption = [];
        $error = [];
        foreach ($option as $value) {
            if (strlen($value) > 0) {
                $valueConst = @constant($value);
                if ($valueConst != null) {
                    $returnOption[] = $valueConst;
                } else {
                    $error[] = "Could not find constant ".$value;
                }
            }
        }
        return array($error,$returnOption);
    }

    public static function executeDb($dbName, $queryName, $param) {
        $dbData = self::$_db_config[$dbName];
        $conn = null;
        $error = [];
        if (array_key_exists($dbName,self::$_db)) {
            $conn = self::$_db[$dbName];
        } else {
            list($error,$option) = self::convertOption($dbData["option"]);
            $conn =  new PDO($dbData["connectionString"],$dbData["username"],$dbData["password"],$option);
            self::$_db[$dbName] =$conn;
        }
        if (!self::$_auto_commit && !$conn->inTransaction()) {
            $conn->beginTransaction();
        }
        $queryData = $dbData["query"][$queryName];
        $resp = null;
        $queryCheck = explode(" ",$queryData["queryStatement"]);
        $queryType = strtolower($queryCheck[0]);
        $errorQuery = [];
        switch ($queryType) {
            case "select":
                list($errorQuery,$resp) = self::processSelect($conn,$queryData,$queryType,$param);
                break;
            case ("insert" || "update" || "delete") :
                list($errorQuery,$resp)= self::processExecute($conn,$queryData,$queryType,$param);
                break;
            default :
                $resp = "";
        }
        $error = array_merge($error,$errorQuery);
        if (count($error) > 0) {
            self::$_allError[] = $error;
            $errorResp["error"] = $error;
            return $errorResp;
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
        return array($error,$responseArray);
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

        return array($error,$responseArray);
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