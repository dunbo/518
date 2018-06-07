<?php




ini_set("display_errors", "On");
error_reporting(E_ALL | E_STRICT);
session_start();
//phpinfo();

//exit;
include_once (dirname(dirname(__FILE__)).'/init.php');
//$tplObj->out['type'] = $_GET['type'];



header('Content-type: application/json');
$log_file = "weixing_get_gift.json";

//var_dump($_SESSION);
$is_login = false;
if(!isset($_SESSION['USER_UID']) || $_SESSION['USER_ID'] == 13176){
	if (!empty($_COOKIE['_AZ_COOKIE_'])) {
		$ucenter = new GoUcenter('weixin');
		//var_dump($ucenter);
		$cookie_data = $ucenter->parse_uc_cookie();
		//var_dump($cookie_data);
		//var_dump($_SESSION);
		//var_dump($ucenter);
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
//var_dump($_SESSION);


$item = (object) array('is_login' => $is_login);


if($_GET['act'] == 'detail') {
	
	$id = intval($_GET['id']);
	$data = gomarket_action('gift.GoGetGiftDetailById', array(
		'GIFT_ID' => $id,
		'FROM' => 16,
	));

	$item->data = isset($data['DATA']) ? $data['DATA'] : array();
	/*
	$data = gomarket_action('gift.GoGetGiftMore', array(
		'FROM' => 16,
		'PACKAGE_NAME' => $item->data[3] 
	));
	*/
	//$data = $data['OTHER_GIFTS'];


	//$item->more = isset($data['DATA']) ? $data['DATA'] : array();
	$item->more = $data['OTHER_GIFTS'];;
	
	permanentlog($log_file, json_encode(array('k' => 'detail','t' => time(),'uid' => $_SESSION['USER_UID'])));
} else if($_GET['act'] == 'my') {
	$data = gomarket_action('gift.GoGetMyGifts', array('VR' => 4,'FROM' => 16));
	$item->data = isset($data['DATA_A']) ? $data['DATA_A'] : array();
} else if($_GET['act'] == 'get') {
	$id = intval($_GET['id']);
	
	for($i = 0;$i < 10;++$i) {
		$data = gomarket_action('gift.GoGetGift', array(
			'GIFT_ID' => $id,
			'FROM' => 16,
		));
		if($data['IS_SUCESSFUL'] != 2) {
			break;
		}
		usleep(100000);
	}

	
	$item->data = $data;
	
	if($data['IS_SUCESSFUL'] == 1) {
		permanentlog($log_file, json_encode(array('k' => 'get_code','t' => time(),'uid' => $_SESSION['USER_UID'],'ip' => $_SERVER['SERVER_ADDR'],'code' => $data['GAME_KEY'])));
	}

} else if($_GET['act'] == 'new') {
	permanentlog($log_file, json_encode(array('k' => 'list','t' => time(),'uid' => $_SESSION['USER_UID'],'ip' => $_SERVER['SERVER_ADDR'])));
	$index = isset($_GET['index']) ? intval($_GET['index']) : 0;
	$data = gomarket_action('gift.GoGetGiftList', array(
		'VR' => 3,
		'TYPE' => 20,
		'FROM' => 16,
		'LIST_INDEX_START' => $index,
		'LIST_INDEX_SIZE' => 20, 
	));
	$item->data = isset($data['DATA']) ? $data['DATA'] : array();
} else {
	permanentlog($log_file, json_encode(array('k' => 'list','t' => time(),'uid' => $_SESSION['USER_UID'],'ip' => $_SERVER['SERVER_ADDR'])));
	
	
	$index = isset($_GET['index']) ? intval($_GET['index']) : 0;
	$pkg = isset($_GET['pkg']) ? $_GET['pkg'] : null;
	
	if(empty($pkg)) {
		$data = gomarket_action('gift.GoGetGiftList', array(
			'VR' => 3,
			//'TYPE' => 6,
			'FROM' => 16,
			'LIST_INDEX_START' => $index,
			'LIST_INDEX_SIZE' => 20, 
		));
	} else {
		$data = gomarket_action('gift.GoGetGiftMore', array(
			'PACKAGE_NAME' => $pkg,
			'FROM' => 16

		));
	}
	
	
	
	$item->data = isset($data['DATA']) ? $data['DATA'] : array();
	
} 

exit(json_encode($item));

