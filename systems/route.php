<?php
$route = array(
    "" => array("homeController"),
    "test" => array("testController0"),
    "{test_r}" => array("testController1","{test_r}"=>"^[a-zก-๙]*$"),
    "test1" => array("testController2"),
    "test/tests" => array("testController3"),
    "test/tests1" => array("testController4"),
    "test2/{test}/test33" => array("testController5","{test}"=>"^test_routes$"),
    "test2/{test}/{test2}" => array("testController6","{test}"=>"^test_route$","{test2}"=>"^test111$"),
    "test2/{test2}/{test3}" => array("testController7","{test2}"=>"^test_route$","{test3}"=>"^a.{3}b$")
);
