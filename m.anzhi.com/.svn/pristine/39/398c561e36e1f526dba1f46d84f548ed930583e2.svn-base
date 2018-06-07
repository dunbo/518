<?php

include_once (dirname(realpath(__FILE__)).'/../init.php');
ini_set('memory_limit',-1);
$active_id = 263;
$config = load_config('lottery_cache/redis',"lottery");
if ($config) {
	$redis = new GoRedisCacheAdapter($config);
} else {
	$redis = GoCache::getCacheAdapter('redis');
}
$model = new GoModel();

$imsi_arr = array("460042359383380","460086504303738","460011705796755","460066600163811","460073760208920","460092178990235","460034351743375","460086530305114","460024872225457","460071144031826","460030233084908","460088584916656","460013804672458","460013855218561","460039294304493","460073258131998","460070875446228","460059990915940","460015370965804","460094962560934","460092343491874","460090393188926","460086690816967","460053412941116","460039791800077","460059800300104","460086622319091","460087246883404","460078192938724","460078140494581","460019626873075","460058071021740","460034240615977","460025858003948","460099604764641","460043462001704","460073659513597","460043237969651","460046116390344","460070159321734","460055352379735","460097129613822","460016603109971","460013098810431","460040402766596","460094160059713","460082125214678","460066191885583","460065071217878","460059791540009");

$uid_arr = array("20150625081919h5iqf3zNfn","20150625101643BK636uTvIQ","20150625153820t94NX5799j","20150625100001pOi9JN14Ck","201506251025439m2i6z5stp","20150625083857HBScI6R415","20150625074217QBk1AJVRNz","20150625074653xJcUF36W6L","20150625101350my5BegfwmS","20150625095510SPPA1JG2jx","20150625082524ypjTh8x6Ac","20150625101014vw96rN9zu5","201506250949229X2d3c4L56","201506250949408x9F0bgHlm","20150625091047b7CMDTJ5Wy","20150625081212NQBqk6yO5m","20150625102231heCDo1xI9O","201506250912203OK6bjQLdt","20150625092838osE5OeCNTs","20150625091513791KUM3Ew2","20150625191747fNnH44Np1F","20150625083223v1VPpJ9EXE","20150625080517gjX0fd1jk2","201506250858066aye6cWMgG","2015062509151667c8da1RwP","201506250935463ph8IVLs9L","201506251905432iw2bSQC3S","201506250812230W18dD7j24","20150625103731SAaTh8BQu9","20150625075145DgQQcIWnYB","20150625101441CQa72mmvg3","20150625102301TlHFB3TEpS","20150625191245q0hEyi9801","20150625101515wHG8CXh5cq","201506250750404xRjluhI0u","2015062507591190qPzfr5w5","20150625191142KKf89SP87L","201506250921598dWcVog4V7","20150625091421aS52kO5kXm","20150625094019n4oBhn59P0","20150625090727nL42yLMN5a","20150625092524SqrFN6b6H7","20150625103211L3zjEbLyfe","20150625084057RU74RjBX7L","201506250826033GxCpS2ihq","20150625082548RpA532EXkR","2015062509251650fTzGK5HD","2015062508001768125Whbcw","20150625083917NSVNjDA6q6","20150625083948y5TJ1n875p");
$r = array_rand(range(0, 49));
$imsi = $imsi_arr[$r];
$uid = $uid_arr[$r];
$pid_arr = array(0,1,2,3,4,5,6,7,8);

