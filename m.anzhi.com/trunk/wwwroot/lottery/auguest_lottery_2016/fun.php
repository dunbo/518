<?php
include_once (dirname(realpath(__FILE__)).'/../../init.php');
$config = load_config('lottery_cache/redis',"lottery");
if ($config) {
	$redis = new GoRedisCacheAdapter($config);
} else {
	$redis = GoCache::getCacheAdapter('redis');
}
$model = new GoModel();
$prefix = "auguest_lottery_2016";
$active_id = $_GET['aid'] ? $_GET['aid'] : $_POST['aid'];
$sid = $_GET['sid'] ? $_GET['sid'] : $_POST['sid'];
//if($_GET['stop'] != 1 && !$_GET['types']){
if($_GET['stop'] != 1){
	$res = activity_is_stop($active_id);
	if(!$res){
		$url = $center_url."http://promotion.anzhi.com/lottery/{$prefix}/{$prefix}_index.php?stop=1&aid=".$active_id."&sid=".$sid; //todo
		//$url = $center_url."http://m.test.anzhi.com/lottery/{$prefix}/index.php?stop=1&aid=".$active_id."&sid=".$sid;
		header("Location: {$url}");
	}
}
$time = time();
//@剩余抽奖次数
function get_lottery_num($uid){
	global $model;
	global $redis;		
	global $active_id;		
	global $prefix;		
	$lottery_num_key = "{$prefix}:".$active_id.":res_lottery_num:".$uid;
	$lottery_num = $redis->get($lottery_num_key);
	if($lottery_num === null ){
		$option = array(
			'where' => array(
				'uid' => $uid,
				'aid' => $active_id
			),
			'table' => 'recharge_top_order',
			'field' => 'sum(lottery_num) as lottery_nums,sum(deduction_num) as deduction_nums',
		);
		$rest_list = $model->findOne($option,'lottery/lottery');	
		$lottery_num = intval($rest_list['lottery_nums'])-intval($rest_list['deduction_nums']);
		$redis->set($lottery_num_key,intval($lottery_num),86400*5);
	}
	return $lottery_num;			
}
//获取用户抽奖的有效的用户id
function get_user_id($uid){
	global $model;	
	global $active_id;		
	$option = array(
		'where' => array(
			'uid' => $uid,
			'aid' => $active_id,
			'deduction_num' => 0,
			'money' => array('exp',">=6")
		),
		'table' => 'recharge_top_order',
		'field' => 'id,money',
		'order' => 'create_tm asc',
	);
	$rest = $model->findOne($option,'lottery/lottery');	
	return $rest;		
}

function save_deduction_num($id){
	global $model;	
	global $active_id;			
	global $time;			
	$new_data = array(
		'update_tm' => $time,
		'deduction_num' => 1,
		'__user_table' => 'recharge_top_order'
	);	
	$where = array('id' => $id);
	$ret =  $model->update($where, $new_data,'lottery/lottery');	
}

function get_couponid(){
	global $model;	
	global $active_id;			
	$option = array(
		'where' => array(
			'aid' => $active_id,
			'level' => 4
		),
		'table' => 'valentine_draw_prize',
		'field' => 'id,gift_file',
		'cache_time' => 86400	
	);
	$rest = $model->findOne($option,'lottery/lottery');		
	return $rest;	
}
//发放礼券
function grant_coupon($uid){
	global $active_id;			
	$prize_arr = get_couponid();	
	$data_arr =  array(
		'uid' => $uid,
		'couponId' => $prize_arr['gift_file'],
		'exchangeTime' =>time(),
		'exchangeNum' => 1,
		'activityName' => '充值抽大奖100%中奖',
		'activityId' => $active_id,
	);
	$js_data = json_encode($data_arr);
	$data = array(
		'serviceId' => '',
		'data' => $js_data
	);
	$res = get_data($data);
	if($res['code'] != '200'){
		return false;
	}else{
		return true;
	}
}
function get_data($vals){
	if($_SERVER['SERVER_ADDR'] == '118.26.203.23' ){
		$url = 'http://dev2.user.anzhi.com:9021/pay/internal/coupon/exchange';//测试地址		
	}else{
		$url = 'http://pay.anzhi.com/pay/internal/coupon/exchange';//线上地址
	}	
    $vals = http_build_query($vals);
    $res = httpGetInfo($url,'', $vals,"coupon.log");
    $last = json_decode($res,true);
    //var_dump($last);
    return $last;
}
