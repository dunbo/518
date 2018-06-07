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
$from_type = $_GET['from_type'] ? $_GET['from_type'] : $_POST['from_type'];
$from_type = $from_type ? $from_type : '';
$tplObj -> out['from_type'] = $from_type;
//ctype_digit  检查时候是只包含数字字符的字符串（0-9）
if(!ctype_digit($active_id)){
	exit;
}
$sid = $_GET['sid'] ? $_GET['sid'] : $_POST['sid'];
session_begin($sid);
$prefix = "cddh";
if($configs['is_test'] == 1 ) {
	list($day_num,$operation,$second)  = get_now_time();
	if($operation == "-"){
		$time  = time()+$day_num*86400-$second;
	}else{
		$time  = time()+$day_num*86400+$second;
	}
	//$time = strtotime("2018-02-08 11:30:00");
}else {
	$time  = time();
}
$today = date('Y-m-d',$time);
if($_SESSION['USER_IMSI']){
	$imsi = $_SESSION['USER_IMSI'];
}
if($_SESSION['DEVICEID'] == "A1000037A000CE" || $_GET['DUG']){
	$imsi = '460028011771946';  //测试用
	$_SESSION['VERSION_CODE'] = 6500;
}
if(!$imsi || $imsi == '000000000000000'){
	$imsi = '';
}
//var_dump($imsi);
user_loging_new();
if(isset($_SESSION['USER_UID']) && $_SESSION['USER_ID'] != 13176){//已登录
	$uid = $_SESSION['USER_UID'];
}else{
	$uid = '';
}
//var_dump($imsi);
$activity_host = $configs['activity_url'];
$tplObj -> out['activity_host'] = $activity_host;
$stop = $_GET['stop'] ? $_GET['stop'] : $_POST['stop'];

if($stop != 1){
	$activity_list = activity_is_stop($active_id);
	if(!$activity_list){
		$url = $activity_host."/lottery/{$prefix}/index.php?stop=1&aid=".$active_id."&sid=".$sid;
		header("Location: {$url}");
		exit;
	}
}

//绑定状态
function get_bind_status(){
	global  $prefix,$active_id,$model,$redis,$imsi;		
	$is_bind_key = $prefix.":".$active_id.":is_bind:".$imsi;
	$is_bind = $redis->get($is_bind_key);
	if($is_bind == null){
		$where = array('aid' => $active_id,'imsi' => $imsi,'binding'=>1);
		$option = array(
			'where' => $where,
			'table' => 'send_verification_code' ,
			'field' => 'imsi,mobile'
		);
		$is_bind = $model->findOne($option, 'lottery/lottery');
		if(!$is_bind) return false;
		$redis->set($is_bind_key,$is_bind,30*86400);
	}
	return $is_bind;
	
}
//验证是否是首次登录的用户
function bind_user(){
	global  $prefix,$active_id,$model,$redis,$imsi,$uid;
	if(!$uid) return false;	
	$bind_uid_key = $prefix.":".$active_id.":bind_uid:".$imsi;
	$bind_uid = $redis->get($bind_uid_key);
	$imsi_list = get_bind_status();
	if($bind_uid == null){
		$where = array('aid' => $active_id,'address' => $imsi);
		$option = array(
			'where' => $where,
			'table' => 'valentine_draw_userinfo' ,
			'field' => 'uid,phone,address'
		);
		$bind_uid = $model->findOne($option, 'lottery/lottery');
		if(!$bind_uid){
			$data = array(
				'aid' => $active_id,
				'uid' => $uid,
				'username' => $_SESSION['USER_NAME'],
				'phone' => $imsi_list['mobile'],
				'address' => $imsi,
				'create_tm' => $time,
				'__user_table' => 'valentine_draw_userinfo',
			);
			$orderid = $model -> insert($data,'lottery/lottery');
		}
		$redis->set($bind_uid_key,$bind_uid,60*86400);
		return 1;
	}else{
		
		if($uid != $bind_uid['uid']){
			return 0;
		}
		return 1;
	}
}
//领取复活次数
function resurrection_number($limit=3){
	global $prefix,$configs,$active_id,$sid,$time,$today,$imsi,$model,$redis;
	$down_num_key = $prefix.":".$active_id.":down_num:".$imsi.":".$today;
	$down_num = $redis->get($down_num_key);
	if(!$down_num){
		return array(
			'code' => 0,
			'msg' => '去完成',
		);				
	}
	$resurrection_num_key = $prefix.":".$active_id.":resurrection_num:".$imsi.":".$today;
	$resurrection_num = $redis->get($resurrection_num_key);
	if($resurrection_num >= $down_num){
		return array(
			'code' => 0,
			'msg' => '去完成~',
		);			
	}		
	$resurrection_num = $redis->setx('incr',$resurrection_num_key,1);
	$redis->expire($resurrection_num_key,86400);
	if($resurrection_num > $limit){
		return array(
			'code' => 0,
			'msg' => '当天已领取，请不要重复领取',
		);
	}		
	//用户领取日志
	$log_data = array(
		'time'	=>	$time,
		'imsi'	=>	$_SESSION['USER_IMSI'],
		'sid' => $sid,	
		'device_id'	=>	$_SESSION['DEVICEID'],
		"DEVICE_SN" => $_SESSION['DEVICE_SN'],
		'activity_id'	=>	$active_id,
		'key'	=>	'receive'
	);
	permanentlog('activity_'.$active_id.'.log', json_encode($log_data));	
	return array(
		'code' => 1,
		'msg' => '领取成功',
	);		
}
//下载
function get_down_flow($limit=3){
	global $prefix,$configs,$active_id,$time,$today,$imsi,$model,$redis;
	$pkg = $_POST['pkg'];
	//一个软件只能用一次
	$is_down_key = $prefix.":".$active_id.":is_down:".$imsi.":".$pkg;
	$is_down = $redis->setnx($is_down_key,1);
	$redis->expire($is_down_key,60*86400);
	if($is_down){
		//一天只能下载三个软件
		$down_num_key = $prefix.":".$active_id.":down_num:".$imsi.":".$today;
		$down_num = $redis->get($down_num_key);
		if($down_num >= $limit){
			return array(
				'code' => 0,
				'msg' => '当天下载次数已经上限',
			);			
		}		
		$down_num = $redis->setx('incr',$down_num_key,1);
		$redis->expire($down_num_key,86400);
		if($down_num > $limit){
			return array(
				'code' => 0,
				'msg' => '当天下载次数已经上限',
			);	
		}else{
			return array(
				'code' => 1,
				'msg' => '下载成功',
			);	
		}
	}else{
		return array(
			'code' => 0,
			'msg' => '该软件已经下载过了',
		);	
	}	
}
//发送手机验证码
function send_active_mobile($mobile) {
	global $prefix,$configs,$active_id,$time,$today,$imsi,$model,$redis;	
    if (!preg_match('/[0-9]{11}/', $mobile)) {
        return array(
            'code' => 0,
            'msg' => '请提供要发送的手机号',
        );
    }
	$send_code_tm_key = $prefix.":".$active_id.":send_code_tm:".$mobile.":".$today;
	$send_code_tm = $redis->setnx($send_code_tm_key,1);
	$redis->expire($send_code_tm_key,60);
	if(!$send_code_tm){
        return array(
            'code' => 0,
            'msg' => '每分钟只能发一条！',
        );				
	}
	$send_num_key = $prefix.":".$active_id.":send_num:".$mobile.":".$today;
	$send_num = $redis->setx('incr',$send_num_key,1);
	$redis->expire($send_num_key,86400);
	if($send_num > 5){
        return array(
            'code' => 0,
            'msg' => '每天最多可获取5次验证码，明天再来吧~',
        );		
	}

		
	$rand = rand_code(6);
	$table = "send_verification_code";
	$where = array('aid' => $active_id,'mobile' => $mobile);
	$option = array(
		'where' => $where,
		'table' => $table ,
	);
	$find = $model->findOne($option, 'lottery/lottery');
	if(!$find){	
		$data = array(
			'aid' => $active_id,
			'mobile' => $mobile,
			'imsi' => $imsi,
			'code' => $rand,
			'send_num' => 1,
			'send_tm' => $time,
			'__user_table' => $table ,
		);
		$ret = $model -> insert($data,'lottery/lottery');
	}else{
		if($find['binding'] == 1){
			return array(
				'code' => 0,
				'msg' => '该手机号已经绑定过了，请不要重复绑定',
			);		
		}
		$data = array(
			'code' => $rand,
			'send_num' => array("exp","`send_num`+1"),
			'send_tm' => $time,
			'__user_table' => $table ,
		);
		$ret = $model->update($where, $data, 'lottery/lottery');		
	}
	
	$email_cont = "您好，您的验证码是：".$rand."，若非本人操作请忽略。";
    $tmp = http_post_mobile(array('phone' => $mobile, 'content' => $email_cont));
    if (isset($tmp['code']) && is_numeric($tmp['code'])) {
        if ($tmp['code'] != 0) {
            return array(
                'code' => 0,
                'msg' => $tmp['msg'],
            );
        }else{
            return array(
                'code' => 1,
                'msg' => "发送成功",
            );			
		}
    } else {
        return array(
            'code' => 0,
            'msg' => '短信发送无返回结果！',
        );
    }		
}
//绑定手机号
function check_mobile($mobile,$code){
	global  $active_id,$model,$redis,$imsi,$time;		
	$where = array('aid' => $active_id,'mobile' => $mobile);
	$option = array(
		'where' => $where,
		'table' => 'send_verification_code' ,
	);
	$find = $model->findOne($option, 'lottery/lottery');
	if($find['code'] == $code){
		if(($time-$find['send_tm']) > 5*60 ){
			return array(
				'code' => 0,
				'msg' => '验证码已过期',
			);				
		}			
		$data = array(
			'binding' => 1,
			'__user_table' => "send_verification_code" ,
		);
		$ret = $model->update($where, $data, 'lottery/lottery');
		if($ret){			
			$is_bind_key = $prefix.":".$active_id.":is_bind:".$imsi;
			$redis->set($is_bind_key,$is_bind,30*86400);
			return array(
				'code' => 1,
				'msg' => '绑定成功',
			);
		}else{
			return array(
				'code' => 0,
				'msg' => '绑定失败',
			);			
		}	
	}else{
		return array(
            'code' => 0,
            'msg' => '验证码错误！',
        );
	}	
}
function http_post_mobile($vals) {
	global $configs;
    if( $configs['is_test'] ) {
        $url = 'http://118.26.224.18/service.php?do=sendSms';
        $host = 'Host: smsapi.goapk.com';
    } else {
        $url = 'http://192.168.1.18/service.php?do=sendSms';
        $host = 'Host: smsapi.goapk.com';
    }
    $url .= '&key=87f337977106a8b12ca1ccb11b3c2637&rand=' . microtime(true);
	$vals	=	http_build_query($vals);
	$res	=	httpGetInfo($url,$host, $vals,"flow_sendsms.log");
	$last	=	json_decode($res,true);
	return $last;	
}

