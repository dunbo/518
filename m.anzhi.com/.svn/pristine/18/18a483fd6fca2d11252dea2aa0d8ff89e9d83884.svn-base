<?php
/*
** 活动初始页
 */
require_once(dirname(realpath(__FILE__)).'/../../init.php');

list($redis, $model) = load_config_redis();

// cdn资源地址
$tplObj->out['new_static_url'] = $configs['new_static_url'];

// 加载session，获取用户相关信息
session_begin();
if ($_SESSION['USER_IMSI']) {
    $imsi = $_SESSION['USER_IMSI'];
}
// $imsi = '405028011771946'; //测试用
$imsi_status = 0; //用户状态 0：异常 1：正常
if (!$imsi || $imsi == '000000000000000') {
	$imsi = '';
} else {
	$imsi_status = 1;
}
$sid = $_GET['sid'];
$tplObj->out['sid'] = $sid;
$aid = $_GET['aid'];
$tplObj->out['aid'] = $aid;

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
$page_id = $result['activity_page_id']; //活动页面id，用来关联软件
$a_name = $result['name']; //活动名称
$a_end_url = $result['end_url']; //活动结束页面
$a_start_time = $result['start_tm']; //活动开始时间
$a_end_time = $result['end_tm']; //活动结束时间

$tplObj->out['a_name'] = $a_name;
$tplObj->out['start_tm'] = date('m月d日', $a_start_time); //活动开始时间
$tplObj->out['end_tm'] = date('m月d日', $a_end_time); //活动结束时间

// 活动日志
$activity_log_file = "activity_{$aid}.log";