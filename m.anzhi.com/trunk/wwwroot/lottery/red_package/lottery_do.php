<?php
include_once ('./fun.php');
$page_id_option = array(
	'where' => array(
		'id' => $aid
	),
	'cache_time' => 300,
	'table' => 'sj_activity'
);
$page_id_result = $model -> findOne($page_id_option);

if($uid){
	$now_num = $redis -> setx('incr',$lottery_num_key,-1);
	//$now_num = 10;
	if($now_num >= 0){
                //刷出来的次数 抽奖直接不中

            	$brush_res = get_brush_all($aid,2);
                if($brush_res['code'] == 0){ 
                    $msg = $brush_res['msg'];
                    $the_awards = -1;
                }else{
                    //抽奖
                    load_helper('task');
                    $task_client = get_task_client();
                    $need = array(
                        'mac' => $mac,
                        'imei' => $imei,
                        'imsi' => $imsi,
                        'uid' =>$uid,
                        'aid' => $aid,
                        'session' => $_SESSION,
                    );

                    $the_awards = $task_client->do('red_package_worker',json_encode($need)); //todo
                }

		$num_where = array('uid' => $uid,'aid' => $aid);
		$num_data = array(
			'lottery_num' => array('exp','lottery_num-1'),
			'update_tm' => time(),
			'__user_table' => 'red_package_lottery_num'
		);
		$num_result = $model -> update($num_where,$num_data,'lottery/lottery');

		if($the_awards == -1){
			$msg_award = 0;
			$the_award = -1;
		}else{
			$the_award = json_decode($the_awards,true);
			$msg_award = $the_award['level'];
		}

		$log_data = array(
			'imsi' => $_SESSION['USER_IMSI'],
			'device_id' => $_SESSION['DEVICEID'],
			'activity_id' => $aid,
			'ip' => $_SERVER['REMOTE_ADDR'],
			'sid' => $_GET['sid'],
			'award' => $msg_award,
			'time' => time(),
			'users' => '',
			//'brush' => $brush,
			'uid' => $uid,
                        'type_lottery' => 4,//4 红包九宫格  5红包翻翻乐
			'type_name' => '红包活动九宫格',
			'key' => 'lottery'
		);

		permanentlog('activity_'.$aid.'.log', json_encode($log_data));
                //set_brush_byip($aid);//todo  ip是否要加

		if($the_award == -1){
			$prize_option = array(
				'where' => array(
					'aid' => $aid,
					'type' => 7,
					'status' => 1
				),
				'cache_time' => 300,
				'table' => 'sign_prize'
			);
			$prize_result = $model -> findAll($prize_option,'lottery/lottery');
			$my_prize = array_rand($prize_result);
			$my_award = $prize_result[$my_prize]['level'];
			$data = array($the_award,$now_num,$my_award);
			echo json_encode($data);
                        permanentlog('activity_'.$aid.'.log', json_encode($data));
			return json_encode($data);
		}else{
                        $data = array($the_award['level'],$now_num,$the_award['name'],$the_award['type'],$the_award['red_package_id'],$the_award['insertid'],$the_award['package']);
                        $log_data = array(
                                'imsi' => $_SESSION['USER_IMSI'],
                                'device_id' => $_SESSION['DEVICEID'],
                                'activity_id' => $aid,
                                'ip' => $_SERVER['REMOTE_ADDR'],
                                'sid' => $_GET['sid'],
                                'time' => time(),
                                'award_level' => $the_award['level'],
			        'red_package_id' => $the_award['red_package_id'],
                                'users' => '',
                                'uid' => $uid,
                                'award_id' => $the_award['insertid'],
                                'award_name' => $the_award['name'],
                                'type_lottery' => $page_result['lottery_style'],
                                'type_name' => $type_name,
                                'key' => 'object_award'//实体奖
                        );

                        permanentlog('activity_'.$aid.'.log', json_encode($log_data));
			
			echo json_encode($data);
			return json_encode($data);
		}
	}else{
		$now_num = $redis -> setx('incr',$lottery_num_key,1);
		echo $now_num;
		return $now_num;
	}
}
