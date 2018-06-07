<?php
/*
** 活动页面初始页
 */
require_once(dirname(realpath(__FILE__)).'/init.php');

// 记录活动页面日志
$log_data = array(
		'imsi' => $imsi,
		'device_id' => $_SESSION['DEVICEID'],
		'activity_id' => $aid,
		'ip' => $_SERVER['REMOTE_ADDR'],
		'sid' => $sid,
		'time' => time(),
		'key' => 'show_page'
	);
permanentlog($activity_log_file, json_encode($log_data));

if(!$imsi_status) {
	// 跳转到提示页面
	$tplObj->out['list'] = get_ap_list($page_id);
	$tplObj->display("lottery/cmt_reply/no_sim.html");
	exit;
}

// 判断版本是否符合要求
$cid = $_SESSION['MODEL_CID'];
$alone_update = $_SESSION['alone_update'];
$version_code = $result['version_code'] ? $result['version_code'] : 6000;
if ($_SESSION['VERSION_CODE'] < $version_code) {
	// 版本号小于6.0，需要检查是否为独立更新包，如果是独立更新包，需要判断能否升级
	if ($alone_update) {
		// 独立更新包
		$channel_option = array(
			'where' => array(
				'cid' => $cid,
				'status' => 1,
				'version_code' => array('exp', ">={$version_code}"),
				'limit_rules' => array('exp', " ='' or limit_rules is null "),
			),
			'cache_time' => 3600,
			'table' => 'sj_market',
		);
		$channel_result = $model->findAll($channel_option);
		if (!empty($channel_result)) {
			$check_status = 3; //在[管理-设置]中检查更新
		} else {
			$check_status = 4; //不可以升级
		}
	} else {
		// 非独立更新包
		$check_status = 2; //点击马上升级按钮
		$intro_option = array(
			'where' => array(
				'package' => 'cn.goapk.market'
			),
			'field' => 'softid, softname, version_code',
			'order' => 'softid DESC',
			'limit' => 1,
			'cache_time' => 86400,
			'table' => 'sj_soft'
		);
		$intro_result = $model->findOne($intro_option);

		$intro_size_option = array(
			'where' => array(
				'softid' => $intro_result['softid']
			),
			'field' => 'filesize',
			'table' => 'sj_soft_file',
			'cache_time' => 86400
		);
		$intro_size_result = $model->findOne($intro_size_option);
		$intro_result['soft_sizes'] = formatFileSize('', $intro_size_result['filesize']);
		$intro_result['soft_size'] = $intro_size_result['filesize'];
		$tplObj->out['intro_result'] = $intro_result;
	}
} else {
	$check_status = 1;
}
// 返回版本状态
$tplObj->out['check_status'] = $check_status;
if ($check_status != 1) {
	// 跳转到提示升级页面
	$tplObj->out['list'] = get_ap_list($page_id);
	$tplObj->display("lottery/cmt_reply/update.html");
	exit;
}