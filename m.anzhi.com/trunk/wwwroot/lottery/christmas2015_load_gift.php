<?php

// 加载礼包码到redis

include_once('/data/www/wwwroot/new-wwwroot/m.goapk.com/lottery/christmas2015_init.php');

$active_id = $aid;

$package_arr = array();
foreach ($award_config as $award) {
	$award_type = $award[2];
	if ($award_type != 2) {
		continue;
	}
	$package = $award[5];
	$package_arr[] = $package;
}

foreach ($package_arr as $package) {
	$option = array(
		'where' => array(
			'package' => $package,
			'status' => 1
		),
		'field' => 'id, package, gift_card_no, gift_card_pwd',
		'order' => 'id asc',
		'table' => 'christmas2015_gift_list'
	);
	$gift_list = $model->findAll($option, 'lottery/lottery');
	var_dump($gift_list);
	if (!empty($gift_list)) {
		$rkey_gift_list = "christmas2015:{$active_id}:" . $package . ":gift_list";
		$redis->setlist_sec($rkey_gift_list, $gift_list, $r_cache_time);
	}
}