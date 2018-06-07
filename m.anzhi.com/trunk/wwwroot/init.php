<?php
define('APP_NAME', 'wap');
define('IMGURL_TRANS',1);
include(dirname(realpath(__FILE__)).'/../newgomarket.goapk.com/init.php');
include_once dirname(realpath(__FILE__)).'/../tools/functions.php';
include_once dirname(realpath(__FILE__)).'/config/config.inc.php';

$channel = isset($_GET['channel']) ? $_GET['channel'] : 'm';
$tmp = explode('_', $channel);
$_SESSION['VERSION_CODE'] = 4401;
if ($tmp[0] != 'concise') {
	$channel = $tmp[0];
}

if (stripos($_GET['channel'], 'concise') !== false) {
	$_GET['concise'] = 1;
}

$concise = isset($_GET['concise']) ? $_GET['concise'] : 0;

if ($channel == 'concise') {
	$channel = 'm';
	$concise = 1;
}

if ($channel == 'ucweb') { //如果是UC浏览器，用特定的模板
    define('TEMPLATE_DIR', dirname(realpath(__FILE__)).'/uc_tpl/');
    define('TEMPLATE_C_DIR', dirname(realpath(__FILE__)).'/uc_tpl_c/');
} else if($concise==1) {
	define('TEMPLATE_DIR', dirname(realpath(__FILE__)).'/concise_tpl/');
	define('TEMPLATE_C_DIR', dirname(realpath(__FILE__)).'/concise_tpl_c/');
} else {
	define('TEMPLATE_DIR', dirname(realpath(__FILE__)).'/tpl/');
	define('TEMPLATE_C_DIR', dirname(realpath(__FILE__)).'/tpl_c/');
}

// 定义apk下载域名，518test为http://118.26.203.23，线上为http://www.apk.anzhi.com
if (getServerIp() == '192.168.1.242') {
	define('APK_DOWNLOAD_DOMAIN', 'http://m.test.anzhi.com');
} else {
	define('APK_DOWNLOAD_DOMAIN', 'http://www.apk.anzhi.com');
}
// 定义搜狗合作puid，518test为551，线上为1058
define('SOGOU_COOPERATE_PUID', 1058);

define('PAGE_LIMITE',15);
define('SUBJECT_PAGE_LIMITE',100);
include(GOPHP_ROOT.'/lib/GoTemplate.class.php');
$tplObj = GoTemplate::getInstance(TEMPLATE_DIR, TEMPLATE_C_DIR);
$tplObj->display_exit = false;
define('STATIC_DIR', dirname(realpath(__FILE__)).'/html_cache/');
$cacheObj = GoCache::getCacheAdapter('Memcached');
//修改文件域名
$tplObj -> out['img_url'] = load_config('m_goapk_img');
include(dirname(realpath(__FILE__)).'/functions.php');
$chl_code = '231e062b072d7effe6ac1505b3b6ce63f65ea17e';//默认渠道
$chl_array = array(
    'yqby' => 'e3f5b1e6c1205458e1c9a3b93d25812c50e13e19',
    'qq' => 'c981a6e01887b89c942304613d3580366d489097',
    'tencent' => '79d92747f7d9758b4f05c21bab8ec173166792ac',
    'ucweb' => '4f50b7ad55e51f2c9bf1242fd39e9cbef821409e',
    'uc' => '4f50b7ad55e51f2c9bf1242fd39e9cbef821409e',
    'm' => '231e062b072d7effe6ac1505b3b6ce63f65ea17e',
    '360' => '2f85d65a9a0be41f9696e1e87f20aee0ca6b80d0',
    'sinaweibo' => 'aada1945ac8cf34fc808892d2e53b172baca124c',
	'bbg' => '7b4b4848c409b98178f8ca302fe859c50d9d5b9d',
    'ptn' => 'aa50a800a02bcad9c7fcbed633392c777de6f5b0',//给游戏更新下载用的
	'mxzm' => '0ed9a0cf5d47fcbc0a1347541c134e87067d9202',//魔秀桌面导到m的渠道
	'semgame' => '754bd3d35ea52f0c39e4dff3de04e40829b2a519', //百度搜索游戏推广渠道
	'baizhu' => '0a814db494f69ecbc7691c76f26a42ce6be05399', //百度搜索游戏推广渠道
    'baidu' =>   '7c5a99e57fe761ab6d54e8e9e773d0f27dae6604', //百度手机浏览器-m站定制
);

//步步高针对安智市场特殊处理配置
$bbg_page = 0;
$bbg_index = 6;

