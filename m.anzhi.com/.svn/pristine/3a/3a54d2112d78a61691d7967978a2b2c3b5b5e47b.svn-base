<?php
include_once (dirname(realpath(__FILE__)).'/../../init.php');
$config = load_config('lottery_cache/redis',"lottery");
if ($config) {
	$redis = new GoRedisCacheAdapter($config);
} else {
	$redis = GoCache::getCacheAdapter('redis');
}

$model = new GoModel();
$aid = $active_id = $_GET['aid'] ? $_GET['aid'] : $_POST['aid'];
//$aid= $active_id =635;//todo
if(ctype_digit($aid)==false){
    exit(0);
}
$sid = $_GET['sid'] ? $_GET['sid'] : $_POST['sid'];
$time = time();
$pid = $_GET['pid']; //todo

if($_GET['sid']){
    session_begin();
}else if($_POST['sid']){
    session_begin($_POST['sid']);
}

$version_code = $_SESSION['VERSION_CODE'];
$alone_update = $_SESSION['ALONE_UPDATE'];  //独立更新
$imei = $_SESSION['DEVICEID'];
$mac= $_SESSION['MAC'];
$uid = $_SESSION['USER_UID'];
$upid = $_SESSION['USER_ID'];
$username=$_SESSION['USER_NAME'];
$imsi = $_SESSION['USER_IMSI'];

//各种key
$lottery_num_key = 'red_package_lottery_num:aid:'.$aid.':uid:'.$uid;

$down_set_num = 'red_package_down_set_num:aid:'.$aid;//后台设置的下载最大增加次数

$down_num_key = 'red_package_down_num:aid:'.$aid.':uid:'.$uid;//统计已增加次数
$p_key = 'red_package_user_packages:aid:'.$aid.':uid:'.$uid;

$p_done_key = 'red_package_user_packages_done:aid:'.$aid.':uid:'.$uid;//已完成任务的软件

$is_send_num= 'red_package_is_send:aid:'.$aid.':uid:'.$uid;//是否已经赠送了次数

	$option = array(
		'where' => array(
			'id' => $active_id,
		),
		'table' => 'sj_activity',
		'field' => 'activity_page_id,activity_end_id,start_tm,end_tm,status',
	);
	$activity = $model->findOne($option);

        if(!$_POST){
            if($activity['end_tm'] <= time()||$activity['start_tm'] > time()||$activity['status']!=1){//未开始或已结束
                    $url = $center_url.$configs['activity_url']."/lottery/red_ffl/currency.php?aid=".$active_id."&sid=".$sid.'&is_share='.$_GET['is_share'];;
                    header("Location: {$url}");
            }
        }

        /*todo
        if($activity['start_tm'] >= time()){//未开始
            exit(0);
        }*/

function give_lottery_num(){
        global $redis;
        global $model;
        global $imsi_num;
        global $active_id;
        global $uid;
        global $username;
        global $imei;
        global $imsi;

        $now_num = $redis -> setx('incr',$imsi_num,1);
        $redis->expire($imsi_num,86400*90);
        $where = array(
                'aid' => $active_id,
                'uid' => $uid
        );
        $data = array(
                'lottery_num' => $now_num,
                'update_tm' => time(),
                '__user_table' => 'red_package_lottery_num'
        );
        $result = $model -> update($where,$data,'lottery/lottery');
        if(!$result){
                $data = array(
                        'aid' => $active_id,
                        'uid' => $uid,
                        'username' => $username,
                        'imsi' => $imsi,
                        'imei' => $imei,
                        'lottery_num' => $now_num,
                        'update_tm' => time(),
                        '__user_table' => 'red_package_lottery_num'
                );
                $result = $model -> insert($data,'lottery/lottery');
        }
}
