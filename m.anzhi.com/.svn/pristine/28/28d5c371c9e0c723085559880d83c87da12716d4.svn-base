<?php

ini_set("display_errors", "On");
error_reporting(E_ALL | E_STRICT);
session_start();
include_once (dirname(dirname(__FILE__)).'/init.php');
//$tplObj->out['type'] = $_GET['type'];

// header('content-type:text/html;charset=utf-8');
header('Content-type: application/json');
$log_file = "weixing_get_strategy.json";
//var_dump($_SESSION);
$is_login = false;
if(!isset($_SESSION['USER_ID']) || $_SESSION['USER_ID'] == 13176){
	if (!empty($_COOKIE['_AZ_COOKIE_'])) {
		$ucenter = new GoUcenter('weixin');
		//var_dump($ucenter);
		$cookie_data = $ucenter->parse_uc_cookie();
		if ($_SESSION['USER_ID'] != $cookie_data['pid']) {
			$user = $ucenter->token_userinfo();
			//var_dump($user);
			if (isset($user['USER_ID']) && $user['USER_ID']!=13176 && isset($user['USER_NAME'])) {
				$_SESSION['USER_ID'] = $user['USER_ID'];
				$_SESSION['USER_UID'] = $user['USER_UID'];
				$_SESSION['USER_NAME'] = $user['USER_NAME'];
				$is_login = true;
			} else {
				//setcookie('_AZ_COOKIE_',null);
				setcookie ("_AZ_COOKIE_", "", time() - 3600,'/','.anzhi.com');
			}
		}
	}
} else {
	$is_login = true;
}	

$item = (object) array('is_login' => $is_login);

if($_GET['act'] == 'all'){
	permanentlog($log_file, json_encode(array('k' => 'strategy_list','t' => time(),'uid' => $_SESSION['USER_UID'],'ip' => $_SERVER['SERVER_ADDR'])));
	$index = isset($_GET['index']) ? intval($_GET['index']) : 0;
	$data = gomarket_action('ucsdk.GoGetRecommendNewsList', array(
				'MORE_TYPE' => 1,
				'LIST_INDEX_START' => $index,
				'LIST_INDEX_SIZE' => 20,
				'VR' =>0
		));
	$item->data = isset($data['DATA']) ? $data['DATA'] : array();
}else if($_GET['act'] == 'query'){
	permanentlog($log_file, json_encode(array('k' => 'query_strategy','t' => time(),'uid' => $_SESSION['USER_UID'],'ip' => $_SERVER['SERVER_ADDR'])));
	$index = isset($_GET['index']) ? intval($_GET['index']) : 0;
	$title = isset($_GET['title']) ? $_GET['title'] : null;
	$data = gomarket_action('v62.SearchGameNews', array(	
		"SEARCH_QUERY"=>$title,
		'LIST_INDEX_START' => $index,
		'LIST_INDEX_SIZE' => 20,
	));
	$item->data = isset($data) ? $data : array();
}
exit(json_encode($item));