$_SESSION['MODEL_CID'] = 0;
// $_SESSION['RES'] = 'm'; //展示小角标用
// $dev_model  = load_model('dev');
// $img_host = getIconHost();
// $offical_icon = $img_host. $dev_model->getIconInfo('offical_icon', $_SESSION['RES']);
$tplObj->out['offical_icon'] = "<img src='/images/offical.png' style=' padding:2px 0 0 15px'/>";
define('CONCISE', $concise);
define('HTTP_URL','http://m.anzhi.com');
if ( $channel ) {
    define('CHANNEL', $channel);
    define('CHL', $chl_array[$channel]);
    $channelObj = load_model('channel');
    $channel_info = $channelObj -> getChannelFields($chl_array[$channel],'cid,is_filter');
	$_SESSION['MODEL_CID'] =  $channel_info['cid'] ? $channel_info['cid']  : 0;
	$rule_cache = $cacheObj->get('channel:has_rule');
	$_SESSION['channel:has_rule'] = isset($rule_cache[$_SESSION['MODEL_CID']]);
	$_SESSION['channel_filter_search'] = $channel_info['is_filter'] ? $channel_info['is_filter'] : 0;
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
//导航栏焦点设置
$tplObj->out['nav_focus'] = array(
    'index_focus' => strstr($_SERVER['SCRIPT_FILENAME'], '/index.php'),
    'app_focus' => strstr($_SERVER['SCRIPT_FILENAME'], '/app.php') && $_GET['parent_cat_id'] != 2,
    'game_focus' => strstr($_SERVER['SCRIPT_FILENAME'], '/app.php') && $_GET['parent_cat_id'] == 2,
    'subject_focus' => strstr($_SERVER['SCRIPT_FILENAME'], '/subject.php'),
	'inapp_focus' => strstr($_SERVER['SCRIPT_FILENAME'], '/inapp.php'),
);
$feature_id = 13; //专题区extent_id 天翼专题id 13
//记录用户机型
/*
if (!$_SESSION['MODEL_DID'] && ($device = ua2device($_SERVER['HTTP_USER_AGENT'])) ) {
    $deviceModel = load_model('device');
    $_SESSION['MODEL_DID'] =  $deviceModel->getDeviceId($device);
    $_SESSION['DEVICE'] = $device;
}
*/

if(!empty($_POST['page_number'])){
	if(strstr($_SERVER['REQUEST_URI'],"subject.php")) {
		$size = SUBJECT_PAGE_LIMITE;
	} else {
		$size = PAGE_LIMITE;
	}
	$index_start = intval($_POST['page_number']) * PAGE_LIMITE - PAGE_LIMITE;
	if(strstr($_SERVER['REQUEST_URI'],"index_start")){
		$url = preg_replace('/index_start=\d+/',"index_start={$index_start}", $_SERVER['REQUEST_URI']);
	} else {
		if(strstr($_SERVER['REQUEST_URI'],"?")){
			$url = "{$_SERVER['REQUEST_URI']}&index_start={$index_start}";
		}else{
			$url = "{$_SERVER['REQUEST_URI']}?index_start={$index_start}";
		}
	}
	header('location: '. $url);
	exit;
}
//兼容游戏sdk里没有正确发送活动id的情况
if (!empty($_GET['real_aid']) ) {
    $_GET['aid'] = $_GET['real_aid'];
}

//如果是活动 则只能允许的渠道可以访问
if(isset($_GET['aid'])){


    /*
    $time = time();
    if($time >='1491667200'&&$time<='1491678000'){
        echo '服务器维护中';
        exit;
    }
     */

    $aid = $_GET['aid'];
    if(ctype_digit($aid)==false){
        exit(0);
    }


    //测试用，某些活动只有公司网络可以访问
    if($aid==1253){
        $ip = $_SERVER["REMOTE_ADDR"];
        $ip_arr  = array('218.241.82.226','218.241.82.231','103.103.12.82','103.103.12.84');
        if(in_array($ip,$ip_arr)==false){
            exit(0);
        }
    }

    $activity_option = array(
            'where' => array(
                    'id' => $aid
            ),
            'cache_time' => 300,
            'table' => 'sj_activity'
    );
    $model = new GoModel();
    $activity_result = $model -> findOne($activity_option);

    session_begin();

    $channel_id = $activity_result['channel_id'];
    if($_SESSION['product']==1){//只有安智市场才会走进来
        $session_channel_id = $_SESSION['MODEL_CID'];



        if($activity_result['platform']==1){

            if($channel_id!==''&&$channel_id!==',,'&&$channel_id!==',0,'){
                $arr = explode(',',$channel_id);
                $arr = array_filter($arr);
                if(in_array($session_channel_id,$arr)==false){
                    exit(0);
                }
            }
        }
    }else{//如果不是市场 又勾选了测试渠道 则只能安智内网访问
        //if($activity_result['platform']!=1){
            //if($channel_id!==''&&$channel_id!==',,'&&$channel_id!==',0,'){
            if($channel_id==',271,'||$channel_id==',3150,'||$channel_id==',271,3150,'||$channel_id==',3150,271,'){//线上
            //if($channel_id==',2900,'||$channel_id==',1496,'||$channel_id==',2900,1496,'||$channel_id==',1496,2900,'){ //测试
                $ip = $_SERVER["REMOTE_ADDR"];
                $ip_arr  = array('218.241.82.226','218.241.82.231','103.103.12.82','103.103.12.84');
                if(in_array($ip,$ip_arr)==false){
                    exit(0);
                }
            }
        //}
    }
}

$tplObj->out ['channel_mk'] = $channel;
$tplObj->out ['prefix_url'] = 'http://m.test.anzhi.com';
$tplObj -> tpl -> registerPlugin("block", "imgurltrans", "imgurl_trans");
$tplObj -> tpl -> registerPlugin("block", "formatFileSize", "formatFileSize");
