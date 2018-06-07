<?php 
#init 接入
include_once (dirname(realpath(__FILE__)).'/init.php');
#type: gift_list my_gift gift_detail game_index giftlist_game
#type：newservice

$type = $_GET['type'];
$type_array = array('gift_list','my_gift','gift_detail','game_index','giftlist_game','newservice');
$offset = $_GET['offset'] ? $_GET['offset'] : 0;
$limit = $_GET['limit'] ? $_GET['limit'] : 10;
$param = array();
$param['FROM'] = 1;
switch($type){
	#礼包列表
	case 'gift_list':	
		$param['GIFT_TYPE'] = 2;
		$param['LIST_INDEX_START'] =  $offset;	
		$param['LIST_INDEX_SIZE'] =  $limit;
		$param['GET_COUNT'] = true;
		$param['VR'] = 2;
		if ($_GET['cache']) {
			$cache_key = 'CACHE_GIFT:' . md5(json_encode($param));
			$redis = new GoRedisCacheAdapter();
			$result = $redis->get($cache_key);
			if (empty($result)) {
				$result = gomarket_action('gift.GoGetGiftList',$param);
				$redis->set($cache_key, $result, 7200);
			}
		} else {
			$result = gomarket_action('gift.GoGetGiftList',$param);
		}
	break;
	#我的礼包
	case 'my_gift':
		$param['UID'] = $_GET['uid'];
		$param['LIST_INDEX_START'] =  $offset;	
		$param['LIST_INDEX_SIZE'] =  $limit;
		if($_GET['sk']) $param['SEARCH_KEY'] = $_GET['sk'];
		$param['GET_COUNT'] = true;
		$result = gomarket_action('gift.GoGetMyGifts',$param);
	break;
	#礼包索引列表
	case 'game_index':
		//gomarket_action(); #未开发
	break;
	#指定游戏礼包列表
	case 'giftlist_game':
		$param['PACKAGE_NAME'] = $_GET['pkg'];
		$param['LIST_INDEX_START'] =  $offset;	
		$param['LIST_INDEX_SIZE'] =  $limit;
		$param['VR'] = 2;
		$result =  gomarket_action('gift.GoGetGiftMore',$param);
	break;
	#新服列表
	case 'newservice':
		$param['VR'] = 1;
		$param['LIST_INDEX_START'] =  $offset;	
		$param['LIST_INDEX_SIZE'] =  $limit - 1;
		$cache_key = 'CACHE_NEWSERVER:' . md5(json_encode($param));
		$redis = new GoRedisCacheAdapter();
		$result = $redis->get($cache_key);
		if (empty($result)) {
			$result = gomarket_action('gift.GoGetBbsNewServicesList',$param);
			$data = $result['DATA'];
			$data = array_slice($data, 0, $limit-1);
			$result = array('DATA'=>$data);
			$redis->set($cache_key, $result, 7200);
		}
	break;
	#礼包详情
	case 'gift_detail':
		$param['GIFT_ID'] = $_GET['giftid'];
		$param['VR'] = 2;
		$forumurl = isset($_GET['forumurl']) ? $_GET['forumurl'] : '';
		$result = gomarket_action('gift.GoGetGiftDetailById',$param);
		$result['FORUMURL'] = $forumurl;
	break;
	case 'gift_index':
		if ($_GET['index'] == '0-9') $_GET['index'] = '0123456789';
		$param['INDEX'] = $_GET['index'];
		$param['VR'] = 1;
		$param['FROM'] = 1;
        $result = gomarket_action('gift.GoGetGameByIndex',$param);
		$result['INDEX'] = $param['INDEX'];
	break;
	case 'detailpkg':
		$param['PACKAGE_NAME'] = $_GET['pkg'];
		$param['VR'] = 8;
		$result = gomarket_action('soft.GoGetSoftDetailPackage',$param);
	break;
	//领取礼包
	case 'getgift':
		$con = (int) $_GET['con'];
		$giftid = (int) $_GET['giftid'];
		$request = $_GET['request'] == 1 ? 1 : 0;
		$param['GIFT_ID'] = $giftid;
		$param['IS_FIRST_REQUEST'] = $request;
		$param['VR'] = 2;
		$result = gomarket_action('gift.GoGetGift',$param);
		$result['GIFTID'] = $giftid;
		$result['CON'] = $con;
	break;
}
$callback = $_GET['callback'];
if ($_GET['action']) $result['DATATYPE'] = $_GET['action'];
//file_put_contents('/tmp/lipeng2014924.log',var_export($result,true),FILE_APPEND);
$json = json_encode($result);
echo "{$callback}({$json})";
