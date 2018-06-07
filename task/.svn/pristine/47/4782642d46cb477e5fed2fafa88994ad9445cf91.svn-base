<?php
/*
 * 元旦砸蛋活动worker
 * 三月份砸蛋活动worker
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
$worker->addFunction("yuandan_lottery", "get_award");
while ($worker->work());

/**
 * 礼券 礼包 礼包直接发放  安智币信息  安智币消费  安智币支付密码
 * @param  array $jobs
 * @return array
 */
function get_award($jobs)
{
	global $model;
	global $redis;
	$jobs	=	$jobs->workload();
	$jobs	=	json_decode($jobs, true);
	print_r($jobs);
	$prefix			=	isset($jobs['prefix']) ? $jobs['prefix'] : ''; //活动前缀
	$uid			=	isset($jobs['uid']) ? $jobs['uid'] : '';
	$aid			=	isset($jobs['aid']) ? (int)$jobs['aid'] : ''; //活动id
	$username		=	isset($jobs['username']) ? $jobs['username'] : '';
	$position		=	is_array($jobs['position']) ? $jobs['position'] : explode(',', $jobs['position']); //奖品区间
	$activityName	=	isset($jobs['activityName']) ? $jobs['activityName'] : ''; //活动名称
	$appkey			=	isset($jobs['appkey']) ? $jobs['appkey'] : '1392365303Jy1R97taJfdtops8Cxum'; //appkey
	$orderDes		=	isset($jobs['orderDes']) ? $jobs['orderDes'] : ''; //订单描述
	$payPwd			=	isset($jobs['payPwd']) ? $jobs['payPwd'] : ''; //支付密码
	$azbAmount		=	isset($jobs['azbAmount']) ? (int)$jobs['azbAmount'] : 0; //安智币数量
	$serviceId		=	isset($jobs['serviceId']) ? (int)$jobs['serviceId'] : 014; //服务id 默认值
	$lottery_num	=	isset($jobs['lottery_num']) ? (int)$jobs['lottery_num'] : 1; //抽奖次数
	//如果缓存没了 重新生成
	//剩余奖品数量缓存
	$redis->pingConn();
	$prize_key = implode('_', $position);		
	$gift_base = $redis->get("{$prefix}:{$aid}:base_list:{$prize_key}");
	$basenum = $redis->get("{$prefix}:{$aid}:basenum:{$prize_key}");
	if(empty($basenum) || empty($gift_base)) {
		$option = array(
			'where' => array(
				'aid'		  =>	$aid, //通用 活动ID
				'num'		  =>	array('exp','>0'),
				'probability' =>	array('exp','!=0'),
				'status'=>1
			),
			'table' => 'valentine_draw_prize',
		);
		if ( !empty($option) ) {
			$option['where']['level']	=	$position;
		}
		$prize_arr = $model->findAll($option, 'lottery/lottery');
		$sql2 = $model->getSql();
		print_r($sql2)."\n";
		print_r($prize_arr)."\n";
		$gift_base = array();
		foreach($prize_arr as $v) {
			$id		=	$v['id'];
			$num	=	intval($v['num']);
			$name	=	$v['name'];
			$level	=	intval($v['level']);
			$type	=	intval($v['type']);
			$redis->set("{$prefix}:{$aid}:prize_type:".$id, $type, 1200);
			$redis->set("{$prefix}:{$aid}:prize_num:".$id, $num, 1200);
			$redis->set("{$prefix}:{$aid}:prize_name:".$id, $name, 1200);
			$redis->set("{$prefix}:{$aid}:prize_rank:".$id, $level, 1200);
			if($type == 4) {
				//礼券
				$couponId = intval($v['gift_file']);
				$redis->set("{$prefix}:{$aid}:couponId:".$id,$couponId,1200);
			}
			//处理中奖率
			$rs = explode('/',$v['probability']);
			if($rs[0]==0 || empty($v['probability'])) {
				continue;
			}
			if(empty($basenum)) {
				$basenum = $rs[1];
			}else {
				if(empty($basenum) || !$rs[1]) {
					continue;
				}
				$basenum = min_multiple($basenum,$rs[1]);
			}
			$gift_base[$v['id']] =  $rs[0]/$rs[1]*$basenum;
		}
		$redis->set("{$prefix}:{$aid}:basenum:{$prize_key}", $basenum, 1200);
		$redis->set("{$prefix}:{$aid}:base_list:{$prize_key}", $gift_base, 1200);
	}
	if(!$basenum) {
		$basenum = 0;
	}
		
	$return_arr = array();
	for ($i=0; $i<$lottery_num; $i++) {
		$pid = lottery($gift_base, $basenum);
		print_r($gift_base)."\n";
		print_r($basenum)."\n";
		echo 'pid:'.$pid."\n";
		//中奖，检查数量 如果没中返回必中的pid
		//aid 629 是测试环境的id
		if($pid == -1 ) {
			$pid = get_other_pid($prefix, $aid,$gift_base);
			var_dump($pid);
		}	
		// 奖品数量-1
		$now_num = $redis -> setx('incr',"{$prefix}:{$aid}:prize_num:".$pid, -1);
		echo 'now_num:'.$now_num."\n";
		if ($now_num < 0) {
			echo 'no shiwu'."\n";
			// 没有剩余奖品了，把缓存数量还原为0
			$now_num = $redis -> set("{$prefix}:{$aid}:prize_num:".$pid, 0);
			//return -1;
			//$return_arr[] = -1;
			continue;
		}
		$type = $redis->get("{$prefix}:{$aid}:prize_type:".$pid);
		$prize_rank = $redis->get("{$prefix}:{$aid}:prize_rank:".$pid);
		if($type != 2) {
			$prizename = $redis -> get("{$prefix}:{$aid}:prize_name:".$pid);
			if($type == 4) {
				//礼卷
				$couponId = $redis->get("{$prefix}:{$aid}:couponId:".$pid);
				if(!$couponId) {
					$resarr = array(
							'code'	=>	0,
							'pid'	=>	$pid,
							'prize_rank' =>	$prize_rank,
							'prizename'	 =>	$prizename,
							'msg'	=>	"礼卷id无效",
					);
					//return json_encode($resarr);
					$return_arr[] = $resarr;
					continue;
				}
				$ret = grant_coupon($aid,$uid,$couponId,$activityName);
				if(!$ret) {
					$resarr = array(
							'code'	=>	0,
							'pid'	=>	$pid,
							'prize_rank'	=>	$prize_rank,
							'prizename'		=>	$prizename,
							'msg'	=>	"礼卷发放失败",
					);
					//return json_encode($resarr);
					$return_arr[] = $resarr;
					continue;
				}
			}
			//减少库中实际数量
			add_award($pid,$aid,$uid,$username,$prizename,$azbAmount);
			$resarr = array(
					'code'	=>	1,
					'pid'	=>	$pid,
					'type'	=>	$type,
					'prize_rank'	=>	$prize_rank,
					'prizename'		=>	$type == 5 ? $app_name : $prizename,
					'gift_number'	=>	$ret['giftCode'] ? $ret['giftCode'] : '',
			);
			print_r($resarr);
			//return json_encode($resarr);
			$return_arr[] = $resarr;
		}
	}
	return json_encode($return_arr);	
}

