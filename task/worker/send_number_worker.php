<?php
include dirname(__FILE__).'/../init.php';
ini_set('display_errors', true);
error_reporting(E_ALL);
define('FROM', 2);
$db = 'sendnum';
$redis = new GoRedisCacheAdapter(load_config('sendNum/redis', 'sendNum'));
load_helper('task');
$worker = get_task_worker('sendNum');
//$task_client = get_task_client('sendNum');
$worker->addFunction("send_number", "send_number_func");

while ($worker->work());
function send_number_func($jobs) {
	global $redis;
	//global $task_client;
	if ( !($p = unserialize($jobs->workload())) ) {
		return False;
	}
	$start = microtime_float();
	var_dump($p);
	//传参
	$uid = $p['uid'];//论坛uid或者是用户唯一码sn
	$active_id = $p['active_id'];//活动id
	$ip = $p['ip'];//参与者的ip
	$now_time = $p['take_tm'];//参加的时间
	$date = date('Y-m-d',$now_time);//提交的日期
	$num_key = "active{$active_id}_uid{$uid}_num";//结果缓存key
	//$prize_key = "prize_list_active{$active_id}";//活动的奖品池
	$prize_key = "NUMBER_LIST:{$active_id}";//活动的奖品池
	$order_key = "order_list_active{$active_id}_date{$date}";//预约列表
	$sended_key = "ACTIVE:{$active_id}:FROM:" . FROM . ":SENDED_NUMBER";
	$live_time = 600;//存活时间

	$redis->pingConn();

	$tmp_num = $redis->get($num_key);
	if (strlen($tmp_num) >= 2)
		return;
	//活动信息
	$active_info = get_active_info($active_id);
	if (intval($active_info['active_from']) & FROM != FROM) {
		return;
	}
	if($active_info['status'] == 0){//已停用
		$redis->set($num_key, 3, $live_time);
		return;
	}
	if($active_info['start_tm'] > $now_time){ //未开 始
		$redis->set($num_key, 1, $live_time);
		return;
	}elseif($active_info['end_tm'] < $now_time){//已结束
		$redis->set($num_key, 2, $live_time);
		return;
	}
	$sended_num = $redis->getx("HLEN", $sended_key);
	//	var_dump($sended_num, $active_info['market_conf_cnt']);
	if ($sended_num >= $active_info['game_conf_cnt']) {
		$redis->set($error_key, 5, $live_time);
		return;
	}

	//根据活动的限定条件判断是否能取到奖品
	$type = $active_info['active_type'];
	$error_no = 5;
	$key = "ACTIVE:{$active_id}:DATE:{$date}:FROM:" . FROM . ":DAY_CNT"; //按天限量
	if ($active_info['conf_cnt'] > 0) $error_no = 6;

	$v = $redis->get($key);
	if ($v === false) {
		$v = get_active_usedcnt($active_id,$type,$date,$ip,$uid);
		$redis->set($key,$v,88400);
	}
	if ($v >= $active_info['conf_cnt'] && $active_info['conf_cnt'] > 0 && $type != 2) {
		$redis->set($num_key, $error_no, $live_time);
		return;
	}
	$num_t = $redis->rpop($prize_key);
	$num = json_decode($num_t, true);
	if ($num_t) {
		$v = $v+1;
		$redis->set($key,$v,88400);
		$order_list = array(
			array(
				"active_num"=>$num,
				"get_tm"=>$now_time,
				"uid"=>$uid,
				"ip"=>$ip
			)
		);
		$redis->setlist($order_key, $order_list);
		$redis->set($num_key, $num, $live_time);
		$redis->sethash($sended_key, array($num => 1));
		$data = $p;
		$data['active_num'] = $num;
		$data['status'] = 1;
		$data['from'] = FROM;
		$data['user_type'] = 2;
		//$task_client->doBackground('update_sendnum_db', json_encode($data));
	} else {
		$redis->set($num_key, $error_no, $live_time);
		return;
	}
}

//获取活动信息
function get_active_info($active_id){
	global $db;
	$model = new GoModel();
	$option = array(
		'table' => 'sendnum_active',
		'where' => array(
			'id' => $active_id,
		),
		'cache_time' => '600'
	);
	$result = $model -> findOne($option,$db);
	return $result;
}

function get_active_usedcnt($active_id, $date, $ip, $uid) {
	global $db;
	$option = array(
		'table' => 'sendnum_number_' . $active_id,
		'where' => array(
			'active_id' => $active_id,
			'from' => FROM,
			'status' => 1,
		),
		'field' => 'count(id) as cnt',
	);
	$option['where']['take_tm'] = array('exp','>='.strtotime($date).' and take_tm <= '.strtotime($date.' 23:59:59'));
	$model = new GoModel();
	$result = $model -> findOne($option,$db);
	$cnt = $result['cnt'];
	return $cnt;
}
