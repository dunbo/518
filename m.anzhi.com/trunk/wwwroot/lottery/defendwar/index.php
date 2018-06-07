<?php

include_once (dirname(realpath(__FILE__)).'/init.php');

// 记日志
$log_data = array(
    'ip' => $_SERVER['REMOTE_ADDR'],
    'time' => time(),
    'key' => 'index'
);
permanentlog($activity_log_file, json_encode($log_data));

// 下载的软件
$all_soft_arr = array(
	// 第一个专题
	array(
		array(
			'package' => 'com.eg.android.AlipayGphone',
			'desc' => '得红包口令，这里压岁钱有的是，快来抢！',
		),
		array(
			'package' => 'com.sina.weibo',
			'desc' => '微博红包随时有，明星福利爽翻天！',
		),
		array(
			'package' => 'com.tencent.mm',
			'desc' => '抢微信红包，收益多多过好节！',
		),
		array(
			'package' => 'com.tencent.mobileqq',
			'desc' => '不用下跪不用送礼，红包自己来喽！',
		),
		array(
			'package' => 'com.immomo.momo',
			'desc' => '认识新朋友，拿到大红包！让你有颜又有钱~',
		),
		array(
			'package' => 'com.taobao.taobao',
			'desc' => '用现金红包淘到各种好货，买到手软！',
		),
	),
	// 第二个专题
	array(
		array(
			'package' => 'com.blogspot.relativescalc',
			'desc' => '面对三姑六婆再也不懵圈了！',
		),
		array(
			'package' => 'cn.xianglianai',
			'desc' => '过年回家不被催婚，安全脱单，好好过年！',
		),
		array(
			'package' => 'net.iaround',
			'desc' => '告别寂寞，终结孤单，遇见心水的TA~',
		),
		array(
			'package' => 'com.p1.mobile.putong',
			'desc' => '左手右手探一探，看对眼就带回家吧！',
		),
		array(
			'package' => 'com.pape.nuannew.lj.anzhi',
			'desc' => '换上最美新装，把她带回家过年吧！',
		),
		array(
			'package' => 'cn.actcap.ayc3.anzhi',
			'desc' => '养成专属你的女神，过年有它不寂寞！',
		),
	),
	// 第三个专题
	array(
		array(
			'package' => 'ctrip.android.view',
			'desc' => '假期这么长，来场说走就走的旅行！',
		),
		array(
			'package' => 'com.dp.android.elong',
			'desc' => '住宿旅行全搞定，轻松玩爽整个假期！',
		),
		array(
			'package' => 'com.taobao.trip',
			'desc' => '一键预订，让你一路放心！畅游天下！',
		),
		array(
			'package' => 'com.gift.android',
			'desc' => '春节旅行攻略为你准备好，快粗发！',
		),
		array(
			'package' => 'com.breadtrip',
			'desc' => '记录分享你的旅行体验，做最文艺旅行家！',
		),
		array(
			'package' => 'com.qyer.android.jinnang',
			'desc' => '出境游也有折扣，安全放心很便宜！',
		),
	),
);

foreach ($all_soft_arr as $feature => $soft_arr) {
	foreach ($soft_arr as $key => $soft) {
		$package = $soft['package'];
		// 根据包名取得最新的软件id
		$r = gomarket_action ( 'soft.GoGetSoftDetailPackage', array ('PACKAGE_NAME' => $package,'EXTRA_OPTION_FIELD' => array('isoffice')));
		$softid = $r['ID'];
		if (!empty($softid)) {
			$softinfo = $model->getsoftinfos($softid, getFilterOption());
			if (!empty($softinfo)) {
				$softinfo = $softinfo[$softid];
				$softinfo['iconurl'] = $img_host . $softinfo['iconurl'];
				$softinfo['down_url'] = "/download.php?softid={$softid}";
				
				// 补充
				$all_soft_arr[$feature][$key]['softid'] = $softid;
				if (empty($soft['softname'])) {
					$all_soft_arr[$feature][$key]['softname'] = $softinfo['softname'];
				}
				if (empty($soft['desc'])) {
					$all_soft_arr[$feature][$key]['desc'] = $softinfo['desc'];
				}
				if (empty($soft['iconurl'])) {
					$all_soft_arr[$feature][$key]['iconurl'] = $softinfo['iconurl'];
				}
				if (empty($soft['down_url'])) {
					$all_soft_arr[$feature][$key]['down_url'] = $softinfo['down_url'];
				}
			}
		}
	}
}

$tplObj->out['all_soft_arr'] = $all_soft_arr;
$tplObj->display('lottery/defendwar/index.html');

exit;