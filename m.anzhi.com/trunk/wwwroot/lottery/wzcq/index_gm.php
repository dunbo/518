<?php
include_once (dirname(realpath(__FILE__)).'/../../init.php');
$config = load_config('lottery_cache/redis',"lottery");
if ($config) {
	$redis = new GoRedisCacheAdapter($config);
} else {
	$redis = GoCache::getCacheAdapter('redis');
}
$model = new GoModel();
$time = time();
$active_id = $_GET['aid'] ? $_GET['aid'] : $_POST['aid'];
//ctype_digit  检查时候是只包含数字字符的字符串（0-9）
if(!ctype_digit($active_id)){
	echo '活动id无效';
	exit;
}

$sid = $_GET['sid'] ? $_GET['sid'] : $_POST['sid'];
session_begin($sid);
if(!$sid){
	$chl_cid =  0;
	$tplObj -> out['chl_cid'] = $chl_cid;
	$chl_config = array(
		0 => '75761ddc3938',//景美20
	);
	$_SESSION['CHL_CID'] = $chl_config[$chl_cid];
}		
$prefix = 'wzcq';
if($_GET['pos']){
	$log_data = array(
		"activity_id" => $active_id,
		"chl_cid" => $_SESSION['CHL_CID'] ? $_SESSION['CHL_CID'] : '',
		"ip" => $_SERVER['REMOTE_ADDR'],
		"sid" => $sid,
		"time" => $time,
		"pos" => $_GET['pos'],//1王者荣耀2全民抢战3皇室战争
		"pkg" => $_GET['pkg'],
		"is_web" => $_GET['is_web'] ? $_GET['is_web'] : 0,
		'key' => 'go_jump',
	);
	permanentlog('activity_'.$active_id.'.log', json_encode($log_data));
	exit;
}
//日志
$log_data = array(
	"imsi"	=>	$_SESSION['USER_IMSI'],
	"device_id"		=>	$_SESSION['DEVICEID'],
	"chl_cid" => $_SESSION['CHL_CID'] ? $_SESSION['CHL_CID'] : '',
	"mac" 	=>  $_SESSION['MAC'],
	"ip"	=>	$_SERVER['REMOTE_ADDR'],
	"sid"	=>	$sid,
	"time"	=>	$time,
	"user"	=>	$_SESSION['USER_UID'] && $_SESSION['USER_ID'] != 13176 ? $_SESSION['USER_NAME'] : '',
	'uid'	=>	$_SESSION['USER_UID'] && $_SESSION['USER_ID'] != 13176 ? $_SESSION['USER_UID'] : '',
	'key'	=>	'show_homepage'
);
permanentlog('activity_'.$active_id.'.log', json_encode($log_data));
$jumpid_conf = array(
	1 => array(
		'jumpid' => $configs['is_test'] ? 481 : 242,
		'pkg' => $configs['is_test'] ? 'com.knetp.lom2jni' : 'com.tencent.tmgp.sgame',
	),
	2 => array(
		'jumpid' => $configs['is_test'] ? 478 : 244,
		'pkg' => $configs['is_test'] ? 'cn.jj' : 'com.crisisfire.android.anzhi',		
	),
	3 => array(
		'jumpid' => $configs['is_test'] ? 482 : 243,
		'pkg' => $configs['is_test'] ? 'com.netease.l10.anzhi' : 'com.supercell.clashroyale.anzhi',		
	),
);
$tplObj -> out['jumpid_conf'] = $jumpid_conf;
$tplObj -> out['aid'] = $active_id;
$tplObj -> out['now'] = $time;
$tplObj -> out['sid'] = $_GET['sid'];
$tplObj -> out['static_url'] = $configs['static_url'];
$tplObj -> out['new_static_url'] = $configs['new_static_url'];
$tplObj -> out['version_code'] = $_SESSION['VERSION_CODE'];
$tplObj -> out['img_url']  = getImageHost();
$soft_info = get_soft_info($configs['is_test']);
$tplObj -> out['soft_info']  =  $soft_info;
$tplObj -> out['is_test'] = $configs['is_test'];
$tplObj -> out['prefix'] = $prefix;
if(!$sid){
	if($configs['is_test']){
		$tplObj -> out['chl_cid_str'] = '4fb52a893294';
	}else{
		$tplObj -> out['chl_cid_str'] = $chl_config[$chl_cid];
	}
	$tpl = "lottery/wzcq/index_gm_web.html";	
}else{
	$tpl = "lottery/wzcq/index_gm.html";	
}
$tplObj -> display($tpl);
function get_soft_info($is_test){
	global $model,$redis,$prefix,$active_id;
	$soft_info_key = $prefix.":".$active_id.':soft_info';
	$soft_info = $redis->get($soft_info_key);	
	if($soft_info === null){
		if($is_test){
			$pkg_arr = array('com.knetp.lom2jni','cn.jj','com.netease.l10.anzhi');
		}else{
			$pkg_arr = array('com.tencent.tmgp.sgame','com.crisisfire.android.anzhi','com.supercell.clashroyale.anzhi');
		}
		$option = array(
			'table' => 'sj_soft AS A',
			'where' => array(
				'A.status' => 1,
				'A.hide' => 1,
				'A.package' => $pkg_arr,
				'B.package_status' => 1,
			),
			'join' => array(
				'sj_soft_file AS B' => array(
					'on' => array('A.package','B.apk_name'),
				)
			),
			'field' => 'A.softid,A.softname,A.package,A.version_code,A.version_code,A.total_downloaded,B.iconurl_125,B.filesize',
			'order' => 'A.softid desc',
			'group' => 'A.package',
		);	
		$softinfo = $model->findAll($option);
		$soft_info = array();
		foreach($softinfo as $k => $v){
			$v['iconurl'] = getImageHost().$v['iconurl_125'];
			$v['describe'] = $describe[$v['package']];
			$soft_info[$v['package']] = $v;
		}
		$redis->set($soft_info_key,$soft_info,30*60);	
	}
	return $soft_info;
}