<?php
/*
 * 安智币worker
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
$worker->addFunction("anzhi_lottery_money", "get_award");
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
	$prefix			=	isset($jobs['prefix']) ? $jobs['prefix'] : '';
	$workerType		=	isset($jobs['workerType']) ? $jobs['workerType'] : null;
	$uid			=	isset($jobs['uid']) ? $jobs['uid'] : null;
	$aid			=	isset($jobs['aid']) ? (int)$jobs['aid'] : null;
	$username		=	isset($jobs['username']) ? $jobs['username'] : '';
	$position		=	is_array($jobs['position']) ? $jobs['position'] : explode(',', $jobs['position']);
	$appName		=	isset($jobs['appName']) ? $jobs['appName'] : '';
	$activityName	=	isset($jobs['activityName']) ? $jobs['activityName'] : '';
	$package		=	isset($jobs['package']) ? $jobs['package'] : '';
	$gift_pkg		=	isset($jobs['gift_pkg']) ? $jobs['gift_pkg'] : ''; //已英文分号（;）分割的包名字符串形式
	$appkey			=	isset($jobs['appkey']) ? $jobs['appkey'] : '1392365303Jy1R97taJfdtops8Cxum';
	$orderDes		=	isset($jobs['orderDes']) ? $jobs['orderDes'] : '';
	$payPwd			=	isset($jobs['payPwd']) ? $jobs['payPwd'] : '';
	$azbAmount		=	isset($jobs['azbAmount']) ? (int)$jobs['azbAmount'] : 0;
	$serviceId		=	isset($jobs['serviceId']) ? (int)$jobs['serviceId'] : 014;
	
	if( in_array($workerType, array(1,2)) ) {
		if($workerType == 1) {
			//获取用户安智币
			$urlParam = '/user/azb/get';
			$js_data = array(
					'uid'	=>	$uid,
			);
		}else if( $workerType == 2 ) {
			//消费安智币
			$urlParam = '/user/azb/consume';
			$js_data = array(
					'uid'		=>	$uid,
					'appkey'	=>	$appkey,
					'orderDes'	=>	$orderDes,
					'payPwd'	=>	$payPwd,
					'azbAmount'	=>	$azbAmount
			);
		}
		$data = array(
				'serviceId'	=>	$serviceId,
				'data'		=>	json_encode($js_data)
		);
		$res = get_money_data($data, $urlParam);
		if($res['code'] != '200') {
			$ret_arr = array(
					'code'	=>	0,
					'msg'	=>	$res['msg'],
			);
			return json_encode($ret_arr);
		}else {
			if($workerType == 1) {
				$res_info = json_decode($res['data'], true);
				$ret_arr = array(
						'code'		=>	1,
						'msg'		=>	$res['msg'],
						'azmoney'		=>	isset($res_info['azmoney']) ? $res_info['azmoney'] : 0,
						'isHasPayPwd'	=>	isset($res_info['isHasPayPwd']) ? $res_info['isHasPayPwd'] : 0,
				);
				return json_encode($ret_arr);
			}else {
				$ret_arr = array(
						'code'	=>	1,
						'msg'	=>	$res['msg']
				);
				return json_encode($ret_arr);
			}
		}
	}else {
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
					$couponId = intval($v['gift_file']);
					$redis->set("{$prefix}:{$aid}:couponId:".$id,$couponId,1200);
				}else if($type == 5) {
					//$giftId	=	intval($v['gift_file']);
					//$redis->set("{$prefix}:{$aid}:giftId:".$id,$giftId,1200);
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
		$pid = lottery($gift_base, $basenum);
		print_r($gift_base)."\n";
		print_r($basenum)."\n";
		echo 'pid:'.$pid."\n";
		//中奖，检查数量 如数量不足 返回未中奖
		if($pid!=-1) {
			// 奖品数量-1
			$now_num = $redis -> setx('incr',"{$prefix}:{$aid}:prize_num:".$pid, -1);
			echo 'now_num:'.$now_num."\n";
			if ($now_num < 0) {
				echo 'no shiwu'."\n";
				// 没有剩余奖品了，把缓存数量还原为0
				$now_num = $redis -> set("{$prefix}:{$aid}:prize_num:".$pid, 0);
				return -1;
			}
			$type = $redis->get("{$prefix}:{$aid}:prize_type:".$pid);
			$prize_rank = $redis->get("{$prefix}:{$aid}:prize_rank:".$pid);
			if($type != 2) {
				if($type == 3) {
					$where = array(
							'id' =>$pid,
							'aid' => $aid,
					);
					$data = array(
							'num' => array('exp','`num`-1'),
							'__user_table' => 'valentine_draw_prize',
					);
					$model -> update($where,$data,'lottery/lottery');
					return -1;
				}
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
						return json_encode($resarr);
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
						return json_encode($resarr);
					}
				}else if($type == 5) {
					//礼包（直接发放）
					//获取配置的礼包id
					$giftId	=	get_gift_id($aid, $uid, $gift_pkg, $prefix);
					echo $giftId ? $giftId : '无礼包id';
					if(!$giftId) {
						$resarr = array(
								'code'	=>	0,
								'pid'	=>	$pid,
								'prize_rank'	=>	$prize_rank,
								'prizename'		=>	$prizename,
								'msg'	=>	"礼包id无效",
						);
						return json_encode($resarr);
					}
					$ret = grant_gift($aid, $uid, $giftId, $activityName, $appName);
					$app_name = get_app_name($giftId);
					if(!$ret) {
						$resarr = array(
								'code'	=>	0,
								'pid'	=>	$pid,
								'prize_rank'	=>	$prize_rank,
								'prizename'		=>	$app_name,
								'msg'	=>	"礼包发放失败",
						);
						return json_encode($resarr);
					}
				}
				//减少库中实际数量
				$param =array(
						'type'	=>	2,
						'uid'	=>	$uid,
						'aid'	=>	$aid,
						'pid'	=>	$pid,
						'username'	=>	$username,
						'prizename'	=>	$type == 5 ? $app_name.":".$ret['giftCode'] : $prizename,
						'table'	=>	'valentine_draw_prize',
						'table_award'	=> 'valentine_draw_award',
				);
				$task_client = get_task_client();
				$task_client->doBackground('recharge_top_db',json_encode($param));
				$resarr = array(
						'code'	=>	1,
						'pid'	=>	$pid,
						'type'	=>	$type,
						'prize_rank'	=>	$prize_rank,
						'prizename'		=>	$type == 5 ? $app_name : $prizename,
						'gift_number'	=>	$ret['giftCode'] ? $ret['giftCode'] : '',
				);
				print_r($resarr);
				return json_encode($resarr);
			}else {
				//礼包（上传文件）
				$gift_number = get_gift($package,$aid,$pid);
				if(!$gift_number){
					return -1;
				}
				$where = array(
						'id'  => $pid,
						'aid' => $aid,
				);
				$data = array(
						'num' => array('exp','`num`-1'),
						'__user_table' => 'valentine_draw_prize',
				);
				$model -> update($where,$data,'lottery/lottery');
				$where = array(
						'gift_number' => json_decode($gift_number,true),
						'aid' => $aid,
						'pid' => $pid,
				);
				$data = array(
						'uid'	 =>	$uid,
						'aid'	 =>	$aid,
						'status' =>	1,
						'update_tm'		=>	time(),
						'__user_table'	=>	'valentine_draw_gift'
				);
				$res = $model -> update($where,$data,'lottery/lottery');
				if($res) {
					$resarr = array(
							'code'			=>	1,
							'pid'			=>	$pid,
							'type'			=>	$type,
							'prize_rank'	=>	$prize_rank,
							'gift_number'	=>	$gift_number
					);
					print_r($resarr);
					return json_encode($resarr);
				}else {
					$resarr = array(
							'code'	=>	0,
							'msg'	=>	'领取礼包失败'
					);
					print_r($resarr);
					return json_encode($resarr);
				}
			}
		}else{
			return -1;
		}
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
function get_data($vals, $type)
{
	$host = load_config('pay_host');
	if( $type == "gift" ) {
		//礼包
		$host	=	$host.'/pay/internal/gift/exchange';
	}else {
		//礼券
		$host	=	$host.'/pay/internal/coupon/exchange';//测试地址
	}	
	$vals	=	http_build_query($vals);
	$res	=	httpGetInfo($host, $vals); 
	$last	=	json_decode($res,true);
	return $last;
}

/**
 * 获取安智币信息接口
 * @param  array $val
 * @return array
 */
