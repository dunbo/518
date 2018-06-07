<?php
include_once ('./fun.php');

if(!empty($is_sign)){
    exit(0);
}

$is_sign = is_sign();

if($is_sign==-1){
    $is_ll = is_get_ll();
    if($is_ll==-1){
        $rs = is_newuser();
        if($rs==-1){
            echo json_encode(array('code'=>2,'msg'=>'抱歉，本活动为新用户独享'));exit(0);
        }
    }
}

//签到操作
$res = add_sign_data();

if($res==true){
    $level = $sign_days+1;
    load_helper('task');
    $task_client = get_task_client();
    $new_array=array(
            'prefix' => $prefix,
            'uid'	 =>	$uid,
            'aid'  => $active_id,
            'username' => $_SESSION['USER_NAME'],
            'position' => $level,
            'activityName' => "下载送流量活动",
            'table' => 'valentine_draw_prize',
            'table_award' => 'valentine_draw_award',
    );
    $the_award = $task_client->do('lssue_prize', json_encode($new_array));

    $log_data = array(
            'time'	=>	$time,
            'imsi'	=>	$_SESSION['USER_IMSI'],
            'uid'	=>	$uid,
            'sid'	=>	$sid,
            'username'	=>	$_SESSION['USER_NAME'],
            'device_id'	=>	$_SESSION['DEVICEID'],
            "DEVICE_SN" => $_SESSION['DEVICE_SN'],
            'activity_id'	=>	$active_id,
            'level'	=>	$level,	
            "award_name" => $the_award,
            'key'	=>	'receive_success'
    );
    permanentlog('activity_'.$active_id.'.log', json_encode($log_data));
    echo json_encode(array('sign_days'=>$level,'code'=>1,'msg'=>'签到成功!<br>'.$sign_show.'元礼券稍候下发到您的账户。'));exit(0);
}
