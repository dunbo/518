<?php
// 要接收的参数 $_GET['is_share'],$_GET['sid'],$_GET['aid']
include_once(dirname(realpath(__FILE__)).'/../../init.php');

$aid = $_GET['aid'] ? $_GET['aid'] : $_POST['aid'];
if(!ctype_digit($aid)){
	echo '活动id无效';
	exit;
}
$tplObj->out['aid'] = $aid;

list($redis, $model) = load_config_redis();

// cdn资源地址
$tplObj->out['is_test'] = $configs['is_test'];
$tplObj->out['static_url'] = $configs['static_url'];
$tplObj->out['new_static_url'] = $configs['new_static_url'];
$tplObj->out['activity_host'] = $configs['activity_url'];
$tplObj->out['is_share'] = $_GET['is_share'];

// 当前时间
$now = time();
$today = date('Ymd', $now);
$prefix = 'download_for_flow';
$tplObj->out['prefix'] = $prefix;
/*echo '<pre>';
$r_keys = $redis->getx('keys', "{$prefix}:*");
print_r($r_keys);
foreach($r_keys as $key){
    $redis->delete($key);
}
exit('</pre>');*/

// 加载session，获取用户相关信息
$sid = $_GET['sid'] ? $_GET['sid'] : $_POST['sid'];
session_begin($sid);
$tplObj->out['sid'] = $sid;
if ($_SESSION['USER_IMSI'] && $_SESSION['USER_IMSI'] != '000000000000000') {
    $imsi = $_SESSION['USER_IMSI'];
}else{
	$imsi = '';
}

// redis相关
$r_cache_time = 5184000; //redis缓存时间为两个月
$rkey_imsi_download_num = "{$prefix}:{$aid}:{$imsi}:{$today}:download_num"; //用户当日下载次数
$rkey_imsi_package_list = "{$prefix}:{$aid}:{$imsi}:{$today}:package_list"; //用户当日下载软件

// 根据活动id，获取活动页面id，用来关联软件
$activity_option = array(
    'where' => array(
        'id' => $aid,
        'status' => 1
    ),
    'cache_time' => 600,
    'table' => 'sj_activity'
);
$result = $model->findOne($activity_option);
$page_id = $result['activity_page_id']; //活动页面id，在download.php中验证下载的包是否有效
$a_start_time = $result['start_tm']; //活动开始时间
$a_end_time = $result['end_tm']; //活动结束时间

// 活动日志
$activity_log_file = "activity_{$aid}.log";

if(strpos($_SERVER['HTTP_USER_AGENT'], 'iPhone') || strpos($_SERVER['HTTP_USER_AGENT'], 'iPad')){
    $tplObj->display("lottery/{$prefix}/ios.html");die;
}

if(strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false){
    $tplObj->display("lottery/{$prefix}/weixin.html");die;
}
