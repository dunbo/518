<?php
include_once (dirname(realpath(__FILE__)).'/../../init.php');
$active_id = $_GET['aid'] ? $_GET['aid'] : $_POST['aid'];
$sid = $_GET['sid'] ? $_GET['sid'] : $_POST['sid'];
$tplObj -> out['video_url'] = $_GET['video_url'];	
$tpl = "lottery/pre_down_operation/video.html";
$tplObj -> display($tpl);