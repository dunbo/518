<?php

include_once (dirname(realpath(__FILE__)).'/../init.php');
if($_GET['sid'] && eregi('[0-9a-zA-z]', $_GET['sid']) && strlen($_GET['sid']) == 32){
	session_id($_GET['sid']);
}
$aid = $_GET['aid'];

$tplObj -> out['aid'] = $_GET['aid'];
$tplObj -> out['static_url'] = $configs['static_url'];
$tplObj -> out['sid'] = $_GET['sid'];
$tplObj -> display("mojianzr.html");