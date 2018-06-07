<?php
/*
** 活动页面需加载此页面，主要处理页面跳转逻辑
*/

include_once (dirname(realpath(__FILE__)).'/octoberflight_init.php');

// 判断当前时间活动是否已结束，结束则跳转到结束页面（结束页面不能include此文件！）
if ($now > $activity_end_time && $_GET['notjump'] != 1) {
	// 超出活动日期，超出后在这里跳转
	header('location:/lottery/octoberflight_end.php?sid='.$sid);
	exit;
}

// 判断是否有imsi
if ($imsi_status != 1) {
	$tplObj->display('octoberflight_no_sim.html');
	exit;
}

// 判断版本是否大于等于5500
$cid = $_SESSION['MODEL_CID'];
$alone_update = $_SESSION['alone_update'];

if ($_SESSION['VERSION_CODE'] < 5500) {
	// 版本号小于5.5，需要检查是否为独立更新包，且如果是独立更新包，需要判断能否升级
	if ($alone_update) {
		// 独立更新包
		$channel_option = array(
			'where' => array(
				'cid' => $cid,
				'status' => 1,
				'version_code' => array('exp','>=5500'),
				'limit_rules' => array('exp'," ='' or limit_rules is null "),
			),
			'cache_time' => 3600,
			'table' => 'sj_market',
		);
		$channel_result = $model -> findAll($channel_option);
		if (!empty($channel_result)) {
			$check_status = 3;
		} else {
			$check_status = 4;
		}
	} else {
		$check_status = 2;
		// 非独立更新包
		$intro_option = array(
			'where' => array(
				'package' => 'cn.goapk.market'
			),
			'field' => 'softid,softname,version_code',
			'order' => 'softid DESC',
			'limit' => 1,
			'cache_time' => 86400,
			'table' => 'sj_soft'
		);
		$intro_result = $model -> findOne($intro_option);

		$intro_size_option = array(
			'where' => array(
				'softid' => $intro_result['softid']
			),
			'field' => 'filesize',
			'table' => 'sj_soft_file',
			'cache_time' => 86400
		);
		$intro_size_result = $model -> findOne($intro_size_option);
		$intro_result['soft_sizes'] = formatFileSize('',$intro_size_result['filesize']);
		$intro_result['soft_size'] = $intro_size_result['filesize'];
		$tplObj -> out['intro_result'] = $intro_result;
	}
} else {
	$check_status = 1;
}

// 版本状态
$tplObj->out['check_status'] = $check_status;
if ($tplObj->out['check_status'] != 1) {
	// 记日志
	$log_data = array(
		'imsi' => $imsi,
		'device_id' => $_SESSION['DEVICEID'],
		'activity_id' => $aid,
		'ip' => $_SERVER['REMOTE_ADDR'],
		'sid' => $_GET['sid'],
		'time' => time(),
		'users' => '',
		'uid' => '',
		'key' => 'update_page'
	);
	permanentlog($activity_log_file, json_encode($log_data));
	// 跳转到提示升级页面
	$tplObj->display("octoberflight_update_hint.html");
	exit;
}

$lottery_num = get_lottery_num();

// 检查用户是否为当天的第一次访问
$open_info = $redis->gethash($rkey_imsi_open_info);
if (empty($open_info)) {
	// 从库里读出来
	$option = array(
		'where' => array(
			'imsi' => $imsi,
		),
		'table' => 'octoberflight_open_info'
	);
	$find = $model->findOne($option, 'lottery/lottery');
	if (!empty($find)) {
		$last_open_day = $find['last_open_day'];
		$open_times = $find['open_times'];
	} else {
		$last_open_day = '';
		$open_times = 0;
	}
} else {
	$last_open_day = $open_info['last_open_day'];
	$open_times = $open_info['open_times'];
}

// 记录访问时间与次数，访问次数为5的位数时增加可抽奖次数
if ($last_open_day != $today) {
	$last_open_day = $today;
	$open_times++;
	// 更新缓存
	$open_info = array(
		'last_open_day' => $last_open_day,
		'open_times' => $open_times,
	);
	$redis->sethash($rkey_imsi_open_info, $open_info, $r_cache_time);
	// 更新数据库
	$ret = $model->query("insert into `octoberflight_open_info` (`imsi`,`last_open_day`,`open_times`) values ('{$imsi}','{$last_open_day}','{$open_times}') ON DUPLICATE KEY UPDATE last_open_day='{$last_open_day}', open_times={$open_times};", 'lottery/lottery');
	
	// 更新可抽奖次数
	if ($open_times && $open_times % 5 == 0) {
		$lottery_num += $open_times/5 + 1;
	}
	
	set_lottery_num($lottery_num);
}


