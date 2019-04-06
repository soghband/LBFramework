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
                self::$_db_config [$value["databaseName"]]["query"] = $queryListByName;
                self::$_db_config [$value["databaseName"]]["connectionString"] = $value["connectionString"];
                self::$_db_config [$value["databaseName"]]["username"] = $value["username"];
                self::$_db_config [$value["databaseName"]]["password"] = $value["password"];
            }
        }
        Cache::setShareCache("database",self::$_db_config);
    }

    public function query($query_string) {
        return $this->_db->query($query_string);
    }

    public function fetch() {

    }
    public static function getData($dbName,$queryName,$param) {
        $dbData = self::$_db_config[$dbName];
        $conn = null;
        $error = [];
        if (array_key_exists($dbName,self::$_db)) {
            $conn = self::$_db[$dbName];
        } else {
            $conn =  new PDO($dbData["connectionString"],$dbData["username"],$dbData["password"]);
            self::$_db[$dbName] =$conn;
        }
        $queryData = $dbData["query"][$queryName];
        $queryStr = $queryData["queryStatement"];
        $checkError = false;
        foreach ($queryData["param"] as $key => $val) {
            if (!array_key_exists($key,$param)) {
                if ($val["require"]) {
                    $checkError = true;
                    $error[] =  "Require parameter: ".$key;
                } else {
                    $queryStr = str_replace("#".$key,"1=1",$queryStr);
                }
            } else {
                $operator = "=";
                if (array_key_exists("operator",$val)) {
                    $operator = $val["operator"];
                }
                $queryStr = str_replace("#".$key,$val["field"].$operator.":".$key,$queryStr);
            }
        }
        $statement = null;
        if (!$checkError) {
            $statement = $conn->prepare($queryStr);
            $statement->execute($param);
        }
        $responseArray = null;
        if ($statement != null) {
            $responseArray = [];
            foreach ($statement as $dataRow) {
                $responseRow = [];
                foreach ($queryData["response"] as $key => $val) {
                    if (array_key_exists($val,$dataRow)) {
                        $responseRow[$key] = $dataRow[$val];
                    } else {
                        $error[] =  "Column not found : ".$val;
                    }
                }
                $responseArray[] = $responseRow;
            }
        }
        if (count($error) > 0) {
            return $error;
        }
        return $responseArray;
    }
}