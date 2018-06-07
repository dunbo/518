<?php
include_once (dirname(realpath(__FILE__)).'/../init.php');
$config = load_config('lottery_cache/redis',"lottery");
if ($config) {
	$redis = new GoRedisCacheAdapter($config);
} else {
	$redis = GoCache::getCacheAdapter('redis');
}
$model = new GoModel();
//没有session 跳转到首页
session_begin();

        if(isset($_SESSION['USER_UID'])){//已登录
            $uid = $_SESSION['USER_UID'];
        }else{//未登录 跳转到首页
            header("Location: http://promotion.anzhi.com/lottery/recharge_top.php");
        }

$userarr= $redis -> gethash("recharge_top:user");
$option = array(
	'where' => array(
		//'uid' => $uid,
		'status' => 1
	),
	'table' => 'recharge_top_award',
        'order' => 'time desc',
        'cache_time' => 600,
        'limit' => 10,
);
$prize = $model->findAll($option,'lottery/lottery');
foreach($prize as $k=>$v){
    $username = $v['username'];
    $prize[$k]['username'] = str_replace(mb_substr($username,2,4,'utf-8'),'XXXX',$username);
}
$tplObj -> out['prize'] = $prize;
$lottery_num = $redis -> get("recharge_top:lottery_num".$uid);
if(empty($lottery_num)){
    $lottery_num=0;
}
$money = $userarr[$uid]['money'];
if(empty($money)){
    $money=0;
}
$tplObj -> out['lottery_num'] = $lottery_num;
$tplObj -> out['money'] = $money;

//抽奖操作
if(isset($_POST['type'])){
    $active_id=216;
    $uid = $_SESSION['USER_UID'];
    if(empty($uid)){
        echo 100;exit(0);
    }
    $now_num_begin = $redis -> get("recharge_top:lottery_num".$uid);
    if(empty($now_num_begin)){
        echo 500;exit(0);
    }

    //if($uid!='20150408160137965qYKVRNl'){ //测试用supwater
    
        $lottery_num_today = $redis -> get('recharge_top_uid_lottery_num_'.$uid.':'.date('Ymd'));
        if($lottery_num_today===null){//一次没抽 可以1 可以10
            if($_POST['type']==10){
                $redis -> setx('incr','recharge_top_uid_lottery_num_'.$uid.':'.date('Ymd'), 10);
            }else{
                $redis -> setx('incr','recharge_top_uid_lottery_num_'.$uid.':'.date('Ymd'), 1);
            }
        }else if($lottery_num_today<10){ //可以一次 不可以10次
            if($_POST['type']==1){
                $redis -> setx('incr','recharge_top_uid_lottery_num_'.$uid.':'.date('Ymd'), 1);
            }else{
                //报错
                echo 300;exit(0);
            }
        }else{  //10次了 不让抽了
                echo 400;exit(0);
        }
    //}

        if($_POST['type']==10){
            $lottery_num = $redis -> get("recharge_top:lottery_num".$uid);
            if($lottery_num<10){
                echo 600;exit(0);
            }
        }
    
        //减少次数
$now_num = $redis -> setx('incr',"recharge_top:lottery_num".$uid, -$_POST['type']);
if ($now_num < 0) {
    // 没有抽奖机会，把缓存数量还原为0
    $now_num = $redis -> set("recharge_top:lottery_num".$uid, 0);
    echo 500;exit(0);
}

$where = array(
    'uid' => $uid
);

// 写回库里
$data = array(
	//'lottery_num' => $now_num,
	//'lottery_num' => array('exp','lottery_num-'.$_POST['type']),
	'have_num' => array('exp','have_num+'.$_POST['type']),
	'update_tm' => time(),
	'__user_table' => 'recharge_top_num'
);

$model -> update($where,$data,'lottery/lottery');

        load_helper('task');
        $task_client = get_task_client();
        $username =  $_SESSION['USER_NAME'];
        if($_POST['type']==10){
                $new_array = array();
                $new_array['uid'] =$uid;
                $new_array['username'] =$username;
                $the_award = $task_client->do('recharge_top_ten', json_encode($new_array));
                $lottery_rs = json_decode($the_award,true);
                $lottery_type = $lottery_rs['type'];
                $return_arr['lottery_type']=$lottery_type;
                if($lottery_type==1){//实物
                    $return_arr['prizename']=$lottery_rs['name'];
                    $rs = $redis->get('recharge_top_is_save_'.$uid);
                    if($rs==null){
                        $return_arr['is_save'] = 2;
                    }else{
                        $return_arr['is_save'] = 1;
                    }
                }else if($lottery_type==2){//礼包
                    $gift_arr = json_decode($lottery_rs['gift_number'],true);
                    $return_arr['softname']=$gift_arr['softname'];
                    $return_arr['gift_number']=$gift_arr['gift_number'];
                    if(empty($gift_arr['gift_number'])){
                        $return_arr['no_gift']=1;
                    }else{
                        $return_arr['no_gift']=2;
                    }
                }else if($lottery_type==-1){
                    $return_arr['no_gift']=1;
                }
        }else if($_POST['type']==1){
                $new_array = array();
                $new_array['uid'] =$uid;
                $new_array['username'] =$username;
                $the_award = $task_client->do('recharge_top_one', json_encode($new_array));
                $lottery_rs = json_decode($the_award,true);
                $lottery_type = $lottery_rs['type'];
                $return_arr['lottery_type']=$lottery_type;
                if($lottery_type==1){//实物
                    $return_arr['prizename']=$lottery_rs['name'];
                    $rs = $redis->get('recharge_top_is_save_'.$uid);
                    if($rs==null){
                        $return_arr['is_save'] = 2;
                    }else{
                        $return_arr['is_save'] = 1;
                    }
                }else if($lottery_type==2){//礼包
                    $gift_arr = json_decode($lottery_rs['gift_number'],true);
                    $return_arr['softname']=$gift_arr['softname'];
                    $return_arr['gift_number']=$gift_arr['gift_number'];
                    if(empty($gift_arr['gift_number'])){
                        $return_arr['no_gift']=1;
                    }else{
                        $return_arr['no_gift']=2;
                    }                    
                }else if($lottery_type==-1){
                    $return_arr['no_gift']=1;
                }
        }
        if($lottery_type!=-1){
            $user_data = $redis->get('recharge_top_userinfo'.$uid);
            $user_data = json_decode($user_data,true);
            if(empty($lottery_rs['pid'])){
                $pid=11;
            }else{
                $pid = $lottery_rs['pid'];
            }
            $log_data = array(
                    'uid'=>$uid,
                    'type'=>$_POST['type'],
                    'imsi' => $_SESSION['USER_IMSI'],
                    'device_id' => $_SESSION['DEVICEID'],
                    'activity_id' => $active_id,
                    'ip' => $_SERVER['REMOTE_ADDR'],
                    'sid' => $_GET['sid'],
                    'time' => time(),
                    'award_level' => $pid,//pid
                    'user' => $_SESSION['USER_NAME'],
                    'name' => $user_data['name'],//姓名
                    'telphone' => $user_data['tel'],
                    'address' => $user_data['address'],
                    'package' => '',
                    'gift' => $gift_arr['gift_number'],
                    'key' => 'award'
            );
            permanentlog('activity_'.$active_id.'.log', json_encode($log_data));
        }

        $return_arr['now_num'] = $now_num;
        echo json_encode($return_arr);exit(0);
}

$tplObj -> out['sid'] = $_GET['sid'];

$tplObj -> out['static_url'] = $configs['static_url'];
$tplObj -> display('recharge_top_lottery.html');
