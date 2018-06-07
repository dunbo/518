<?php

include dirname(__FILE__).'/../init.php';
$config = load_config('lottery_cache/redis',"lottery");
if ($config) {
	$redis = new GoRedisCacheAdapter($config);
} else {
	$redis = GoCache::getCacheAdapter('redis');
}

$model = new GoModel();

$activity_option = array(
    'where' => array(
        'config_type' => 'OCTOBERFLIGHT_LOTTERY_AWARD',
        'status' => 1
    ),
    'cache_time' => 86400,
    'table' => 'pu_config'
);
$result = $model -> findOne($activity_option);
$activity_config = json_decode($result['configcontent'],true);

$active_id = $activity_config['activity_id'];//活动id
$award_config = $activity_config['award_config'];

var_dump($award_config);

// 奖品等级个数
$award_level_count = count($award_config);


// 线上中奖概率
$each_day_award = array(
	'20150928' => array(0,0,0,0,0,0,0),
	'20150929' => array(0,0,0,0,0,0,0),
	'20150930' => array(0,1,1,0,1,5,1500),
	'20151001' => array(0,0,0,0,0,2,1300),
	'20151002' => array(0,0,0,0,0,2,1300),
	'20151003' => array(0,0,1,1,0,2,1500),
	'20151004' => array(0,0,0,0,0,3,1300),
	'20151005' => array(0,0,0,0,0,2,1300),
	'20151006' => array(1,0,1,0,1,2,1500),
	'20151007' => array(0,0,0,0,0,3,1300),
	'20151008' => array(0,0,0,0,0,3,1300),
	'20151009' => array(0,0,0,0,0,2,1000),
	'20151010' => array(0,0,0,0,0,5,1000),
	'20151011' => array(0,0,1,0,1,3,1500),
	'20151012' => array(0,0,0,0,0,3,1000),
	'20151013' => array(0,0,0,0,0,3,1000),
	'20151014' => array(0,0,1,0,1,5,1500),
	'20151015' => array(0,0,0,0,0,5,700),
);

// 算出下一天累计奖品总数
$the_time = array();
$mount_award_num = array();
for ($i = 0; $i < $award_level_count; $i++) {
    $mount_award_num[$i] = 0;
}

foreach ($each_day_award as $day => $award_num) {
    if (count($award_num) != $award_level_count) {
        var_dump("award_num wrong");
        exit;
    }
    foreach ($award_num as $key => $value) {
        if (!isset($mount_award_num[$key])) {
            var_dump("award_num wrong");
            exit;
        }
        $mount_award_num[$key] += $value;
    }
    $the_time[$day] = $mount_award_num;
}

var_dump($the_time);

$sum = 5000; //评估每日参加活动人数
$r_cache_time = '5184000';//redis缓存时间为两个月

ini_set('default_socket_timeout', -1);
load_helper('task');
$worker = get_task_worker();
$worker->addFunction("octoberflight_lottery", "get_num");
while ($worker->work());

/*
** 返回中奖信息数据，一等奖：award_level=1，依次类推
*/

function get_num($jobs) {
	global $redis;
	global $model;
	global $active_id;
    global $the_time;
    global $award_level_count;//中奖等级数量
	global $award_config;
	global $sum;
	global $r_cache_time;
	
    
	$now = date('Ymd');//当天日期
	$imsi = $jobs->workload();
	
	$return_arr = array();
	
	if (empty($imsi)) {
		$return_arr['award_level']= $award_level_count + 1;
		return json_encode($return_arr);;
	}
	
	// 截止今天剩余奖品个数缓存
	$rkey_today_award_left = "octoberflight:{$active_id}:{$now}:award_left";
	$today_award_left = $redis->gethash($rkey_today_award_left);
	if (empty($today_award_left)) {
		// 实时统计入缓存
		$option = array(
			'field' => 'award, count(*) as count',
			'group' => 'award',
			'table' => 'octoberflight_lottery_award',
		);
		$list = $model->findAll($option, 'lottery/lottery');
		$already_use = array();
		foreach ($list as $row) {
			$award = $row['award'];
			$count = $row['count'];
			$already_use[$award - 1] = $count;
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
			'award' => $award_level,
		),
		'field' => 'award, count(*) as count',
		'group' => 'award',
		'table' => 'octoberflight_lottery_award',
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
	$award_type = $award_config[$award_level - 1][2];
	$package = $card_no = $card_pwd = '';
	if (!empty($award_config[$award_level - 1][5])) {
		$package = $award_config[$award_level - 1][5];
	}
	$gift_table = !empty($package) ? 'octoberflight_gift_list' : 'octoberflight_gift_list_common';
	if ($award_type == 2) {
		// 中的是礼包，判断此用户之前是否中过此奖，如果中过，则返回没中奖
		$rkey_user_gift_win = "octoberflight:{$active_id}:giftwin:{$imsi}:{$award_level}";
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
		
		$card_no = $card_pwd = '';
		/*
		// 中了礼包，从redis的礼包列表里pop一个值出来
		$rkey_gift_list = "octoberflight:{$active_id}:" . $package . ":gift_list";
		$gift_content = $redis->rpop($rkey_gift_list);
		$gift_content = json_decode($gift_content, true);
		if (empty($gift_content)) {
			// 还是没奖了
			$return_arr['award_level']= $award_level_count + 1;
			return json_encode($return_arr);;
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
		// 更新表octoberflight_gift_list
		$update_data = array(
			'status' => 0,
			'__user_table' => $gift_table
		);
		$update_where = array(
			'id' => $gift_id
		);
		$model->update($update_where, $update_data, 'lottery/lottery');
		*/
	}
	
	// 写表
	$status = ($award_type == 2) ? 1 : 0;//礼包类奖品status置1，其他置0
	$data = array(
		'imsi' => $imsi,
		'award' => $award_level,
		'package' => $package,
		'gift_card_no' => $card_no,
		'gift_card_pwd' => $card_pwd,
		'time' => time(),
		'status' => $status,
		'__user_table' => 'octoberflight_lottery_award',
	);
	$model->insert($data, 'lottery/lottery');
	$return_arr['award_level']= $award_level;
	$return_arr['package'] = $package;
	$return_arr['gift_card_no']= $card_no;
	$return_arr['gift_card_pwd']= $card_pwd;
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