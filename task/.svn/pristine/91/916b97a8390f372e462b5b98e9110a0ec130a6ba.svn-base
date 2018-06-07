<?php
/*
 *   双色球活动--发放奖品worker
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
$worker->addFunction("doublecolor_prize", "get_award");
while ($worker->work());

function get_award($jobs){
	global $model,$redis;
	$jobs= $jobs->workload();
	$jobs = json_decode($jobs,true);
	print_r($jobs);
	$issue = $jobs['issue'];
	if(!$issue){
		echo "开奖期无效:".$issue."\n";
		return false;		
	}	
	$aid = $jobs['aid'];	
	$redis->pingConn();	 
	$table = "double_color_numbers";
	$key = "doublecolor_is_award:".$aid.":".$issue;
	$is_award = $redis->setnx($key,1);
	$redis->expire($key,86400);
	if($is_award === false){
		echo "该期已发奖:".$key."\n";
		return false;
	}
	save_is_succ_award($aid,$issue,0);			
	$activityName = "双色球活动";
	$option = array(
		'where' => array(
			'issue' => $issue,
			'admin_id'	=> 0,
			'status' =>1,
			'prizelevel' => array('exp',">0"),
			'is_award' => 1,
			'is_send' => 0,
		),
		'field' => 'id,uid,prizenum',
		'table' => $table,
	);
	$prize_arr= $model->findAll($option,'lottery/lottery');	
	if(!$prize_arr){
		echo $model->getsql()."\n";
		echo "无人中奖开启下一期";
		//无人中奖开启下一期
		open_next_phase();	
		save_is_succ_award($aid,$issue,1);		
		return false;
	}		
	$is_succ = 1;
	foreach($prize_arr as $k => $v){
		$uid = $v['uid'];
		$amount = $v['prizenum'];
		$ret = grant_coupon($aid,$uid,$amount,$activityName);
		if($ret){
			$where = array('id'=>$v['id']);
			$data = array(
				'send_time' => time(),
				'is_send' => 1,
				'__user_table' =>  $table
			);
			$res = $model -> update($where,$data,'lottery/lottery');
			if(!$res){
				$is_succ = 0;
			}	
		}else{
			$is_succ = 0;
		}		
	}
	if($is_succ){	
		//发奖成功后开启下一期
		open_next_phase();
		save_is_succ_award($aid,$issue,1);			
	}else{
		sleep(300);
		echo "sleep300秒二次发奖\n";
		$is_succ = grant_coupon_do($aid,$activityName,$issue,$table);
		if(!$is_succ){
			$content = "双色球".$issue."期发奖不成功请检查";
			send_mail($content);
			echo $content;
			$redis->delete($key);	
			save_is_succ_award($aid,$issue,2);	
		}
	}
	return 1;

}
function save_is_succ_award($aid,$issue,$status){
	global $redis;	
	$is_succ_key = "doublecolor:is_succ_award:".$aid;	
	$arr = array(
		'issue' => $issue,
		'is_succ' => $status,//0发奖中 1发奖成功 2发奖失败
		'time' => time(),				
	);
	$redis->set($is_succ_key,$arr,86400*365);		
}
function grant_coupon_do($aid,$activityName,$issue,$table){
	global $model;
	$option = array(
		'where' => array(
			'issue' => $issue,
			'admin_id'	=> 0,
			'status' =>1,
			'prizelevel' => array('exp',">0"),
			'is_award' => 1,
			'is_send' => 0,
		),
		'field' => 'id,uid,prizenum',
		'table' => $table,
	);
	$prize_arr= $model->findAll($option,'lottery/lottery');		
	$is_succ = 1;
	foreach($prize_arr as $k => $v){
		$uid = $v['uid'];
		$amount = $v['prizenum'];
		$ret = grant_coupon($aid,$uid,$amount,$activityName);
		if($ret){
			$where = array('id'=>$v['id']);
			$data = array(
				'send_time' => time(),
				'is_send' => 1,
				'__user_table' =>  $table
			);
			$res = $model -> update($where,$data,'lottery/lottery');
			if(!$res){
				$is_succ = 0;
			}	
		}else{
			$is_succ = 0;
		}		
	}
	return 	$is_succ;
}
//开启下一期
function open_next_phase(){
	global $model,$redis;
	$where = array('status'=>0);
	$data = array(
		'status'=>1,
		'__user_table' => 'double_color_issue',
	);
	$model -> update($where,$data,'lottery/lottery');		
	$redis->set('double_color_ball:isover',2,86400*365);	
}
//发放礼券，金额
function grant_coupon($aid,$uid,$amount,$activityName){		
	$data_arr =  array(
		'uid' => $uid,
		'exchangeTime' =>time(),
		'exchangeNum' => 1,
		'activityName' => $activityName,
		'activityId' => $aid,
		'amount' => $amount*100,
		'couponId' => load_config('float_couponid','lottery'), //测试环境1015
	);
	$js_data = json_encode($data_arr);
	$data = array(
		'serviceId' => '',
		'data' => $js_data
	);
	$res = get_data($data,'coupon');
	//var_dump($data_arr,$res);
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
		//礼券-浮动
		$url   =   $host.'/pay/internal/coupon/float/exchange';
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
		$log_name = "coupon_float_http.log";
	}	
	writelog($log_name,"{$http_code}|{$errno}|{$error}\n" .$url."\n". print_r($vals, true) . "\n" . print_r($result, true) . "\n\n");
	return $result;
}
function send_mail($content){
	$mailarr = array(
		'303710507@qq.com',
		//'chenzhiyi@anzhi.com',
		//'suntao@anzhi.com',
	);
	//报警
	foreach($mailarr as $mv){
		if(!empty($mv)){
			$map = array(
				'email'=>trim($mv),
				'name'=>'安智管理',
				'subject'=>'双色球活动',
				'content'=>$content
			);
			$tmp = _http_post_email($map);
		}
	}		
}
function _http_post_email($vals) {
	$url = 'http://192.168.1.143/service.php';
	$host = 'Host: mail.goapk.com';
	$url .= '?key=019f160f2ae0c8990eb94653bd101857';

	$res = curl_init();
	curl_setopt($res, CURLOPT_URL, $url);
	curl_setopt($res, CURLOPT_TIMEOUT, 5);
	curl_setopt($res, CURLOPT_HTTPHEADER, array($host));
	curl_setopt($res, CURLOPT_POST, true);
	curl_setopt($res, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($res, CURLOPT_POSTFIELDS, $vals);
	$result = curl_exec($res);
	$http_code = curl_getinfo($res,CURLINFO_HTTP_CODE);
	curl_close($res);
	return array(
		'ret' => $result,
		'http_code' => $http_code,
	);
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
