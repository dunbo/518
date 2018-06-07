<?php
include_once ('./fun.php');
if($_SERVER['SERVER_ADDR'] == '118.26.203.23' ){
	$h_str = 'dev.';
	$tplObj -> out['is_test'] = 1;
}	
$build_query = http_build_query($_GET);
$active_id = $_GET['aid'] ? $_GET['aid'] : $_POST['aid'] ;
$sid = $_GET['sid'] ? $_GET['sid'] : $_POST['sid'];
session_begin($sid);

$build_query = http_build_query($_GET);
$url = "http://promotion.anzhi.com/lottery/xy2/yd_index.php?".$build_query;

if(isset($_SESSION['USER_UID'])){//已登录
	$uid = $_SESSION['USER_UID'];
}else{//未登录 跳转到首页
	if($_POST){
		exit(json_encode(array('code'=>2,'url'=>$url)));
	}else{
		header("Location: {$url}");
	}
}


$time = time();
$now_day = date('Ymd');
if($_POST){
        $type = $_POST['type'];
        //砸蛋，翻牌 共用一个  type不一样
	if(isset($_SESSION['USER_UID'])){//已登录
		$uid = $_SESSION['USER_UID'];
	}else{//未登录 跳转到首页
		$url = "http://promotion.anzhi.com/lottery/xy2/yd_index.php?".$build_query;
		exit(json_encode(array('code'=>2,'url'=>$url)));
	}

	//抽奖日志
	$log_data = array(
		"imsi" => $_SESSION['USER_IMSI'],
		"device_id" => $_SESSION['DEVICEID'],
		"activity_id" => $active_id,
		"ip" => $_SERVER['REMOTE_ADDR'],
		"sid" => $sid,
		"time" => $time,
		//"type" => $type,
		//"eggtype" => $_POST['eggtype'],
		"award_level" => '',//pid
		"user" => $_SESSION['USER_NAME'],
		'uid'=>$uid,
		"award_name" =>'',
		'key' => 'lottery'
	);
	permanentlog('activity_'.$active_id.'.log', json_encode($log_data));

	//剩余抽奖次数
        $lottery_num_key='yd:sign:lottery_num:uid:'.$uid;
	$now_num = $redis->setx('incr',$lottery_num_key,-1);
	if($now_num < 0){
		$redis->set($lottery_num_key,0);
		exit(json_encode(array('code'=>0,'msg'=>'抱歉，您账号今日可用抽奖次数已用完！','title'=>"【抽奖次数已用完】")));
	}



                 $data = array(
                        'turncard_num' => array('exp','`turncard_num`-1'),
                        'update_tm' => time(),
                        '__user_table' => 'xy2_draw_userinfo'
                );

        $where = array(
                'uid' => $uid
        );
        $model->update($where, $data,'lottery/lottery');


	load_helper('task');
	$task_client = get_task_client();
	$new_array = array(
		'uid' => $uid,
		'type' => $type,
		'aid' => $active_id,
		'username' => $_SESSION['USER_NAME'],
	);	

	$new_array['package'] = get_gift_pkg_yd($active_id,$type,$uid,$_POST['pkg'],"xy2");
		//exit(json_encode(array('code'=>2,'url'=>$new_array['package'])));

	$the_award = $task_client->do('newyears', json_encode($new_array));





	$lottery_rs = json_decode($the_award,true);

	if($lottery_rs['pid'] == 0){
		$gift_info = json_decode($lottery_rs['gift_number'],true);
		del_gift_pkg_yd($active_id,$type,$uid,$gift_info['package'],"xy2");
	}

	//抽奖成功日志
	$log_data = array(
			"imsi" => $_SESSION['USER_IMSI'],
			"device_id" => $_SESSION['DEVICEID'],
			"activity_id" => $active_id,
			"ip" => $_SERVER['REMOTE_ADDR'],
			"sid" => $sid,
			"time" => $time,
		        //"type" => $type,
		        //"eggtype" => $_POST['eggtype'],
			"award_level" => $lottery_rs['pid'],
			"user" => $_SESSION['USER_NAME'],
			"package" => $lottery_rs['pid'] ==0 ? $gift_info['package'] : '',
			"softname" => $lottery_rs['pid'] ==0 ? $gift_info['softname'] : '',
			"gift" =>  $lottery_rs['pid'] ==0 ? $gift_info['gift_number'] : '',
			'uid'=>$uid,
			"award_name" => $lottery_rs['pid'] ==0 ? "礼包" : $lottery_rs['name'],
			'key' => 'lottery_success'
	);
	permanentlog('activity_'.$active_id.'.log', json_encode($log_data));	


        if($lottery_rs['pid'] == 0){
		//$kind_award_gift = get_user_kind_gift_new_yd($type,$uid,$active_id,"yd","xy2_draw_gift");	


		//用户中奖信息
		$arr = array(	
			'gift_number' => $gift_info['gift_number'],
			'uid' => $uid,
			'package' => $gift_info['package'] ,
			'softname' => $gift_info['softname'],
			'time' => date("Y-m-d",$time) ,
		);
		//礼包的所有兑换信息
		$redis -> lPush("yd:{$active_id}_type:{$type}_gift_prize:{$uid}",json_encode($arr));	
	}else{
		//$prize_list = get_user_kind_award_new_yd($type,$uid,$active_id,"yd","xy2_draw_award");	
		//实物的所有兑换信息
		$arr = array(	
			'uid' => $uid,
			'prizename' => $lottery_rs['name'],
			'time' => date("Y-m-d",$time)
		);
		$arr['username'] = str_replace_cn_new($_SESSION['USER_NAME'], 1, -2 );
		$redis -> lPush("yd:{$active_id}_type:{$type}_draw_award:{$uid}",json_encode($arr));	
        }





        //supwater 返回的信息 自定义
	$array = array(
		'code' => 1,
		'pid' => $lottery_rs['pid'],
		'package' => $gift_info['package'],
		'softname' => $gift_info['softname'],
		'gift_num' => $gift_info['gift_number'],
		'prizename' => $lottery_rs['name'],
	);
	exit(json_encode($array));
}


