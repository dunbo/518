<?php
include_once ('./fun.php');
$build_query = http_build_query($_GET);
if($configs['is_test'] == 1 ) {
	$h_str = 'dev.';
	$tplObj -> out['is_test'] = 1;
}
//场次
$screenings = get_screenings();
if($screenings == 1 || $screenings == 3){
	$exit_tab_key = $prefix.":".$active_id.":exit_tab:".$imsi.":".$screenings.":".$today;
}else{
	if($uid == ''){
		$url = $activity_host."/lottery/{$prefix}/index.php?aid=".$active_id."&sid=".$sid;
		header("Location: {$url}");	
		exit;
	}
	$exit_tab_key = $prefix.":".$active_id.":exit_tab:".$uid.":".$screenings.":".$today;
}
$screenings_tm = get_screenings_tm();
$end_tm = $screenings_tm[$today][$screenings]+1800;
if($screenings == 1 || $screenings == 3){
	$is_answer_key = $prefix.":".$active_id.":is_answer:".$imsi.":".$screenings.":".$today;
}else{
	$is_answer_key = $prefix.":".$active_id.":is_answer:".$uid.":".$screenings.":".$today;
}
if($_POST['is_answer']){
	if($time >= $end_tm){
        return array(
            'code' => 0,
            'msg' => '时间到不能答题!',
        );	
	}	
	//答题成功失败日志
	$log_data = array(
		"imsi" => $imsi,
		"device_id" => $_SESSION['DEVICEID'],
		"activity_id" => $active_id,
		"ip" => $_SERVER['REMOTE_ADDR'],
		"sid" => $sid,
		"time" => $time,
		"user" => $_SESSION['USER_NAME'],
		'uid'=> $uid,
		'screenings'=> $screenings,//当天场次
		'code' => $_POST['code'],//1成功2失败
		'num' => $_POST['num'],
		'key' => 'answer'
	);
	$config = get_limit_list();
	$award = $config[$today][$screenings]['award'];//流量是单位 礼券是礼券id
	$log_data['award'] = $award;	
	$is_answer = $redis->setnx($is_answer_key,1);	
	$redis->expire($is_answer_key,86400);
	if(!$is_answer){
        return array(
            'code' => 0,
            'msg' => '该场次已经参加过了',
        );				
	}
	if($_POST['code'] == 1){
		$json_str = json_encode(array($today,$screenings,$end_tm));
		if($screenings == 1 || $screenings == 3){
			$bind_list = get_bind_status();
			$where = array('aid' => $active_id,'imsi' => $imsi,'address'=>$json_str);
			$option = array(
				'where' => $where,
				'table' => 'imsi_lottery_award' ,
			);
			$res = $model->findOne($option, 'lottery/lottery');
			if(!$res){
				$data = array(
					'aid' => $active_id,
					'imsi' => $imsi,
					'award' => $award,//单位M
					'time' => $time,
					'status' => 0,
					'address' => $json_str,
					'telephone' => $bind_list['mobile'],
					'__user_table' => "imsi_lottery_award" ,
				);
				$ret = $model -> insert($data,'lottery/lottery');
			}else{
				return array(
					'code' => 0,
					'msg' => '该场次已经参加过了',
				);	
			}
		}else{
			list($a,$coupon_config,$c) = coupon_config();
			$where = array('aid' => $active_id,'uid' => $uid,'ext'=>$json_str);
			$option = array(
				'where' => $where,
				'table' => 'valentine_draw_award' ,
			);
			$res = $model->findOne($option, 'lottery/lottery');
			if(!$res){		
				$data = array(
					'aid' => $active_id,
					'uid' => $uid,
					'username' => $_SESSION['USER_NAME'],
					'pid' => $award,//礼券id
					'prizename' => $config[$today][$screenings]['pkg'], //包名
					'status' => 0,
					'create_tm' => $time,
					'money' => $coupon_config[$award],
					'ext' => $json_str,
					'__user_table' => "valentine_draw_award" ,
				);
				$ret = $model -> insert($data,'lottery/lottery');	
			}else{
				return array(
					'code' => 0,
					'msg' => '该场次已经参加过了',
				);	
			}	
		}
	}
	permanentlog('activity_'.$active_id.'.log', json_encode($log_data));	
	exit;	
}else if($_POST['exit_tab']){
	//没答完退出
	$redis->set($exit_tab_key,$_POST['num']);	
	$redis->expire($exit_tab_key,86400);
	exit;		
}else if($_POST['answer_submit']){
	//答题开始
	$nn = $_POST['n'];
	$num = $_POST['num'];
	$redis->set($exit_tab_key,$num);	
	$redis->expire($exit_tab_key,86400);	
	$log_data = array(
		"imsi" => $imsi,
		"device_id" => $_SESSION['DEVICEID'],
		"activity_id" => $active_id,
		"ip" => $_SERVER['REMOTE_ADDR'],
		"sid" => $sid,
		"time" => $time,
		"user" => $_SESSION['USER_NAME'],
		'uid'=> $uid,
		'screenings'=> $screenings,//当天场次
		'answer' => $nn,//答案
		'num' => $num,
		'key' => 'answer_submit'
	);
	$questions = random_questions(10,$screenings);
	$succ_answer = trim(strtoupper($questions[$num][5]));
	$log_data['succ_answer'] = $succ_answer;
	if($nn == $succ_answer){
		$log_data['msg'] = '正确';
		permanentlog('activity_'.$active_id.'.log', json_encode($log_data));
		exit(json_encode(array('code'=>1,'msg'=>'正确')));
	}else{
		$log_data['msg'] = '错误';
		permanentlog('activity_'.$active_id.'.log', json_encode($log_data));
		exit(json_encode(array('code'=>0,'msg'=>'错误')));
	}
}

$tplObj -> out['end_tm'] = $end_tm;	
$tplObj -> out['format_end_tm'] = date("H:i",$end_tm);	
//题库
$questions = random_questions(10,$screenings);
$tplObj -> out['questions_json'] = json_encode($questions);	
$tplObj -> out['imsi'] = $imsi;	
$tplObj -> out['aid'] = $active_id;
$tplObj -> out['sid'] = $sid;
$tplObj -> out['sid'] = $sid;
$tplObj -> out['is_revive'] = $_GET['is_revive'];
$tplObj -> out['static_url'] = $configs['static_url'];
$tplObj -> out['new_static_url'] = $configs['new_static_url'];
$tplObj -> out['prefix'] = $prefix;
$tplObj -> out['now_tm'] = $time;	
$is_answer = $redis->get($is_answer_key);
$tplObj -> out['is_answer'] = $is_answer;
list($down_num,$resurrection_num,$res_num) = get_res_num();
$tplObj -> out['resurrection_num'] = $resurrection_num;
$tplObj -> out['res_num'] = $res_num;
$exit_tab = $redis->get($exit_tab_key);	
$tplObj -> out['exit_tab'] = $exit_tab;
$online_num = get_online_num($questions,$screenings);
$tplObj -> out['online_num_json'] =  json_encode($online_num); 
$tpl = "lottery/".$prefix."/answer.html";	
$tplObj -> display($tpl);