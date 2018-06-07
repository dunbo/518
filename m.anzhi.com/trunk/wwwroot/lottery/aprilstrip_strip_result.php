<?php

include_once (dirname(realpath(__FILE__)).'/aprilstrip_init_page.php');

$is_girl = 0;
if (!empty($_GET['is_girl'])) {
	$is_girl = 1;
}

$tplObj->out['is_girl'] = $is_girl;
$tplObj->display("aprilstrip_strip_result.html");