<?php
$app = $_GET['app'];

$dir = dirname(__FILE__).'/../GoPHP/cache';

$json1 = file_get_contents("{$dir}/{$app}.json");
$json2 = file_get_contents("{$dir}/{$app}_time.json");

$data = array_merge(json_decode($json1, true), json_decode($json2, true));

exit(json_encode($data));