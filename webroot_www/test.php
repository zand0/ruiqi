<?php
$t1 = time();


for($i=0;$i<100000000;$i++){
    $tmp = $i;
}


$t2 = time();

echo $tmp;
echo $t2-$t1;