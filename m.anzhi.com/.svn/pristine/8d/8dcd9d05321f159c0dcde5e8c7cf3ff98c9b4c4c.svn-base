<?php
include_once (dirname(realpath(__FILE__)).'/../init.php');
$config = load_config('lottery_cache/redis',"lottery");
if ($config) {
	$redis = new GoRedisCacheAdapter($config);
	$sign_config = load_config('sendNum/redis', 'sendNum');
	$sign_redis = new GoRedisCacheAdapter($sign_config);		
} else {
	$redis = GoCache::getCacheAdapter('redis');
}
$model = new GoModel();
$prefix		=	"web_sign";
$sid = $_GET['sid'] ? $_GET['sid'] : $_POST['sid'];
//var_dump($sid);
session_begin($sid);
$sid = session_id();
if($configs['is_test'] == 1 ) {
	//$time  = get_now_time();
	// if($_SESSION['USER_UID'] == "20150625153820t94NX5799j"){
		// $time  =  strtotime("2017-04-15");
	// }else{
		$time  = time();
	//}
}else {
	$time  = time();
}
$today = date('Y-m-d',$time);
$activity_host = $configs['activity_url'];
$tplObj -> out['activity_host'] = $activity_host;	
$url = $activity_host."/{$prefix}/index.php";

if($_SERVER['SCRIPT_NAME'] != "/web_sign/index.php" && $_SERVER['SCRIPT_NAME'] != "/web_sign/prize_info.php"){
	if($_SESSION['USER_UID'] && $_SESSION['USER_ID'] != 13176){//已登录
		//已登录
		$tplObj -> out['is_login'] = 1;		
		$uid = $_SESSION['USER_UID'];
	}else {
		$_SESSION['USER_ID'] = 13176;	
		//未登录 跳转到首页
		if($_POST){
			exit(json_encode(array('code'=>2, 'msg'=>'USER_UID失效')));	
		}else{
			header("Location: {$url}");	
		}	
	}	
}
//月份id
$m_arr = get_mid();
if(!$m_arr['id']){
	echo "无效月份id";
	$now_month_id_key = "{$prefix}:now_month_id:".$today;
	$sign_redis->delete($now_month_id_key);		
	header("Location: {$url}");	
}
$log_key = $prefix.'_'.$m_arr['id'].'.log';
//获取当前的月份id
function get_mid(){
	global $sign_redis,$prefix,$model,$time,$today,$configs;	
	$now_month_id_key = "{$prefix}:now_month_id:".$today;
	$m_list = $sign_redis->get($now_month_id_key);	
	if($m_list === null){	
		$Y = date("Y",$time);
		$m = date("m",$time);
		$option = array(
			'where' => array(
				'year' => $Y,
				'month' => $m,
				'status' => array("exp"," not in (0,2)")
			),
			'table' => 'qd_sign_month',
			'field' => 'id,year,month,status',	
		);
		$m_list = $model->findOne($option,'lottery/lottery');
		if(!$m_list){
			$data = array(
				'year' => $Y,
				'month' => $m,
				'create_tm' => $time,
				'status' => 4,
				'__user_table' => 'qd_sign_month'
			);
			$mid =  $model->insert($data,'lottery/lottery');	
			$m_list = array(
				'id' => $mid,
				'year' => $Y,
				'month' => $m,			
				'status' => 4,
			);	
		}
		if($configs['is_test'] == 1 ) {
			$expire_tm = 600;
		}else{
			$expire_tm = 86400;
		}
		$m_list['same_month_day'] = date("t");
		$sign_redis->set($now_month_id_key,$m_list, $expire_tm);
	}
	return 	$m_list;
}

