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

$option = array(
	'where' => array(
		'uid' => $uid,
	),
	'table' => 'recharge_top_num',
	'field' => 'tel,name,address',
);
$userinfo = $model->findOne($option,'lottery/lottery');
if($userinfo==false){//新用户
    $tplObj -> out['new_user'] = 1;
}else{
    $tplObj -> out['new_user'] = 2;
}
$tplObj -> out['userinfo'] = $userinfo;


if(isset($_POST['lxname'])){
    $model = new GoModel();
        $uid = $_SESSION['USER_UID'];
            //$uid = '20150330130627TU01y592gH';
    if($_POST['new_user']==2){
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
    }else if($_POST['new_user']==1){//新增
        $data = array(
                'uid' => $uid,
                'tel' => $_POST['mobile_phone'],
                'name' => $_POST['lxname'],
                'address' => $_POST['address'],
                'update_tm' => time(),
                'create_tm' => time(),
                'os_from' => 2,
                '__user_table' => 'recharge_top_num'
        );
        $model->insert($data,'lottery/lottery');
    }
    $redis->set('recharge_top_is_save_'.$uid,1,86400*10);
    $redis->set('recharge_top_userinfo'.$uid,json_encode($data),86400*10);
    echo 1;exit(0);
}

$tplObj -> out['sid'] = $_GET['sid'];

$tplObj -> out['static_url'] = $configs['static_url'];
$tplObj -> display('recharge_top_lottery_userinfo.html');
