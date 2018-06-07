<?php
include_once (dirname(realpath(__FILE__)).'/../init.php');
session_begin();
$aid = $_GET['aid'];
$tplObj -> out['aid'] = $aid;
$tplObj -> out['sid'] = $_GET['sid'];
$tplObj -> out['static_url'] = $configs['static_url'];
$tplObj -> display("schoolseason_end.html");