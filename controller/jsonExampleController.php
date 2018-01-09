<?php
view::setCachePage(false);
$data = array("aaa"=>"11","bbb");
echo json_encode($data);