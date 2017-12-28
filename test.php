<?php
$str = "apple/orange/banana";
$i = 0;
while(1){
    if($str = strstr($str,"a")){
        $i++;
        $str = substr($str,1);
    } else { break;  }
}
echo $i;