function add_award($pid,$aid,$uid,$username,$prizename,$azbAmount){
	global $model;	
	$where = array(
		'id' =>$pid,
		'aid' => $aid,
	);
	$data = array(
		'num' => array('exp','`num`-1'),
		'__user_table' => 'valentine_draw_prize' 
	);
	$model -> update($where,$data,'lottery/lottery');
	//处理中奖表
	$now_tm= time();
	$award_data = array(
		'uid' => $uid,
		'aid' => $aid,
		'username' => $username,
		'create_tm' => $now_tm,
		'status' => 1,
		'pid' => $pid,
		'prizename' => $prizename,
		'money' => $azbAmount,
		'__user_table' => 'valentine_draw_award'
	);
	$model -> insert($award_data,'lottery/lottery');	
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
		return true;
	}
}
/**
 * 礼包直接方法和礼券接口
 * @param array $vals
 * @param int   $type  coupon 礼券  gift礼包
 * @return str
 */
function get_data($vals, $type){
	$host = load_config('pay_host');
	if( $type == "gift" ) {
		//礼包
		$host	=	$host.'/pay/internal/gift/exchange';
	}else {
		//礼券
		$host	=	$host.'/pay/internal/coupon/exchange';//测试地址
	}	
	$vals	=	http_build_query($vals);
	$res	=	httpGetInfo($host, $vals,"internal.log"); 
	$last	=	json_decode($res,true);
	return $last;
}

function httpGetInfo($url, $vals,$log_name)
{
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
	writelog($log_name,"{$http_code}|{$errno}|{$error}\n" .$url. print_r($vals, true) . "\n" . print_r($result, true) . "\n\n");
	return $result;
}

//日志
function writelog($filename,$msg)
{
	$now	=	time();
	$path	=	"/data/att/permanent_log/promotion.anzhi.com/".date("Y-m-d", $now);
	if( !file_exists($path) ) {
		mkdir($path, 0755, true);
	}	
	$path_log = $path."/".$filename;
	$msg = date('Y-m-d H:i:s', $now). " {$msg}\n";
	file_put_contents($path_log, $msg, FILE_APPEND);
}

//最小公倍数
function min_multiple($a, $b)
{
	$m = max($a,$b);
	$n = min($a,$b);
	for($i=1; ; $i++) {
		if (is_int($m*$i/$n)) {
			return $mix=$m*$i;
		}
	}
}
	
function lottery($gift_base, $sum)
{
	$gift_line = array();
	$nows = 0;
	if(!$gift_base){
	return -1;
	}
	foreach ($gift_base as $k=>$v) {
		$gift_line[$k] = array($nows+1, $nows+$v);
		$nows += $v;
	}
	$rand = mt_rand(1, $sum);
	foreach($gift_line as $k => $v) {
		if($rand >= $v[0] && $rand <= $v[1]) {
			return $k;
		}
	}
	return -1;
}

function get_other_pid($prefix, $aid, $gift_base){
	if(is_array($gift_base) && !empty($gift_base)){
		arsort($gift_base);
		$i = 0;
		$new_arr = array();
		foreach($gift_base as $key => $val){
			$i++;
			if($i>2){
				break;
			}
			$new_arr[$key] = 1;
		}
		return array_rand($new_arr);
	}else{
		return -1;
	}
}

