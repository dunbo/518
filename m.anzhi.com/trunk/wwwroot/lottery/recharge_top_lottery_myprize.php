<?php
include_once (dirname(realpath(__FILE__)).'/../init.php');
$config = load_config('lottery_cache/redis',"lottery");
if ($config) {
	$redis = new GoRedisCacheAdapter($config);
} else {
	$redis = GoCache::getCacheAdapter('redis');
}
//没有session 跳转到首页
session_begin();
        if(isset($_SESSION['USER_UID'])){//已登录
            //$uid = '20150330130627TU01y592gH';      
            $uid = $_SESSION['USER_UID'];
        }else{//未登录 跳转到首页
            header("Location: http://promotion.anzhi.com/lottery/recharge_top.php");
        }

$model = new GoModel();

$rs = $redis->get('recharge_top_is_save_'.$uid);
if($rs==null){
    $tplObj -> out['is_save'] = 2;
}else{
    $tplObj -> out['is_save'] = 1;
}

if(isset($_POST['lxname'])){
    $model = new GoModel();
    $uid = $_SESSION['USER_UID'];
            //$uid = '20150330130627TU01y592gH';
    $data = array(
            'tel' => $_POST['mobile_phone'],
            'name' => $_POST['lxname'],
            'address' => $_POST['address'],
            '__user_table' => 'recharge_top_num'
    );
    $where = array(
            'uid' => $uid
    );
    $model->update($where, $data,'lottery/lottery');
    $redis->set('recharge_top_is_save_'.$uid,1,86400*10);
    $redis->set('recharge_top_userinfo'.$uid,json_encode($data),86400*10);
    echo 1;exit(0);
}

$option = array(
	'where' => array(
		'uid' => $uid,
		'status' => 1
	),
	'table' => 'recharge_top_award',
	'field' => 'prizename',
	'order' => 'time desc'
);
$prize = $model->findAll($option,'lottery/lottery');


$tplObj -> out['prize'] = $prize;

$option = array(
	'where' => array(
		'uid' => $uid,
		'status' => 1
	),
	'table' => 'recharge_top_gift',
	'field' => 'softname,gift_number',
	'order' => 'update_tm desc'
);
$gift = $model->findAll($option,'lottery/lottery');
if(empty($prize)&&empty($gift)){
    $tplObj -> out['is_empty'] = 1;
}else{
    $tplObj -> out['is_empty'] = 2;
}

$tplObj -> out['gift'] = $gift;
$tplObj -> out['prize'] = $prize;
$tplObj -> out['sid'] = $_GET['sid'];

$tplObj -> out['static_url'] = $configs['static_url'];
$tplObj -> display('recharge_top_lottery_myprize.html');