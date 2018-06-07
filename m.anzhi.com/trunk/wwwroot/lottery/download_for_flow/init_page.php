<?php
// 判断imsi和市场版本
include_once(dirname(realpath(__FILE__)).'/init.php');

// 判断是否有imsi
if (!$imsi && !$_GET['is_share']) {
	$tplObj->display("lottery/{$prefix}/no_sim.html");
	exit;
}

// 判断版本是否符合要求
$cid = $_SESSION['MODEL_CID'];
$alone_update = $_SESSION['alone_update'];
if ($_SESSION['VERSION_CODE'] < 6000 && !$_GET['is_share']) {
	// 版本号小于6.0，需要检查是否为独立更新包，如果是独立更新包，需要判断能否升级
	if ($alone_update) {
		// 独立更新包
		$channel_option = array(
			'where' => array(
				'cid' => $cid,
				'status' => 1,
				'version_code' => array('exp', '>=6000'),
				'limit_rules' => array('exp', " ='' or limit_rules is null "),
			),
			'cache_time' => 3600,
			'table' => 'sj_market',
		);
		$channel_result = $model->findAll($channel_option);
		if (!empty($channel_result)) {
			$check_status = 3; //可以升级
		} else {
			$check_status = 4; //不可以升级
		}
	} else {
		// 非独立更新包
		$check_status = 2; //进入软件详情页
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
if ($check_status != 1 && !$_GET['is_share']) {
	// 跳转到提示升级页面
	$tplObj->display("lottery/{$prefix}/update.html");
	exit;
}