//获取活动配置
function sign_config($uid){
	global $sign_redis,$prefix,$m_arr,$time;
	$con_key = "{$prefix}:{$m_arr['id']}:tm_config:".$uid;
	$tm_config = $sign_redis->get($con_key);	
	if($tm_config === null){
		//已经签到和补签的数据
		$already_sign = get_sign_data($uid);
		$pic_arr = get_days_info();
		$day_num = date('t',$time);
		$tm_config = array();
		for($i=1;$i<=$day_num;$i++){
			if($i<10){
				$key = $m_arr['year']."-".$m_arr['month']."-0".$i;
			}else{
				$key = $m_arr['year']."-".$m_arr['month']."-".$i;
			}
			$tm_config[$key]['level'] = $i;
			$tm_config[$key]['is_card_task'] = 0;//补签卡的任务1完成0未完成
			$tm_config[$key]['status'] = $already_sign[$key]['status'] ? $already_sign[$key]['status'] : 0;
			$tm_config[$key]['pic'] = $pic_arr[$i]['pic_path'] ? $pic_arr[$i]['pic_path'] : '';
			$tm_config[$key]['did'] = $pic_arr[$i]['id'] ? $pic_arr[$i]['id'] : '';
			if($m_arr['status'] == 1){
				$type = $pic_arr[$i]['type'] ? $pic_arr[$i]['type'] : 0;
			}else{
				$type = 0;
			}
			$tm_config[$key]['type'] = $type;
			$tm_config[$key]['package'] = $pic_arr[$i]['package'] ? $pic_arr[$i]['package'] : '';
			$tm_config[$key]['task_id'] = $pic_arr[$i]['task_id'] ? $pic_arr[$i]['task_id'] : 0;
			$tm_config[$key]['task_type'] = $pic_arr[$i]['task_type'] ? $pic_arr[$i]['task_type'] : 0;
			$tm_config[$key]['redid'] = $pic_arr[$i]['redid'] ? $pic_arr[$i]['redid'] : 0;
		}
		$sign_redis->set($con_key,$tm_config, 60*86400);
	}
	return $tm_config;			
}
//取每天的配置
function get_days_info(){
	global $model;
	global $m_arr;		
	$option = array(
		'where' => array('mid'=>$m_arr['id']),
		'table' => 'qd_sign_days',
		'field' => 'id,day,type,package,task_id,task_type,redid,pic_path',
	);
	$day_arr = $model->findAll($option,'lottery/lottery');	
	$return_arr = array();
	foreach($day_arr as $key => $val){
		$return_arr[$val['day']] = $val;
	}
	return 	$return_arr;
}
//获得抽奖当天的奖品
function get_now_prize($did =0,$cid =0){
	global $model,$redis,$prefix,$m_arr;		
	if($did > 0){
		$now_prize_key = $prefix.":".$m_arr['id'].":did:".$did.':now_prize';		
	}else if($cid > 0){
		$now_prize_key = $prefix.":".$m_arr['id'].":cid:".$cid.':now_prize';			
	}
	$now_prize = $redis->get($now_prize_key);
	if($now_prize === null){
		$where = array(
			'status' =>1,
			'mid'=>$m_arr['id']			
		);
		if($did > 0){
			$where['did'] = $did;
		}else{
			$where['cid'] = $cid;
		}
		$option = array(
			'where' => $where,
			'table' => 'qd_draw_prize',
			'field' => 'name,id,level,pic_path',
			'order' => 'level asc',
		);
		$list_arr = $model->findAll($option,'lottery/lottery');	
		if(!$list_arr) return false;
		$now_prize = array();
		foreach($list_arr as $k => $v){
			$now_prize[$v['level']] = $v;
		}
		$redis->set($now_prize_key,$now_prize,30*60);
	}
	return $now_prize;
}
//获得谢谢参与的等级
function get_nowin_level($did =0,$cid =0){
	global $model,$redis,$prefix,$m_arr;		
	if($did > 0){
		$nowin_level_key = $prefix.":".$m_arr['id'].":did:".$did.':nowin_level';		
	}else if($cid > 0){
		$nowin_level_key = $prefix.":".$m_arr['id'].":cid:".$cid.':nowin_level';			
	}
	$nowin_level = $redis->get($nowin_level_key);
	if($nowin_level === null){
		$where = array(
			'status' =>1,
			'type' => 6,
			'mid'=>$m_arr['id']			
		);
		if($did > 0){
			$where['did'] = $did;
		}else{
			$where['cid'] = $cid;
		}
		$option = array(
			'where' => $where,
			'table' => 'qd_draw_prize',
			'field' => 'id,level',
		);
		$list = $model->findOne($option,'lottery/lottery');	
		if(!$list) return false;
		$nowin_level = intval($list['level']);
		$redis->set($nowin_level_key,$nowin_level,30*60);
	}
	return $nowin_level;
}
//获取签到天数
function get_sign_days($uid,$sign_type,$conf_arr,$day){
	$status	= 'status';	
	//连续签到
	if($sign_type ==1){
		$array =  array();
		$new_arr =  array();
		$index = 0;		
		foreach($conf_arr as $k => $v){
			if($v['level'] > $day) break;
			$array[$k][$status] = $v[$status];			
			if($v[$status] >= 1 &&  $array[$k][$status] >=1){
				$index++;
			}else{
				if($index) $new_arr[] = $index;
				$index = 0;				
			}
		}	
		if($index) $new_arr[] = $index;
		$pos = array_search(max($new_arr), $new_arr);
		$index = $new_arr[$pos];		
	//签到总天数	
	}else if($sign_type ==2){
		$index = 0;		
		foreach($conf_arr as $k => $v){
			if($v['level'] > $day) break;	
			if($v[$status] >= 1){
				$index++;
			}
		}
	}
	return $index;
}
//获取已经签到和补签的数据
function get_sign_data($uid){
	global $model,$m_arr;		
	$option = array(
		'where' => array('uid'=>$uid,'mid'=>$m_arr['id']),
		'table' => 'qd_sign_user_data',
	);
	$list_arr = $model->findAll($option,'lottery/lottery');	
	$new_arr = array();
	foreach($list_arr as $key => $val){
		$new_arr[$val['sign_date']]['status'] = $val['status'];
	}
	unset($list_arr);
	return $new_arr;
}
//添加用户签到、补签数据
function add_sign_data($uid,$sign_date,$status){
	global $m_arr,$model,$time;
	$where = array(
		'uid' => $uid,
		'mid' => $m_arr['id'],
		'sign_date' => $sign_date,
	);
	$option = array(
		'table' => 'qd_sign_user_data',
		'where' => $where,
	);
	$rest_list = $model->findOne($option,'lottery/lottery');	
	if(!$rest_list){	
		$data = array(
			'uid' => $uid,
			'mid' => $m_arr['id'],
			'sign_date' => $sign_date,
			'add_tm' => $time,
			'status' => $status,
			'__user_table' => 'qd_sign_user_data'
		);
		$ret =  $model->insert($data,'lottery/lottery');	
	}	
}
//签到提醒开关状态
function get_remind_status($uid){
	global $prefix,$m_arr,$redis,$model;	
	$switch_remind_key = "{$prefix}:switch_remind:".$_SESSION['DEVICEID'];
	$switch_remind = $redis->get($switch_remind_key);	
	if($switch_remind === null){
		$where = array( 'imei' => $_SESSION['DEVICEID']	);		
		$option = array(
			'where' => $where,
			'table' => 'qd_switch_remind',
			'field' => 'switch_remind',
		);
		$ret = $model->findOne($option,'lottery/lottery');		
		$switch_remind = intval($ret['switch_remind']);
		$redis->set($switch_remind_key,$switch_remind,60*86400);
	}
	return 	$switch_remind;
}
//签到提醒开关
//根据设备提醒
function switch_remind($status,$uid){
	global $prefix,$redis,$model,$time;	
	$where = array( 'imei' => $_SESSION['DEVICEID']	);		
	$option = array(
		'where' => $where,
		'table' => 'qd_switch_remind',
	);
	$ret = $model->findOne($option,'lottery/lottery');	
	if($ret){
		$data_update = array(
			'switch_remind' => $status,
			'update_tm' => $time,
			'uid' => $uid,
			'__user_table' => 'qd_switch_remind'
		);
		$ret =  $model -> update($where,$data_update,'lottery/lottery');				
	}else{
        $new_data = array(
			'imei' => $_SESSION['DEVICEID'],
			'add_tm' => $time,
			'uid' => $uid,
			'switch_remind' => $status,
			'__user_table' => 'qd_switch_remind'
        );	
        $ret =  $model->insert($new_data,'lottery/lottery');		
	}
	$switch_remind_key = "{$prefix}:switch_remind:".$_SESSION['DEVICEID'];	
	$redis->set($switch_remind_key,$status,60*86400);
	return $ret;
}
//后台签到提醒配置开关
//后台开启后前台才显示
function get_admin_switch_remind(){
	global $prefix,$m_arr,$redis,$model;	
	$admin_switch_remind_key = "{$prefix}:admin_switch_remind";
	$admin_switch_remind = $redis->get($admin_switch_remind_key);
	if($admin_switch_remind === null){
		$where = array( 'config_type' => 'SIGN_REMIND'	);		
		$option = array(
			'where' => $where,
			'table' => 'pu_config',
		);
		$ret = $model->findOne($option);	
		$admin_switch_remind = intval($ret['configcontent']);
		$redis->set($admin_switch_remind_key,$admin_switch_remind,1800);
	}
	return 	$admin_switch_remind;	
}
//补签卡数量
function get_cards_num($uid){
	global $prefix,$m_arr,$redis;		
	$cards_num_key = "{$prefix}:{$m_arr['id']}:res_cards_num:".$uid;
	$cards_num = $redis->get($cards_num_key);
	if($cards_num === null){
		$res = get_user_data($uid);
		$cards_num = intval($res['cards_num_jb']+$res['cards_num_task']-$res['used_cards_num']);	
		$redis->set($cards_num_key,$cards_num,1800);
	}
	return 	$cards_num;
}
function get_user_data($uid){
	global $model,$m_arr;	
	$option = array(
		'where' => array(
			'uid' => $uid,
			'mid' => $m_arr['id']
		),
		'table' => 'qd_draw_userinfo',
	);
	$userinfo = $model->findOne($option,'lottery/lottery');	
	return 	$userinfo;
}
//用户信息获取
function get_user_info($uid){
	global $prefix,$m_arr,$redis;	
	$userinfo_key = "{$prefix}:{$m_arr['id']}:userinfo:".$uid;
	$userinfo = $redis->get($userinfo_key);
	if($userinfo === null){
		$userinfo = get_user_data($uid);
		if($userinfo){	
			$redis->set($userinfo_key,$userinfo,60*86400);
		}
	}	
	return $userinfo;			
}
//添加用户、修改用户信息
function add_user($data){
	global $prefix,$redis,$model,$m_arr,$time;
	$table = 'qd_draw_userinfo';
	$userinfo = get_user_data($data['uid']);
    if($userinfo){
		$where = array(
			'uid' => $data['uid'],
			'mid' => $m_arr['id'] 
		);		
        $new_data = array(
			'username' => $_SESSION['USER_NAME'],
			'phone' => $data['phone'] ? $data['phone'] : $userinfo['phone'] ,
			'contact_name' => $data['contact_name'] ? $data['contact_name'] : $userinfo['contact_name'],
			'address' => $data['address'] ? $data['address'] : $userinfo['address'],
			'update_tm' => time(),
			'__user_table' => $table
        );
        $ret =  $model->update($where, $new_data,'lottery/lottery');
    }else {//新增
        $new_data = array(
			'uid' => $data['uid'],
			'mid' => $m_arr['id'],
			'username' => $_SESSION['USER_NAME'],
			'phone' => $data['phone'] ? $data['phone'] : '' ,
			'contact_name' => $data['contact_name'] ? $data['contact_name'] : '',
			'address' => $data['address'] ? $data['address'] :'',
			'update_tm' => $time,
			'create_tm' => $time,
			'os_from' => 2,
			'__user_table' => $table
        );	
		if($data['deduction_num'])  $new_data['deduction_num'] = $data['deduction_num'];
        $ret =  $model->insert($new_data,'lottery/lottery');
    }
    $redis->set("{$prefix}:{$m_arr['id']}:userinfo:".$data['uid'],$new_data,86400*60);
	return 	$ret;
}
//用户已用抽奖次数、获得的补签卡次数、补签卡实用次数
function save_deduction_num($uid,$field){
	global $prefix,$redis,$model,$m_arr,$time;	
	$where = array(
		'uid' => $uid,
		'mid' => $m_arr['id'] 
	);
	$res = get_user_data($uid);
	if($res){
		$data_update = array(
			$field => array('exp',"`{$field}`+1"),
			'update_tm' => $time,
			'username' =>  $_SESSION['USER_NAME'],
			'__user_table' => 'qd_draw_userinfo'
		);
		$ret = $model -> update($where,$data_update,'lottery/lottery');	
		if($field == "cards_num_jb" || $field == "cards_num_task"){
			//补签卡
			$cards_num = intval($res['cards_num_jb']+$res['cards_num_task']-$res['used_cards_num'])+1;	
			$cards_num_key = "{$prefix}:{$m_arr['id']}:res_cards_num:".$uid;
			$redis->set($cards_num_key,$cards_num,1800);	
		}
		return $ret;		
	}else{
        $new_data = array(
			'uid' => $uid,
			'mid' => $m_arr['id'],
			'username' => $_SESSION['USER_NAME'],
			$field => 1,
			'update_tm' => $time,
			'create_tm' => $time,
			'os_from' => 2,
			'__user_table' => 'qd_draw_userinfo'
        );	
        $ret =  $model->insert($new_data,'lottery/lottery');
		if($field == "cards_num_jb" || $field == "cards_num_task"){
			//补签卡
			$cards_num_key = "{$prefix}:{$m_arr['id']}:res_cards_num:".$uid;
			$redis->set($cards_num_key,1,1800);	
		}		
		return $ret;
	}
}
//连续签到抽奖配置
function get_continuity_config($uid){
	global $prefix,$redis,$model,$m_arr;
	$continuity_config_key = "{$prefix}:{$m_arr['id']}:continuity_config:".$uid;
	$continuity_config = $redis->get($continuity_config_key);
	if($continuity_config === null){	
		$option = array(
			'where' => array('status'=>1,'mid'=>$m_arr['id']),
			'table' => 'qd_sign_continuity',
			'order' => 'count asc'
		);
		$list_arr = $model->findAll($option,'lottery/lottery');	
		$new_arr = array();
		foreach($list_arr as $k => $v){
			$v['is_lottery'] = 0;
			$new_arr[$v['id']] = $v;
		}
		$continuity_config = $new_arr;
		$redis->set($continuity_config_key,$new_arr,86400*60);
	}
	return 	$continuity_config;	
}
//连续签到参与人数
function get_attendance($data){
	global $prefix, $redis, $m_arr;		
	$attendance_key = "{$prefix}:{$m_arr['id']}:attendance";
	$attendance_arr = $redis->get($attendance_key);
	if($attendance_arr === null){		
		krsort($data);
		$last_key = array();
		$i = 0;
		$attendance_arr = array();
		foreach($data as $k => $v){
			$lottery_num_key = "{$prefix}:{$m_arr['id']}:lottery_num:".$k;
			$lottery_num = $redis->get($lottery_num_key);	
			$i++;
			$last_num = $lottery_num*rand($v['random_start'],$v['random_end'])+intval($last_key[$i-1]['last_num']);
			$last_key[$i]['last_num'] = $last_num;
			$attendance = $v['base_num']+$last_num;
			$attendance_arr[$k] = $attendance;
		}
		$redis->set($attendance_key,$attendance_arr,10*60);	
	}
	return $attendance_arr;
}
//连续签到获得奖品最近30条数据
function get_continuity_top30(){
	global $prefix,$redis,$model,$m_arr;	
	$continuity_top30_key = "{$prefix}:{$m_arr['id']}:continuity_top30";
	$continuity_top30 = $redis->get($continuity_top30_key);
	if($continuity_top30 === null){	
		$option = array(
			'where' => array(
				'status'=>1,
				'mid'=>$m_arr['id'],
				'cid'=>array("exp",">0"),
				'type'=>array(1,2)
			),
			'table' => 'qd_draw_award',
			'order' => 'id desc',
			'field' => 'username,email,mobile,prizename,create_tm',
			'limit' => 30
		);
		$continuity_top30 = $model->findAll($option,'lottery/lottery');	
		foreach($continuity_top30 as $k => $v){
			$continuity_top30[$k]['username'] = account_policy($v['username'],$v['email'],$v['mobile']);	
			$continuity_top30[$k]['create_tm'] = date("Y-m-d",$v['create_tm']);	
		}
		$redis->set($continuity_top30_key,$continuity_top30,1800);
	}
	return 	$continuity_top30;		
}
//一年的奖品配置
function get_year_prize_info(){
	global $prefix,$redis,$model,$m_arr,$time,$today;	
	$year_prize_con = "{$prefix}:year_prize_info:".$today;
	$prize_info = $redis->get($year_prize_con);
	if($prize_info === null){		
		$day_tm = $time-(366*86400);
		$option = array(
			'where' => array(
				'status'=>1,
				'update_tm'=>array("exp",">{$day_tm}")
			),
			'table' => 'qd_draw_prize',
			'field' => 'id,name,did,mid,cid,pic_path,gift_file,`desc`',
			'order' => 'did asc,type asc'
		);
		$list_arr = $model->findAll($option,'lottery/lottery');	
		$prize_info = array();
		foreach($list_arr as $k => $v){
			$prize_info[$v['id']] = $v;
		}
		$redis->set($year_prize_con,$prize_info,86400);	
	}
	return 	$prize_info;
}
//我的奖品列表
function get_my_prize($uid,$is_expired){
	global $prefix,$redis,$model,$m_arr,$time;	
	//is_expired 1过期  0正常
	$my_prize_key = "{$prefix}:{$m_arr['id']}:my_prize:is_expired:".$is_expired.":".$uid;
	$my_prize = $redis->get($my_prize_key);
	if($my_prize === null){	
		$prize_info = get_year_prize_info();
		$option = array(
			'where' => array(
				'status'=>1,
				//'mid'=>$m_arr['id'],
				'uid'=>$uid,
			),
			'table' => 'qd_draw_award',
			'order' => 'create_tm desc',
		);
		$day_30_tm = $time-(30*86400);
		if($is_expired == 1){
			$Y_tm = $time-(365*86400);
			$option['where']['create_tm'] = array('exp',"<{$day_30_tm} and `create_tm` >{$Y_tm} and `type` in(1,2) or (`virtual_end_tm` < {$time} and `virtual_end_tm` >0)");
		}else{
			$option['where']['create_tm'] = array('exp',">{$day_30_tm} and `type` in(1,2) or (`virtual_end_tm` > {$time} and `virtual_end_tm` >0)");
		}
		$my_prize_list = $model->findAll($option,'lottery/lottery');
		//echo $model->getsql();
		$my_prize = array();
		if($my_prize_list){
			foreach($my_prize_list as $k => $v){
				$my_prize[$v['id']] = $v;
				$my_prize[$v['id']]['pic_path'] = $prize_info[$v['pid']]['pic_path'];
				$my_prize[$v['id']]['desc'] = nl2br($prize_info[$v['pid']]['desc']);
				if($v['type'] == 2){
					$my_prize[$v['id']]['czk_money'] = $prize_info[$v['pid']]['gift_file']."元";
				}
				if($is_expired == 1){
					$my_prize[$v['id']]['create_tm'] = '已过期';
				}else{
					if(in_array($v['type'],array(3,4,5))){
						$my_prize[$v['id']]['create_tm'] = date("Y-m-d",$v['virtual_end_tm']);
					}else{
						$my_prize[$v['id']]['create_tm'] = date("Y-m-d",$v['create_tm']+30*86400);
					}
				}
				if(in_array($v['type'],array(3,4,5))){
					$my_prize[$v['id']]['expiry_date'] = date("Y-m-d",$v['virtual_start_tm'])."~".date("Y-m-d",$v['virtual_end_tm']);
				}else if($v['type'] == 4){
					$my_prize[$v['id']]['desc'] = nl2br($prize_info[$v['pid']]['desc']);
					$my_prize[$v['id']]['pic_path'] = $prize_info[$v['pid']]['pic_path'];
					$my_prize[$v['id']]['prizename'] = $prize_info[$v['pid']]['name'];				
				}
			}
		}
		unset($my_prize_list);
		$redis->set($my_prize_key,$my_prize,1800);		
	}
	return 	$my_prize;
}
function get_my_prize_old($uid,$is_expired){
	global $prefix,$redis,$model,$m_arr,$time;	
	//is_expired 1过期  0正常
	$my_prize_key = "{$prefix}:{$m_arr['id']}:my_prize:is_expired:".$is_expired.":".$uid;
	$my_prize = $redis->get($my_prize_key);
	if($my_prize === null){	
		$prize_info = get_year_prize_info();
		$option = array(
			'where' => array(
				'status'=>1,
				//'mid'=>$m_arr['id'],
				'uid'=>$uid,
			),
			'table' => 'qd_draw_award',
			'order' => 'create_tm desc',
		);
		$day_30_tm = $time-(30*86400);
		if($is_expired == 1){
			$Y_tm = $time-(365*86400);
			$option['where']['create_tm'] = array('exp',"<{$day_30_tm} and `create_tm` >{$Y_tm} and `type` in(1,2,4) or (`virtual_end_tm` < {$time} and `virtual_end_tm` >0)");
		}else{
			$option['where']['create_tm'] = array('exp',">{$day_30_tm} and `type` in(1,2,4) or (`virtual_end_tm` > {$time} and `virtual_end_tm` >0)");
		}
		$my_prize_list = $model->findAll($option,'lottery/lottery');
		$my_prize = array();
		if($my_prize_list){
			foreach($my_prize_list as $k => $v){
				$my_prize[$v['id']] = $v;
				$my_prize[$v['id']]['pic_path'] = $prize_info[$v['pid']]['pic_path'];
				$my_prize[$v['id']]['desc'] = nl2br($prize_info[$v['pid']]['desc']);
				if($v['type'] == 2){
					$my_prize[$v['id']]['czk_money'] = $prize_info[$v['pid']]['gift_file']."元";
				}
				if($is_expired == 1){
					$my_prize[$v['id']]['create_tm'] = '已过期';
				}else{
					if($v['type'] == 3 || $v['type'] == 5){
						$my_prize[$v['id']]['create_tm'] = date("Y-m-d",$v['virtual_end_tm']);
					}else{
						$my_prize[$v['id']]['create_tm'] = date("Y-m-d",$v['create_tm']+30*86400);
					}
				}
				if($v['type'] == 3 || $v['type'] == 5){
					$my_prize[$v['id']]['expiry_date'] = date("Y-m-d",$v['virtual_start_tm'])."~".date("Y-m-d",$v['virtual_end_tm']);
				}
				$my_prize[$v['id']]['pos_tm'] = $v['create_tm'];
			}
		}
		unset($my_prize_list);
		unset($option['where']['create_tm']);
		if($is_expired == 1){
			$option['where']['end_tm'] = array('exp',"<{$time}");
		}else{
			$option['where']['end_tm'] = array('exp',">{$time}");
		}		
		$option['table'] = 'qd_sign_gift';
		$my_gift_list = $model->findAll($option,'lottery/lottery');
		foreach($my_gift_list as $k => $v){
			$my_prize["g_".$v['id']] = $v;
			$my_prize["g_".$v['id']]['type'] = 4;
			$my_prize["g_".$v['id']]['desc'] = nl2br($prize_info[$v['pid']]['desc']);
			$my_prize["g_".$v['id']]['pic_path'] = $prize_info[$v['pid']]['pic_path'];
			$my_prize["g_".$v['id']]['prizename'] = $prize_info[$v['pid']]['name'];
			if($is_expired == 1){
				$my_prize["g_".$v['id']]['create_tm'] = '已过期';
			}else{
				$my_prize["g_".$v['id']]['create_tm'] = date("Y-m-d",$v['end_tm']);
			}
			$my_prize["g_".$v['id']]['expiry_date'] = date("Y-m-d",$v['start_tm'])."~".date("Y-m-d",$v['end_tm']);
			$my_prize["g_".$v['id']]['pos_tm'] = $v['update_tm'];			
		}
		if($my_gift_list){
			//此处对数组进行降序排列 
			$my_prize = array_sort($my_prize,'pos_tm','desc'); 
		}
		$redis->set($my_prize_key,$my_prize,1800);		
	}
	return 	$my_prize;
}
//多维数组按照某个键值排序
function array_sort($array,$keys,$type='asc'){  
	//$array为要排序的数组,$keys为要用来排序的键名,$type默认为升序排序  
	$keysvalue = $new_array = array();  
	foreach ($array as $k=>$v){  
		$keysvalue[$k] = $v[$keys];  
	}  
	if($type == 'asc'){  
		asort($keysvalue);  
	}else{  
		arsort($keysvalue);  
	}  
		reset($keysvalue);  
	foreach ($keysvalue as $k=>$v){  
		$new_array[$k] = $array[$k];  
	}  
	return $new_array;  
}  
//补签卡配置
function signature_card_config(){
	global $prefix,$redis,$model,$m_arr,$time;	
	$card_config_key = $prefix.":".$m_arr['id'].':signature_card_config';
	$card_config = $redis->get($card_config_key);	
	if($card_config === null){
		$option = array(
			'where' => array('mid'=>$m_arr['id'],'status'=>1),
			'table' => 'qd_sign_mend',
			'field' => 'price,buy_num,task_num,type',
		);
		$ret = $model->findAll($option,'lottery/lottery');
		$card_config = array();		
		if(!$ret){
			$card_config[0]['buy_num'] = 0;
			$card_config[1]['task_num'] = 0;
		}else{ 
			foreach($ret as $k => $v){
				$card_config[$v['type']] = $v;
			}
		}
		$redis->set($card_config_key,$card_config,1800);	
	}
	return $card_config;	
}
//用户签到卡配置
function get_user_card_config($uid){
	global $prefix, $redis, $m_arr;		
	$user_card_config_key = $prefix.":".$m_arr['id'].':user_card_config:'.$uid;
	$user_card_config = $redis->gethash($user_card_config_key);		
	if(!$user_card_config){
		$res = get_user_data($uid);
		$user_card_config = array(
			'jb_num' => intval($res['cards_num_jb']),
			'task_num' => intval($res['cards_num_task']),
		);
		$redis->sethash($user_card_config_key,$user_card_config,60*86400);		
	}
	return $user_card_config;
}
//充值卡填写手机号
function save_czk_phone($uid,$id,$is_expired,$czk_phone){
	global $prefix,$redis,$model,$m_arr,$time;	
	$where = array(
		'uid' => $uid,
		'id' => $id,
	);	
	$data_update = array(
		'phone' => $czk_phone,
		//'pub_tm' => $time,
		'__user_table' => 'qd_draw_award'
	);
	$ret = $model -> update($where,$data_update,'lottery/lottery');		
	if($ret){
		$prize_list = get_my_prize($uid,$is_expired);
		$prize_list[$id]['phone'] = $czk_phone;
		$my_prize_key = "{$prefix}:{$m_arr['id']}:my_prize:is_expired:".$is_expired.":".$uid;
		$my_prize = $redis->set($my_prize_key,$prize_list,1800);		
	}
	return $ret;
}
//@金币消费
function jb_consume($uid,$money){
	global $m_arr,$model,$sid,$time,$log_key;	
	$device_arr = getDeviceInfo();
	if(!$device_arr['deviceid']){
		return array('code'=>0,'msg'=>'无效设备');
	}
	$device_arr['appversion'] = '6500';
	$header = getHeaderInfo();
	$uc_sid = $_SESSION['UCENTER_SID'];
	$pid = $_SESSION['USER_ID'];
	if(!$pid||$pid=='13176'){
		return array('code'=>0,'msg'=>'pid无效');
	}	
	$new_data = array(
		'uid' => $uid,
		'mid' => $m_arr['id'],
		'money' => $money,
		'add_tm' => $time,
		'__user_table' => 'qd_consume'
	);	
	$id = $model->insert($new_data,'lottery/lottery');
	if(!$id){
		return array('code'=>0,'msg'=>'生成流水失败');
	}	
	//$pid = '45036398';
	//$uc_sid='NDI0MzI0NTN8MTQ5MjE0MDA1M3wwMTR8TU9WRV9URVJNSU5BTHwwNDM5MzJ8QTEwMDAwMzdBMDAwQ0U=';
	load_helper('task');
	$task_client = get_task_client();	
	$task_data = array(
		'water_id' => $id,
		'pid' => $pid,
		'uc_sid' => $uc_sid,
		'amount'=>$money,
		'device' => json_encode($device_arr),
		'header' => json_encode($header),
	);
	$task_client->doBackground('web_sign_consumer_gold', json_encode($task_data));
	return array('code'=>1,'water_id'=>$id);	
}
function jb_consume_res($water_id,$uid){
	global $model;
	$status = 0;
	$msg = '';	
	$i = 0;
	for(;;){
		$option = array(
			'where' => array('id'  =>$water_id),
			'table' => 'qd_consume',
			'field' => 'money,status,msg',
		);
		$list = $model->findOne($option,'lottery/lottery');	
		if($list['status'] == 0){
			$i++;
			if($i > 10){
				break;
			}
			sleep(6);
		}else{
			$status = intval($list['status']);
			$msg = $list['msg'];
			if($status == 1){
				jb_consume_success($uid,$list['money']);
			}
			break;
		}
	}
	return array($status,$msg); 	
}
function jb_consume_success($uid,$money){
	global $m_arr,$prefix,$redis,$model,$sid,$time,$log_key;			
	$user_card_config_key = $prefix.":".$m_arr['id'].':user_card_config:'.$uid;
	$redis->setx("HINCRBY",$user_card_config_key,"jb_num",1);
	//金币消费成功日志
	$log_data = array(
		"imsi"			=>	$_SESSION['USER_IMSI'],
		"device_id"		=>	$_SESSION['DEVICEID'],
		"mac" 			=>  $_SESSION['MAC'],
		"mid"			=>	$m_arr['id'],
		"ip"			=>	$_SERVER['REMOTE_ADDR'],
		"sid"			=>	$sid,
		"time"			=>	$time,
		"user"			=>	$_SESSION['USER_NAME'],
		'money'			 => $money,
		'uid'			=>	$uid,
		'key'			=>	'jb_consume_success',
	);
	permanentlog($log_key, json_encode($log_data));	
	//金币购买补签卡成功日志	
	$log_data = array(
		'time'	=>	$time,
		'imsi'	=>	$_SESSION['USER_IMSI'],
		'uid'	=>	$uid,
		'sid' => $sid,	
		'username'	=>	$_SESSION['USER_NAME'],
		'device_id'	=>	$_SESSION['DEVICEID'],
		"mac" => $_SESSION['MAC'],
		"mid"	=>	$m_arr['id'],
		'money' => $money,
		'type' => 0,//0：金币购买补签卡 1：做任务获得补签卡
		'key'	=>	'signature_card'
	);			
	permanentlog($log_key, json_encode($log_data));		
	//补签卡总数
	save_deduction_num($uid,"cards_num_jb");		
}	
//用户软件下载和打开状态
function user_soft_status($uid,$pkg,$status){
	global $prefix,$redis,$m_arr,$today;	
	//1下载 2安装 3打开
	$sign_config = sign_config($uid);
	if($sign_config[$today]['is_card_task'] == 1){
		//当天已领
		return false;
	}	
	$soft_status_key = $prefix.":".$m_arr['id'].':soft_status:'.$uid;
	$soft_status = $redis->get($soft_status_key);
	if($status == 3 && $soft_status[$pkg]['is_down'] != 1){
		//必须先下载后打开才算
		return false;
	}
	$task_status_key = $prefix.":".$m_arr['id'].':task_status:'.$uid;
	$task_status = $redis->get($task_status_key);
	if($task_status ==1){
		return false;
	}	
	if($status == 1){
		//不可领
		$redis->set($task_status_key,0,31*86400);
	}else if($status == 3){
		//可领
		$redis->set($task_status_key,1,31*86400);
	}	
	$con_arr = array(
		'1' => 'is_down',
		'2' => 'is_install',
		'3' => 'is_open'
	);
	if($soft_status === null){
		$array = array();
		$array[$pkg][$con_arr[$status]] = 1;
		$redis->set($soft_status_key,$array,60*86400);

	}else{
		$soft_status[$pkg][$con_arr[$status]] = 1;
		$redis->set($soft_status_key,$soft_status,60*86400);
	}
}
//实物奖品是否已发
function get_is_pub($awardid){
	global $model;	
	$option = array(
		'where' => array(
			'id' => $awardid,
			'is_pub' => 1,
		),
		'table' => 'qd_draw_award',
		'field' => 'id,is_pub',
	);
	$award = $model->findOne($option,'lottery/lottery');
	if($award['is_pub'] == 1){
		return 1;
	}else{
		return 0;	
	}
}
function device_user_num($uid){
	global $redis, $m_arr, $prefix;	
	$device_user = $prefix.":".$m_arr['id'].':device_user:'.$_SESSION['DEVICEID'];
	$uid_arr = $redis->get($device_user);	
	$uid_arr[$uid] = 1;
	$redis->set($device_user,$uid_arr,60*86400);	
	$total = count($uid_arr);
	return $total;
}
function get_soft_info($pkg){
	global $model;	
	$option = array(
		'where' => array(
			'package' => $pkg,
			'status' => 1,
		),
		'table' => 'sj_soft',
		'order' => 'softid desc',
		'field' => 'softid,package',
		'cache_time' => 20*60
	);
	$softinfo = $model->findOne($option);
	return 	$softinfo;
}
function getHeaderInfo(){
	$header = array('appchannel'=>$_SESSION['CHANNEL_ID']);
	return $header;
}

