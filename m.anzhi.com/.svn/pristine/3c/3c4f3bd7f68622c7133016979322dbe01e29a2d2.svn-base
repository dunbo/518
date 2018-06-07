<?php
include_once (dirname(realpath(__FILE__)).'/../../init.php');
$config = load_config('lottery_cache/redis',"lottery");
if ($config) {
	$redis = new GoRedisCacheAdapter($config);
} else {
	$redis = GoCache::getCacheAdapter('redis');
}
$model = new GoModel();
$active_id = $_GET['aid'] ? $_GET['aid'] : $_POST['aid'];
if(!isset($active_id)){
	if($_SERVER['SERVER_ADDR'] == '118.26.203.23' ){
		$active_id = 464;
	}else{
		$active_id = 466;
	}	
	$url = $center_url."http://promotion.anzhi.com/lottery/peak_warship/index.php?aid=".$active_id."&sid=".$sid;
	header("Location: {$url}");
}

$sid = $_GET['sid'] ? $_GET['sid'] : $_POST['sid'];
if($_GET['stop'] != 1){
	$res = activity_is_stop($active_id);
	if(!$res){
		$url = $center_url."http://promotion.anzhi.com/lottery/peak_warship/index.php?stop=1&aid=".$active_id."&sid=".$sid;
		header("Location: {$url}");
	}
}
//添加用户、修改用户信息
function add_user($data,$time){
	global $model;
	global $redis;		
	$option = array(
		'where' => array(
			'uid' => $data['uid'],
			'aid' => $data['aid']
		),
		'table' => 'valentine_draw_userinfo',
	);
	$userinfo = $model->findOne($option,'lottery/lottery');	
    if($userinfo){
        $new_data = array(
			'uid' => $data['uid'],
			'username' => $_SESSION['USER_NAME'],
			'phone' => $data['phone'] ? $data['phone'] : $userinfo['phone'] ,
			'contact_name' => $data['contact_name'] ? $data['contact_name'] : $userinfo['contact_name'],
			'address' => $data['address'] ? $data['address'] : $userinfo['address'],
			'update_tm' => $time,
			'__user_table' => 'valentine_draw_userinfo'
        );
		if($data['draw_data_num']){
			 $new_data['draw_data_num'] = $data['draw_data_num'];
		}else{
			 $new_data['draw_data_num'] = $userinfo['draw_data_num'];
		}
        $where = array(
			'uid' => $data['uid'],
			'aid' =>$data['aid'],
        );
        $ret =  $model->update($where, $new_data,'lottery/lottery');
		if($ret){
			//剩余抽奖次数
			$rest = intval($new_data['draw_data_num']-$userinfo['deduction_num']);
			if($rest < 0){
				$rest = 0;
			}
			$redis->set("peak_warship:{$data['aid']}_rest_lottery_num:".$data['uid'],$rest,30*86400);			
		}
    }else {//新增
        $new_data = array(
			'uid' => $data['uid'],
			'aid' => $data['aid'],
			'username' => $_SESSION['USER_NAME'],
			'phone' => $data['phone'] ? $data['phone'] : $userinfo['phone'] ,
			'contact_name' => $data['contact_name'] ? $data['contact_name'] : $userinfo['contact_name'],
			'address' => $data['address'] ? $data['address'] : $userinfo['address'],			
			'update_tm' => $time,
			'create_tm' => $time,
			'os_from' => 2,
			'__user_table' => 'valentine_draw_userinfo'
        );
		if($data['draw_data_num']){
			 $new_data['draw_data_num'] = $data['draw_data_num'];
		}		
        $ret =  $model->insert($new_data,'lottery/lottery');
		if($ret){
			$redis->set("peak_warship:{$data['aid']}_rest_lottery_num:".$data['uid'],intval($data['draw_data_num']),30*86400);	
		}
    }
    $redis->set("peak_warship:{$data['aid']}_userinfo:".$data['uid'],$new_data,86400*30);
	return 	$ret;
}
