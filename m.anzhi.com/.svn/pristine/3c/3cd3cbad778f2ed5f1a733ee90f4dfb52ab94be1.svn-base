<?php
include_once (dirname(realpath(__FILE__)).'/../init.php');
if(!empty($_POST['sid'])){
    session_begin($_POST['sid']);
}else{
    session_begin();
}

$config = load_config('lottery_cache/redis',"lottery");
if ($config) {
	$redis = new GoRedisCacheAdapter($config);
} else {
	$redis = GoCache::getCacheAdapter('redis');
}
$model = new GoModel();

//$aid = $_GET['aid'];
$aid = $_GET['aid'];
$tplObj -> out['sid'] = $_GET['sid'];
$tplObj -> out['aid'] = $aid;
$tplObj -> out['static_url'] = $configs['static_url'];
$imsi = $_SESSION['USER_IMSI'];
$imei= $_SESSION['DEVICEID'];

if(isset($_POST['az_user']))
{
    $user_arr = json_decode($redis->get('xy2_sign:imei:'.$imei),true);
    if(empty($user_arr)){
        $data = array(
            'imei' => $imei,
            'az_user' => $_POST['az_user'],
            'server_name' => $_POST['server_name'],
            'play_name' => $_POST['play_name'],
            'create_tm' => time(),
            '__user_table' => 'xy2_userinfo'
        );
        $model->insert($data,'lottery/lottery');
    }else{
        //更新记录
        $data = array(
            'az_user' => $_POST['az_user'],
            'server_name' => $_POST['server_name'],
            'play_name' => $_POST['play_name'],
            'update_tm' => time(),
            '__user_table' => 'xy2_userinfo'
        );
        $where = array(
                'imei' => $imei
        );
        $model->update($where, $data,'lottery/lottery');
    }

    $redis->set('xy2_sign:imei:'.$imei,json_encode($data),86400*10);
    echo 1;exit(0);
}

if($_GET['is_end'] == 1){
	$tplObj -> display("lottery/xy2_sign_end.html");
}else{
	$log_data = array(
		'imsi' => $imsi,
		'device_id' => $_SESSION['DEVICEID'],
		'activity_id' => $aid,
		'ip' => $_SERVER['REMOTE_ADDR'],
		'sid' => $_GET['sid'],
		'time' => time(),
		'users' => '',
		'uid' => '',
		'key' => 'show_homepage'
	);
	permanentlog('activity_'.$aid .'.log', json_encode($log_data));
        $user_arr = json_decode($redis->get('xy2_sign:imei:'.$imei),true);
        $tplObj -> out['user_arr'] = $user_arr;
	$tplObj -> display("lottery/xy2_sign.html");
}