function getDeviceInfo(){
	$device_arr = array(
		'deviceid'=>$_SESSION['DEVICEID'],
		'osver'=>$_SESSION['FIRMWARE'],
		'nettype'=>$_SESSION['NET_TYPE'],
		'netserver'=>$_SESSION['MODEL_OID'],
		'screen'=>$_SESSION['RESOLUTION'],
		'imsi'=>$_SESSION['USER_IMSI'],
		'mac'=>$_SESSION['MAC'],
		'ip'=>$_SESSION['ip'],
		'abi'=>$_SESSION['ABI'],
		'appversion' => $_SESSION['VERSION_CODE']
	);
	return $device_arr;
}
//获取红包数量
function get_red_now($redid){
	global $prefix,$redis,$model,$m_arr,$time,$configs;	
	$is_red_num_key = $prefix.":".$m_arr['id'].':is_red_num:'.$redid;
	$red_num = $redis->get($is_red_num_key);	
	if($red_num === null){
		$send_arr = json_encode(array('KEY' =>'REDPACK_INFO','PACKID'=>array($redid),'VR'=>1));
		if($configs['is_test'] == 1 ) {
			$cil = "curl -d '{$send_arr}' 'http://api.test.anzhi.com/goserv.php?'";
		}else{
			$cil = "curl -d '{$send_arr}' 'http://dev.gomarket.goapk.com/goserv.php?'";
		}
		
		$ret_num = shell_exec($cil);
		$ret_arr = json_decode($ret_num,true);
		$red_num = intval($ret_arr['DATA'][0]['restnum']);
		if($red_num < 1) $redis->set($is_red_num_key,0,86400);
	}
	return $red_num;
}
function get_now_time(){
	return strtotime("2017-03-23");
	global $model;
	$option = array(
		'where' => array(
				'status'  => 1,
				'conf_id' => 333
		),
		'table' => 'pu_config',
		'field' => 'configcontent',
	);
	$list = $model->findOne($option);
	return strtotime($list['configcontent']);
}

