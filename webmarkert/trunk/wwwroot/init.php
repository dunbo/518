<?php
define('DS', DIRECTORY_SEPARATOR);
define("SERVER_ROOT", dirname(dirname(__FILE__)) . DS);
define('GO_APP_ROOT', dirname(realpath(__FILE__)));
define('PAGE_LIMITE',15);
define('SUBJECT_PAGE_LIMITE',100);
define('GO_UID_DEFAULT', 13176);
define('GO_CONFIG_DIR', GO_APP_ROOT. DS . '..' . DS. 'newgomarket.goapk.com'. DS. 'config');
define('GO_MODEL_DIR', GO_APP_ROOT. DS . '..' . DS. 'newgomarket.goapk.com'. DS. 'model');
include_once GO_APP_ROOT. DS.'..'.DS. 'GoPHP'. DS. 'Startup.php';
define('STATIC_DIR', GO_APP_ROOT.'/html_cache/');
define("IS_STATIC", 1);
define("IMGURL_TRANS",1);
define("ACTION_WEB_DOWNLOAD", 3);
define('LOG','/data/att/permanent_log/www.anzhi.com/');
include(dirname(realpath(__FILE__)).'/function.php');
if(!function_exists('rc4_decode')) include(GO_APP_ROOT. '/../tools/functions.php');
session_start();
$_SESSION['RES'] = 'm'; //offical icon min px
//是否使用新用户中心接入方式
$uc = load_config('uc');
if ($uc['isnew'] == 1) {
	if (!empty($_COOKIE['_AZ_COOKIE_'])) {
		$ucenter = new GoUcenter('www');
		$cookie_data = $ucenter->parse_uc_cookie();
		if (empty($_SESSION['user_data']) || $_SESSION['USER_ID'] != $cookie_data['pid']) {
			$user = $ucenter->token_userinfo();
			if (isset($user['USER_ID']) && isset($user['USER_NAME'])) {
				$_SESSION = array();
				$_SESSION['USER_ID'] = $user['USER_ID'];
				$_SESSION['USER_NAME'] = $user['USER_NAME'];
				$_SESSION['user_data'] = array(
					'login_account' => $cookie_data['loginAccount'],
					'user_name' => $user['USER_NAME'],
					'userid' => $user['USER_ID'],
				);
			} else {
                setcookie('_AZ_COOKIE_', '', time()-31536000, '/', 'anzhi.com');
                setcookie('_AZ_COOKIE_KEY', '', time()-31536000, '/', 'anzhi.com');
            }
		}
	} else {
		if (!empty($_SESSION['user_data'])) {
			unset($_SESSION['USER_ID']);
			unset($_SESSION['USER_NAME']);
			unset($_SESSION['user_data']);
		}
	}
} else {
	if (!empty($_COOKIE['__gosession']) && empty($_SESSION['user_data'])){
		$user = rc4_decode($_COOKIE['__gosession']);
		if (isset($user['USER_ID']) && isset($user['USER_NAME'])) {
			$_SESSION['USER_ID'] = $user['USER_ID'];
			$_SESSION['USER_NAME'] = $user['USER_NAME'];
			$_SESSION['user_data'] = array(
				'user_name' => $user['USER_NAME'],
				'userid' => $user['USER_ID'],
			);
		}
	}
}


//wandoujia,tcl,zte,baidu,tencent,qqhelper,360_game,360_app,baidu,jinliqc

$channel_map = array(
	'wandoujia' => 'wandoujia',
	'www' => 'webmarket',
	'zte' => 'webmarket',
	'360' => '360_app',
	'360_game' => '360_game',
	'360_app' => '360_app',
	'hao123' => 'webmarket',
);
if (!empty($_GET['amp;channel']) && empty($_GET['channel'])) {
    $_GET['channel'] = $_GET['amp;channel'];
}

if ($_SERVER['SERVER_NAME'] == 'hao123pc.anzhi.com') {
	$_GET['channel'] = 'hao123';
}

