<?php
include_once (dirname(realpath(__FILE__)).'/../../init.php');
$config = load_config('lottery_cache/redis',"lottery");
if ($config) {
	$redis = new GoRedisCacheAdapter($config);
} else {
	$redis = GoCache::getCacheAdapter('redis');
}
$model = new GoModel();
session_begin();
$aid = $_GET['aid'];
if(ctype_digit($aid)==false){
    exit(0);
}

$uid = $_SESSION['USER_UID'];

$package = $_GET['package'];
if($uid){
	$imsi = $_SESSION['USER_IMSI'];
	$imsi_num = "general_lottery:{$uid}_num_{$aid}";
}

$virtual_prize_option = array(
	'where' => array(
		'aid' => $aid
	),
	'cache_time' => 300,
	'group' => 'pid',
	'table' => 'gm_virtual_prize'
);
$virtual_prize_result = $model -> findAll($virtual_prize_option,'lottery/lottery');

$page_id_option = array(
	'where' => array(
		'id' => $aid
	),
	'cache_time' => 300,
	'table' => 'sj_activity'
);
$page_id_result = $model -> findOne($page_id_option);

$page_info_option = array(
	'where' => array(
		'ap_id' => $page_id_result['activity_page_id']
	),
	'cache_time' => 300,
	'table' => 'sj_activity_page'
);
$page_info_result = $model -> findOne($page_info_option);

/*
for($i=0;$i<count($virtual_prize_result);$i++){
	$gift_package = "general_lottery:package_{$virtual_prize_result[$i]['pid']}";
	$str = 'gift_package_'.$virtual_prize_result[$i]['pid'];
	$$str = $redis -> get($gift_package);
	if($$str){
		foreach($$str as $key => $val){
			$all_package .= $val . ',';
		}
	}
}

//数组去重后，获取是否优先已安装（未安装），是否用户不可重复中同一礼包，读取到用户已获取到礼包的包名；
$all_package_str = substr($all_package,0,-1);
$all_package_arr_go = explode(',',$all_package_str);
$all_package_arr = array_unique($all_package_arr_go);

if($package){
	$package_stall_str = substr($package,0,-1);
	$package_stall_arr = explode(',',$package_stall_str);
	$need_package_1_arr = array_intersect($package_stall_arr,$all_package_arr);
}else{
	$need_package_1_arr = $all_package_arr;
}


if($page_info_result['is_repeat'] == 1 && $the_have_package){//后台配置为不中相同软件礼包
	foreach($need_package_1_arr as $key => $val){
		if(!in_array($val,$the_have_package)){
			$last_package_arr[] = $val;
		}
	}
}else{
	$last_package_arr = $need_package_1_arr;  //全部软件
}

//随机取出一个包名，并从原数组中去掉该包名
$need_packages = array_rand($last_package_arr,1);
$need_package = $last_package_arr[$need_packages];
*/
$have_package_redis = "general_lottery:have_package_{$uid}_{$aid}";
$the_have_package = $redis -> get($have_package_redis);

    $gift_package = "general_lottery:package_{$virtual_prize_result[0]['pid']}";
    $new_pkg = $redis->get($gift_package);
    $need_package = $new_pkg[0]['third_text'];
    /*
if(count($last_package_arr) > 1){
	foreach($last_package_arr as $key => $val){
		if($key == $need_packages){
			unset($last_package_arr[$key]);
		}
	}
}*/

