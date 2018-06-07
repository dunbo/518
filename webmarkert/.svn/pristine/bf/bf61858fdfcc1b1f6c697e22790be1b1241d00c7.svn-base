<?php
include_once(dirname(realpath(__FILE__)).'/init.php');
$query = $_POST['k'];
$res = gomarket_action('v60.GoInstantSearchSuggest', array('SUGGEST_STRING' => $query));
$api_data = $res['DATA'];
$api_hit_items = $res['HIT_ITEMS'];

$data = array();
if ($api_hit_items) {
	$tmp = array();
	$tmp['TYPE'] = 1;
	
	$tmp['DATA'] = array(
		'url' => url2static_url('detail.php?id='. $api_hit_items[0]),
		'iconurl' => $api_hit_items[1],
		'softname' => $api_hit_items[2],
		'score' => $api_hit_items[3],
		'filesize' => formatFileSize($api_hit_items[9],1),
		'official' => $api_hit_items[20],
		'download_url' => '/dl_app.php?n=5&s='. $api_hit_items[0],
	);
	$data[] = $tmp;
}

foreach ($api_data as $val) {
	$tmp = array();
	$tmp['TYPE'] = 2;
	$tmp['DATA'] = $val;
	$data[] = $tmp;
}

$result = array(
	'k' => $_POST['k'],
	't' => $_POST['t'],
	'DATA' => $data,
);
exit(json_encode($result));