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
$prefix = "flow";
if($configs['is_test'] == 1 ) {
	$time  = get_now_time();
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
$activity_host = $configs['activity_url'];
$stop = $_GET['stop'] ? $_GET['stop'] : $_POST['stop'];
$aid_jump = array(
	1110 => 1108,
	1157 => 1156,
	1207 => 1206,
	1298 => 1297,
);
if($configs['is_test'] != 1 && in_array($active_id,array_flip($aid_jump))){
	$log_data = array(
		"imsi" => $_SESSION['USER_IMSI'],
		"device_id" => $_SESSION['DEVICEID'],
		"activity_id" => $active_id,
		"ip" => $_SERVER['REMOTE_ADDR'],
		"sid" => $sid,
		"time" => $time,
		"user" => $_SESSION['USER_NAME'],
		'uid'=> $_SESSION['USER_UID'],
		'key' => 'show_homepage'
	);
	permanentlog('activity_'.$active_id.'.log', json_encode($log_data));	
	unset($_GET['aid']);
	$build_query = http_build_query($_GET);
	$aid_str = $aid_jump[$active_id];
	$url = $activity_host."/lottery/{$prefix}/index.php?aid=".$aid_str."&".$build_query;
	header("Location: {$url}");	
	exit;
}
if($stop != 1 && !$_GET['ap_id']){
	$activity_list = activity_is_stop($active_id);
	if(!$activity_list){
		$aid_arr = array(1135,1136,1172,1226,1242);
		if(in_array($active_id,$aid_arr)){
			//双旦嘉年华1135  	流量大波送1136	
			$url = $activity_host."/lottery/{$prefix}/collection.php?from_type=2&stop=1&aid=".$active_id."&sid=".$sid;
		}else{
			$url = $activity_host."/lottery/{$prefix}/index.php?stop=1&aid=".$active_id."&sid=".$sid;
		}
		header("Location: {$url}");
		exit;
	}
}

//设备激活状态
function deviceid_activation_state(){
	global $configs,$today,$activity_list,$active_id,$aid_jump;
    if( $configs['is_test'] ) {
		$act_stmp_arr = array(
			'357541051314153' => 1511971200,//活动开始后激活
			'867495010642936' => 1511971200,//活动开始后激活
			'A100004DE0268B' => 1511971200,//活动开始后激活
			'869765029927199' => 1511971200,//活动开始后激活
			'A1000037A000CE' => 1511971200,//活动开始后激活
			'867931020096614' => 1511971200,//活动开始后激活
			'867931020126924' => 1511971100,//活动开始前激活
			'864368035808647' => 1511971100,//活动开始前激活
			'354017072329457' => 1511971100,//活动开始前激活
			'353460082577510' => 1511971100,//活动开始前激活
			'866042022025931' => 1511971100,//活动开始前激活
		);
		$act_stmp = $act_stmp_arr[$_SESSION['DEVICEID']];
		if($act_stmp){
			return 1;
		}else{
			return 0;
		} 
    } else {
		//是否为新用户推送
		$user_model = load_model('ucenter');
		$continue = $user_model->is_new_user($activity_list);
		file_put_contents("/tmp/zhuang.log",var_export($activity_list,true));
		if(!$continue){
			return 1;
		}else{
			$act_stmp = $_SESSION['ATC_TMS'];
			if(in_array($active_id,$aid_jump) && ($act_stmp >= $activity_list['start_tm'])){
				return 1;
			}else{			
				return 0;
			}
		}	
    }
}
//获取活动开始时间
function get_activity_start_tm(){
	global  $prefix,$active_id,$model,$redis;		
	$activity_start_tm_key = $prefix.":".$active_id.":activity_start_tm";
	$activity_start_tm = $redis->get($activity_start_tm_key);	
	if($activity_start_tm == null){	
		$option = array(
			'where' => array(
				'id' => $active_id,
			),
			'table' => 'sj_activity',
			'field' => 'start_tm,end_tm,activation_time,cover_user_type,activation_date_start,activation_date_end',
		);
		$activity = $model->findOne($option);	
		$activity_start_tm = $activity['start_tm'];
		$redis->set($activity_start_tm_key,$activity_start_tm,86400);		
	}
	return $activity_start_tm;
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

//领流量 @type 1签到领取流量 2下载领取流量
function collar_flow($type){
	global $prefix,$configs,$active_id,$sid,$time,$today,$imsi,$model,$redis;
	if($type == 1){	
		$sign_num_key = $prefix.":".$active_id.":sign_num:".$imsi;
		/*
		if($sign_num == 1){
			$price = 30;
			$sign_num_last_tm_key = $prefix.":".$active_id.":sign_num_last_tm:".$imsi;
			$redis->set($sign_num_last_tm_key,strtotime($today),30*86400);
		}else if($sign_num == 2){
			$sign_num_last_tm_key = $prefix.":".$active_id.":sign_num_last_tm:".$imsi;
			$last_tm = $redis->get($sign_num_last_tm_key);
			if(($time-$last_tm) >= 2*86400){
				$price = 10;
			}else{
				$price = 20;
			}
		}else{
			$price = 10;
		}
		*/
		$last_day = date("Y-m-d",$time-86400);
		$last_sign_key = $prefix.":".$active_id.":sign_flow:".$imsi.":".$last_day;
		$last_sign = $redis->get($last_sign_key);
		if(!$last_sign){
			//第一天
			$redis->set($sign_num_key,1);
			$price = 50;
		}else{
			$sign_num = $redis->setx('incr',$sign_num_key,1);
			if($sign_num == 2 || $sign_num == 7){
				//第2天，//第7天
				$price = 100;
			}else{
				$price = 30;
			}
		}
		$redis->expire($sign_num_key,60*86400);	
		$sign_flow_key = $prefix.":".$active_id.":sign_flow:".$imsi.":".$today;
		$sign_flow = $redis->setnx($sign_flow_key,1);
		$redis->expire($sign_flow_key,86400);	
		if(!$sign_flow){
			$redis->setx('incr',$sign_num_key,-1);
			return array(
				'code' => 0,
				'msg' => '当天已领取，请不要重复领取',
			);			
		}		
	}else{
		$specify_soft_key = $prefix.":".$active_id.":specify_soft:".$imsi.":".$today;
		$now_specify_soft =  $redis->get($specify_soft_key);
		if($now_specify_soft > 0){
			$price = 50;		
			$now_specify_soft = $redis->setx('incr',$specify_soft_key,-1);
		}else{
			$price = 10;
		}
		$down_num_key = $prefix.":".$active_id.":down_num:".$imsi.":".$today;
		$down_num = $redis->get($down_num_key);
		if(!$down_num){
			return array(
				'code' => 0,
				'msg' => '去完成',
			);				
		}
		$down_flow_key = $prefix.":".$active_id.":down_flow:".$imsi.":".$today;
		$down_flow = $redis->get($down_flow_key);
		if($down_flow >= $down_num){
			return array(
				'code' => 0,
				'msg' => '去完成~',
			);			
		}		
		$down_flow = $redis->setx('incr',$down_flow_key,1);
		$redis->expire($down_flow_key,86400);
		if($down_flow > 3){
			return array(
				'code' => 0,
				'msg' => '当天已领取，请不要重复领取',
			);
		}		
	}
	//用户领取日志
	$log_data = array(
		'time'	=>	$time,
		'imsi'	=>	$_SESSION['USER_IMSI'],
		'sid' => $sid,	
		'device_id'	=>	$_SESSION['DEVICEID'],
		"DEVICE_SN" => $_SESSION['DEVICE_SN'],
		'activity_id'	=>	$active_id,
		'price'=>$price,//单位M
		'type'	=>	$type,//1签到领取流量 2下载领取流量
		'key'	=>	'receive'
	);
	permanentlog('activity_'.$active_id.'.log', json_encode($log_data));
	$data = array(
		'aid' => $active_id,
		'imsi' => $imsi,
		'award' => $price,//单位M
		'time' => $time,
		'address'=>$type,//1签到领取流量 2下载领取流量
		'__user_table' => "imsi_lottery_award" ,
	);
	$ret = $model -> insert($data,'lottery/lottery');
	if(!$ret){
        return array(
            'code' => 0,
            'msg' => '领取失败',
        );	
	}else{
		$my_prize_key = $prefix.":".$active_id.":my_prize:".$imsi;
		$redis->delete($my_prize_key);			
        return array(
            'code' => 1,
            'msg' => '领取成功',
			"price" => $price,
        );		
	}	
}
//双旦活动，下载获取流量
function collar_down_flow($price=2,$limit=10){
	global $prefix,$configs,$active_id,$sid,$time,$today,$imsi,$model,$redis;
	$down_num_key = $prefix.":".$active_id.":down_num:".$imsi.":".$today;
	$down_num = $redis->get($down_num_key);
	if(!$down_num){
		return array(
			'code' => 0,
			'msg' => '去完成',
		);				
	}
	$down_flow_key = $prefix.":".$active_id.":down_flow:".$imsi.":".$today;
	$down_flow = $redis->get($down_flow_key);
	if($down_flow >= $down_num){
		return array(
			'code' => 0,
			'msg' => '去完成~',
		);			
	}		
	$down_flow = $redis->setx('incr',$down_flow_key,1);
	$redis->expire($down_flow_key,86400);
	if($down_flow > $limit){
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
		'price'=>$price,//单位M
		'type'	=>	2,//1签到领取流量 2下载领取流量
		'key'	=>	'receive'
	);
	permanentlog('activity_'.$active_id.'.log', json_encode($log_data));
	$data = array(
		'aid' => $active_id,
		'imsi' => $imsi,
		'award' => $price,//单位M
		'time' => $time,
		'address'=>2,//1签到领取流量 2下载领取流量
		'__user_table' => "imsi_lottery_award" ,
	);
	$ret = $model -> insert($data,'lottery/lottery');
	if(!$ret){
        return array(
            'code' => 0,
            'msg' => '领取失败',
        );	
	}else{
		$my_prize_key = $prefix.":".$active_id.":my_prize:".$imsi;
		$redis->delete($my_prize_key);			
        return array(
            'code' => 1,
            'msg' => '领取成功',
			"price" => $price,
        );		
	}	
}
//下载获取流量
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
			$pkg_arr = get_specify_soft();
			if(in_array($pkg,$pkg_arr)){	
				$specify_soft_key = $prefix.":".$active_id.":specify_soft:".$imsi.":".$today;
				$redis->setx('incr',$specify_soft_key,1);
				$redis->expire($specify_soft_key,86400);
			}
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
function flow_recharge_check($price,$limit_flow){
	global $configs,$active_id,$time,$imsi,$model,$prefix,$redis,$today;
	$my_prize = my_prize();	
	$grant_flow = grant_flow();
	$res_flow = $my_prize['price_total']-$grant_flow['price_total'];
	if($res_flow < $price){
		return array('code'=>0,'msg'=>"<span>安智君发现你剩余的流量不足了哦,</span>少年切莫贪心哦~",'pic'=>'08');
	}
	$use_num_key = $prefix.":".$active_id.":use_num:".$today;
	$use_num = $redis->get($use_num_key);
	if($use_num >= $limit_flow){
		return array('code'=>0,'msg'=>"<span>今天的流量已被其他小伙伴提走啦，</span>明天记得早点提取哦~",'pic'=>'06');	
	}	
	$use_num = $redis->setx('incr',$use_num_key,$price);
	$redis->expire($use_num_key,86400);
	if($use_num > $limit_flow){
		$redis->setx('incr',$use_num_key,-$price);
		return array('code'=>0,'msg'=>"<span>今天的流量已被其他小伙伴提走啦，</span>明天记得早点提取哦~",'pic'=>'06');	
	}
	$mon_use_num_key = $prefix.":".$active_id.":mon_use_num:".$imsi;
	$mon_use_num = $redis->get($mon_use_num_key);
	if($mon_use_num >= 3){
		$redis->setx('incr',$use_num_key,-$price);
		return array('code'=>0,'msg'=>"<span>提取流量的机会用完啦！</span>少年不要贪心哦~",'pic'=>'07');	
	}	
	$mon_use_num = $redis->setx('incr',$mon_use_num_key,1);
	$redis->expire($mon_use_num_key,60*86400);
	if($mon_use_num > 3){
		$redis->setx('incr',$use_num_key,-$price);
		$redis->setx('incr',$mon_use_num_key,-1);
		return array('code'=>0,'msg'=>"<span>提取流量的机会用完啦！</span>少年不要贪心哦~",'pic'=>'07');	
	}	
	$mon_price_num_key = $prefix.":".$active_id.":mon_price_num:".$imsi;
	$mon_price_num = $redis->get($mon_price_num_key);
	if($mon_price_num >= 500){
		$redis->setx('incr',$use_num_key,-$price);
		$redis->setx('incr',$mon_use_num_key,-1);
		return array('code'=>0,'msg'=>"<span>安智君发现你已经提取500M了哦,</span>少年切莫贪心哦~",'pic'=>'08');			
	}
	$mon_price_num = $redis->setx('incr',$mon_price_num_key,$price);
	$redis->expire($mon_price_num_key,60*86400);
	if($mon_price_num > 500){
		$redis->setx('incr',$use_num_key,-$price);
		$redis->setx('incr',$mon_use_num_key,-1);
		$where = array('aid' => $active_id,'imsi' => $imsi,'status'=>array(0,1));
		$option = array(
			'where' => $where,
			'table' => 'recharge_flow_bill' ,
			'field' => 'SUM(`price`) AS price'
		);
		$list = $model->findOne($option, 'lottery/lottery');
		$redis->set($mon_price_num_key,intval($list['price']),60*86400);	
		$use_price = 500-$list['price'];
		return array('code'=>0,'msg'=>"<span>您本月还可提取".$use_price."M,</span>不要贪心哦~",'pic'=>'08');	
	}
	return array('code'=>1);	
}	
/****流量充值*****/
if( $_GET['DUG']){
	//var_dump(flow_recharge(18511247253,10));
}
//var_dump(flow_recharge(18310215648,300));//移动
function flow_recharge($mobile,$price,$limit_flow=100000){
	global $configs,$active_id,$sid,$time,$imsi,$model,$prefix,$redis,$today;
	if(empty($mobile)){
		return array('code'=>0,'msg'=>'请先绑定手机号');
	}
	if( $_GET['DUG'] != 1){
		$res = flow_recharge_check($price,$limit_flow);
		if($res['code'] == 0){
			return $res;
		}
	}
	if( $configs['is_test'] ) {
		$secret_key = "FhbYDSZuzJiXpwy4";
		$partid = "1000000000000174";
		$notifyurl = 'http://m.test.anzhi.com/lottery/flow/callback.php';
	}else{
		$secret_key = "FXIpNWC3vz8DMMCZ";
		$partid = "1000000000000109";
		$notifyurl = 'http://promotion.anzhi.com/lottery/flow/callback.php';		
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
		'channelorderid' => "az".$orderid,
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
	$grant_flow_key = $prefix.":".$active_id.":grant_flow:".$imsi;
	$redis->delete($grant_flow_key);	
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
		//失败后回滚
		//所有用户的流量总数
		$use_num_key = $prefix.":".$active_id.":use_num:".$today;
		$redis->setx('incr',$use_num_key,-$price);
		//当月可提取的次数
		$mon_use_num_key = $prefix.":".$active_id.":mon_use_num:".$imsi;
		$redis->setx('incr',$mon_use_num_key,-1);
		//当月限可提取500M
		$mon_price_num_key = $prefix.":".$active_id.":mon_price_num:".$imsi;
		$redis->setx('incr',$mon_price_num_key,-($price));		
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
	global  $prefix,$active_id,$model,$redis,$imsi;		
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
			'field'=>'id,imsi,award,address,FROM_UNIXTIME(`time`, "%Y%m%d") as date,SUM(award) as price',
			'group' => '`date`,address',
			'order' => 'time desc'
		);
		$list = $model->findAll($option, 'lottery/lottery');
		$my_prize = array();
		$price_total = 0;
		foreach($list as $k =>$v){
			$price_total = $price_total+$v['price'];
			$my_prize[$v['date']][] = $v;
		}
		$my_prize['price_total'] = $price_total;
		$grant_flow = grant_flow();
		foreach($grant_flow as $key => $val){
			if($my_prize[$key]) continue;
			$my_prize[$key][]['address'] = -1;
		}
		krsort($my_prize);
		$redis->set($my_prize_key,$my_prize,86400);
	}
	return $my_prize;	
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
				'conf_id' => 294
		),
		'table' => 'pu_config',
		'field' => 'configcontent',
	);
	$list = $model->findOne($option);
	return strtotime($list['configcontent']);
}
//获取指定软件
function get_specify_soft(){
	global $configs,$active_id,$today,$imsi,$model,$prefix,$redis;
	if( $configs['is_test'] ) {
		$config = array(
			'2018-03-08' => array('com.molybdenum.anzhi','com.praseodymium.anzhi','com.yinhan.hunter.anzhi','com.mdd05.anzhi'),
			'2018-03-09'=>array('com.bf.ERShuangKou.anzhi','com.netease.gamewin3.anzhi','com.kdfgkdjfgsd.floatwindowdemo.anzhi','com.mdd07.anzhi'),
		);
		if(!$config[$today]){
			$config[$today] = array('com.molybdenum.anzhi','com.praseodymium.anzhi','com.yinhan.hunter.anzhi','com.mdd05.anzhi');
		}
	}else{
		$config = array(
			//
			'2018-05-25'=> array('com.pokercity.bydrqp.anzhi','com.netease.my.anzhi','com.shanghailvbing.hhw.anzhi','com.netease.dhxy.anzhi'),
			'2018-05-26'=> array('com.pokercity.bydrqp.anzhi','com.netease.my.anzhi','com.shanghailvbing.hhw.anzhi','com.netease.dhxy.anzhi'),
			'2018-05-27'=> array('com.pokercity.bydrqp.anzhi','com.netease.my.anzhi','com.shanghailvbing.hhw.anzhi','com.netease.dhxy.anzhi'),
			'2018-05-28'=> array('com.pokercity.bydrqp.anzhi','com.netease.my.anzhi','com.shanghailvbing.hhw.anzhi','com.netease.dhxy.anzhi'),
			'2018-05-29'=> array('com.pokercity.bydrqp.anzhi','com.netease.my.anzhi','com.shanghailvbing.hhw.anzhi','com.netease.dhxy.anzhi'),
			
			'2018-05-30'=> array('com.netease.l10.anzhi','com.regin.gcld.anzhi','com.shenghe.wzcq.zqy.anzhi','com.youzu.snsgz.anzhi'),
			'2018-05-31'=> array('com.netease.l10.anzhi','com.regin.gcld.anzhi','com.shenghe.wzcq.zqy.anzhi','com.youzu.snsgz.anzhi'),
			'2018-06-01'=> array('com.netease.l10.anzhi','com.regin.gcld.anzhi','com.shenghe.wzcq.zqy.anzhi','com.youzu.snsgz.anzhi'),
			'2018-06-02'=> array('com.netease.l10.anzhi','com.regin.gcld.anzhi','com.shenghe.wzcq.zqy.anzhi','com.youzu.snsgz.anzhi'),
			'2018-06-03'=> array('com.netease.l10.anzhi','com.regin.gcld.anzhi','com.shenghe.wzcq.zqy.anzhi','com.youzu.snsgz.anzhi'),
			
			'2018-06-04'=> array('com.regin.tjxs.anzhi','com.KingOfTank.kkk.anzhi','com.gbits.atm.anzhi','com.wanmei.zhuxian.anzhi'),
			'2018-06-05'=> array('com.regin.tjxs.anzhi','com.KingOfTank.kkk.anzhi','com.gbits.atm.anzhi','com.wanmei.zhuxian.anzhi'),
			'2018-06-06'=> array('com.regin.tjxs.anzhi','com.KingOfTank.kkk.anzhi','com.gbits.atm.anzhi','com.wanmei.zhuxian.anzhi'),
			'2018-06-07'=> array('com.regin.tjxs.anzhi','com.KingOfTank.kkk.anzhi','com.gbits.atm.anzhi','com.wanmei.zhuxian.anzhi'),
			'2018-06-08'=> array('com.regin.tjxs.anzhi','com.KingOfTank.kkk.anzhi','com.gbits.atm.anzhi','com.wanmei.zhuxian.anzhi'),
			
			'2018-06-09'=> array('com.GF.PalaceM2_cn_cn.hwy_android_anzhi.anzhi','com.sy37.yhjyj.anzhi','com.gaea.xianjian.anzhi','com.netease.stzb.anzhi'),
			'2018-06-10'=> array('com.GF.PalaceM2_cn_cn.hwy_android_anzhi.anzhi','com.sy37.yhjyj.anzhi','com.gaea.xianjian.anzhi','com.netease.stzb.anzhi'),
			'2018-06-11'=> array('com.GF.PalaceM2_cn_cn.hwy_android_anzhi.anzhi','com.sy37.yhjyj.anzhi','com.gaea.xianjian.anzhi','com.netease.stzb.anzhi'),
			'2018-06-12'=> array('com.GF.PalaceM2_cn_cn.hwy_android_anzhi.anzhi','com.sy37.yhjyj.anzhi','com.gaea.xianjian.anzhi','com.netease.stzb.anzhi'),
			'2018-06-13'=> array('com.GF.PalaceM2_cn_cn.hwy_android_anzhi.anzhi','com.sy37.yhjyj.anzhi','com.gaea.xianjian.anzhi','com.netease.stzb.anzhi'),
			
			'2018-06-14'=> array('com.ddianle.lovedance.anzhi','com.shengtu.twltcs.anzhi','com.hcg.cok.anzhi','com.jedigames.p16.anzhi'),
			'2018-06-15'=> array('com.ddianle.lovedance.anzhi','com.shengtu.twltcs.anzhi','com.hcg.cok.anzhi','com.jedigames.p16.anzhi'),
			'2018-06-16'=> array('com.ddianle.lovedance.anzhi','com.shengtu.twltcs.anzhi','com.hcg.cok.anzhi','com.jedigames.p16.anzhi'),
			'2018-06-17'=> array('com.ddianle.lovedance.anzhi','com.shengtu.twltcs.anzhi','com.hcg.cok.anzhi','com.jedigames.p16.anzhi'),
			'2018-06-18'=> array('com.ddianle.lovedance.anzhi','com.shengtu.twltcs.anzhi','com.hcg.cok.anzhi','com.jedigames.p16.anzhi'),
			
			'2018-06-19'=> array('com.bf.sgs.hdexp','com.supercell.clashofclans.anzhi','com.netease.onmyoji.anzhi','net.crimoon.pm.anzhi'),
			'2018-06-20'=> array('com.bf.sgs.hdexp','com.supercell.clashofclans.anzhi','com.netease.onmyoji.anzhi','net.crimoon.pm.anzhi'),
			'2018-06-21'=> array('com.bf.sgs.hdexp','com.supercell.clashofclans.anzhi','com.netease.onmyoji.anzhi','net.crimoon.pm.anzhi'),
			'2018-06-22'=> array('com.bf.sgs.hdexp','com.supercell.clashofclans.anzhi','com.netease.onmyoji.anzhi','net.crimoon.pm.anzhi'),
			'2018-06-23'=> array('com.bf.sgs.hdexp','com.supercell.clashofclans.anzhi','com.netease.onmyoji.anzhi','net.crimoon.pm.anzhi'),
			
			'2018-06-24'=> array('com.jiguang.dtszj.anzhi','com.taiyouxi.jiwushuang.anzhi','com.netease.ldxy.anzhi','com.netease.tx.anzhi'),
			'2018-06-25'=> array('com.jiguang.dtszj.anzhi','com.taiyouxi.jiwushuang.anzhi','com.netease.ldxy.anzhi','com.netease.tx.anzhi'),
		);
	}
	return $config[$today];
}

function get_soft_info(){
	global $model,$redis,$prefix,$active_id,$today;
	$soft_info_key = $prefix.":".$active_id.':soft_info:'.$today;
	$soft_info = $redis->get($soft_info_key);	
	if($soft_info === null){
		$pkg_arr = get_specify_soft();
		$option = array(
			'table' => 'sj_soft AS A',
			'where' => array(
				'A.status' => 1,
				'A.hide' => 1,
				'A.package' => $pkg_arr,
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
			$soft_info[$v['softid']] = $v;
		}
		$redis->set($soft_info_key,$soft_info,86400);	
	}
	return $soft_info;
}