//if($imsi && strlen($imsi) == 15 && $imsi != '000000000000000'){
if($uid){
        //brush_second_do($aid);
	$now_num = $redis -> setx('incr',$imsi_num,-1);
	if($now_num >= 0){

                //刷出来的次数 抽奖直接不中
                /*
                $share_key_today = 'gen_pre_brush:share:uid:'.$uid.':aid:'.$aid.':'.date('Ymd',time());

                $yuyue_key_today = 'gen_pre_brush:yuyue:uid:'.$uid.':aid:'.$aid.':'.date('Ymd',time());

                $home_key_today = 'gen_pre_brush:home:uid:'.$uid.':aid:'.$aid.':'.date('Ymd',time());

                $share_key_today_time = $redis->get($share_key_today);
                $yuyue_key_today_time = $redis->get($yuyue_key_today);
                $home_key_today_time = $redis->get($home_key_today);

                if($share_key_today_time==$home_key_today_time||$yuyue_key_today_time==$home_key_today_time||$home_key_today_time==time()||$share_key_today_time==time()||$yuyue_key_today_time==time()){
                $black_list_uid = "black_name_list:{$aid}";
                $black_list_name = $redis->gethash($black_list_uid,$uid);
                */

	        $brush_res = get_brush_all($aid,2);
                if($brush_res['code'] == 0){ 
                    $msg = $brush_res['msg'];
                    $the_awards = -1;
                }else{
                        //抽奖
                        load_helper('task');
                        $task_client = get_task_client();
                        $need = array(
                            'imsi' => $imsi
                            ,'uid'=>$uid,
                            'aid' => $aid,
                            'package' => $need_package,
                            'activityName' => $$page_id_result['name'],
                            'package_arr' => $last_package_arr);
                        if(in_array($need_package,$the_have_package) && $page_info_result['is_repeat'] == 1){
                            $need['no_gift']=1;
                            $the_awards = $task_client->do('general_pre_lottery',json_encode($need));
                        }else{
                                $the_awards = $task_client->do('general_pre_lottery',json_encode($need));
                        }
                }

                /*
                if($black_list_name){
                    $brush=1;
                    $the_awards = -1;
                }else{
                    $total = get_brush_byusernum($aid);
                    if($total>=4){
                        $brush=2;
                        $the_awards = -1;
                    }else{
                        $brush=0;
                        //抽奖
                        load_helper('task');
                        $task_client = get_task_client();
                        $need = array(
                            'imsi' => $imsi
                            ,'uid'=>$uid,
                            'aid' => $aid,
                            'package' => $need_package,
                            'activityName' => $$page_id_result['name'],
                            'package_arr' => $last_package_arr);
                        if(in_array($need_package,$the_have_package) && $page_info_result['is_repeat'] == 1){
                            $need['no_gift']=1;
                            $the_awards = $task_client->do('general_pre_lottery',json_encode($need));
                        }else{
                                $the_awards = $task_client->do('general_pre_lottery',json_encode($need));
                        }
                    }
                }
                */

		//$the_award = $task_client->do('general_lottery_test',json_encode($need));

		//$the_award = -1;
		//$the_award = json_encode(array('level' => 2,'type' => 2,'name' => 'sdfasdf','gift_number' => array('first_text' => 'asfsdf','second_text' => 'asdfsd','third_text' => 'com.baidu.ghjklzx')));
		//$the_award = array('level' => 1,'type' => 2,'name' => '四等奖 京东E卡','gift_number' => array('324234234','234234234'));
		//$the_awards = json_encode(array('pid' => 186,'name' => '四等奖  京东E卡一张','level' => 4,'imsi' => 460011114907782,'type' => 2,'gift_number' => array('first_text' => '5rgvqx 挑战自我','third_text' => 'org.jiaxxhaha.samegameym')));
		//$the_awards = json_encode(array('pid' => 184,'name' => '二等奖 迪奥香水一瓶','level' => 2,'imsi' => 460011114907782,'type' => 1));
		$num_where = array('uid' => $uid,'aid' => $aid);
		$num_data = array(
			'lottery_num' => array('exp','lottery_num-1'),
			'update_tm' => time(),
			'__user_table' => 'gm_lottery_num'
		);
		$num_result = $model -> update($num_where,$num_data,'lottery/lottery');
		if($the_awards == -1){
			$msg_award = 0;
			$the_award = -1;
		}else{
			$the_award = json_decode($the_awards,true);
			$msg_award = $the_award['level'];
		}

				$activity_option = array(
					'where' => array(
						'id' => $aid
					),
					'cache_time' => 300,
					'table' => 'sj_activity'
				);
				$activity_result = $model -> findOne($activity_option);

				$page_option = array(
					'where' => array(
						'ap_id' => $activity_result['activity_page_id']
					),
					'cache_time' => 300,
					'table' => 'sj_activity_page'
				);
				$page_result = $model -> findOne($page_option);


                if($page_result['lottery_style']==1){$type_name = '老虎机';}
                if($page_result['lottery_style']==2){$type_name = '九宫格';}
                if($page_result['lottery_style']==3){$type_name = '大转盘';}

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
			'msg' => $msg,
			'uid' => $uid,
                        'type_lottery' => $page_result['lottery_style'],
			'type_name' => $type_name,
			'key' => 'lottery'
		);

		permanentlog('activity_'.$aid.'.log', json_encode($log_data));

		if($the_award == -1){
			$prize_option = array(
				'where' => array(
					'aid' => $aid,
					'type' => 3,
					'status' => 1
				),
				'cache_time' => 300,
				'table' => 'gm_lottery_prize'
			);
			$prize_result = $model -> findAll($prize_option,'lottery/lottery');
			$my_prize = array_rand($prize_result);
			$my_award = $prize_result[$my_prize]['level'];
			$data = array($the_award,$now_num,$my_award);
			echo json_encode($data);
			return json_encode($data);
		}else{
			if($the_award['type'] == 1){
				$data = array($the_award['level'],$now_num,$the_award['name'],$the_award['type']);
				$log_data = array(
					'imsi' => $_SESSION['USER_IMSI'],
					'device_id' => $_SESSION['DEVICEID'],
					'activity_id' => $aid,
					'ip' => $_SERVER['REMOTE_ADDR'],
					'sid' => $_GET['sid'],
					'time' => time(),
					'award_level' => $the_award['level'],
					'users' => '',
					'uid' => $uid,
					'award_id' => $the_award['insertid'],
                                        'award_name' => $the_award['name'],
					'type_lottery' => $page_result['lottery_style'],
					'type_name' => $type_name,
					'key' => 'object_award'
				);

				permanentlog('activity_'.$aid.'.log', json_encode($log_data));
                                set_brush_byip($aid);
			}elseif($the_award['type'] == 2||$the_award['type'] == 4||$the_award['type'] == 5){
				$pid = $the_award['pid'];
				$prize_option = array(
					'where' => array(
						'pid' => $pid
					),
					'cache_time' => 300,
					'table' => 'gm_lottery_prize'
				);
				$prize_result = $model -> findOne($prize_option,'lottery/lottery');
				$softinfo = gomarket_action('soft.GoGetSoftDetailPackage', array('PACKAGE_NAME' => $the_award['gift_number']['third_text']));
				$soft_icon = $softinfo['ICON'];
				$soft_size = formatFileSize(1,$softinfo['SOFT_SIZE']);
				$data = array($the_award['level'],$now_num,$the_award['name'],$the_award['type'],$the_award['gift_number'],$prize_result['desc'],$soft_icon,$soft_size,$softinfo['SOFT_NAME']);
				$have_package_go = $redis -> get($have_package_redis);
				if(!in_array($the_award['gift_number']['third_text'],$have_package_go)){
					if($have_package_go){
						array_push($have_package_go,$the_award['gift_number']['third_text']);
						$new_have_package = $have_package_go;
					}else{
						$new_have_package = array($the_award['gift_number']['third_text']);
					}	
					$redis -> set($have_package_redis,$new_have_package);
				}
				if($the_award['gift_number']['third_text']){
					$the_package = $the_award['gift_number']['third_text'];
				}else{
					$the_package = '';
				}

                                /*
				$activity_option = array(
					'where' => array(
						'id' => $aid
					),
					'cache_time' => 300,
					'table' => 'sj_activity'
				);
				$activity_result = $model -> findOne($activity_option);
				$page_option = array(
					'where' => array(
						'ap_id' => $activity_result['activity_page_id']
					),
					'cache_time' => 300,
					'table' => 'sj_activity_page'
				);
				$page_result = $model -> findOne($page_option);
                                if($page_result['lottery_style']==1){$type_name = '老虎机';}
                                if($page_result['lottery_style']==2){$type_name = '九宫格';}
                                if($page_result['lottery_style']==3){$type_name = '大转盘';}
                                 */
				$log_data = array(
					'imsi' => $_SESSION['USER_IMSI'],
					'device_id' => $_SESSION['DEVICEID'],
					'activity_id' => $aid,
					'ip' => $_SERVER['REMOTE_ADDR'],
					'sid' => $_GET['sid'],
					'time' => time(),
					'award_level' => $the_award['level'],
					'pid' => $pid,
					'name' => '',
					'tel' => '',
					'address' => '',
					'gift' => $the_award['gift_number']['first_text'],
                                        "package" =>  $the_award['type'] == 5  ? $the_award['package'] : $the_package,
                                        "softname" => $the_award['type'] == 5 ? $the_award['softname'] : '',
                                        "gift" => $the_award['type'] == 5 ? $the_award['gift_number'] : '',
                        		'type'=>$the_award['type'],//1实物2礼包3谢谢参与4礼券5礼包（直接发放）		
					'users' => '',
					'uid' => $uid,
					'award_id' => $the_award['insertid'],
					'type_lottery' => $page_result['lottery_style'],
					'type_name' => $type_name,
					'award_name' => $the_award['name'],
					'key' => 'award'
				);
				permanentlog('activity_'.$aid.'.log', json_encode($log_data));
			}
			
			echo json_encode($data);
			return json_encode($data);
		}
	}else{
		$now_num = $redis -> setx('incr',$imsi_num,1);
		echo $now_num;
		return $now_num;
	}
}