function get_money_data($vals, $urlParam)
{
	$host	=	load_config('pay_host');
	$host	=	$host.'/pay/internal'.$urlParam;
	$vals	=	http_build_query($vals);
	$res	=	httpGetInfo($host, $vals);
	$last	=	json_decode($res,true);
	return $last;
}

function httpGetInfo($url, $vals)
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
	writelog('anzhi_lottery_money_http.log',"{$http_code}|{$errno}|{$error}\n" .$url. print_r($vals, true) . "\n" . print_r($result, true) . "\n\n");
	return $result;
}

//日志
function writelog($filename,$msg)
{
	$now	=	time();
	$path	=	"/data/att/permanent_log/admin_cron_log/".date("Y-m-d", $now);
	if( !file_exists($path) ) {
		mkdir($path, 0755, true);
	}	
	$path_log = $path."/".$filename;
	$msg = date('Y-m-d H:i:s', $now). " {$msg}\n";
	file_put_contents($path_log, $msg, FILE_APPEND);
}

function get_gift($package, $aid, $pid)
{
	global $redis;
	$prefix = "custom";
	$redis->pingConn();
	$pkg_key = "{$prefix}:{$aid}:{$pid}_gift_pkg";
	$new_pkg = $redis->get($pkg_key);
	if(!$new_pkg) {
		return false;
	}
	if(empty($package)||!in_array($package,$new_pkg)) {
		$pkg_key = array_rand($new_pkg);
		$package = $new_pkg[$pkg_key];
	}
	//中礼包   包名 supwater
	//处理  redis 的礼包
	$gift_key = "{$prefix}:{$aid}:{$pid}_gift:{$package}";
	$gift_number = $redis -> rpop($gift_key);
	if(empty($gift_number)){
		$gift_number = $redis -> rpop($gift_key);
		if(empty($gift_number)) {
			//删除 没有礼包的包名
			$new_pkg = $redis->get($pkg_key);
			foreach($new_pkg as $k=>$v){
				if($v==$package){
					echo 'unset...pkg';
					unset($new_pkg[$k]);
				}
			}
			$redis->set($pkg_key,$new_pkg);
			return get_gift();
		}
	}
	return $gift_number;
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
	$gift_arr = isset($prize_gift_pkg_conf[$package]) ? $prize_gift_pkg_conf[$package] : 0;

	if( !empty($gift_arr) ) {
		$gift_id = get_gift_pkg_id($aid, $uid, $package, $gift_arr, $prefix);
		del_gift_pkg($aid, $uid, $package, $prefix);
		del_gift_pkg_id($aid, $uid, $package, $gift_id, $prefix);
		return $gift_id;
	}else {
		return false;
	}
}