function get_gift_pkg_yd($aid,$type,$uid,$gift_pkg,$prefix){
	list($redis,$model) = load_config_redis();
	$user_gift_pkg = $redis->get("{$prefix}:{$aid}_{$type}_gift_pkg:".$uid);
	$open_gift_pkg = explode(";",$gift_pkg);
	//file_put_contents("/tmp/christmas.log",var_export($open_gift_pkg,true)."\n".var_export($user_gift_pkg,true),FILE_APPEND);
	if(!$user_gift_pkg){
                $prize_gift_pkg = $redis->get('xy2_gift_pkg:type:'.$type.':aid:'.$aid);
		$redis -> set("{$prefix}:{$aid}_{$type}_gift_pkg:".$uid,$prize_gift_pkg,10*86400);
        $user_gift_pkg = $prize_gift_pkg;
		$intersection =  array_intersect($open_gift_pkg, $prize_gift_pkg);
	}else{	
		$intersection =  array_intersect($open_gift_pkg, $user_gift_pkg);
	}
	if($intersection){
		//a.优先抽中已安装游戏的虚拟礼包；
		foreach($intersection as $v){
			return $v;
			exit;
		}
	}else{
		return $user_gift_pkg[0]; 
	}
}

//去除已获得的礼包包名
function del_gift_pkg_yd($aid,$type,$uid,$pkg,$prefix){
	list($redis,$model) = load_config_redis();	
	$user_gift_pkg = $redis->get("{$prefix}:{$aid}_{$type}_gift_pkg:".$uid);	
	$new_gift_pkg = array();
	foreach($user_gift_pkg as $k => $v){
		if($v != $pkg){
			$new_gift_pkg[] = $v;
		}
	}
	//file_put_contents("/tmp/christmas.log",var_export($new_gift_pkg,true),FILE_APPEND);
	$redis -> set("{$prefix}:{$aid}_{$type}_gift_pkg:".$uid,$new_gift_pkg,10*86400);
}

        //用户我的奖品--实物
function get_user_kind_award_new_yd($type,$uid,$aid,$prefix,$table){
	list($redis,$model) = load_config_redis();	
	$kind_award_list = $redis -> getlist("{$prefix}:{$aid}_type:{$type}_draw_award:{$uid}");
	if(!$kind_award_list){
		$option = array(
			'where' => array(
				'uid' => $uid,
				'type' => $type,
				'aid' => $aid,
			),
			'table' => $table,
			'field' => 'id,aid,uid,username,pid,prizename,create_tm',
		);
		$kind_award = $model->findAll($option,'lottery/lottery');	
		if(!$kind_award) return false;
		$kind_award_list = array();
		foreach((array)$kind_award as $k => $v){
			$kind_award_list[$v['id']] = $v;
			$kind_award_list[$v['id']]['time'] = date("Y-m-d",$v['create_tm']);
		}
		unset($kind_award);
		$redis -> setlist("{$prefix}:{$aid}_type:{$type}_draw_award:{$uid}",$kind_award_list,30*60);
	}else{
		foreach($kind_award_list as $k => $v){
			$kind_award_list[$k] = json_decode($v,true);
		}		
	}	
	return $kind_award_list;
}
//用户我的奖品--礼包
function get_user_kind_gift_new_yd($type,$uid,$aid,$prefix,$table){
	list($redis,$model) = load_config_redis();	
	$gift_prize_list = $redis -> getlist("{$prefix}:{$aid}_type:{$type}_gift_prize:{$uid}");
	if(!$gift_prize_list){
		$option = array(
			'where' => array(
				'status' => 1,
				'type' => $type,
				'uid' => $uid,
				'aid' => $aid,				
			),
			'table' => $table,
			'field' => 'gift_number,uid,package,softname,update_tm',
		);
		$kind_gift = $model->findAll($option,'lottery/lottery');	
		if(!$kind_gift) return false;
		$gift_prize_list = array();
		foreach((array)$kind_gift as $k => $v){
			$gift_prize_list[$v['gift_number']] = $v;
			$gift_prize_list[$v['gift_number']]['time'] = date("Y-m-d",$v['update_tm']);
		}
		$redis -> setlist("{$prefix}:{$aid}_type:{$type}_gift_prize:{$uid}",$gift_prize_list,86400*10);
		unset($kind_gift);
	}else{
		foreach($gift_prize_list as $k => $v){
			$gift_prize_list[$k] = json_decode($v,true);
		}		
	}
	return $gift_prize_list;
	
}
