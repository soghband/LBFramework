<?php

class SDB {
    public $_db;
    function __construct($con_string="") {
        if ($con_string != "") {
            $_db = new PDO($con_string);
        } else {
            $_db = new PDO(DB_CONNECT_STRING);
        }
    }
    public function query($query_string) {
        $data = $this->_db->query($query_string);
        return $data;
    }
    public function fetch() {

    }
}