$channel = isset($_GET['channel']) ? $_GET['channel'] : 'www';
if ($channel == 'wandoujia' || $channel == 'wdj' ) {
	define('TEMPLATE_DIR', dirname(realpath(__FILE__)).'/wandoujia_tpl/');
	define('TEMPLATE_C_DIR', dirname(realpath(__FILE__)).'/wandoujia_tpl_c/');
} elseif($channel == 'zte'){
	define('TEMPLATE_DIR', dirname(realpath(__FILE__)).'/zhongxing_tpl/');
	define('TEMPLATE_C_DIR', dirname(realpath(__FILE__)).'/zhongxing_tpl_c/');
}elseif($channel == '360' || $channel == '360_app' || $channel == '360_game'){
	define('TEMPLATE_DIR', dirname(realpath(__FILE__)).'/360_tpl/');
	define('TEMPLATE_C_DIR', dirname(realpath(__FILE__)).'/360_tpl_c/');
}elseif($channel == 'tcl'){
	define('TEMPLATE_DIR', dirname(realpath(__FILE__)).'/tcl_tpl/');
	define('TEMPLATE_C_DIR', dirname(realpath(__FILE__)).'/tcl_tpl_c/');
}elseif($channel == 'qqhelper'){
	define('TEMPLATE_DIR', dirname(realpath(__FILE__)).'/tencent_tpl/');
	define('TEMPLATE_C_DIR', dirname(realpath(__FILE__)).'/tencent_tpl_c/');
}elseif ($channel == 'assistant'){
	define('TEMPLATE_DIR', dirname(realpath(__FILE__)).'/assistant_tpl/');
	define('TEMPLATE_C_DIR', dirname(realpath(__FILE__)).'/assistant_tpl_c/');
}elseif($channel == 'TG'){
	define('TEMPLATE_DIR', dirname(realpath(__FILE__)).'/tuiguang/');
	define('TEMPLATE_C_DIR', dirname(realpath(__FILE__)).'/tuiguang_tpl_c/');
}else{
	define('TEMPLATE_DIR', dirname(realpath(__FILE__)).'/tpl/');
	define('TEMPLATE_C_DIR', dirname(realpath(__FILE__)).'/tpl_c/');
}
define('UC_SERVER', 'http://bbs.anzhi.com/uc_server/');
define('GOAPK_IMG_HOST',getImageHost());
define('DOWNLOAD_NO_SOFT', -1);
define('DOWNLOAD_IPBANNED', -2);
//修改文件域名
$tplObj -> out['img_url'] = load_config('m_goapk_img');
load_helper('utiltool');
$chl_code = '902ba004d1140689294d3f2492ec4a07c82b7450';//默认渠道
$chl_array = array(
    'www' => '902ba004d1140689294d3f2492ec4a07c82b7450',
	'360' => '1eb231ac5c073ba76a2d0ba6df5d634c9750449c',
	'360_app' => '1eb231ac5c073ba76a2d0ba6df5d634c9750449c',
	'360_game' => '1eb231ac5c073ba76a2d0ba6df5d634c9750449c',
	'wandoujia' => '85ec11c52cfd7bfd3b54ff63cbf0d07e46640b5c',
	'wdj' => '85ec11c52cfd7bfd3b54ff63cbf0d07e46640b5c',
	'zte' => 'aefd63a96719e294e7901ea670672817d7fd179d',//中兴
	'tcl' => 'ad6666670de56a2544070a181810a0ea2e780f3a',//
	'qqhelper' => '877d6571e9d34313071385459fb00917788d9e13', //腾讯手机助手
	//'assistant' => 'aefd63a96719e294e7901ea670672817d7fd179d', // 手机助手

	'tencent' => 'eadaad39aae39f4106e28b4795ae55ff9d535127', //腾讯soso
	'baidu' => '32b86b304c9426f01d17dae491d9e6641bba7218',
	'jinliqc' => '3cc17f07e3223a9c0881ed0854c42831b3a8500b',//金立二维码
	'htc' => '1b07241036d91417c5c3d663e781f6393aa3b361',//htc接口合作
	'hisense_tv' => '7c4b45840b669667ad15a204b4f1c29be1c6fca3',//海信tv资源合作
	'sogou' => 'ce78d7e887ac8583a003e005c0a9ea99f7c357db',//搜狗搜索2
	'sougou' => 'ce78d7e887ac8583a003e005c0a9ea99f7c357db',//搜狗搜索2
	#后台软件外投
	'mobilemm' => 'cf33d5d88f4efd564d0020a58f1a12ee2c80cc8f',//移动mm
	'sugar' => '19177f66bcced3f3233cbbf63e6903930a74ca5d',//糖果手机
	'yezi' => '2ca33b43ae61f456c51efee75fe1e45302bef78b',//椰子
	'bdhk' => '0562010b78f157e0841c88307a62c8c39fb2b532',//百度好看，好123
	'pengfuwang' => 'adb59463b9410037c204e8bda06863ac147cc0db',//捧腹网
	'test' => '514f101298d899df30a85afc3f67abfd97cc9e79',//软件投放测试渠道
	'wanka' => '7efc9a3f01400d1908f9b21debd6db11fd40702a',//玩咖-软件外投
	'zhangxingliyi' => 'c84100cd20bcdb2ff10226046642ed9b7b1feb82',//掌星立意-软件外投
	'99zhuan' => 'a37d8b995dfc8e1b0351e99acce8c2dc669ad4c7',//99赚-软件外投
	'hao123' =>	  'e48c3d8b26f05ca2a811af3b1bf1616838359a6b',//hao123-www定制
	'anzhuobizhi' => 'd94be84f4acf38b0ff64bdf72478f542f5f15ed2',//安卓壁纸-软件外投
	'kuan' => 'f7b478353c4016096e586b3a090305bd685ab190',//酷安-软件外投
	'mopinkeji' => '721caded57c171df91ff8578c40eed9587d9a713',//魔品科技-软件外投
	'xingzhetianxia' => '94a97892500b1d01d33400f0b1fbfbb7dc8e4717',//行者天下-软件外投
	'dituiba' => '8802f10c74a28dbe486bd97e09ea43f51fc87d9b',//地推吧-软件外投
	'quansheng' => 'b7e8b8ccbd2c7cd687377f978507fd37f9d3d49b',//全盛佳道-软件外投
	'qingmang' => '3e8576dbeb17443ac024fc4b5c3538c524edc1c0',//青芒市场-软件外投
	'mzw' => '11ceb96f1e020f339d9811e21f828649f0b87f47',//拇指玩-全量合作
	'xianguo' => 'f618e6833a169fd468ea64fd9ba7aa8608ae43fb',//仙果-软件外投
	'landunanquan' => '211d254cae8bbb6b017cc555d50b4a75a728516b',//蓝盾安全卫士-软件外投
	'hongtuxinda' => 'bd1f619bf10ea3ddc6d9c6898ef7fbdd73bf4c1e',//鸿途信达-软件外投
	'hlzs' => '11ceb96f1e020f339d9811e21f828649f0b87f47',//狐狸助手-全量合作
	'aibizhi' => '8e857890db26cdd92142dbecc61e7468d591b949',//爱壁纸-软件外投
	'mpkj' => '7d314db500e62403e44b77bc43ef98517e927ec5',//魔品科技-全量合作
	'anzhuobizhi1' => '2f81e3f4f0762f534230c3f64b2d448fc5b19a53',//安卓壁纸1-软件外投
	'hulizhushou' => '47598d49d8bb0968435467e1f2a92aaf27b96350',//狐狸助手api接口-软件外投
	'yitichuanmei' => '696b3f6979e9b30c0390153017b66ed28b01dd2b',//一体传媒-软件外投
	'yezizhu' => '5eac932f92562f24204280d5c63368213032c014',//叶子猪游戏资讯网-软件外投
);

