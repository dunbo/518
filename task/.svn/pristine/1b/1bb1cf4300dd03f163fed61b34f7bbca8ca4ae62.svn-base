<?php

include dirname(__FILE__).'/../init.php';
$config = load_config('lottery_cache/redis',"lottery");
if ($config) {
	$redis = new GoRedisCacheAdapter($config);
} else {
	$redis = GoCache::getCacheAdapter('redis');
}

$model = new GoModel();
/*
$each_day_award = array(
	'20160315' => array(10,10,10,10,10,10,10),
	'20160316' => array(0,0,0,1,3,3,10),
	'20160317' => array(0,0,0,1,3,3,10),
	'20160318' => array(0,0,0,1,3,3,10),
	'20160319' => array(0,0,0,1,3,3,10),
	'20160320' => array(0,0,0,1,3,3,10),
	'20160321' => array(0,0,0,1,3,3,10),
	'20160322' => array(0,0,0,1,3,3,10),
	'20160323' => array(0,0,0,1,3,3,10),
	'20160324' => array(0,0,0,1,3,3,10),
	'20160325' => array(0,0,0,1,3,3,10),
	'20160326' => array(0,0,0,1,3,3,10),
	'20160327' => array(0,0,0,1,3,3,10),
	'20160328' => array(0,0,0,1,3,3,10),
	'20160329' => array(0,0,0,1,3,3,10),
	'20160330' => array(0,1,0,1,1,1,2),
	'20160331' => array(0,0,0,1,1,1,1),
	'20160401' => array(1,0,0,1,1,1,2),
	'20160402' => array(0,0,0,1,1,1,2),
	'20160403' => array(0,0,1,1,1,1,1),
	'20160404' => array(0,0,0,1,1,1,1),
	'20160405' => array(0,0,0,1,1,1,1),
);
*/
// 线上的中奖概率
$each_day_award = array(
	'20160329' => array(0,0,0,0,0,0,0),
	'20160330' => array(0,0,0,0,0,0,0),
	'20160331' => array(0,0,0,0,0,0,0),
	'20160401' => array(1,0,0,1,1,1,2),
	'20160402' => array(0,0,0,1,1,1,1),
	'20160403' => array(0,1,0,1,1,1,2),
	'20160404' => array(0,0,0,1,1,1,2),
	'20160405' => array(0,0,1,1,1,1,1),
	'20160406' => array(0,0,0,1,1,1,1),
	'20160407' => array(0,0,0,1,1,1,1),
);

// 算出下一天累计奖品总数
$the_time = array();//活动期间的中奖数
$mount_award_num = array();//每天累计的中奖数

foreach ($each_day_award as $day => $award_num) {
    foreach ($award_num as $key => $value) {
        if (!isset($mount_award_num[$key])) {
            $mount_award_num[$key] = 0;
        }
        $mount_award_num[$key] += $value;
    }
    $the_time[$day] = $mount_award_num;
}

var_dump($the_time);

$sum = 2000; //评估每日参加活动人数
$r_cache_time = '5184000';//redis缓存时间为两个月

ini_set('default_socket_timeout', -1);
load_helper('task');
$worker = get_task_worker();
$worker->addFunction("guessappbattle_lottery", "get_num");
while ($worker->work());

/*
** 返回中奖信息数据，一等奖：award_level=1，依次类推
*/

