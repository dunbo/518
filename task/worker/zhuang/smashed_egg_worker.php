<?php
/*
 *   砸蛋领取礼券worker
 *	 9月开学领取礼券worker	
 */
include dirname(__FILE__).'/../../init.php';
$config = load_config('lottery_cache/redis',"lottery");
if ($config) {
    $redis = new GoRedisCacheAdapter($config);
} else {
    $redis = GoCache::getCacheAdapter('redis');
}

$model = new GoModel();
$gift_base = array();
ini_set('default_socket_timeout', -1);

load_helper('task');
$worker = get_task_worker();
$worker->addFunction("smashed_egg", "get_award");
while ($worker->work());

function get_award($jobs){
	global $model;
	global $redis;
	$jobs= $jobs->workload();
	$jobs = json_decode($jobs,true);
	print_r($jobs);             
	$prefix = $jobs['prefix'];	
	$uid = $jobs['uid'];	
	$aid = $jobs['aid'];	
	$username= $jobs['username'];
	$position = $jobs['position'];     
	//如果缓存没了 重新生成
	//剩余奖品数量缓存
	$redis->pingConn();
	$key = "{$prefix}:{$aid}_prize_rank:".$position;
	$prize_arr = $redis->get($key);
	if(empty($prize_arr)){
		$option = array(
			'where' => array(
				'level' => $position,
				'aid' => $aid, //通用 活动ID
			),
			'table' => 'valentine_draw_prize',
			'field' => 'id,level,name,num,gift_file'
		);
		$prize_arr= $model->findOne($option,'lottery/lottery');
		$redis->set($key,$prize_arr,1200);
		$redis->set("{$prefix}:{$aid}_prize_num:".$prize_arr['id'],intval($prize_arr['num']),1200);
	}

	// 奖品数量-1
	$now_num = $redis -> setx('incr',"{$prefix}:{$aid}_prize_num:".$prize_arr['id'], -1);
	echo 'now_num:'.$now_num."\n";
	if ($now_num < 0) {
		 $ret_arr = array(
			'code' => 0,
			'msg' => '剩余奖品不足',
		 );
		 return json_encode($ret_arr);		
	}
	//if($jobs['position'] > 11){
		//领取礼券
		$data_arr =  array(
			'uid' => $uid,
			'couponId' => $prize_arr['gift_file'],
			'exchangeTime' =>time(),
			'exchangeNum' => 1,
			'activityName' => '9月开学签到',
			'activityId' => $aid,
		);
		$js_data = json_encode($data_arr);
		$data = array(
			'serviceId' => '',
			'data' => $js_data
		);
		$res = get_data($data);
		if($res['code'] != '200'){
			$ret_arr = array(
				'code' => 0,
				'msg' => '礼券领取失败',
			);
			$redis -> setx('incr',"{$prefix}:{$aid}_prize_num:".$prize_arr['id'], 1);
			return json_encode($ret_arr);	
		}		
	//}
	//减少库中实际数量
	$param =array(
		'type' => 2,
		'uid' => $uid,
		'aid' => $aid,
		'pid' => $prize_arr['id'],
		'username' => $username,
		'prizename' => $prize_arr['name'],
		'table' => 'valentine_draw_prize',
		'table_award' => 'valentine_draw_award',
	);
	$task_client = get_task_client();
	$task_client->doBackground('recharge_top_db',json_encode($param));
	//if($jobs['position'] > 11){
		$ret_arr = array(
			'code' => 1,
			'msg' => $res['msg'],
			'prizename' => $prize_arr['name'],
			'pid' => $prize_arr['id'],
			'residueNum' => $res['residueNum']
		);
		return json_encode($ret_arr);	
	// }else{
		// $resarr = array(
			// 'code' => 1,
			// 'prizename' => $prize_arr['name'],
			// 'pid' => $prize_arr['id'],
			// 'msg' => '领取成功'
		// );
		// return json_encode($resarr);
	// }
}

function get_data($vals){		
	$host = 'http://pay.anzhi.com/pay/internal/coupon/exchange';//线上地址
	//$host = 'http://dev2.user.anzhi.com:9021/pay/internal/coupon/exchange';//测试地址
	$vals = http_build_query($vals);
	$res = httpGetInfo($host, $vals); 
	$last = json_decode($res,true);
	return $last;
}
function httpGetInfo($url, $vals) {
	$res = curl_init();
	curl_setopt($res, CURLOPT_URL, $url);
	curl_setopt($res, CURLOPT_POST, true);
	curl_setopt($res, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($res, CURLOPT_POSTFIELDS, $vals);
	$result = curl_exec($res);
	$http_code = curl_getinfo($res, CURLINFO_HTTP_CODE);
	$errno = curl_errno($res);
	$error = curl_error($res);
	curl_close($res);
	writelog('smashed_egg_http.log',"{$http_code}|{$errno}|{$error}\n" .$url. print_r($vals, true) . "\n" . print_r($result, true) . "\n\n");
	return $result;
}

//日志
function writelog($filename,$msg){
	$now = time();
	$path = "/data/att/permanent_log/admin_cron_log/".date("Y-m-d", $now);
	if(!file_exists($path)){
		mkdir($path, 0755, true);
	}	
	$path_log = $path."/".$filename;
	$msg = date('Y-m-d H:i:s', $now). " {$msg}\n";
	file_put_contents($path_log, $msg, FILE_APPEND);
}
