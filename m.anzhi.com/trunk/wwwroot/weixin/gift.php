<?php

ini_set("display_errors", "On");
error_reporting(E_ALL | E_STRICT);
session_start();

include_once (dirname(dirname(__FILE__)).'/init.php');
//$tplObj->out['type'] = $_GET['type'];


header('Content-type: application/json');
$log_file = "weixing_get_gift.json";
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
	permanentlog($log_file, json_encode(array('k' => 'game_list','t' => time(),'uid' => $_SESSION['USER_UID'],'ip' => $_SERVER['SERVER_ADDR'])));
	$index = isset($_GET['index']) ? intval($_GET['index']) : 0;
	$data = gomarket_action('v62.GiftSoft', array(
		'VR' => 1,
		// 'TYPE' => 6,
		'FROM' => 16,
		'LIST_INDEX_START' => $index,
		'LIST_INDEX_SIZE' => 20, 
	));
	// echo "<pre>";var_dump($data);die;
	$item->data = isset($data['DATA']) ? $data['DATA'] : array();
}else if($_GET['act'] == 'query'){
	permanentlog($log_file, json_encode(array('k' => 'query_game','t' => time(),'uid' => $_SESSION['USER_UID'],'ip' => $_SERVER['SERVER_ADDR'])));
	$index = isset($_GET['index']) ? intval($_GET['index']) : 0;
	if($_GET['biaoshi']==1){
		$softname = isset($_GET['softname']) ? base64_decode($_GET['softname']): null;
		$softname_base=$_GET['softname'];
	}else{
		$softname = isset($_GET['softname']) ? $_GET['softname'] : null;
		$softname_base=base64_encode($softname);
	}
	$data = gomarket_action('v62.SearchGift', array(
		'VR' => 4,
		// 'TYPE' => 6,
		'FROM' => 16,
		"SEARCH_QUERY"=>$softname,
		'LIST_INDEX_START' => $index,
		'LIST_INDEX_SIZE' => 20
	));
	$item->data = isset($data) ? $data : array();
	$item->softname_base = $softname_base;
	$item->softname = $softname;
}
// var_dump($item);
exit(json_encode($item));

