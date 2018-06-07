<?php
require_once(dirname(realpath(__FILE__)).'/init.php');

// 记录活动页签日志
$log_data = array(
    'activity_id' => $aid,
    'sid' => $sid,
    'imsi' => $imsi,
    'device_id' => $_SESSION['DEVICEID'],
    'ip' => $_SERVER['REMOTE_ADDR'],
    'time' => $now,
);

if($_GET['click']){
    $log_data['tab'] = $_GET['click'];
    $log_data['key'] = 'click';
    permanentlog($activity_log_file, json_encode($log_data));
    exit;
}else{
    $log_data['key'] = 'show_homepage';
    permanentlog($activity_log_file, json_encode($log_data));
}

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

$a_start_time = $result['start_tm']; //活动开始时间
$a_end_time = $result['end_tm']; //活动结束时间

$category_option = array(
    'where' => array(
        'active_id' =>  $result['activity_page_id'],
        'status'    =>  1,
    ),
    'order' => 'rank asc',
    'cache_time'=>  600,
    'table'     =>  'sj_actives_category',
    'field'     =>  'id,rank',
);
$category = $model -> findAll($category_option);
$category = array_column($category, 'id', 'rank');
$tplObj->out['category'] = $category;
//作为日志记录的参数和请求接口的参数，跟后台页面配置软件分类对应
$click_arr = array(
    1 => 'xigua',
    2 => 'huajiao',
    3 => 'cddh',
    4 => 'zscr',
);
$tplObj->out['click'] = $click_arr;
$stop_tm = strtotime('2018-02-22 00:00:00');
if($now>=$stop_tm){
    $tplObj->out['hidden'] = 2;
}else{
    $tplObj->out['hidden'] = 1;
}
/*echo '<pre>';
print_r($category);
exit('<pre>');*/

/*if ($now > $a_end_time) {
    $tplObj->display("lottery/{$prefix}/end.html");
    exit;
}*/

$tplObj->display("lottery/{$prefix}/index.html");