function isExistsBillByDevice($redid,$imei,$mac,$is_pre=0){
	global $model;	
	if(!$imei||!$mac){
		return -1;
	}
	$option = array(
		'table' => 'sj_redpacket_bill',
		'where' => array(
			'packid' => $redid,
			'imei' => array('exp',"='{$imei}' or mac = '{$mac}'"),
		),
		'cache_time' => 20*60
	);
	
	if($is_pre) $option['table'] = 'sj_redpacket_pre_bill';
	$ret = $model -> findOne($option,'redpk_s/redpacket') ? 1 : 0;
	//file_put_contents('/tmp/zhuang.log',$model->getsql());
	return $ret;

}
function check_user_jb_num($uid){
	global $prefix,$redis,$m_arr;		
	$user_jb_num_key = $prefix.":".$m_arr['id'].':user_jb_num:'.$uid;
	$user_jb_num = $redis->get($user_jb_num_key);
	if($user_jb_num === null){		
		$redis->set($user_jb_num_key,1,86400*30);		
		$user_jb_num = 1;
	}else{
		$user_jb_num = $redis->setx('incr',$user_jb_num_key,1);		
	}
	return $user_jb_num;
}
//同步金币补签卡数量
function check_card_num($uid,$jb_num,$buy_num){
	global $prefix,$redis,$model,$m_arr;
	$option = array(
		'where' => array(
			'uid'  =>$uid,
			'mid'=>$m_arr['id'],
			'status' => 1
		),
		'table' => 'qd_consume',
		'field' => 'count(*) as total,money',
	);
	$list = $model->findOne($option,'lottery/lottery');
	$total = intval($list['total']);
	if($total > $jb_num){
		$num = ($total-$jb_num);
		if(!$num || $num < 0){
			$user_jb_num_key = $prefix.":".$m_arr['id'].':user_jb_num:'.$uid;
			$user_jb_num = $redis->set($user_jb_num_key,$total,86400*30);				
			return $total;
		}		
		for($i=1;$i<=$num;$i++){
			jb_consume_success($uid,$list['money']);
		}
	}else{
		$user_jb_num_key = $prefix.":".$m_arr['id'].':user_jb_num:'.$uid;
		$user_jb_num = $redis->set($user_jb_num_key,$total,86400*30);		
	}
	return $total;
}