function get_num($jobs) {
	global $redis;
	global $model;
    global $the_time;
	global $sum;
	global $r_cache_time;
	
	$redis->pingConn();
	
	$now = date('Ymd');//当天日期
	$param_arr = json_decode($jobs->workload(), true);
	$imsi = $param_arr['imsi'];
	$aid = $param_arr['aid'];
	
	// 奖品等级个数
	$option = array(
		'where' => array(
			'aid' => $aid,
			'status' => 1
		),
		'cache_time' => '600',
		'order' => 'level',
		'table' => 'brand_general_lottery_prize',
	);
	$ret = $model->findAll($option, 'lottery/lottery');
	$award_config = array();
	foreach ($ret as $row) {
		$level = $row['level'];
		$award_config[$level] = $row;
	}
	$award_level_count = count($award_config);//中奖等级数量
	
	$return_arr = array();
	
	if (empty($imsi) || empty($aid)) {
		$return_arr['award_level']= $award_level_count + 1;
		return json_encode($return_arr);
	}
	
	// 截止今天剩余奖品个数缓存
	$rkey_today_award_left = "guessappbattle:{$aid}:{$now}:award_left";
	$today_award_left = $redis->gethash($rkey_today_award_left);
	if (empty($today_award_left)) {
		// 实时统计入缓存
		$option = array(
			'field' => 'level, count(*) as count',
			'group' => 'level',
			'table' => 'brand_general_lottery_award',
		);
		$list = $model->findAll($option, 'lottery/lottery');
		$already_use = array();
		foreach ($list as $row) {
			$level = $row['level'];
			$count = $row['count'];
			$already_use[$level - 1] = $count;
		}
		$today_award_left = array();
		foreach ($the_time[$now] as $key => $value) {
			$left = $value - $already_use[$key];
			if ($left >= 0) {
				$today_award_left[$key] = $left;
			} else {
				$today_award_left[$key] = 0;
			}
		}
		$redis->sethash($rkey_today_award_left, $today_award_left, $r_cache_time);
	}
	$award_level = lottery($today_award_left, $sum);
	$award_level++;//1=>一等奖，。。。7=>7等奖
	if ($award_level > $award_level_count) {
		$return_arr['award_level']= $award_level_count + 1;
		return json_encode($return_arr);
	}
	// 中奖了
	// 更新缓存
	$today_award_left[$award_level - 1] = $today_award_left[$award_level - 1] - 1;
	$redis->sethash($rkey_today_award_left, $today_award_left, $r_cache_time);
	// 扫数据库，查看此奖项是不是真有剩（防止缓存出错）
	$option = array(
		'where' => array(
			'level' => $award_level,
		),
		'field' => 'level, count(*) as count',
		'group' => 'level',
		'table' => 'brand_general_lottery_award',
	);
	$list = $model->findOne($option, 'lottery/lottery');
	$award_used_count = empty($list['count']) ? 0 : $list['count'];
	if ($award_used_count >= $the_time[$now][$award_level - 1]) {
		// 真没奖了，一般不会走到这的
		$return_arr['award_level']= $award_level_count + 1;
		return json_encode($return_arr);
	}
	// 确定有奖剩了
	// 查看是否为礼包类型奖，如果是，还要从redis中读出卡号、卡密
	$award_type = $award_config[$award_level]['type'];
	$no_recur = $award_config[$award_level]['no_recur'];
	$package = $card_no = $card_pwd = '';
	if (!empty($award_config[$award_level]['package'])) {
		$package = $award_config[$award_level]['package'];
	}
	if ($award_type == 2 || $award_type == 3) {
		if ($no_recur) {
			// 不允许重复
			// 中的是礼包，判断此用户之前是否中过此奖，如果中过，则返回没中奖
			$rkey_user_gift_win = "activity:{$aid}:giftwin:{$imsi}:{$award_level}";
			$user_gift_win = $redis->get($rkey_user_gift_win);
			if (!empty($user_gift_win)) {
				// 中过礼包奖，礼包奖数量还原+1
				$today_award_left[$award_level - 1] = $today_award_left[$award_level - 1] + 1;
				$redis->sethash($rkey_today_award_left, $today_award_left, $r_cache_time);
				// 返回没有中奖
				$return_arr['award_level']= $award_level_count + 1;
				return json_encode($return_arr);
			}
			// 写缓存，表明此用户已中过此礼包奖
			$redis->set($rkey_user_gift_win, 1, $r_cache_time);
		}
	}
	if ($award_type == 2) {
		// 是有礼包的礼包奖，需要去取礼包码
		// 中了礼包，从redis的礼包列表里pop一个值出来
		$gift_table = 'brand_general_gift_list';
		$rkey_gift_list = "guessappbattle:{$aid}:" . $package . ":gift_list";
		$gift_content = $redis->rpop($rkey_gift_list);
		$gift_content = json_decode($gift_content, true);
		if (empty($gift_content)) {
			// 还是没奖了
			$return_arr['award_level']= $award_level_count + 1;
			return json_encode($return_arr);
		}
		$gift_id = $gift_content['id'];
		if (!empty($gift_content['package'])) {
			$package = $gift_content['package'];
		}
		if (!empty($gift_content['gift_card_no'])) {
			$card_no = $gift_content['gift_card_no'];
		}
		if (!empty($gift_content['gift_card_pwd'])) {
			$card_pwd = $gift_content['gift_card_pwd'];
		}
		// 更新表guessappbattle_gift_list
		$update_data = array(
			'status' => 0,
			'__user_table' => $gift_table
		);
		$update_where = array(
			'id' => $gift_id
		);
		$model->update($update_where, $update_data, 'lottery/lottery');
	} else if ($award_type == 3) {
		// 没有礼包的礼包奖，不用去取礼包码
	}
	
	// 写表
	$status = ($award_type == 2 || $award_type == 3) ? 1 : 0;//礼包类奖品status置1，其他置0
	$pid = $award_config[$award_level]['pid'];
	$data = array(
		'imsi' => $imsi,
		'level' => $award_level,
		'package' => $package,
		'gift_card_no' => $card_no,
		'gift_card_pwd' => $card_pwd,
		'time' => time(),
		'status' => $status,
		'aid' => $aid,
		'pid' => $pid,
		'__user_table' => 'brand_general_lottery_award',
	);
	$ret = $model->insert($data, 'lottery/lottery');
	
	$return_arr['award_level']= $award_level;
	$return_arr['package'] = $package;
	$return_arr['gift_card_no']= $card_no;
	$return_arr['gift_card_pwd']= $card_pwd;
	$return_arr['award_id']= $ret;
	
	return json_encode($return_arr);
	
}

function lottery($gift_base, $sum) {
    $gift_line = array();
    $nows = 0;
	if(!$gift_base){
		return count($gift_base);
	}
    foreach ($gift_base as $v) {
        $gift_line[] = array($nows+1, $nows+$v);
        $nows += $v;
    }
    $rand = mt_rand(1, $sum);
    foreach ($gift_line as $k => $v) {
        if ($rand >= $v[0] && $rand <= $v[1]) {
            return $k;
        }
    }
    return $k+1;
}