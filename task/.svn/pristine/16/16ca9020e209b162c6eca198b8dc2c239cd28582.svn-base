<?php
/*
 *   发放奖品worker
 *   
 */
include dirname(__FILE__).'/../../init.php';
$config = load_config('lottery_cache/redis',"lottery");
if ($config) {
    $redis = new GoRedisCacheAdapter($config);
} else {
    $redis = GoCache::getCacheAdapter('redis');
}

$model = new GoModel();
ini_set('default_socket_timeout', -1);

load_helper('task');
$worker = get_task_worker();
$worker->addFunction("lssue_prize", "get_award");
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
	$activityName = $jobs['activityName'];
	$table = $jobs['table'];
	$table_award = $jobs['table_award'];	
	//如果缓存没了 重新生成
	//剩余奖品数量缓存
	$redis->pingConn();
	$key = "{$prefix}:{$aid}_lssue_prize_rank:".$position;
	$prize_arr = $redis->get($key);
	$prize_num_key = "{$prefix}:{$aid}_lssue_prize_num:".$position;
	$prize_num = $redis->get($prize_num_key);
	if(empty($prize_arr) || empty($prize_num)){
		$option = array(
			'where' => array(
				'level'	=>	$position,
				'aid'	=>	$aid, //通用 活动ID
				'status' =>1
			),
			'table' => $table,
		);
		$prize_arr= $model->findOne($option,'lottery/lottery');
		$redis->set($key,$prize_arr,1200);
		$redis->set($prize_num_key,intval($prize_arr['num']),1200);
	}
	// 奖品数量-1
	$now_num = $redis -> setx('incr',$prize_num_key, -1);
	echo 'now_num:'.$now_num."\n";
	if ($now_num < 0) {
			$ret_arr = array(
				'code'	=>	0,
				'msg'	=>	'剩余奖品不足',
			);
		return json_encode($ret_arr);		
	}
	if(($prize_arr['type'] == 4 || $prize_arr['type'] == 5) && !$prize_arr['gift_file']){
		$resarr = array(
			'code' => 0,
			'pid' => $prize_arr['id'],
			'prize_rank' => $prize_arr['level'],
			'prizename' => $prize_arr['name'],
		);		
		if($prize_arr['type'] == 4){
			$resarr['msg'] =  "礼卷id无效";
			return json_encode($resarr);
		}else if($prize_arr['type'] == 5){
			$resarr['msg'] =  "礼包id无效";
			return json_encode($resarr);
		}
	}
	if( $prize_arr['type'] == 4 ) {
		//礼券			
		$ret = grant_coupon($aid,$uid,$prize_arr['gift_file'],$activityName);
		if(!$ret){
			$resarr = array(
				'code' => 0,
				'pid' => $prize_arr['id'],
				'prize_rank' => $prize_arr['level'],
				'prizename' => $prize_arr['name'],
				'msg' => "礼卷发放失败",
			);
			return json_encode($resarr);
		}
	}elseif ( $prize_arr['type'] == 5 ) {
		//礼包（直接发放）					
		$ret = grant_gift($aid,$uid,$prize_arr['gift_file'],$activityName);
		if(!$ret){
			$resarr = array(
				'code' => 0,
				'pid' => $prize_arr['id'],
				'prize_rank' => $prize_arr['level'],
				'prizename' => $prize_arr['name'],
				'msg' => "礼包发放失败",
			);
			return json_encode($resarr);
		}	
	}
	//减少库中实际数量
	$param =array(
		'uid'	=>	$uid,
		'aid'	=>	$aid,
		'pid'	=>	$prize_arr['id'],
		'username'	=>	$username,
		'prizename' => $prize_arr['type'] == 5 ? $ret['giftSoftName'].":".$ret['giftCode'] : $prize_arr['name'],
		'table' => $table,
		'table_award' => $table_award,
		'ext' => $prize_arr['type'] == 4 || $prize_arr['type'] == 5 ? $ret['data'] : '',
	);
	add_prize_db($param);
	
	$resarr = array(
		'code' => 1,
		'pid' => $prize_arr['id'],
		'type' => $prize_arr['type'],
		'prize_rank' => $prize_arr['level'],
		'prizename' => 	$prize_arr['name'],
		'package' => $prize_arr['type'] == 5 ? $ret['giftSoftPname'] : '',
		'softname' => $prize_arr['type'] == 5 ? $ret['giftSoftName'] : '',
		'gift_number' => $prize_arr['type'] == 5 ? $ret['giftCode'] : '',
		'ext' => $prize_arr['type'] == 4 || $prize_arr['type'] == 5 ? $ret['data'] : '',		
	);
	print_r($resarr);
	return json_encode($resarr);

}
//奖品入库
function add_prize_db($param){
	global $model;	
	$where = array(
		'id' =>$param['pid'],
		'aid' => $param['aid'],
	);
	$data = array(
		'num' => array('exp','`num`-1'),
		'__user_table' => $param['table']
	);
	$model -> update($where,$data,'lottery/lottery');
	//处理中奖表
	$now_tm= time();
	$award_data = array(
		'uid' => $param['uid'],
		'username' => $param['username'],
		'pid' => $param['pid'],
		'prizename' => $param['prizename'],
		'status' => 1,
		'create_tm' => $now_tm,
		'aid' => $param['aid'],
		'from' => 2,
		'ext' => $param['ext'],
		'__user_table' => $param['table_award']
	);
	$model -> insert($award_data,'lottery/lottery');
	echo $model->getsql();	
}
//发放礼券
function grant_coupon($aid,$uid,$couponId,$activityName){		
	$data_arr =  array(
		'uid' => $uid,
		'couponId' => $couponId,
		'exchangeTime' =>time(),
		'exchangeNum' => 1,
		'activityName' => $activityName,
		'activityId' => $aid,
	);
	$js_data = json_encode($data_arr);
	$data = array(
		'serviceId' => '',
		'data' => $js_data
	);
	$res = get_data($data,'coupon');
	if($res['code'] != '200'){
		return false;
	}else{
		return $res;
	}
}

