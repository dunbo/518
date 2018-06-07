<?php
/*
** 页面加载此
*/
require_once(dirname(realpath(__FILE__)) . '/init.php');

// session是否已失效
if (empty($_SESSION['VERSION_CODE'])) {
	// 跳转到提示页面
	$tplObj->display("lottery/commentreply/page_expired_hint.html");
	exit;
}

// 判断版本是否大于等于6000
$cid = $_SESSION['MODEL_CID'];
$alone_update = $_SESSION['alone_update'];

if ($_SESSION['VERSION_CODE'] < 6000) {
	// 版本号小于6.0，需要检查是否为独立更新包，且如果是独立更新包，需要判断能否升级
	if ($alone_update) {
		// 独立更新包
		$channel_option = array(
			'where' => array(
				'cid' => $cid,
				'status' => 1,
				'version_code' => array('exp','>=6000'),
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
		'imsi' => $_SESSION['USER_IMSI'],
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
	$tplObj->display("lottery/commentreply/update_hint.html");
	exit;
}