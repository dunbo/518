<?php
include_once(dirname(realpath(__FILE__)) . '/init.php');
date_default_timezone_set('Asia/Shanghai');


/** 正式地址 */
$url = 'http://appi.12321.cn:8080/interfaces/report.api';

/** 测试地址 */
//$url = 'http://appi.12321.cn:8080/interfaces/report.api/test';
/**
 * 举报
 */
function report_action() {

	$static_data = load_config('static_data');

	$map_category = array(
		'39' => 1,
		'49' => 2,
		'52' => 3,
		'43' => 4,
		'53' => 5,
		'50' => 6,
		'51' => 3,
		'55' => 5,
		'47' => 6,
		'40' => 1,
		'42' => 1,
		'41' => 4,
		'45' => 4,
		'46' => 4,
		'44' => 1,
		'48' => 6,
		'54' => 7,
		'56' => 8,
		'57' => 8,
		'14' => 8,
		'15' => 8,
		'16' => 8,
		'19' => 8,
		'20' => 8,
		'21' => 8,
	);
	$post_array = array();

	$post_array['account'] = 'APP.B86244F';
	
	$model = new GoModel();
	
	$option1 = array(
	
		'where' => array(
			'softid' => $_GET['packageurl'],
			'status' => 1
		),
		'table' => 'sj_soft'
		
	);
	$file_info = $model->findOne($option1);
	
	$option2 = array(
	
		'where' => array(
			'softid' => $_GET['packageurl'],
		),
		'field' => 'md5_file, url',
		'table' => 'sj_soft_file'
		
	);
	
	$file_url = $model->findOne($option2);
	
	$post_array['packageurl'] = getApkHost($file_info) . $file_url['url'];

	$post_array['packagename'] = $file_info['softname'];
	
	if (!$file_url['md5_file']) {
		$file_url['md5_file'] = md5_file($static_data . $file_url['url']);	
	}
	$post_array['filemd5'] = $file_url['md5_file'];

	$post_array['vcode'] = $file_info['version_code'];

	$post_array['packagetype'] = $_GET['packagetype'];

	if (!$category_id = $map_category[substr($file_info['category_id'], 1, -1)]) {
		$category_id = 0;		
	}

	$post_array['appchannel'] = $category_id;
	
	$post_array['orig'] = 2;
	
	$post_array['clientip'] = $_SERVER['REMOTE_ADDR'];
	
	$post_array['descr'] = $category_id;

	$post_array['reporttime'] = date('Y-m-d H:i:s', time());
	
	$post_json = json_encode($post_array);
	return $post_json;
}

function curl_post($post_json) {

	global $url;

	$http = curl_init();					
	curl_setopt($http, CURLOPT_URL, $url);
	curl_setopt($http, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($http, CURLOPT_POST, 1);
	
	curl_setopt($http, CURLOPT_POSTFIELDS, $post_json);
	
	return curl_exec($http);
	
}

$post_json = report_action();

$result = curl_post($post_json);
echo 1;
$dir = '/data/att/permanent_log/report_app/';

if (!is_dir($dir)) {
	mkdir($dir, 0777, true);
}

file_put_contents($dir . date('Y-m-d', time()) . '.log', date('H:i:s', time()) . $post_json . $result . "\n", FILE_APPEND);

?>
