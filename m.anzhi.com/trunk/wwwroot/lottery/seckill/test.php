<?php
include_once (dirname(realpath(__FILE__)).'/../../init.php');
$config = load_config('lottery_cache/redis',"lottery");
if ($config) {
	$redis = new GoRedisCacheAdapter($config);
} else {
	$redis = GoCache::getCacheAdapter('redis');
}
$prefix		=	"seckill_test";
$active_id	=	$_GET['aid'] ? $_GET['aid'] : $_POST['aid'];
//ctype_digit	检查时候是只包含数字字符的字符串（0-9）
if(!ctype_digit($active_id)){
	exit;
}
$model = new GoModel();
$sid		=	$_GET['sid'] ? $_GET['sid'] : $_POST['sid'];
$workerType	=	$_GET['workerType'] ? $_GET['workerType'] : $_POST['workerType'];
$position	=	$_GET['position'] ? $_GET['position'] : $_POST['position'];
$position	=	!empty($position) ? explode(',', $position) : '';
$gift_pkg	=	$_GET['gift_pkg'] ? $_GET['gift_pkg'] : $_POST['gift_pkg'];


if($configs['is_test'] == 1 ) {
	$time  = time();
}else {
	$time  = time();
}

test_worker($workerType);

function test_worker( $workerType )
{
	global $prefix;
	global $position;
	global $gift_pkg;
	global $active_id;
	

	
	load_helper('task');
	$task_client = get_task_client();
	if( $workerType == 1 ) {
		//获取用户安智币
		$new_array=array(
				'workerType'=>	1,
				'uid'		=>	'20150327170252AqQWMt03HI'
		);
		$the_award = $task_client->do('anzhi_lottery_money', json_encode($new_array));
	}else if( $workerType == 2 ) {
		//消费安智币
		$new_array=array(
				'workerType'=>	2,
				'uid'		=>	'20150327170252AqQWMt03HI',
				'appkey'	=>	'1392365303Jy1R97taJfdtops8Cxum',
				'orderDes'	=>	'测试活动',
				'payPwd'	=>	'123456',
				'azbAmount'	=>	5,
		);
		$the_award = $task_client->do('anzhi_lottery_money', json_encode($new_array));
	}else if( $workerType == 3 ) {
		//测试礼券
		$new_array=array(
				'workerType'=>	null,
				'uid'		=>	'20150625101643BK636uTvIQ',
				'aid'		=>	$active_id,
				'username'	=>	'zaitest1000006',
				'prefix'	=>	$prefix,
				'position'	=>	$position,
				'gift_pkg'	=>	$gift_pkg,
				'appName'	=>	'测试游戏',
				'activityName' => '测试游戏抽奖活动',
		);
		$the_award = $task_client->do('anzhi_lottery_money', json_encode($new_array));
	}else if( $workerType == 4 ) {
		//测试礼包直接发放
		$new_array=array(
				'workerType'=>	null,
				'uid'		=>	'20150625153820t94NX5799j',
				'aid'		=>	$active_id,
				'username'	=>	'zaitest1000042',
				'prefix'	=>	$prefix,
				'position'	=>	$position,
				'gift_pkg'	=>	$gift_pkg,
				'appName'	=>	'测试游戏',
				'activityName' => '测试游戏抽奖活动',
		);
		$the_award = $task_client->do('anzhi_lottery_money', json_encode($new_array));
	}else if( $workerType == 5 ) {
			$res = get_gift_id($active_id, 'zaitest1000006', $gift_pkg, 'seckill_test_test');
			print_r($res).'\n';die;
	}else {
		die('type参数有误');
	}
	
	print_r(json_decode($the_award, true));
	
}

/**
 * 获取包名对用的礼包id
 * @param int $aid		活动Id
 * @param str $uid		用户Id
 * @param arr $gift_pkg	用户已安装的游戏包名
 * @param str $prefix	活动前缀
 * @return int|boolean
 */
