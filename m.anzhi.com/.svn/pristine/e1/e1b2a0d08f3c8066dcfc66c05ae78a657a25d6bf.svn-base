<?php
include_once (dirname(realpath(__FILE__)).'/../init.php');
$config = load_config('lottery_cache/redis',"lottery");
if ($config) {
	$redis = new GoRedisCacheAdapter($config);
} else {
	$redis = GoCache::getCacheAdapter('redis');
}
$active_id =263;//278 518test
$model = new GoModel();
//没有session 跳转到首页
session_begin();
$sid = $_GET['sid']?$_GET['sid']:$_POST['sid'];
if(isset($_SESSION['USER_UID'])){//已登录
	$uid = $_SESSION['USER_UID'];    	
}else{//未登录 跳转到首页
	header("Location: http://promotion.anzhi.com/lottery/integral.php?sid={$sid}");        
}
if($_POST){
	$userinfo = $redis->get('integral_userinfo'.$uid);			
	$exchange_num = $redis->get('integral_exchange_num'.$uid);
	if($exchange_num <= 0){
		$redis->set('integral_exchange_num'.$uid,0,86400);
		exit(json_encode(array('code'=>0,'msg'=>'您今天可用兑换次数已经用完')));
	}
	$rest_integral = $redis->get('rest_integral'.$uid);
	if($rest_integral < 20){
		exit(json_encode(array('code'=>0,'msg'=>'您当前可用的积分不足，请充值获取积分')));
	}
	$pkg = $_POST['pkg'];
	$gift_pkg = $redis -> getlist("integral_gift:{$pkg}");
	if(!$gift_pkg){
		load_gift($pkg);
		exit(json_encode(array('code'=>0,'msg'=>'包名'.$pkg.'礼包已被领完')));
	}
	$ret = get_gift_number($uid,$pkg);
	if($ret['code'] == 0){
		load_gift($pkg);
		exit(json_encode(array('code'=>0,'msg'=>$ret['msg'])));		
	}
	//兑换成功日志
	$log_data = array(
			"imsi" => $_SESSION['USER_IMSI'],
			"device_id" => $_SESSION['DEVICEID'],
			"activity_id" => $active_id,
			"ip" => $_SERVER['REMOTE_ADDR'],
			"sid" => $sid,
			"time" => time(),
			"award_level" => 0,//pid
			"user" => $_SESSION['USER_NAME'],
			"name" => $userinfo['contact_name'],
			"telphone" => $userinfo['phone'],
			"address" => $userinfo['address'],
			"package" => $pkg,
			"gift" =>  $ret['gift_number'],
			"users" => '',
			'uid'=>$uid,
			"lottery_type" => 2,//1实物2礼包
			"award_name" => '游戏礼包',
			'key' => 'integral_success'
	);
	permanentlog('activity_'.$active_id.'.log', json_encode($log_data));		
	exit(json_encode(array('code'=>1,'gift_number'=>$ret['gift_number'])));
}else{

	$tplObj -> out['gift_succ'] = 1;
	$tplObj -> out['gift_arr'] = get_gift();
	$tplObj -> out['sid'] = $_GET['sid'];
	$tplObj -> out['static_url'] = $configs['static_url'];
	$tplObj -> display('lottery/integral_gift.html');
}

function get_gift($del){
	global $model;
	global $redis;	
	//$redis->delete('integral_gift_pkg');	
	$prize_gift_pkg = $redis->get('integral_gift_pkg');
	if(!$prize_gift_pkg){
		$limit = 1000;
		$start = 0;
		$prize_gift_pkg = array();		
		for($start=0;;$start++){
			$option = array(
				'table' => 'integral_kind_gift',
				'where' => array( 'status' => 0 ),
				'limit' => $limit,
				'offset' => $start*$limit,
			);
			$list = $model->findAll($option,'lottery/lottery');	
			//echo $model->getSql()."\n";
			if(!$list) break;
			$prize_gift = array();
			foreach($list as $k => $v){
				$prize_gift_pkg[$v['package']] = $v['softname'];
				$prize_gift[$v['package']][$v['id']] = $v;				
			}
			$redis->set('integral_gift_pkg',$prize_gift_pkg,86400*10);
			foreach($prize_gift as $k => $v){
				$redis->setlist("integral_gift:{$k}",$v,86400*10);
			}
		}
	}	
	return $prize_gift_pkg;		
}

function load_gift($pkg){
	global $redis;	
	$prize_gift_pkg = $redis->get('integral_gift_pkg');
	unset($prize_gift_pkg[$pkg]);
	$redis->set('integral_gift_pkg',$prize_gift_pkg,86400*10);
}

function get_gift_number($uid,$pkg){
	load_helper('task');
	$task_client = get_task_client();
	$new_array = array();
	$new_array['uid'] =$uid;
	$new_array['package'] =$pkg;
	$new_array['username'] = $_SESSION['USER_NAME'];
	$new_array['type'] = 2;
	$the_award = $task_client->do('integral_work', json_encode($new_array));
	$lottery_rs = json_decode($the_award,true);	
	return $lottery_rs;
}