//发放礼包
function grant_gift($aid,$uid,$giftId,$activityName,$appName){
	$data_arr = array(
		'uid'			=>	$uid,
		'giftId'		=>	$giftId,
		'exchangeTime'	=>	time(),
		'exchangeNum'	=>	1,
		'appName'		=>	$appName,
		'activityName'	=>	$activityName,
	);	
	$js_data = json_encode($data_arr);
	$data = array(
		'serviceId' => '',
		'data' => $js_data
	);
	$res = get_data($data,'gift');
	if($res['code'] != '200'){
		return false;
	}else{
		return $res;
	}	
}

/**
 * 接口
 * @param array $vals
 * @param int   $type  0礼券  1礼包
 * @return str
 */

function get_data($vals,$type){
	$host = load_config('pay_host');
	if( $type =="gift" ) {
		//礼包
		$url	=	$host.'/pay/internal/gift/exchange';
	}else {
		//礼券
		$url   =   $host.'/pay/internal/coupon/exchange';
	}		
    $vals = http_build_query($vals);
    $res = httpGetInfo($url,$vals,$type);
    $last = json_decode($res,true);
    //var_dump($last);
    return $last;
}

function httpGetInfo($url,$vals,$type) {
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
	if( $type =="gift" ) {
		$log_name = "gift_http.log";
	}else{
		$log_name = "coupon_http.log";
	}	
	writelog($log_name,"{$http_code}|{$errno}|{$error}\n" .$url. print_r($vals, true) . "\n" . print_r($result, true) . "\n\n");
	return $result;
}

//日志
function writelog($filename,$msg) {
	$now = time();
	$path = "/data/att/permanent_log/admin_cron_log/".date("Y-m-d", $now);
	if(!file_exists($path)){
		mkdir($path, 0755, true);
	}	
	$path_log = $path."/".$filename;
	$msg = date('Y-m-d H:i:s', $now). " {$msg}\n";
	file_put_contents($path_log, $msg, FILE_APPEND);
}
