<?php

/*
** 我要思春/发春/春快来
*/

include_once (dirname(realpath(__FILE__)).'/spring_init.php');

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

if ($imsi == '') {
    $tplObj -> display('spring_share.html');
    exit;
}

// 不同日期展示的内容不一样
$now = time();

if ($now < 1423584000) {//2月11日凌晨时间戳
    $spring_step = 1;//思春
} else if ($now < 1424102400) {//2月17日凌晨时间戳
    $spring_step = 2;//发春
} else {
    $spring_step = 3;//春快来
}
$tplObj -> out['spring_step'] = $spring_step;

// 今天的日期
$now_day = date('Ymd');
// 今天是否分享过，从redis中读出来，今天分享过则进入抽奖页按钮可点击
$ever_share =  0;
$last_share_day = $redis->hget($rkey_imsi_ever_share, 'last_share_day');
if (!empty($last_share_day) && $last_share_day == $now_day) {
    $ever_share =  1;
}

$tplObj->out['ever_share'] = $ever_share;
$tplObj -> display('spring_share.html');
exit;