//var_dump(flow_recharge(18310215648,300));//移动
function flow_recharge($mobile,$price){
	global $configs,$active_id,$sid,$time,$imsi,$model,$prefix,$redis,$today;
	if( $configs['is_test'] ) {
		$secret_key = "FhbYDSZuzJiXpwy4";
		$partid = "1000000000000174";
		$notifyurl = 'http://m.test.anzhi.com/lottery/cddh/callback.php';
	}else{
		$secret_key = "FXIpNWC3vz8DMMCZ";
		$partid = "1000000000000109";
		$notifyurl = 'http://promotion.anzhi.com/lottery/cddh/callback.php';		
	}
	$data = array(
		'aid' => $active_id,
		'mobile' => $mobile,
		'price' => $price,
		'type' => 2,
		'imsi' => $imsi,
		'day'=>date("Ymd",$time),
		'add_tm' => $time,
		'__user_table' => 'recharge_flow_bill',
	);
	$orderid = $model -> insert($data,'lottery/lottery');
		
	# 加密
	$send_data = array(
		'interfacecode' => 'N2002',
		'channelorderid' => $orderid,
		'billid' => $mobile,
		'modeltype'=>'2',
		'prodtype'=>'2',//1话费2流量
		'proddenominationprice'=>$price,//单位为分或者M
		'billtype'=>'2',//1话费2流量
		'requestdate' => date("Y-m-d H:i:s"),
		'content'=>'充流量',//订单描述
		'notifyurl'=> $notifyurl."?aid=".$active_id."&sid=".$sid."&orderid=".$orderid."&imsi=".$imsi,//回调接口
		'callreqparam'=>'',
		'extparam'=>'',
	);
	$json_str = json_encode($send_data);
	
	include_once ('./aes.php');
	$aes = new AES();
	//设置密钥
	$aes->__set("key",$secret_key);
	$endata= $aes->encrypt($json_str);
	$md5_str = md5($endata.get_total_millisecond().$partid);
	$vals = array(
		'partid' => $partid,
		'data' => $endata,
		'sign' => $md5_str,
		'time'=> get_total_millisecond(),	
	);
	$ret = post_flow_data($vals,'post_flow_'.$active_id.".log");
	$my_prize_key = $prefix.":".$active_id.":my_prize:".$imsi;
	$redis->delete($my_prize_key);		
	if($ret['result'] != 1){
		$where = array('orderid' => $orderid);
		$data = array(
			'status' => 2,
			'update_tm' => $time,
			'__user_table' => 'recharge_flow_bill',
		);
		$model->update($where, $data, 'lottery/lottery');
		//用户提取流量日志
		$log_data = array(
			'time'	=>	$time,
			'imsi'	=>	$_SESSION['USER_IMSI'],
			'sid' => $sid,	
			'device_id'	=>	$_SESSION['DEVICEID'],
			"DEVICE_SN" => $_SESSION['DEVICE_SN'],
			'activity_id'	=>	$active_id,
			'price'=>$price,//单位M
			'orderid' => $orderid,
			'key'	=>	'use_flow',
			'msg' => '提取失败',
		);
		permanentlog('activity_'.$active_id.'.log', json_encode($log_data));		
		return array('code'=>0,'msg'=>'提取失败');	
	}
	return array('code'=>1,'msg'=>'提取成功');	
}
//生成随机码
function rand_code($num) {
    $str = '';
    for ($i = 0; $i < $num; $i++) {
        $str .= mt_rand(0, 9);
    }
    return $str;
}
/*  *  *返回字符串的毫秒数时间戳  */  
function get_total_millisecond(){  
	$time = explode (" ", microtime () );   
	$time = $time [1] . ($time [0] * 1000);   
	$time2 = explode ( ".", $time );   
	$time = $time2 [0];  
	return $time;  
}
/**********记录页***********/
function my_prize(){
	global  $prefix,$active_id,$model,$redis,$imsi,$uid;		
	$my_prize_key = $prefix.":".$active_id.":my_prize:".$imsi;
	$my_prize = $redis->get($my_prize_key);	
	if($my_prize == null){
		$option = array(
			'where' => array(
				'aid' => $active_id,
				'status'=>1,
				'imsi'=>$imsi,
			),
			'table' => 'imsi_lottery_award',
			'field'=>'award,address,time',
			'order' => '`time` desc'
		);
		$list = $model->findAll($option, 'lottery/lottery');
		$my_prize = array();
		$tm_arr = array(
			1 => "11:30",
			2 => "13:30",
			3 => "19:30",
			4 => "20:30",
			5 => "21:30",
		);		
		foreach($list as $k =>$v){
			$data = json_decode($v['address'],true);
			$my_prize[$data[0]]['date'] = date("m",$v['time'])."月".date("d",$v['time'])."日";
			$my_prize[$data[0]][$data[1]]['time_slot'] = $tm_arr[$data[1]];
			$my_prize[$data[0]][$data[1]]['price'] = $v['award']."M流量";
		}		
		$redis->set($my_prize_key,$my_prize,86400);
	}
	$my_prize2_key = $prefix.":".$active_id.":my_prize2:".$uid;
	$my_prize2 = $redis->get($my_prize2_key);	
	if($my_prize2 == null){	
		$option = array(
			'where' => array(
				'aid' => $active_id,
				'status'=>1,
				'uid'=>$uid,
			),
			'table' => 'valentine_draw_award',
			'field'=>'username,ext,create_tm,money,prizename',
			'order' => 'create_tm desc'
		);
		$list = $model->findAll($option, 'lottery/lottery');
		$tm_arr = array(
			1 => "11:30",
			2 => "13:30",
			3 => "19:30",
			4 => "20:30",
			5 => "21:30",
		);			
		foreach($list as $k =>$v){
			$data = json_decode($v['ext'],true);
			$my_prize2[$data[0]]['date'] = date("m",$v['create_tm'])."月".date("d",$v['create_tm'])."日";
			$my_prize2[$data[0]][$data[1]]['time_slot'] = $tm_arr[$data[1]];
			$my_prize2[$data[0]][$data[1]]['price'] = $v['money']."元礼券";
			$my_prize2[$data[0]][$data[1]]['pkg'] = $v['prizename'];
		}
		$redis->set($my_prize2_key,$my_prize2,86400);		
	}	
	return array($my_prize,$my_prize2);	
}
/***已提取流量****/
function grant_flow(){
	global  $prefix,$active_id,$model,$redis,$imsi;		
	$grant_flow_key = $prefix.":".$active_id.":grant_flow:".$imsi;
	$grant_flow = $redis->get($grant_flow_key);	
	if($grant_flow == null){
		$option = array(
			'where' => array(
				'aid' => $active_id,
				'imsi'=>$imsi,
				'status'=>array(0,1),
			),
			'table' => 'recharge_flow_bill',
			'field'=>'mobile,day,sum(price) as price',
			'group' => 'day',
			'order' => 'day asc'
		);
		$list = $model->findAll($option, 'lottery/lottery');
		$grant_flow = array();
		$total = 0;
		foreach($list as $k => $v){
			$total = $total+$v['price'];
			$grant_flow[$v['day']] = $v['price'];
		}
		$grant_flow['price_total'] = $total;
		$redis->set($grant_flow_key,$grant_flow,86400);
	}
	return $grant_flow;	
}
//获取手机归属地
//get_phone_ascription('15901084927');
function get_phone_ascription($mobile){
	global $configs,$active_id,$time,$imsi,$model,$prefix,$redis;
	$phone_ascription_key = $prefix.":".$active_id.":phone_ascription:".$mobile;
	$phone_ascription = $redis->get($phone_ascription_key);	
	if($phone_ascription == null){
		if( $configs['is_test'] ) {
			$secret_key = "FhbYDSZuzJiXpwy4";
			$partid = "1000000000000174";
		}else{
			$secret_key = "FXIpNWC3vz8DMMCZ";
			$partid = "1000000000000109";
		}
			
		# 加密
		$send_data = array(
			'interfacecode' => 'N2005',
			'phoneNo' => $mobile,
			'extparam'=>'',
		);
		$json_str = json_encode($send_data);
		
		include_once ('./aes.php');
		$aes = new AES();
		//设置密钥
		$aes->__set("key",$secret_key);
		$endata= $aes->encrypt($json_str);
		$md5_str = md5($endata.get_total_millisecond().$partid);
		$vals = array(
			'partid' => $partid,
			'data' => $endata,
			'sign' => $md5_str,
			'time'=> get_total_millisecond(),	
		);
		$ret = post_flow_data($vals,'post_flow_phone_ascription'.$active_id.".log");
		if($ret['result']){
			$phone_ascription = $ret['detailinfo'];
			$redis->set($phone_ascription_key,$ret['detailinfo'],60*86400);	
		}
	}
	return $phone_ascription;
}
/*********订单查询
****************/
//get_order_data("10022");
function get_order_data($orderid){
	global $configs,$active_id,$time,$imsi,$model,$prefix,$redis;

	if( $configs['is_test'] ) {
		$secret_key = "FhbYDSZuzJiXpwy4";
		$partid = "1000000000000174";
	}else{
		$secret_key = "FXIpNWC3vz8DMMCZ";
		$partid = "1000000000000109";
	}
			
	# 加密
	$send_data = array(
		'interfacecode' => 'N2001',
		'channelorderid' => $orderid,
		'extparam'=>'',
	);
	$json_str = json_encode($send_data);
		
	include_once ('./aes.php');
	$aes = new AES();
	//设置密钥
	$aes->__set("key",$secret_key);
	$endata= $aes->encrypt($json_str);
	$md5_str = md5($endata.get_total_millisecond().$partid);
	$vals = array(
		'partid' => $partid,
		'data' => $endata,
		'sign' => $md5_str,
		'time'=> get_total_millisecond(),	
	);
	$ret = post_flow_data($vals,'post_flow_'.$active_id);
	//var_dump($ret);exit;
	return $ret;
}
/**
 * 流量发放接口
 * @param  array $val
 * @return array
 */
