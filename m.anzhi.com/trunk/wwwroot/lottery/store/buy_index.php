<?php
include_once ('./init.php');
session_begin();
$imei= $_SESSION['DEVICEID'];

if($imei=='000000000000000'){
    $tplObj -> out['status'] = 1;
}else{
    $tplObj -> out['status'] = 2;
}


if(!$_POST){
    $log_data = array(
                    "imsi" => $_SESSION['USER_IMSI'],
                    "device_id" => $_SESSION['DEVICEID'],
                    "activity_id" => 359,
                    "ip" => $_SERVER['REMOTE_ADDR'],
                    "sid" => $_GET['sid'],
                    "time" => time(),
                    'key' => 'show_homepage'
    );
    permanentlog('activity_359.log', json_encode($log_data));
}


$tplObj -> out['static_url'] = $configs['static_url'];

$tpl = "lottery/store/buy_index.html";
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

if($_POST){

    if(empty($imei)){
        echo 5;exit(0);
    }

    $is_bd = $redis->get('azstore:bd:imei:'.$imei);
    if(empty($is_bd)){
                $activity_option = array(
                    'where' => array(
                            'tel' => $_POST['mobile'],
                            'imei' => $imei
                    ),
                    'table' => 'store_order'
                );
                $res= $model -> findOne($activity_option,'lottery/lottery');
                $is_bd = $res['tel'];
                if(empty($res)){
                    $rs = $redis->sIsMemberSet('azstore:mobile:set',$_POST['mobile']);
                    if($rs == true){ //自己设备没有绑定时 输入的手机号已绑定过
                        echo 2;exit(0);
                    }else{
                        echo 1;exit(0);//imei 手机号都可用
                    }
                }else if($is_bd == $_POST['mobile']){//supwater
                        //跳转到我的优惠券页面
                        $redis->set('azstore:bd:imei:'.$imei,$_POST['mobile'],86400*90);
                        echo 3;exit(0);
                }else{
                        //自己设备绑定了 输入了其他手机号
                        $redis->set('azstore:bd:imei:'.$imei,$_POST['mobile'],86400*90);
                        echo 4;exit(0);
                }
                
    }else if($is_bd == $_POST['mobile']){
        //跳转到我的优惠券页面
            echo 3;exit(0);
    }else{
        //自己设备绑定了 输入了其他手机号
            echo 4;exit(0);
    }
}


$tplObj -> out['mobile'] = $_GET['mobile'];
$tplObj -> display($tpl);