function get_gift_id($aid, $uid, $gift_pkg, $prefix)
{
	global $redis;
	$prize_gift_pkg = $redis->get("{$prefix}:{$aid}_gift_pkg");
	$prize_gift_pkg_conf = get_pk_config();
	if( empty($prize_gift_pkg) ) {
		$prize_gift_pkg = array_keys($prize_gift_pkg_conf);
		$redis -> set("{$prefix}:{$aid}_gift_pkg", $prize_gift_pkg, 10*86400);
	}

	$package = get_gift_pkg($aid, $uid, $gift_pkg, $prefix);
	echo $package.'<br/>';
	$gift_arr = isset($prize_gift_pkg_conf[$package]) ? $prize_gift_pkg_conf[$package] : 0;
	print_r($gift_arr);
	echo '<br/>';
	
	if( !empty($gift_arr) ) {
		$gift_id = get_gift_pkg_id($aid, $uid,$package, $gift_arr, $prefix);
		del_gift_pkg($aid, $uid, $package, $prefix);
		del_gift_pkg_id($aid, $uid, $package, $gift_id, $prefix);
		return $gift_id;
	}else {
		return false;
	}
}



//a.优先抽中已安装游戏的虚拟礼包id；
//b.同一用户不重复中同一款礼包id；
//c.若用户抽奖的次数，超过了虚拟礼包的种类数量，则随机中虚拟礼包id。
function get_gift_pkg_id($aid, $uid, $package, $gift_arr, $prefix)
{
	global $redis;
	$user_gift_id	=	$redis->get("{$prefix}:{$aid}_gift_id:{$package}:".$uid);
	$open_gift_id	=	$gift_arr;
	if(!$user_gift_id) {
		$prize_gift_id	=	$gift_arr;
		$redis -> set("{$prefix}:{$aid}_gift_id:{$package}:".$uid,$prize_gift_id,10*86400);
		$user_gift_id	=	$prize_gift_id;
		$intersection	=	array_intersect($open_gift_id, $prize_gift_id);
	}else {
		$intersection	=	array_intersect($open_gift_id, $user_gift_id);
	}
	if($intersection) {
		//a.优先抽中已安装游戏的虚拟礼包；
		foreach($intersection as $v) {
			return $v;
			exit;
		}
	}else {
		return $user_gift_id[0];
	}
}

//去除已获得的礼包id
function del_gift_pkg_id($aid, $uid, $package, $gift_id, $prefix)
{
	global $redis;
	$user_gift_id	=	$redis->get("{$prefix}:{$aid}_gift_id:{$package}:".$uid);
	$new_gift_id	=	array();
	foreach($user_gift_id as $k => $v) {
		if($v != $gift_id) {
			$new_gift_id[] = $v;
		}
	}
	$redis -> set("{$prefix}:{$aid}_gift_id:{$package}:".$uid, $new_gift_id, 10*86400);
}


function get_pk_config()
{
	return 	array(
			'com.baidu.baidutranslate'		=>	array(1,2,3,4,5),
			'com.yinhan.hunter.anzhi'		=>	array(7,8,9,10,11),
			'com.sina.weibo'				=>	array(12,13,14,15,16),
			'com.playgame.GoodStudent1'		=>	array(17,18,19,20,21),
			'com.baidu.homework'			=>	array(22,23,24,25,26),
			'com.jingdong.app.mall'			=>	array(27,28,29,30,31),
			'colorbox.wordk'				=>	array(32,33,34,35,36),
			'com.hj.nce'					=>	array(37,38,39,40,41),
			'com.ijinshan.kbatterydoctor'	=>	array(42,43,44,45,46),
			'com.youdao.dict'				=>	array(47,48,49,50,51),
			'com.autonavi.minimap'			=>	array(52,53,54,55,56),
			'com.baidu.BaiduMap'			=>	array(57,58,59,60,61),
			'com.taobao.taobao'				=>	array(62,63,64,65,66),
			'com.Actiongames.GhostGun.enwo' =>	array(67,68,69,70,71),
		);
}




