<?php
/*
** 提前准备好礼包表与缓存
*/
include_once (dirname(realpath(__FILE__)).'/aprilstrip_init.php');
$film_csv = dirname(realpath(__FILE__)) . '/film_gift.csv';
$joke_csv = dirname(realpath(__FILE__)) . '/joke_gift.csv';


// 活动id，奖品配置
$activity_option = array(
    'where' => array(
        'config_type' => 'APRILSTRIP_LOTTERY_AWARD',
        'status' => 1
    ),
    //'cache_time' => 86400,
    'table' => 'pu_config'
);
$result = $model -> findOne($activity_option);
$activity_config = json_decode($result['configcontent'],true);
$active_id = $activity_config['activity_id'];//活动id


$r_cache_time = 5184000;

// 电影礼包
$csv_handle = fopen($film_csv, "r");
if (!$csv_handle) {
	var_dump("open file error");
	exit;
}
$package = 'com.kokozu.android';
while (($line_arr = mygetcsv($csv_handle, 1000, ",")) != FALSE) {
	$gift_card_pwd = $line_arr[0];
	// 入库
	$data = array(
		'package' => $package,
		'gift_card_no' => '',
		'gift_card_pwd' => $gift_card_pwd,
		'status' => 1,
		'__user_table' => 'aprilstrip_gift_list'
	);
	$model->insert($data, 'lottery/lottery');
}
fclose($csv_handle);

// 写缓存
$where = array();
$where['status'] = 1;
$where['package'] = $package;
$option = array(
	'where' => $where,
	'field' => 'id, package, gift_card_no, gift_card_pwd',
	'table' => 'aprilstrip_gift_list'
);
$gift_list = $model->findAll($option, 'lottery/lottery');
if (!empty($gift_list)) {
	$rkey_gift_list = "aprilstrip_{$active_id}:" . $package . ":gift_list";
	$redis->setlist_sec($rkey_gift_list, $gift_list, $r_cache_time);
}

// 十冷礼包
$csv_handle = fopen($joke_csv, "r");
if (!$csv_handle) {
	var_dump("open file error");
	exit;
}
$package = 'com.linekong.cjad.anzhi';
while (($line_arr = mygetcsv($csv_handle, 1000, ",")) != FALSE) {
	$gift_card_pwd = $line_arr[0];
	// 入库
	$data = array(
		'package' => $package,
		'gift_card_no' => '',
		'gift_card_pwd' => $gift_card_pwd,
		'status' => 1,
		'__user_table' => 'aprilstrip_gift_list'
	);
	$model->insert($data, 'lottery/lottery');
}
fclose($csv_handle);

// 写缓存
$where = array();
$where['status'] = 1;
$where['package'] = $package;
$option = array(
	'where' => $where,
	'field' => 'id, package, gift_card_no, gift_card_pwd',
	'table' => 'aprilstrip_gift_list'
);
$gift_list = $model->findAll($option, 'lottery/lottery');
if (!empty($gift_list)) {
	$rkey_gift_list = "aprilstrip_{$active_id}:" . $package . ":gift_list";
	$redis->setlist_sec($rkey_gift_list, $gift_list, $r_cache_time);
}


function mygetcsv(& $handle, $length = null, $d = ',', $e = '"') {
    $d = preg_quote($d);
    $e = preg_quote($e);
    $_line = "";
    $eof = false;
    while ($eof != true){
        $_line .= (empty ($length) ? fgets($handle) : fgets($handle, $length));
        $itemcnt = preg_match_all('/' . $e . '/', $_line, $dummy);
        if ($itemcnt % 2 == 0)
        $eof = true;
    }
    $_csv_line = preg_replace('/(?: |[ ])?$/', $d, trim($_line));
    $_csv_pattern = '/(' . $e . '[^' . $e . ']*(?:' . $e . $e . '[^' . $e . ']*)*' . $e . '|[^' . $d . ']*)' . $d . '/';
    preg_match_all($_csv_pattern, $_csv_line, $_csv_matches);
    $_csv_data = $_csv_matches[1];
    for ($_csv_i = 0; $_csv_i < count($_csv_data); $_csv_i++){
        $_csv_data[$_csv_i] = preg_replace('/^' . $e . '(.*)' . $e . '$/s', '$1', $_csv_data[$_csv_i]);
        $_csv_data[$_csv_i] = str_replace($e . $e, $e, $_csv_data[$_csv_i]);
    }
    return empty ($_line) ? false : $_csv_data;
}