if($imsi && $uid){
	$rank_key = array_rand($pid_arr);
	//$rank = 0;
	$rank = $pid_arr[$rank_key];
	$rest_integral = get_rest_integral($uid);
	/*
	$exchange_num = get_exchange_num($uid);
	if($exchange_num <= 0){
		$r_tm = strtotime(date('Y-m-d').' 23:59:59')-time();
		$redis->set('integral_exchange_num'.$uid,0,$r_tm);
		exit(json_encode(array('code'=>0,'msg'=>'您今天可用兑换次数已经用完')));
	}	
	*/
	if($rank >0 ){
		//每日兑换次数
		$now_num_begin = $redis->gethash("integral_prize:{$rank}");
		if(empty($now_num_begin)){
			echo 400;
			exit;
		}	
		if($rest_integral < $now_num_begin['prize_integral']){
			echo $uid.$rest_integral ."----".$now_num_begin['prize_integral'];
			exit(json_encode(array('code'=>0,'msg'=>'您当前可用的积分不足，请充值获取积分')));
		} 	
		if($now_num_begin['num'] <= 0){
			$redis->setx("HSET","integral_prize:{$rank}","num",0);
			exit(json_encode(array('code'=>0,'msg'=>'【'.$now_num_begin['name'].'】奖品已经被兑换完')));
		}
		load_helper('task');
		$task_client = get_task_client();
		$new_array = array();
		$new_array['uid'] =$uid;
		$new_array['rank'] =$rank;
		$new_array['username'] = $_SESSION['USER_NAME'];
		$new_array['type'] = 1;
		$the_award = $task_client->do('integral_work', json_encode($new_array));
		$lottery_rs = json_decode($the_award,true);	
		if($lottery_rs['code'] == 0 ){
			exit(json_encode(array('code'=>0,'msg'=>$lottery_rs['msg'])));
		}
	}else{	
		$pkg_arr = array_keys(get_gift());
		if($rest_integral < 20){
			exit(json_encode(array('code'=>0,'msg'=>'您当前可用的积分不足，请充值获取积分')));
		}
		$pkg_key = array_rand($pkg_arr);
		$pkg = $pkg_arr[$pkg_key];
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
	}
	//兑换成功日志
	$log_data = array(
			"imsi" => $imsi,
			"device_id" => $_SESSION['DEVICEID'],
			"activity_id" => $active_id,
			"ip" => $_SERVER['REMOTE_ADDR'],
			"sid" => $sid,
			"time" => time(),
			"award_level" => $rank > 0 ? $rank : 0,//pid
			"user" => $_SESSION['USER_NAME'],
			"name" => '',
			"telphone" => '',
			"address" => '',
			"package" => $pkg,
			"gift" =>  $rank == 0 ? $ret['gift_number'] : '',
			"users" => '',
			'uid'=>$uid,
			"lottery_type" => $rank == 0 ? 2 : 1,//1实物2礼包
			"award_name" => $rank == 0 ? '游戏礼包' : $lottery_rs['prizename'],
			'key' => 'integral_success'
	);
	permanentlog('activity_'.$active_id.'.log', json_encode($log_data));		
	//exit(json_encode(array('code'=>1,'gift_number'=>$ret['gift_number'])));
}

//每日兑换剩余次数
function get_exchange_num($uid){
	global $model;
	global $redis;
	//$redis->delete("integral_exchange_num{$uid}");
	$exchange_num = $redis->get('integral_exchange_num'.$uid);
	if($exchange_num === null){
		//计算当天领取的实物次数
		$option = array(
			'where' => array(
				'uid' => array('exp',"='{$uid}' and DATE_FORMAT(FROM_UNIXTIME(`time`),'%Y-%m-%d') = DATE_FORMAT(NOW(),'%Y-%m-%d')"),
			),
			'table' => 'integral_kind_award',
			'field' => 'count(*) as counts',
		);
		$kind_award = $model->findOne($option,'lottery/lottery');	
		//计算当天领取的礼包次数
		$option = array(
			'where' => array(
				'status' => 1,
				'uid' => array('exp',"='{$uid}' and DATE_FORMAT(FROM_UNIXTIME(`update_tm`),'%Y-%m-%d') = DATE_FORMAT(NOW(),'%Y-%m-%d')"),
			),
			'table' => 'integral_kind_gift',
			'field' => 'count(*) as counts',
		);
		$kind_gift = $model->findOne($option,'lottery/lottery');	
		$exchange_num = 500-$kind_award['counts']-$kind_gift['counts'];
		$r_tm = strtotime(date('Y-m-d').' 23:59:59')-time();
		$redis->set('integral_exchange_num'.$uid,$exchange_num,$r_tm);
	}	
	return $exchange_num;
}

//@积分剩余量
function get_rest_integral($uid){
	global $model;
	global $redis;		
	//$redis->delete('rest_integral'.$uid);
	$rest = $redis->get('rest_integral'.$uid);
	if(!$rest){
		$option = array(
			'where' => array(
				'uid' => $uid,
			),
			'table' => 'integral_userinfo',
			'field' => 'integral_num,deduction_integral',
		);
		$rest_list = $model->findOne($option,'lottery/lottery');	
		$rest = $rest_list['integral_num']-$rest_list['deduction_integral'];
		$redis->set('rest_integral'.$uid,$rest,15*60);
	}	
	return $rest;			
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
			$prize_gift_arr = array();
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