function post_flow_data($vals,$log_name){
	global $configs;
	if( $configs['is_test'] ) {
		$str = "sandbox";
	}
	$host = $str."morp.blueplus.cc";
	$url = "https://".$str."morp.blueplus.cc/cgi_bin/bcmdp/interServer/interface";	
	$vals	=	http_build_query($vals);
	$res	=	httpGetInfo($url,$host, $vals,$log_name);
	$last	=	json_decode($res,true);
	return $last;
}
function getthemonth(){
	global $today,$time;	
	$firstday = date('Y-m-01', strtotime($today));
	$lastday = strtotime("$firstday +1 month -1 day");
	if($time >= $lastday){
		return 1;
	}else{
		return 0;
	}
}



function get_now_time(){
	global $model;
	$option = array(
		'where' => array(
				'status'  => 1,
				'conf_id' => 345
		),
		'table' => 'pu_config',
		'field' => 'configcontent',
	);
	$list = $model->findOne($option);
	return explode(",",$list['configcontent']);
}
//获取前场次
function get_screenings(){
	global $time;
	$h = date("H",$time);
	if($h == 11){
		return 1;
	}else if($h == 13){
		return 2;
	}else if($h == 19){
		return 3;
	}else if($h == 20){
		return 4;
	}else if($h == 21){
		return 5;
	}else if($h > 21){
		return -1;
	}else if($h == 12){
		return -2;
	}else if($h > 13){
		return -3;
	}else{
		return 0;
	}
}
//获取场次的时间段
function get_screenings_tm(){
	global $prefix,$active_id,$time,$redis;
	$screenings_key = $prefix.":".$active_id.":screenings";
	$screenings = $redis->get($screenings_key);	
	if($screenings == null){	
		$screenings = array();
		$tm_arr = array(
			1 => "11:30:00",
			2 => "13:30:00",
			3 => "19:30:00",
			4 => "20:30:00",
			5 => "21:30:00",
		);
		for($i=8;$i<=21;$i++){
			for($ii=1;$ii<=5;$ii++){
				$num = ($i<10) ? "0" : '';
				$screenings["2018-02-".$num.$i][$ii] = strtotime("2018-02-".$i." ".$tm_arr[$ii]);
			}
		}
		$redis->set($screenings_key,$screenings,30*86400);	
	}
	return $screenings;	
}
function coupon_config(){
	global $configs,$today;	
	if($configs['is_test'] ) {		
		$coupon_arr = array(
			3 => 946,//val 礼券id   key 3元礼券
			6 => 949,//6元礼券
			10 => 950,//10元礼券
			20 => 951,//20元礼券
			30 => 952,//30元礼券
			50 => 953,//50无礼券
		);	
		$coupon_flip = array_flip($coupon_arr);
		
		$coupon_day_arr = array();
		$coupon_day_arr['2018-02-09'][3] = 946;	
		$coupon_day_arr['2018-02-12'][3] = 946;	
		$coupon_day_arr['2018-02-13'][3] = 946;	
		$coupon_day_arr['2018-02-14'][3] = 946;	
		$coupon_day_arr['2018-02-16'][3] = 946;	
		$coupon_day_arr['2018-02-21'][3] = 946;	
		
		$coupon_day_arr['2018-02-08'][6] = 949;	
		$coupon_day_arr['2018-02-10'][6] = 949;	
		$coupon_day_arr['2018-02-11'][6] = 949;	
		$coupon_day_arr['2018-02-15'][6] = 949;	
		$coupon_day_arr['2018-02-16'][6] = 949;	
		$coupon_day_arr['2018-02-17'][6] = 949;	
		$coupon_day_arr['2018-02-18'][6] = 949;	
		$coupon_day_arr['2018-02-20'][6] = 949;			
	}else{
		$coupon_flip = array(
			7054 => 3,
			6990 => 3,
			7050 => 3,
			6998 => 3,
			6992 => 3,
			7053 => 6,
			6989 => 6,
			7049 => 6,
			6997 => 6,
			6991 => 6,
			7052 => 6,
			7051 => 6,
			6985 => 10,
			6986 => 20,
			6993 => 30,
			6994 => 50,
		);
		$coupon_day_arr = array();
		$coupon_day_arr['2018-02-09'][3] = 6998;	
		$coupon_day_arr['2018-02-12'][3] = 6998;	
		$coupon_day_arr['2018-02-19'][3] = 6998;	
		
		$coupon_day_arr['2018-02-13'][3] = 6990;	
		$coupon_day_arr['2018-02-14'][3] = 7050;	
		$coupon_day_arr['2018-02-16'][3] = 6998;	
		$coupon_day_arr['2018-02-21'][3] = 6992;	
		
		$coupon_day_arr['2018-02-08'][6] = 6997;	
		$coupon_day_arr['2018-02-10'][6] = 6997;	
		$coupon_day_arr['2018-02-16'][6] = 6997;
		
		$coupon_day_arr['2018-02-11'][6] = 6989;	
		$coupon_day_arr['2018-02-15'][6] = 7049;	
		$coupon_day_arr['2018-02-17'][6] = 6991;	
		$coupon_day_arr['2018-02-18'][6] = 7052;	
		$coupon_day_arr['2018-02-20'][6] = 7051;	
		$coupon_arr = array(
			3 => $coupon_day_arr[$today][3],//val 礼券id   key 3元礼券
			6 => $coupon_day_arr[$today][6],//6元礼券
			10 => 6985,//10元礼券
			20 => 6986,//20元礼券
			30 => 6993,//30元礼券
			50 => 6994,//50无礼券
		);	
	}
	return array($coupon_arr,$coupon_flip,$coupon_day_arr);
}
function get_coupon_pkg(){
	global $configs;	
	if($configs['is_test'] ) {	
		$coupon_pkg = array(
			'2018-02-08' => 'com.mobimtech.natives.ivp',
			'2018-02-09' => 'com.bf.ERShuangKou.anzh',
			'2018-02-10' => 'com.tmall.wireless',
			'2018-02-11' => 'com.tencent.qqpim',
			'2018-02-12' => 'com.mobimtech.natives.ivp',
			'2018-02-13' => 'com.mobimtech.natives.ivp',
			'2018-02-14' => 'com.mobimtech.natives.ivp',
			'2018-02-15' => 'com.mobimtech.natives.ivp',
			'2018-02-16' => 'com.mobimtech.natives.ivp',
			'2018-02-17' => 'com.mobimtech.natives.ivp',
			'2018-02-18' => 'com.mobimtech.natives.ivp',
			'2018-02-19' => 'com.mobimtech.natives.ivp',
			'2018-02-20' => 'com.mobimtech.natives.ivp',
			'2018-02-21' => 'com.mobimtech.natives.ivp',
		);  	
	}else{
		$coupon_pkg = array(
			'2018-02-08' => 'com.tuoyin.jdzc.anzhi',
			'2018-02-09' => 'com.tuoyin.jdzc.anzhi',
			'2018-02-10' => 'com.tuoyin.jdzc.anzhi',
			'2018-02-11' => 'com.xianyugame.zjzr.anzhi',
			'2018-02-12' => 'com.tuoyin.jdzc.anzhi',
			'2018-02-13' => 'com.xianyugame.zjzr.anzhi',
			'2018-02-14' => 'com.shuiguotang.ylcs.sy37',
			'2018-02-15' => 'com.shuiguotang.ylcs.sy37',
			'2018-02-16' => 'com.tuoyin.jdzc.anzhi',
			'2018-02-17' => 'com.pocketmon.anzhi',
			'2018-02-18' => 'com.xulong.pokemon.anzhi',
			'2018-02-19' => 'com.tuoyin.jdzc.anzhi',
			'2018-02-20' => 'com.xulong.pokemon.anzhi',
			'2018-02-21' => 'com.pocketmon.anzhi',
		);   
	}
	return $coupon_pkg;	
}                        
//上限配置
function get_limit_list(){
	global $configs,$active_id,$time,$imsi,$model,$prefix,$redis;
	$limit_list_key = $prefix.":".$active_id.":limit_list";
	$limit_list = $redis->get($limit_list_key);	
	if($limit_list == null){
		$tm_arr = array(
			1 => "11:30-12:00",
			2 => "13:30-14:00",
			3 => "19:30-20:00",
			4 => "20:30-21:00",
			5 => "21:30-22:00",
		);
		$coupon_arr = coupon_config();
		list($coupon_arr,$coupon_flip,$coupon_day_arr) = coupon_config();
		$coupon_pkg = get_coupon_pkg();
		$limit_list = array(			
			'2018-02-08' => array(
				1 => array(
					'limit' =>10,
					'onlie_num' => array(99632,127432),
					'time' => $tm_arr[1],
					'award' => 100,//11:30  100M流量
				),
				2 => array(
					'limit' =>10,
					'onlie_num' => array(107453,153652),
					'time' => $tm_arr[2],
					'award' => $coupon_day_arr['2018-02-08'][6],//13:30礼券id		
					'pkg' => $coupon_pkg['2018-02-08'],					
				),
				3 => array(
					'limit' =>50,
					'onlie_num' => array(202524,279736),
					'time' => $tm_arr[3],
					'award' => 30,//17:30  30M流量					
				),
				4 => array(
					'limit' =>50,
					'onlie_num' => array(487635,552719),
					'time' => $tm_arr[4] ,
					'award' =>$coupon_arr[30],//20:30   礼券id 	
					'pkg' => $coupon_pkg['2018-02-08'],						
				),
				5 => array(
					'limit' =>30,
					'onlie_num' => array(396352,421726),
					'time' => $tm_arr[5] ,
					'award' =>$coupon_arr[10],//21:30   礼券id 	
					'pkg' => $coupon_pkg['2018-02-08'],						
				),
			),
			'2018-02-09' => array(
				1 => array(
					'limit' =>10,
					'onlie_num' => array(99632,127432),
					'time' => $tm_arr[1] ,
					'award' => 30,//11:30  30M流量
				),
				2 => array(
					'limit' =>10,
					'onlie_num' => array(107453,153652),
					'time' => $tm_arr[2]  ,
					'award' =>$coupon_arr[10],//20:30   礼券id 	
					'pkg' => $coupon_pkg['2018-02-09'],					
				),
				3 => array(
					'limit' =>50,
					'onlie_num' => array(202524,279736),
					'time' => $tm_arr[3] ,
					'award' => 30,//11:30  30M流量
				),
				4 => array(
					'limit' =>50,
					'onlie_num' => array(487635,552719),
					'time' => $tm_arr[4]  ,
					'award' =>$coupon_day_arr['2018-02-09'][3],//20:30   礼券id 	
					'pkg' => $coupon_pkg['2018-02-09'],					
				),
				5 => array(
					'limit' =>30,
					'onlie_num' => array(396352,421726),
					'time' => $tm_arr[5]  ,
					'award' =>$coupon_arr[30],//20:30   礼券id 	
					'pkg' => $coupon_pkg['2018-02-09'],						
				),
			),
			'2018-02-10' => array(
				1 => array(
					'limit' =>10,
					'onlie_num' => array(99632,127432),
					'time' => $tm_arr[1],
					'award' => 100,//11:30  100M流量 
				),
				2 => array(
					'limit' =>10,
					'onlie_num' => array(107453,153652),
					'time' => $tm_arr[2]  ,
					'award' =>$coupon_arr[10],//20:30   礼券id 	
					'pkg' => $coupon_pkg['2018-02-10'],						
				),
				3 => array(
					'limit' =>50,
					'onlie_num' => array(202524,279736),
					'time' => $tm_arr[3] ,
					'award' => 100,//11:30  100M流量
				),
				4 => array(
					'limit' =>100,
					'onlie_num' => array(687635,752349),
					'time' => $tm_arr[4]  ,
					'award' =>$coupon_arr[50],//20:30   礼券id 	
					'pkg' => $coupon_pkg['2018-02-10'],										
				),
				5 => array(
					'limit' =>50,
					'onlie_num' => array(436352,501726),
					'time' => $tm_arr[5] ,
					'award' =>$coupon_day_arr['2018-02-10'][6],//20:30   礼券id 	 
					'pkg' => $coupon_pkg['2018-02-10'],										
				),			
			),
			'2018-02-11' => array(
				1 => array(
					'limit' =>10,
					'onlie_num' => array(99632,127432),
					'time' => $tm_arr[1] ,
					'award' => 100,//11:30  100M流量 
				),
				2 => array(
					'limit' =>10,
					'onlie_num' => array(107453,153652),
					'time' => $tm_arr[2]  ,
					'award' =>$coupon_arr[10],//20:30   礼券id 	 
					'pkg' => $coupon_pkg['2018-02-11'],										
				),
				3 => array(
					'limit' =>50,
					'onlie_num' => array(202524,279736),
					'time' => $tm_arr[3] ,
					'award' => 30,//11:30  30M流量 
				),
				4 => array(
					'limit' =>100,
					'onlie_num' => array(687635,752349),
					'time' => $tm_arr[4]  ,
					'award' =>$coupon_day_arr['2018-02-11'][6],//20:30   礼券id 	 
					'pkg' => $coupon_pkg['2018-02-11'],							
				),
				5 => array(
					'limit' =>50,
					'onlie_num' => array(436352,501726),
					'time' => $tm_arr[5]  ,
					'award' =>$coupon_arr[30],//20:30   礼券id 	
					'pkg' => $coupon_pkg['2018-02-11'],						
				),	
			),
			'2018-02-12' => array(
				1 => array(
					'limit' =>10,
					'onlie_num' => array(99632,127432),
					'time' => $tm_arr[1]  ,
					'award' => 30,//11:30  100M流量
				),
				2 => array(
					'limit' =>10,
					'onlie_num' => array(107453,153652),
					'time' => $tm_arr[2],
					'award' =>$coupon_arr[50],//20:30   礼券id 	
					'pkg' => $coupon_pkg['2018-02-12'],							
				),
				3 => array(
					'limit' =>50,
					'onlie_num' => array(202524,279736),
					'time' => $tm_arr[3]  ,
					'award' => 30,//11:30  100M流量
				),
				4 => array(
					'limit' =>50,
					'onlie_num' => array(487635,552719),
					'time' => $tm_arr[4],
					'award' =>$coupon_day_arr['2018-02-12'][3],//20:30   礼券id 
					'pkg' => $coupon_pkg['2018-02-12'],					
				),
				5 => array(
					'limit' =>30,
					'onlie_num' => array(396352,421726),
					'time' => $tm_arr[5] ,
					'award' =>$coupon_arr[10],//20:30   礼券id 	
					'pkg' => $coupon_pkg['2018-02-12'],					
				),			
			),
			'2018-02-13' => array(
				1 => array(
					'limit' =>10,
					'onlie_num' => array(99632,127432),
					'time' => $tm_arr[1],
					'award' => 30,//11:30  100M流量 
				),
				2 => array(
					'limit' =>10,
					'onlie_num' => array(107453,153652),
					'time' => $tm_arr[2] ,
					'award' =>$coupon_day_arr['2018-02-13'][3],//20:30   礼券id 	
					'pkg' => $coupon_pkg['2018-02-13'],					
				),
				3 => array(
					'limit' =>50,
					'onlie_num' => array(202524,279736),
					'time' => $tm_arr[3],
					'award' => 30,//11:30  100M流量 
				),
				4 => array(
					'limit' =>50,
					'onlie_num' => array(487635,552719),
					'time' => $tm_arr[4] ,
					'award' =>$coupon_arr[30],//20:30   礼券id 	
					'pkg' => $coupon_pkg['2018-02-13'],						
				),
				5 => array(
					'limit' =>30,
					'onlie_num' => array(396352,421726),
					'time' => $tm_arr[5] ,
					'award' =>$coupon_arr[10],//20:30   礼券id 	
					'pkg' => $coupon_pkg['2018-02-13'],							
				),
			),
			'2018-02-14' => array(
				1 => array(
					'limit' =>10,
					'onlie_num' => array(99632,127432),
					'time' => $tm_arr[1] ,
					'award' => 30,//11:30  100M流量
				),
				2 => array(
					'limit' =>10,
					'onlie_num' => array(107453,153652),
					'time' => $tm_arr[2],
					'award' =>$coupon_day_arr['2018-02-08'][3],//20:30   礼券id  
					'pkg' => $coupon_pkg['2018-02-14'],							
				),
				3 => array(
					'limit' =>50,
					'onlie_num' => array(202524,279736),
					'time' => $tm_arr[3] ,
					'award' => 100,//11:30  100M流量
				),
				4 => array(
					'limit' =>50,
					'onlie_num' => array(487635,552719),
					'time' => $tm_arr[4],
					'award' =>$coupon_arr[20],//20:30   礼券id  
					'pkg' => $coupon_pkg['2018-02-14'],						
				),
				5 => array(
					'limit' =>30,
					'onlie_num' => array(396352,421726),
					'time' => $tm_arr[5],
					'award' =>$coupon_arr[30],//20:30   礼券id  
					'pkg' => $coupon_pkg['2018-02-14'],						
				),
			),
			'2018-02-15' => array(
				1 => array(
					'limit' =>10,
					'onlie_num' => array(99632,127432),
					'time' => $tm_arr[1] ,
					'award' => 100,//11:30  100M流量 
				),
				2 => array(
					'limit' =>10,
					'onlie_num' => array(107453,153652),
					'time' => $tm_arr[2],
					'award' =>$coupon_arr[10],//20:30   礼券id  
					'pkg' => $coupon_pkg['2018-02-15'],					
				),
				3 => array(
					'limit' =>50,
					'onlie_num' => array(202524,279736),
					'time' => $tm_arr[3]  ,
					'award' => 30,//11:30  30M流量
				),
				4 => array(
					'limit' =>100,
					'onlie_num' => array(687635,752349),
					'time' => $tm_arr[4] ,
					'award' =>$coupon_day_arr['2018-02-15'][6],//20:30   礼券id 
					'pkg' => $coupon_pkg['2018-02-15'],					
				),
				5 => array(
					'limit' =>50,
					'onlie_num' => array(436352,501726),
					'time' => $tm_arr[5],
					'award' =>$coupon_arr[30],//20:30   礼券id  
					'pkg' => $coupon_pkg['2018-02-15'],					
				),				
			),
			'2018-02-16' => array(
				1 => array(
					'limit' =>10,
					'onlie_num' => array(99632,127432),
					'time' => $tm_arr[1] ,
					'award' => 30,//11:30  30M流量 
				),
				2 => array(
					'limit' =>10,
					'onlie_num' => array(107453,153652),
					'time' => $tm_arr[2],
					'award' =>$coupon_day_arr['2018-02-16'][6],//20:30   礼券id 
					'pkg' => $coupon_pkg['2018-02-16'],						
				),
				3 => array(
					'limit' =>50,
					'onlie_num' => array(202524,279736),
					'time' => $tm_arr[3]  ,
					'award' => 100,//11:30  100M流量
				),
				4 => array(
					'limit' =>100,
					'onlie_num' => array(687635,752349),
					'time' => $tm_arr[4],
					'award' =>$coupon_arr[50],//20:30   礼券id 
					'pkg' => $coupon_pkg['2018-02-16'],						
				),
				5 => array(
					'limit' =>50,
					'onlie_num' => array(436352,501726),
					'time' => $tm_arr[5],
					'award' =>$coupon_arr[10],//20:30   礼券id 
					'pkg' => $coupon_pkg['2018-02-16'],					
				),	
			),
			'2018-02-17' => array(
				1 => array(
					'limit' =>10,
					'onlie_num' => array(99632,127432),
					'time' => $tm_arr[1]  ,
					'award' => 100,//11:30  100M流量 
				),
				2 => array(
					'limit' =>10,
					'onlie_num' => array(107453,153652),
					'time' => $tm_arr[2],
					'award' =>$coupon_arr[10],//20:30   礼券id 
					'pkg' => $coupon_pkg['2018-02-17'],					
				),
				3 => array(
					'limit' =>50,
					'onlie_num' => array(202524,279736),
					'time' => $tm_arr[3]   ,
					'award' => 100,//11:30  100M流量
				),
				4 => array(
					'limit' =>100,
					'onlie_num' => array(687635,752349),
					'time' => $tm_arr[4] ,
					'award' =>$coupon_day_arr['2018-02-17'][6],//20:30   礼券id
					'pkg' => $coupon_pkg['2018-02-17'],					
				),
				5 => array(
					'limit' =>50,
					'onlie_num' => array(436352,501726),
					'time' => $tm_arr[5],
					'award' =>$coupon_arr[50],//20:30   礼券id 
					'pkg' => $coupon_pkg['2018-02-17'],					
				),	
			),
			'2018-02-18' => array(
				1 => array(
					'limit' =>10,
					'onlie_num' => array(99632,127432),
					'time' => $tm_arr[1]  ,
					'award' => 100,//11:30  100M流量 
				),
				2 => array(
					'limit' =>10,
					'onlie_num' => array(107453,153652),
					'time' => $tm_arr[2],
					'award' =>$coupon_arr[10],//20:30   礼券id  
					'pkg' => $coupon_pkg['2018-02-18'],					
				),
				3 => array(
					'limit' =>50,
					'onlie_num' => array(202524,279736),
					'time' => $tm_arr[3]   ,
					'award' => 100,//11:30  100M流量
				),
				4 => array(
					'limit' =>100,
					'onlie_num' => array(687635,752349),
					'time' => $tm_arr[4],
					'award' =>$coupon_arr[30],//20:30   礼券id  
					'pkg' => $coupon_pkg['2018-02-18'],								
				),
				5 => array(
					'limit' =>50,
					'onlie_num' => array(436352,501726),
					'time' => $tm_arr[5],
					'award' =>$coupon_day_arr['2018-02-18'][6],//20:30   礼券id  
					'pkg' => $coupon_pkg['2018-02-18'],									
				),	
			),
			'2018-02-19' => array(
				1 => array(
					'limit' =>10,
					'onlie_num' => array(99632,127432),
					'time' => $tm_arr[1]  ,
					'award' => 30,
				),
				2 => array(
					'limit' =>10,
					'onlie_num' => array(107453,153652),
					'time' => $tm_arr[2],
					'award' =>$coupon_day_arr['2018-02-08'][3], 
					'pkg' => $coupon_pkg['2018-02-19'],									
				),
				3 => array(
					'limit' =>50,
					'onlie_num' => array(202524,279736),
					'time' => $tm_arr[3]  ,
					'award' => 100,
				),
				4 => array(
					'limit' =>50,
					'onlie_num' => array(487635,552719),
					'time' => $tm_arr[4],
					'award' =>$coupon_arr[10],  
					'pkg' => $coupon_pkg['2018-02-19'],					
				),
				5 => array(
					'limit' =>30,
					'onlie_num' => array(396352,421726),
					'time' => $tm_arr[5],
					'award' =>$coupon_arr[30], 
					'pkg' => $coupon_pkg['2018-02-19'],					
				),			
			),
			'2018-02-20' => array(
				1 => array(
					'limit' =>10,
					'onlie_num' => array(99632,127432),
					'time' => $tm_arr[1]  ,
					'award' => 30,
				),
				2 => array(
					'limit' =>10,
					'onlie_num' => array(107453,153652),
					'time' => $tm_arr[2],
					'award' =>$coupon_day_arr['2018-02-20'][6],
					'pkg' => $coupon_pkg['2018-02-20'],					
				),
				3 => array(
					'limit' =>50,
					'onlie_num' => array(202524,279736),
					'time' => $tm_arr[3]  ,
					'award' => 30,
				),
				4 => array(
					'limit' =>50,
					'onlie_num' => array(487635,552719),
					'time' => $tm_arr[4],
					'award' =>$coupon_arr[20],
					'pkg' => $coupon_pkg['2018-02-20'],					
				),
				5 => array(
					'limit' =>30,
					'onlie_num' => array(396352,421726),
					'time' => $tm_arr[5],
					'award' =>$coupon_arr[10],
					'pkg' => $coupon_pkg['2018-02-20'],					
				),
			),
			'2018-02-21' => array(
				1 => array(
					'limit' =>10,
					'onlie_num' => array(99632,127432),
					'time' => $tm_arr[1] ,
					'award' => 100,//11:30  100M流量 
				),
				2 => array(
					'limit' =>10,
					'onlie_num' => array(107453,153652),
					'time' => $tm_arr[2] ,
					'award' =>$coupon_day_arr['2018-02-21'][3],//13:30   礼券id
					'pkg' => $coupon_pkg['2018-02-21'],					
				),
				3 => array(
					'limit' =>50,
					'onlie_num' => array(202524,279736),
					'time' => $tm_arr[3]  ,
					'award' => 30,//19:30  30M流量
				),
				4 => array(
					'limit' =>50,
					'onlie_num' => array(487635,552719),
					'time' => $tm_arr[4] ,
					'award' =>$coupon_arr[20],//20:30   礼券id
					'pkg' => $coupon_pkg['2018-02-21'],					
				),
				5 => array(
					'limit' =>30,
					'onlie_num' => array(396352,421726),
					'time' => $tm_arr[5] ,
					'award' =>$coupon_arr[30],//21:30   礼券id
					'pkg' => 'com.mobimtech.natives.ivp',
					'pkg' => $coupon_pkg['2018-02-21'],					
				),
			),
		);
		$redis->set($limit_list_key,$limit_list,30*86400);	
	}
	return 	$limit_list;
}
//题库
function get_questions(){
	global $configs,$active_id,$time,$imsi,$model,$prefix,$redis;
	$questions_key = $prefix.":".$active_id.":questions";
	$questions = $redis->get($questionst_key);	
	if($questions == null){
		$file = mb_convert_encoding(file_get_contents("./questions.csv"), 'UTF-8', 'UTF-8,GBK,GB2312,BIG5');
		$arr = explode("\n",$file);
		$questions = array();
		foreach($arr as $k => $v){
			if($k == 0){
				continue;
			}	
			$questions[$k] = explode(",",$v);
		}
		$redis->set($questionst_key,$questions,30*86400);	
	}
	return 	$questions;
}
//随机取10道题 limit随机数  Screenings 场次
function random_questions($limit=12,$screenings){
	global $configs,$active_id,$time,$imsi,$model,$prefix,$redis,$today;
	$random_questions_key = $prefix.":".$active_id.":random_questions:".$today.":".$screenings;
	$random_questions = $redis->get($random_questions_key);	
	if($random_questions == null){	
		$questions = get_questions();
		$rank_arr = array_rand($questions,$limit); 
		$random_questions = array();
		foreach($rank_arr as $val){
			$random_questions[] = $questions[$val];
		}
		$redis->set($random_questions_key,$random_questions,86400);	
	}
	return $random_questions;
}
//获取复活剩余次数、下载数、领取数
function get_res_num(){
	global $configs,$active_id,$time,$imsi,$model,$prefix,$redis,$today;	
	//下载数
	$down_num_key = $prefix.":".$active_id.":down_num:".$imsi.":".$today;
	$down_num = $redis->get($down_num_key);
	$down_num =  $down_num  ? $down_num  : 0;
	//领取数
	$resurrection_num_key = $prefix.":".$active_id.":resurrection_num:".$imsi.":".$today;
	$resurrection_num = $redis->get($resurrection_num_key);
	$resurrection_num = $resurrection_num ? $resurrection_num : 0;
	//复活剩余次数
	$use_relive_num_key = $prefix.":".$active_id.":use_relive_num:".$imsi.":".$today;
	$use_relive_num = $redis->get($use_relive_num_key);
	$res_num = intval($resurrection_num-$use_relive_num);	
	return array($down_num,$resurrection_num,$res_num);
}
//使用复活机会
function use_resurrection_number(){
	global $prefix,$configs,$active_id,$sid,$time,$today,$imsi,$model,$redis;
	//领取数
	$resurrection_num_key = $prefix.":".$active_id.":resurrection_num:".$imsi.":".$today;
	$resurrection_num = $redis->get($resurrection_num_key);
	
	$use_relive_num_key = $prefix.":".$active_id.":use_relive_num:".$imsi.":".$today;
	$use_relive_num = $redis->get($use_relive_num_key);	
	if($use_relive_num >= 3 || !$resurrection_num || ($use_relive_num > $resurrection_num)){
		return array(
			'code' => 0,
			'msg' => '复活机会已经用完',
		);			
	}else{
		//使用复活卡
		$log_data = array(
			"imsi" => $imsi,
			"device_id" => $_SESSION['DEVICEID'],
			"activity_id" => $active_id,
			"ip" => $_SERVER['REMOTE_ADDR'],
			"sid" => $sid,
			"time" => $time,
			"user" => $_SESSION['USER_NAME'],
			'uid'=> $_SESSION['USER_UID'],
			'key' => 'use_relive'
		);
		permanentlog('activity_'.$active_id.'.log', json_encode($log_data));		
		$use_relive_num = $redis->setx('incr',$use_relive_num_key,1);
		$redis->expire($use_relive_num_key,86400);
		return array(
			'code' => 1,
			'msg' => '复活成功',
		);			
	}
}
//榜单用户
function get_top_conf_user(){
	$phone = array(
		'186xxxx1878','137xxxx1313','152xxxx0452','177xxxx1113','186xxxx7576',		'130xxxx6019','183xxxx9158','186xxxx0567','139xxxx1363','188xxxx3000',
	);
	$user = array(
		'az171256025','云淡天高1989','az139628373','az171524109','王开心199306','嘟嘟鱼','az171256025','丢了翅膀的鱼2','az1213721','az1369854612',
	);
	return array($phone,$user);
}
//榜单假数据
function get_top_config(){
	global $configs,$active_id,$time,$imsi,$model,$prefix,$redis;
	$top_config_key = $prefix.":".$active_id.":top_config";
	$top_config = $redis->get($top_config_key);	
	if($top_config == null){	
		list($a,$coupon_config,$c) = coupon_config();
		$limit_list = get_limit_list();
		$top_config = array();
		foreach($limit_list as $k => $v){
			foreach($v as $key=>$val){
				if($k != "2018-02-08"){
					$kk = date("Y-m-d",strtotime($k)-86400);
					if($key == 1 || $key == 3){
						if($key == 1){
							$top_config[$k][$key] = intval($top_config[$kk][3]);
						}else{
							$top_config[$k][$key] = intval($top_config[$k][1]);
						}
						$top_config[$k][$key] = $top_config[$k][$key]+$val['award'];
					}else{
						if($key == 2){
							$top_config[$k][$key] = intval($top_config[$kk][5]);
						}else if($key == 4){
							$top_config[$k][$key] = intval($top_config[$k][2]);
						}else{
							$top_config[$k][$key] = intval($top_config[$k][4]);
						}
						$top_config[$k][$key] = $top_config[$k][$key]+$coupon_config[$val['award']];						
					}					
				}else{
					$top_config[$k][1] = 100;
					$top_config[$k][2] = 6;
					$top_config[$k][3] = 130;
					$top_config[$k][4] = 36;
					$top_config[$k][5] = 46;
				}
			}
		}
		$redis->set($top_config_key,$top_config,30*86400);	
	}
	return $top_config;
}
function my_array_multisort($data,$sort_order_field,$sort_order=SORT_DESC,$sort_type=SORT_NUMERIC){
	foreach($data as $val){
		$key_arrays[]=$val[$sort_order_field];
	}
	array_multisort($key_arrays,SORT_DESC,SORT_NUMERIC,$data);
	return $data;
}
//榜单
function top_20(){
	global  $prefix,$active_id,$model,$redis,$imsi,$uid,$today,$time;		
	$h = date("H",$time);
	$top20_key = $prefix.":".$active_id.":top20:".$today.":".$h;
	$top20 = $redis->get($top20_key);	
	if($top20 == null){	
		$option = array(
			'where' => array(
				'aid' => $active_id,
		//		'id' => array("exp","not in(86391,86395,88088)"),
				'status'=>1,
			),
			'table' => 'imsi_lottery_award',
			'field'=>'sum(award) as price,telephone',
			'group' => 'imsi',
			'order' => 'price desc',
			'limit' => '20'
		);
		$top20_list = $model->findAll($option, 'lottery/lottery');
		list($phone,$user) = get_top_conf_user();
		$top_config = get_top_config();	
		
		$screenings = get_screenings();
		$screenings_tm = get_screenings_tm();
		$end_tm1 = $screenings_tm[$today][1]+1800;
		$end_tm3 = $screenings_tm[$today][3]+1800;
		if($today == "2018-02-08" && $time >= $end_tm1 && $time < $end_tm3){
			foreach($phone as  $k => $v){
				$top20[] = array(
					'price' => 100,
					'telephone' => $v,
				);
			}
		}else if($today == "2018-02-08" && $time >= $end_tm3){
			foreach($phone as $k => $v){
				if($k>4) continue;
				$top20[] = array(
					'price' => 130,
					'telephone' => $v,
				);
			}			
		}else if($today == "2018-02-08" && $time < $end_tm1){
	
		}else if($time >= $end_tm1 && $time < $end_tm3){
			foreach($phone as  $k => $v){
				if($k>2) continue;
				$top20[] = array(
					'price' => $top_config[$today][1],
					'telephone' => $v,
				);
			}
		}else{
			foreach($phone as  $k => $v){
				if($k>2) continue;
				$top20[] = array(
					'price' => $top_config[$today][3],
					'telephone' => $v,
				);
			}			
		}
		foreach($top20_list as $key => $val){
			$price = $val['price'];
			$telephone = substr($val['telephone'],0,3)."xxxx".substr($val['telephone'],-4,4);
			$top20[] = array(
				'price' =>$price,
				'telephone' => $telephone ,
			);
		}
		$top20 = my_array_multisort($top20,'price');
		$redis->set($top20_key,$top20,60*60);
	}
	$top20_uid_key = $prefix.":".$active_id.":top20_uid:".$today.":".$h;
	$top20_uid = $redis->get($top20_uid_key);	
	if($top20_uid == null){	
		list($phone,$user) = get_top_conf_user();
		$top_config = get_top_config();	
		$screenings = get_screenings();
		$screenings_tm = get_screenings_tm();
		$end_tm1 = $screenings_tm[$today][1]+1800;
		$end_tm2 = $screenings_tm[$today][2]+1800;
		$end_tm3 = $screenings_tm[$today][3]+1800;
		$end_tm4 = $screenings_tm[$today][4]+1800;
		$end_tm5 = $screenings_tm[$today][5]+1800;
		if($today == "2018-02-08" && $time < $end_tm2){
	
		}else if($today == "2018-02-08" && $time >= $end_tm2 && $time < $end_tm4){
			foreach($user as  $k => $v){
				$top20_uid[] = array(
					'price' => $top_config[$today][2],
					'username' => $v,
				);
			}
		}else if($today == "2018-02-08" && $time >= $end_tm4 && $time < $end_tm5 ){
			foreach($user as $k => $v){
				if($k > 4) continue;
				$top20_uid[] = array(
					'price' => $top_config[$today][4],
					'username' => $v,
				);
			}			
		}else if($time >= $end_tm1 && $time < $end_tm4){
			foreach($user as  $k => $v){
				if($k > 2) continue;
				$top20_uid[] = array(
					'price' => $top_config[$today][2],
					'username' => $v,
				);
			}			
		}else if( $time >= $end_tm4 && $time < $end_tm5 ){
			foreach($user as $k => $v){
				if($k > 2) continue;
				$top20_uid[] = array(
					'price' => $top_config[$today][4],
					'username' => $v,
				);
			}			
		}else{
			foreach($user as $k => $v){
				if($k > 2) continue;
				$top20_uid[] = array(
					'price' => $top_config[$today][5],
					'username' => $v,
				);
			}				
		}
		$option = array(
			'where' => array(
				'aid' => $active_id,
			//	'id' => array("exp","not in(571142,571146,571153,571224,571254,571256)"),
				'status'=>1,
			),
			'table' => 'valentine_draw_award',
			'field'=>'username,sum(money) as price,username',
			'group' => 'uid',
			'order' => 'price desc',
			'limit' => '20'			
		);
		$top20_uid_list = $model->findAll($option, 'lottery/lottery');		
		foreach($top20_uid_list as $key => $val){
			$top20_uid[] = array(
				'price' =>$val['price'],
				'username' => $val['username'] ,
			);
		}		
		$top20_uid = my_array_multisort($top20_uid,'price');
		$redis->set($top20_uid_key,$top20_uid,60*60);
	}	
	return array($top20,$top20_uid);		
}
//在线人数
function get_online_num($questions,$screenings){
	global  $prefix,$active_id,$model,$redis,$today,$uid;		
	$online_num_key = $prefix.":".$active_id.":online_num:".$screenings.":".$today;
	$online_num = $redis->get($online_num_key);	
	if($online_num == null){	
		$limit_list = get_limit_list();
		list($a,$b) = $limit_list[$today][$screenings]['onlie_num'];
		$rand = mt_rand($a,$b); 
		$online_num = array();
		for($i=0;$i<=9;$i++){
			if($i > 0){
				$rand = intval($rand-($rand*0.09));
			}
			$count = count(array_filter($questions[$i]));	
			if($i >0 ){
				$num = intval($online_num[$i-1][111]*0.9);
				$num2 = intval($online_num[$i-1][222]*0.9);
				$num3 = intval($online_num[$i-1][333]*0.9);
				$num4 = intval($online_num[$i-1][444]*0.9);
				$val = array(                                     
					0 => $rand,                                   
					1 => round($num*0.0001,2),
					11 => $num/$rand*100,
					111 => $num,                                
					2 => round($num2*0.0001,2),
					22 => $num2/$rand*100,
					222 => $num2,                           
					3 => round($num3*0.0001,2),
					33 => $num3/$rand*100,
					333 => $num3,                        
					4 => round($num4*0.0001,2),
					44 => $num4/$rand*100,
					444 => $num4,
				);					
			}else{
				$a = randomDivInt(4);                             
				$val = array(                                     
					0 => $rand,                                   
					1 => round(intval($rand*($a[0]/100))*0.0001,2),
					11 => intval($rand*($a[0]/100))/$rand*100,
					111 => intval($rand*($a[0]/100)),
					2 => round(intval($rand*($a[1]/100))*0.0001,2),
					22 => intval($rand*($a[1]/100))/$rand*100,
					222 => intval($rand*($a[2]/100)),
					3 => round(intval($rand*($a[2]/100))*0.0001,2),
					33 => intval($rand*($a[2]/100))/$rand*100,
					333 => intval($rand*($a[2]/100)),
					4 => round(intval($rand*($a[3]/100))*0.0001,2),
					44 => intval($rand*($a[3]/100))/$rand*100,
					444 => intval($rand*($a[3]/100)),
				);	
			}	
			$online_num[$i] = $val;		
		}
		$redis->set($online_num_key,$online_num,86400);	
	}
	if($_GET['DUG']){
		//var_dump($online_num);
	}
	return $online_num;
}
function get_online_num_old($questions,$screenings){
	global  $prefix,$active_id,$model,$redis,$today,$uid;		
	$online_num_key = $prefix.":".$active_id.":online_num:".$screenings.":".$today;
	$online_num = $redis->get($online_num_key);	
	if($online_num == null){	
		$limit_list = get_limit_list();
		list($a,$b) = $limit_list[$today][$screenings]['onlie_num'];
		$rand = mt_rand($a,$b); 
		$online_num = array();
		for($i=0;$i<=9;$i++){
			if($i > 0){
				$rand = intval($rand-($rand*0.09));
			}
			$count = count(array_filter($questions[$i]));
			if($count == 5){
				$a = randomDivInt(3);
				$val = array(
					0 => $rand,
					1 => round(intval($rand*($a[0]/100))*0.0001,2)."万",
					11 => intval($rand*($a[0]/100))/$rand*100,
					2 => round(intval($rand*($a[1]/100))*0.0001,2)."万",
					22 => intval($rand*($a[1]/100))/$rand*100,
					3 => round(intval($rand*($a[2]/100))*0.0001,2)."万",
					33 => intval($rand*($a[2]/100))/$rand*100,
				);                                                
			}else{                                                
				$a = randomDivInt(4);                             
				$val = array(                                     
					0 => $rand,                                   
					1 => round(intval($rand*($a[0]/100))*0.0001,2)."万",
					11 => intval($rand*($a[0]/100))/$rand*100,
					2 => round(intval($rand*($a[1]/100))*0.0001,2)."万",
					22 => intval($rand*($a[1]/100))/$rand*100,
					3 => round(intval($rand*($a[2]/100))*0.0001,2)."万",
					33 => intval($rand*($a[2]/100))/$rand*100,
					4 => round(intval($rand*($a[3]/100))*0.0001,2)."万",
					44 => intval($rand*($a[3]/100))/$rand*100,
				);			
			}
			$online_num[$i] = $val;		
		}
		$redis->set($online_num_key,$online_num,86400);	
	}
	return $online_num;
}
//随机整型利用“不同”就有顺序的原理，
function randomDivInt($div){
    $remain=100;
    $max_sum=($div-1)*$div/2;
    $p=$div; $min=0;
    $a=array();
    for($i=0; $i<$div-1; $i++){
        $max=($remain-$max_sum)/($div-$i);
        $e=rand($min,$max);    
        $min=$e+1; $max_sum-=--$p;
        $remain-=$e;
        $a[$e]=true;
    }
    $a=array_keys($a);
    $a[]= $remain;
	//shuffle($a);//随机
    return $a;
}
function get_soft_info($is_test){
	global $model,$redis,$prefix,$active_id;
	$soft_info_key = $prefix.":".$active_id.':soft_info';
	$soft_info = $redis->get($soft_info_key);	
	if($soft_info === null){
		$coupon_pkg = get_coupon_pkg();
		$option = array(
			'table' => 'sj_soft AS A',
			'where' => array(
				'A.status' => 1,
				'A.hide' => 1,
				'A.package' => $coupon_pkg,
				'B.package_status' => 1,
			),
			'join' => array(
				'sj_soft_file AS B' => array(
					'on' => array('A.softid','B.softid'),
				)
			),
			'field' => 'A.softid,A.softname,A.package,A.version_code,A.version_code,A.total_downloaded,B.iconurl_125,B.filesize',
			'order' => 'A.softid desc',
			'group' => 'A.package',
		);	
		$softinfo = $model->findAll($option);

		$soft_info = array();
		foreach($softinfo as $k => $v){
			$v['iconurl'] = getImageHost().$v['iconurl_125'];
			$v['short'] = $pkg_arr[$v['package']];
			$soft_info[$v['package']] = $v;
		}
		$redis->set($soft_info_key,$soft_info,30*60);	
	}
	return $soft_info;
}