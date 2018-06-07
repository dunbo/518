<?php
/**
 *   排行榜页面
 */
include_once (dirname(realpath(__FILE__)).'/superbowl_init.php');
$img_host = 'http://img5.anzhi.com';
if(!$_GET['type']){
    $type = 1;
}else{
    $type = $_GET['type'];
}
if(!$_GET['all']) $limit = 10;
$activity_soft = $redis->gethash($vote_app_num);
//var_dump($activity_soft);
$game = $app = array();
foreach($activity_soft as $key=>$val){
    $val['package'] = $key;
    if($val['category']==1){
        $game[] = $val;
    }else{
        $app[] = $val;
    }
}

$game_num = $app_num = array();
foreach ($game as $k => $v) {

    $game_num[] = $v['num'];
}
array_multisort($game_num, SORT_DESC, $game);

foreach ($app as $k => $v) {
    $app_num[] = $v['num'];
}
array_multisort($app_num, SORT_DESC, $app);
if($type == 1){
    if(!$_GET['all']){
        $activity_result = array_slice($game, 0, 10);
    }else{
        $activity_result = $game;
    }
}else{
    if(!$_GET['all']){
        $activity_result = array_slice($app, 0, 10);
    }else{
        $activity_result = $app;
    }
}
$package = array();
foreach($activity_result as $k=>$v){
    $package[] = $v['package'];
}
$package_icon_info = get_soft($package);
foreach($activity_result as $k=>$v){
    $activity_result[$k]['iconurl'] = $package_icon_info[$v['package']]['iconurl'];
    $activity_result[$k]['softid'] = $package_icon_info[$v['package']]['softid'];
}
//var_dump($activity_result);
if(isset($_GET['end'])){
    $tplObj->out['end'] = 1;
}
$tplObj->out['img_host'] = $img_host;
$tplObj->out['type'] = $type;
$tplObj->out['all'] = $_GET['all'];
$tplObj->out['activity_result'] = $activity_result;
$tplObj->out['package_icon_info'] = $package_icon_info;
$tplObj->display("superbowl/superbowl_ranklist.html");

function get_soft($package){
    global $model;
    $max_info = $model->findAll(array(
        'where' => array(
            'apk_name' => $package
        ),
        'field' =>'MAX(id) as max',
        'table' => 'sj_soft_file',
        'group' =>'apk_name',
        'cache_time' => 3600
    ));
    foreach($max_info as $k=>$v){
        $max_id[] = $v['max'];
    }

    $option = array(
        'where' => array(
            'id' => $max_id
        ),
        'field' =>'apk_name,iconurl_125',
        'table' => 'sj_soft_file',
        'order'=>'id desc',
        'cache_time' => 3600
    );
    $data = $model->findAll($option);

    foreach($data as $k=>$v){
        $package_info[$v['apk_name']]['iconurl'] = $v['iconurl_125'];
    }
    return $package_info;
}