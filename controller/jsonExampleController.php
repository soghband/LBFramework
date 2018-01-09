<?php
View::setCachePage(false);
$data = array("aaa"=>"11","bbb");
echo json_encode($data);