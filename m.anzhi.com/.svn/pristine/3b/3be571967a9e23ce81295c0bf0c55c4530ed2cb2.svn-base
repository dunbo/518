<?php
/*
** 1，统计访问
** 2，imsi为空、或版本号低则统一显示提示页
*/

include_once (dirname(realpath(__FILE__)).'/aprilstrip_init.php');

// 如果活动结束（2015-5-8），则跳转到介结束页
$now = time();
if ($now >= 1431100800) {
	header("location:/lottery/aprilstrip_lottery_end.php?sid={$_GET['sid']}");
	exit;
}

// 如果没有imsi，则跳转到魔镜页
if (!$imsi_status) {
	$tplObj->display("aprilstrip_mirror.html");
	exit;
}

// 检查版本、是否独立更新
$cid = $_SESSION['MODEL_CID'];
$alone_update = $_SESSION['alone_update'];

if ($_SESSION['VERSION_CODE'] < 5410) {
	// 版本号小于5.4.1，需要检查是否为独立更新包，且如果是独立更新包，需要判断能否升级
	if ($alone_update) {
		// 独立更新包
		$channel_option = array(
			'where' => array(
				'cid' => $cid,
				'status' => 1,
				'version_code' => array('exp','>=5410'),
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
		'imei' => $_SESSION['USER_IMEI'],
		'device_id' => $_SESSION['DEVICEID'],
		'ip' => $_SERVER['REMOTE_ADDR'],
		'sid' => $_GET['sid'],
		'time' => time(),
		'key' => 'aprilstrip_update_hint'
	);
	permanentlog($activity_log_file, json_encode($log_data));
	// 跳转到提示升级页面
	$tplObj->display("aprilstrip_update_hint.html");
	exit;
}

// 检查上次访问的时间与总访问次数
$open_info = $redis->gethash($rkey_imis_open_info);
if (empty($open_info)) {
	// 从库里读出来
	$option = array(
		'where' => array(
			'imsi' => $imsi,
		),
		'table' => 'aprilstrip_open_info'
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

// 记录访问时间与次数，访问次数为3的位数时增加可抽奖次数
if ($last_open_day != $today) {
	$last_open_day = $today;
	$open_times++;
	// 上次访问的时间不是今天，访问天数可以+1
	$option = array(
		'where' => array(
			'imsi' => $imsi,
		),
		'table' => 'aprilstrip_open_info'
	);
	$find = $model->findOne($option, 'lottery/lottery');
	if (empty($find)) {
		$insert_data = array(
			'imsi' => $imsi,
			'last_open_day' => $last_open_day,
			'open_times' => $open_times,
			'time' => time(),
			'__user_table' => 'aprilstrip_open_info'
		);
		$model->insert($insert_data, 'lottery/lottery');
	} else {
		$update_data = array(
			'last_open_day' => $last_open_day,
			'open_times' => $open_times,
			'time' => time(),
			'__user_table' => 'aprilstrip_open_info'
		);
		$update_where = array(
			'imsi' => $imsi
		);
		$model->update($update_where, $update_data, 'lottery/lottery');
	}
	// 更新缓存
	$open_info = array(
		'last_open_day' => $last_open_day,
		'open_times' => $open_times
	);
	$redis->sethash($rkey_imis_open_info, $open_info, $r_cache_time);
	// 判断打开天数是否可以增加抽奖机会
	if ($open_times % 3 == 0) {
		$award_num = $open_times/3;
		// 更新抽奖次数表、缓存
		$option = array(
			'where' => array(
				'imsi' => $imsi,
			),
			'table' => 'aprilstrip_lottery_num',
		);
		$find = $model->findOne($option, 'lottery/lottery');
		$now_num = 0;
		if (!empty($find)) {
			$now_num += $find['lottery_num'];
		}
		$new_num = $now_num + $award_num;
		if (empty($find)) {
			$data = array(
				'imsi' => $imsi,
				'lottery_num' => $new_num,
				'time' => time(),
				'__user_table' => 'aprilstrip_lottery_num'
			);
			$model->insert($data, 'lottery/lottery');
		} else {
			$data = array(
				'imsi' => $imsi,
				'lottery_num' => $new_num,
				'time' => time(),
				'__user_table' => 'aprilstrip_lottery_num'
			);
			$where = array(
				'imsi' => $imsi,
			);
			$model->update($where, $data, 'lottery/lottery');
		}
		// 更新缓存
		$redis->set($rkey_imsi_lottery_num, $new_num, $r_cache_time);
	}
}