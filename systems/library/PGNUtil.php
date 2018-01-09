<?php
class PGNUtil{
    static function jsonDecode($json) {
        $array = json_decode($json,true);
        if ($array == null) {
            throw new InvalidArgumentException('Json Return NULL value');
        }
        return $array;
    }
    static function showMsg($message) {
        echo "<div style='color:red;margin:10px 25%;padding:10px;;border-radius: 5px;border:4px double #a90000;font-weight: bold;display: block;text-align: center'>" .$message."</div>";
        exit();
    }
}