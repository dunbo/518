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
$prefix = 'wzcq';
if($_GET['pos']){
	$log_data = array(
		"activity_id" => $active_id,
		"chl_cid" => $_SESSION['CHL_CID'] ? $_SESSION['CHL_CID'] : '',
		"ip" => $_SERVER['REMOTE_ADDR'],
		"sid" => $sid,
		"time" => $time,
		"pos" => $_GET['pos'],//1链接2点击软件图片3复制
		"pkg" => $_GET['pkg'],
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

$tplObj -> out['aid'] = $active_id;
$tplObj -> out['now'] = $time;
$tplObj -> out['sid'] = $_GET['sid'];
$tplObj -> out['static_url'] = $configs['static_url'];
$tplObj -> out['new_static_url'] = $configs['new_static_url'];
$tplObj -> out['version_code'] = $_SESSION['VERSION_CODE'];
$tplObj -> out['img_url']  = getImageHost();
$soft_info = get_soft_info('',$configs['is_test']);
$tplObj -> out['soft_info']  =  $soft_info;
$tplObj -> out['is_test'] = $configs['is_test'];
$tplObj -> out['prefix'] = $prefix;
$tpl = "lottery/wzcq/index_sem.html";	
$tplObj -> display($tpl);
function get_soft_info($wzcq_pkg,$is_test){
	global $model,$redis,$prefix,$active_id;
	$soft_info_key = $prefix.":".$active_id.':soft_info';
	$soft_info = $redis->get($soft_info_key);	
	if($soft_info === null){
		if($is_test){
			$pkg_arr = array(
				'com.knetp.lom2jni' => '无传奇不兄弟，万人同屏，决战王者。',
				'cn.jj' => '登陆随机赠送英雄皮肤',
				'com.netease.l10.anzhi' => '登陆即送杨洋同款妖狐装',
				'mgyly1.anzhi' => "登陆即送<span>688</span>元独家礼包",
			);			
		}else{
			$pkg_arr = array(
				'com.tencent.tmgp.sgame' => '登陆送随机英雄皮肤',
				'com.lilithgame.sgame.anzhi' => '首充免费领取<span>558</span>豪华礼包',
				'com.pokercity.bydrqp.anzhi' => "海量金币免费领，百万话费送不停",
				'com.shenghe.wzcq.zqy.anzhi' => "下载就送<span>998</span>独家大礼包",
				'com.happyelements.AndroidAnimal' => "下载即送<span>38</span>元新手好礼",
				'com.netease.l10.anzhi' => '登陆领取<span>1288</span>新手大礼',
				'com.netease.my.anzhi' => '无处不在，超过1亿玩家的选择',
			);			
		}
		$option = array(
			'table' => 'sj_soft AS A',
			'where' => array(
				'A.status' => 1,
				'A.hide' => 1,
				'A.package' => array_keys($pkg_arr),
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
			$v['describe'] = $pkg_arr[$v['package']];
			$soft_info[$v['package']] = $v;
		}
		$redis->set($soft_info_key,$soft_info,30*60);	
	}
	return $soft_info;
}