//cp组权限
$GLOBALS["CP_ADMINS"] = array(
	'admin',
	//'fatty228',//薛凯
	'GoMarket',
	'wangyl',//王永亮
	//'tewilove',//胡明
	'随风飞的猪',//陈志义
	'lipengallen',//李芃
	"haoyl",//郝永丽
	//'chrisigp',//龚萍
	'honking',//林洪清
	//'linana',//李娜娜--离职
	//'PeterYD',//李云昌
	//'smallstroll',//赵亚楠--离职
	//'mijiao',//段狄--离职
	//'jz3003',//王俊杰
	//'wrain1989',//王巍
	//'大平锅',//赵强平
	//'鴉羽',//刘安琪
	//'laceylee',//李晓静--离职
	'东方不败小可爱',//艾青
	'xinxin8840',//晓霞
	'lijia1234',//李佳
	'tangchunli0910',//唐春丽
);

include(GOPHP_ROOT.'/lib/GoTemplate.class.php');
$cacheObj = GoCache::getCacheAdapter('Memcached');
$tplObj = GoTemplate::getInstance(TEMPLATE_DIR, TEMPLATE_C_DIR);
$tplObj->display_exit = false;
$soft_logic = pu_load_logic('soft');
$user_logic = pu_load_logic('user');

$_SESSION['MODEL_CID'] = 495;
if (isset($chl_array[$channel])) {
    define('CHANNEL', $channel);
    define('CHL', $chl_array[$channel]);
    $channelObj = load_model('channel');
    $channel_info = $channelObj -> getChannelFields(CHL,'cid,is_filter');
    $_SESSION['MODEL_CID'] =  $channel_info['cid'] ? $channel_info['cid']  : 0;
    $rule_cache = $cacheObj->get('channel:has_rule');
	$_SESSION['channel:has_rule'] = isset($rule_cache[$_SESSION['MODEL_CID']]);
	$_SESSION['channel_filter_search'] = $channel_info['is_filter'] ? $channel_info['is_filter'] : 0;
} else {
	exit;
}
if($_COOKIE['autoLogin'] == true){
    include_once("autoLogin.php");
}

//过滤gpc
if (!empty($_GET)) {
    $_GET   =    array_map('stripslashes_deep', $_GET);
}
if (!empty($_COOKIE)) {
    $_COOKIE   =    array_map('stripslashes_deep', $_COOKIE);
}
if (!empty($_POST)) {
    $_POST   =    array_map('stripslashes_deep', $_POST);
}

//专题列表特殊定义的专题id
$default_subject = array(
	'necesssary' => 31,
	'best_app' => 55,
	'best_game' => 56
);
$ctc_subject = 87;//电信专题
$tplObj -> out['ctc_subject'] = $ctc_subject;
$tplObj -> display_exit = false;
//seo配置
include(dirname(realpath(__FILE__)).'/seoconf.php');
$key = $_SERVER['REQUEST_URI'];
$tplObj -> out['seo'] = $seo_conf[$key];
$tplObj -> tpl -> registerPlugin("modifier", "sub_str_cn", "sub_str_cn");
$tplObj -> tpl -> registerPlugin("block", "imgurltrans", "imgurl_trans");


$tplObj -> out['uc'] = $uc;
$tplObj -> out['channel'] = $_GET['channel'];	
