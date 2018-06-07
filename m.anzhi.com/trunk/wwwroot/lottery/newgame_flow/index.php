<?php
include_once ('./fun.php');
$is_sign = is_sign();

$is_get_ll_pkg = $redis->get($is_get_ll_pkg_key);
$is_get_ll_pkg = empty($is_get_ll_pkg)?0:1;
$tplObj -> out['is_get_ll_pkg'] = $is_get_ll_pkg;

//领取
if($_POST['get_gprs']==1){
    $package_use_num = $redis->get($package_use_num_key);
    if($package_use_num >= 1){//todo
        echo json_encode(array('code'=>0,'msg'=>"<span>抱歉，该游戏已经兑换过</span>"));exit(0);
    }

    $rs = is_newuser();
    if($rs==-1){
	echo json_encode(array('code'=>0,'msg'=>"<span>对不起，该活动只支持新激活用户！</span>"));exit(0);
    }else{
        $ret = flow_recharge_check_new();
        if($ret['code']==0){
            echo json_encode($ret);exit(0);
        }else if($ret['code']==1){
            $rs = get_bind_status();
            if($rs!=false){//绑定过了 直接领取
                //echo json_encode(array('code'=>1,'msg'=>'流量兑换申请提交成功，您可到安智市场内继续参与活动。'));exit(0);
                echo json_encode(flow_recharge($rs['mobile'],30));exit(0);
            }else{
                echo json_encode(array('code'=>2));exit(0);
            }
        }
    }
}

//发送验证码
if($_POST['send_code']==1){
    $res_arr = send_active_mobile($_POST['mobile_phone']);
    echo json_encode($res_arr);
    exit(0);
}

//绑定
if($_POST['binding']==1){
    	//echo json_encode(array('code'=>1,'msg'=>'流量兑换申请提交成功，您可到安智市场内继续参与活动。'));exit;
    $retbd = check_mobile($_POST['mobile_phone'],$_POST['code']);
    if($retbd['code']==1){
        echo json_encode(flow_recharge($_POST['mobile_phone'],30));exit(0);
    }
    echo json_encode($retbd);
    exit(0);
}

$build_query = http_build_query($_GET);
if($configs['is_test'] == 1){
	$h_str = 'dev.';
	$tplObj -> out['is_test'] = 1;
}
$center_url = "http://".$h_str."i.anzhi.com/mweb/account/login?serviceId=014&serviceVersion=5410&serviceType=0&redirecturi=";
$login_url = $center_url.$configs['activity_url']."lottery/appointment/index.php?".$build_query;
$tplObj -> out['login_url'] = $login_url;	
$share = $_GET['share'];
$tplObj->out ['prefix_url'] = $configs['activity_url'];
$tplObj->out ['activity_video_url'] = $configs['activity_video_url'];

//日志
$log_data = array(
		"imsi" => $_SESSION['USER_IMSI'],
		"device_id" => $_SESSION['DEVICEID'],
		"activity_id" => $active_id,
		"ip" => $_SERVER['REMOTE_ADDR'],
		"sid" => $sid,
		"time" => $time,
		"user" => $_SESSION['USER_UID'] && $_SESSION['USER_ID'] != 13176 ? $_SESSION['USER_NAME'] : '',
		'uid'=> $_SESSION['USER_UID'] && $_SESSION['USER_ID'] != 13176 ? $_SESSION['USER_UID'] : '',
		'key' => 'show_homepage'
);
permanentlog('activity_'.$active_id.'.log', json_encode($log_data));

user_loging_new();
if(isset($_SESSION['USER_UID']) && $_SESSION['USER_ID'] != 13176){//已登录
	if($_GET['is_register'] == 1){
		//注册成功日志
		$log_data = array(
			'uid' => $_SESSION['USER_UID'],
			'imsi' => $_SESSION['USER_IMSI'],
			'device_id' => $_SESSION['DEVICEID'],
			'activity_id' => $active_id,
			'ip' => $_SERVER['REMOTE_ADDR'],
			'sid' => $sid,
			'time' => $time,
			'key' => 'register'
		);
		permanentlog('activity_'.$active_id.'.log', json_encode($log_data));
	}else{
		//登录日志
		$log_data = array(
			'uid' => $_SESSION['USER_UID'],
			'imsi' => $_SESSION['USER_IMSI'],
			'device_id' => $_SESSION['DEVICEID'],
			'activity_id' => $active_id,
			'ip' => $_SERVER['REMOTE_ADDR'],
			'sid' => $sid,
			'time' => $time,
			'key' => 'login'
		);
		permanentlog('activity_'.$active_id.'.log', json_encode($log_data));
	}
	$tplObj -> out['username'] = $_SESSION['USER_NAME'];
	$tplObj -> out['is_login'] = 1;
	$tplObj -> out['uid'] = $_SESSION['USER_UID'];

}else{//未登录
	$tplObj -> out['is_login'] = 2;
}

$tplObj -> out['is_share'] = $_GET['is_share'];

$tplObj -> out['imgurl'] = getImageHost();
$tplObj -> out['imsi'] = $_SESSION['USER_IMSI'];
$tplObj -> out['sid'] = $_GET['sid'];
$tplObj -> out['aid'] = $aid;
$tplObj -> out['static_url'] = $configs['static_url'];
$tplObj -> out['new_static_url'] = $configs['new_static_url'];
if($stop==1){
    $tplObj -> display('lottery/newgame_flow/end.html');
}else{
    $tplObj -> display('lottery/newgame_flow/index.html');
}