//a.优先抽中已安装游戏的虚拟礼包；
//b.同一用户不重复中同一款礼包；
//c.若用户抽奖的次数，超过了虚拟礼包的种类数量，则随机中虚拟礼包。
function get_gift_pkg($aid, $uid, $gift_pkg, $prefix)
{
	global $redis;
	$user_gift_pkg	=	$redis->get("{$prefix}:{$aid}_gift_pkg:".$uid);
	$open_gift_pkg	=	explode(";",$gift_pkg);
	if(!$user_gift_pkg) {
		$prize_gift_pkg	=	$redis->get("{$prefix}:{$aid}_gift_pkg");
		$redis -> set("{$prefix}:{$aid}_gift_pkg:".$uid,$prize_gift_pkg,10*86400);
		$user_gift_pkg	=	$prize_gift_pkg;
		$intersection	=	array_intersect($open_gift_pkg, $prize_gift_pkg);
	}else {
		$intersection	=	array_intersect($open_gift_pkg, $user_gift_pkg);
	}
	if($intersection) {
		//a.优先抽中已安装游戏的虚拟礼包；
		foreach($intersection as $v) {
			return $v;
			exit;
		}
	}else {
		return $user_gift_pkg[0];
	}
}

//去除已获得的礼包包名
function del_gift_pkg($aid, $uid, $pkg, $prefix)
{
	global $redis;
	$user_gift_pkg	=	$redis->get("{$prefix}:{$aid}_gift_pkg:".$uid);
	$new_gift_pkg	=	array();
	foreach($user_gift_pkg as $k => $v) {
		if($v != $pkg) {
			$new_gift_pkg[] = $v;
		}
	}
	$redis -> set("{$prefix}:{$aid}_gift_pkg:".$uid, $new_gift_pkg, 10*86400);
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

function get_pk_config(){
	return 	array(
		'com.netease.dhxy.anzhi'		=>	array(10905),
		'com.tianmashikong.qmqj.anzhi'  =>	array(10906,10907,10908,10909),
		'cc.thedream.qinsmoon.anzhi'	=>	array(10910,10911,10912,10913),
		'com.linekong.dbm.anzhi'		=>	array(10914,10915,10916,10917),
		'com.linekng.sszj.anzhi'		=>	array(10918,10919,10920),
		'com.longtugame.jymfdsj.anzhi'	=>	array(10921,10922,10923,10924),
		'com.longtugame.lxjjx.anzhi'	=>	array(10925,10926,10927,10928),
		'com.tencent.tmgp.sgame'		=>	array(10930,10931,10932,10933),
		'com.yinhan.hunter.anzhi'	    =>	array(10934,10935,10936,10937),
		'com.wanmei.zhuxian.anzhi'		=>	array(10941),
		'com.yunchang.buliangren.anzhi'	=>	array(10942,10943),
		'com.netease.l10.anzhi'			=>	array(10944),
		'com.babeltime.fknsg2.anzhi'	=>	array(10957,10958),
		'sh.lilith.dgame.anzhi'         =>	array(10959),
		'com.sy37.yhjyj.anzhi'          =>	array(10960,10961),
		'com.netease.dhhzl.anzhi'       =>	array(10962,10963),
		'jianghu.lanjing.com.anzhi'     =>	array(10964),
		'com.kx.sgqyz.anzhi'       		=>	array(10965,10966,10967),
		'com.cyou.cx.mtlbb.anzhi'       =>	array(10968,10969),
		'com.brianbaek.popstar'       	=>	array(10980,10982,10983,10984),
		'com.yinhu.hjjx.anzhi'    		=>	array(10985,10986,10987,10988),
	);
}
function get_app_name($giftid){
	$arr = array(
		'10905' => '大话西游',
		'10906' => '全民奇迹','10907' => '全民奇迹','10908' => '全民奇迹','10909' => '全民奇迹',
		'10910' => '秦时明月','10911' => '秦时明月','10912' => '秦时明月','10913' => '秦时明月',
		'10914' => '黎明之光','10915' => '黎明之光','10916' => '黎明之光','10917' => '黎明之光',
		'10918' => '蜀山战纪','10919' => '蜀山战纪','10920' => '蜀山战纪',
		'10921' => '新剑与魔法','10922' => '新剑与魔法','10923' => '新剑与魔法','10924' => '新剑与魔法',
		'10925' => '螺旋境界线','10926' => '螺旋境界线','10927' => '螺旋境界线','10928' => '螺旋境界线',
		'10930' => '王者荣耀','10931' => '王者荣耀','10932' => '王者荣耀','10933' => '王者荣耀',
		'10934' => '时空猎人','10935' => '时空猎人','10936' => '时空猎人','10937' => '时空猎人',
		'10941' => '诛仙',
		'10942' => '不良人2','10943' => '不良人2',
		'10944' => '倩女幽魂',
		'10957' => '放开那三国2-登陆送关羽','10958' => '放开那三国2-登陆送关羽',
		'10959' => '小冰冰传奇',
		'10960' => '永恒纪元:戒','10961' => '永恒纪元:戒',
		'10962' => '大航海之路','10963' => '大航海之路',
		'10964' => '江湖风云录',
		'10965' => '三国群英传-争霸','10966' => '三国群英传-争霸','10967' => '三国群英传-争霸',
		'10968' => '天龙八部3D','10969' => '天龙八部3D',
		'10980' => 'PopStar!消灭星星官方正版','10982' => 'PopStar!消灭星星官方正版','10983' => 'PopStar!消灭星星官方正版','10984' => 'PopStar!消灭星星官方正版',
		'10985' => '幻剑剑心','10986' => '幻剑剑心','10987' => '幻剑剑心','10988' => '幻剑剑心',
	);
	return $arr[$giftid];
}