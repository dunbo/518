<?php
include_once ('./init.php');
session_begin();
$imei= $_SESSION['DEVICEID'];
if(empty($imei)){
    echo '页面已失效，请退出活动重新进入';
    exit(0);
}

$tplObj -> out['static_url'] = $configs['static_url'];

$tpl = "lottery/store/buy_myprize.html";

$activity_option = array(
	'where' => array(
		'id' => 1
	),
	'cache_time' => 3600,
	'table' => 'store_config'
);
$config_info = $model -> findOne($activity_option,'lottery/lottery');
$config_info['explain'] = str_replace("\n","<br/>",$config_info['explain']);
$tplObj -> out['config_info'] = $config_info;


//优惠券已使用缓存
$ret = $redis->get('azstore:used:imei:'.$imei.':'.$_GET['mobile']);
if(!empty($ret)){
    $tplObj -> out['mobile'] = $_GET['mobile'];
    $tplObj -> out['school'] = $ret;
    $tplObj -> out['status'] = 1;
    $tplObj -> display($tpl);
    exit(0);
}

$activity_option = array(
	'where' => array(
		'tel' => $_GET['mobile'],
		'imei' => $imei,
	),
	//'cache_time' => 120,
	'table' => 'store_order'
);
$orderinfo = $model -> findOne($activity_option,'lottery/lottery');
if(!empty($orderinfo)&&$orderinfo['status']!=1){//已使用
    $redis->set('azstore:used:imei:'.$imei.':'.$_GET['mobile'],$orderinfo['school'],86400*90);
    $tplObj -> out['mobile'] = $_GET['mobile'];
    $tplObj -> out['school'] = $orderinfo['school'];
    $tplObj -> out['status'] = 1;
    $tplObj -> display($tpl);
    exit(0);
}

if($_GET['fromindex']==1){
    $school = $orderinfo['school'];
    $city = $orderinfo['city'];
}else{
    $school = $_GET['school'];
    $city = $_GET['city'];
}

$tplObj -> out['store_name'] = $orderinfo['store_name'];


//未绑定或者未兑换

$tplObj -> out['status'] = 2;

//店铺列表
$activity_option = array(
	'where' => array(
		'school' => $school,
		'status' => 1,
	),
	'cache_time' => 1200,
	'table' => 'store_user'
);
$shoplist = $model -> findAll($activity_option,'lottery/lottery');


if($_POST){
    if(empty($imei)){
        echo 5;exit(0);
    }

    $redis->set('azstore:djs:imei:'.$imei,time(),60);
    load_helper('task');
    $task_client = get_task_client();
    $_POST['imei'] = $imei;
    $the_award = $task_client->do('store_sendnum', json_encode($_POST));
    if($the_award==1){
            $active_id = 359;

            $log_data = array(
                    'imsi' => $_SESSION['USER_IMSI'],
                    'device_id' => $_SESSION['DEVICEID'],
                    'activity_id' => $active_id,
                    'ip' => $_SERVER['REMOTE_ADDR'],
                    'sid' => $_GET['sid'],
                    'time' => time(),
                    'telphone' => $_POST['mobile'],
                    'city' => $_POST['city'],
                    'school' => $_POST['school'],
                    'store_name' => $_POST['store_name'],
                    'shopid' => $_POST['shopid'],
                    'key' => 'store_user_use'
            );
            permanentlog('activity_'.$active_id.'.log', json_encode($log_data));
    }

    if($the_award==1){
        $return_data['store_name'] = $_POST['store_name'];
        echo json_encode($return_data);exit(0);
    }else if($the_award==-1){
        echo 2;exit(0);
    }else if($the_award==-2){
        echo 3;exit(0);
    }
}


//处理验证码倒计时
$send_time = $redis->get('azstore:djs:imei:'.$imei);
if(empty($send_time)){
    $tplObj -> out['send_time'] = 60;
}else{
    $cha_time = time()-$send_time;
    if($cha_time>=60){
        $cha_time=60;
    }else{
        $cha_time=60-$cha_time;
    }
    $tplObj -> out['send_time'] = $cha_time;
}

$shop_str = '';
foreach($shoplist as $v){
    $shop_str .= $v['store_name'].'、';
}
$shop_str = mb_substr($shop_str,0,-1);
$tplObj -> out['shop_str'] = $shop_str;
$tplObj -> out['mobile'] = $_GET['mobile'];
$tplObj -> out['city'] = $city;
$tplObj -> out['school'] = $school;
$tplObj -> out['shoplist'] = $shoplist;
$tplObj -> display($tpl);
