<?php

include_once (dirname(realpath(__FILE__)).'/../init.php');
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

$package = $_GET['package'];
if($_SESSION['USER_IMSI']){
	$imsi = $_SESSION['USER_IMSI'];
	$imsi_num = "general_lottery:{$imsi}_num_{$aid}";
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

$have_package_redis = "general_lottery:have_package_{$imsi}_{$aid}";
$the_have_package = $redis -> get($have_package_redis);

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
if(count($last_package_arr) > 1){
	foreach($last_package_arr as $key => $val){
		if($key == $need_packages){
			unset($last_package_arr[$key]);
		}
	}
}

if($imsi && strlen($imsi) == 15 && $imsi != '000000000000000'){
        //brush_second_do($aid);
	$now_num = $redis -> setx('incr',$imsi_num,-1);
	//$now_num = 10;
	if($now_num >= 0){


	        $brush_res = get_brush_all($aid,1);
                if($brush_res['code'] == 0){ 
                    $msg = $brush_res['msg'];
                    $the_awards = -1;
                }else{
                        $time=time();
                        load_helper('task');
                        $task_client = get_task_client();
                        $need = array('imsi' => $imsi,'aid' => $aid,'package' => $need_package,'package_arr' => $last_package_arr);
                        if(in_array($need_package,$the_have_package) && $page_info_result['is_repeat'] == 1){
                                $the_awards = -1;
                        }else{
                                $the_awards = $task_client->do('general_lottery',json_encode($need));
                        }
                }

                /*
                $black_imsi_lottery = 'gen_brush:black:aid:'.$aid.'imsi:'.$imsi;
                $black_imsi_lottery_value = $redis->get($black_imsi_lottery);
                if($black_imsi_lottery_value==1){
                        $brush = 3;
			$the_awards = -1;
                }else{

                    $black_list = "black_name_list:{$aid}";
                    $black_list_name = $redis->gethash($black_list,$imsi);

                    if(!$black_list_name){//如果不在下载黑名单
                        $time=time();
                        //抽奖

                        //刷出来的次数 抽奖直接不中
                        $share_key_today = 'gen_brush:share:imsi:'.$imsi.':aid:'.$aid.':'.date('Ymd',time());

                        $down_key_today = 'gen_brush:down:imsi:'.$imsi.':aid:'.$aid.':'.date('Ymd',time());

                        $home_key_today = 'gen_brush:home:imsi:'.$imsi.':aid:'.$aid.':'.date('Ymd',time());

                        $share_key_today_time = $redis->get($share_key_today);
                        $down_key_today_time = $redis->get($down_key_today);
                        $home_key_today_time = $redis->get($home_key_today);

                        if($share_key_today_time==$home_key_today_time||$down_key_today_time==$home_key_today_time||$home_key_today_time==$time||$share_key_today_time==$time||$down_key_today_time==$time){ //刷的直接不中奖
                                $res=$redis->setnx($black_imsi_lottery,1);
                                $redis->expire($black_imsi_lottery,86400*60);
                                $brush = 1;
                                $the_awards = -1;
                        }else{
                                $brush = 2;
                            load_helper('task');
                            $task_client = get_task_client();
                            $need = array('imsi' => $imsi,'aid' => $aid,'package' => $need_package,'package_arr' => $last_package_arr);
                            if(in_array($need_package,$the_have_package) && $page_info_result['is_repeat'] == 1){
                                    $the_awards = -1;
                            }else{
                                    $the_awards = $task_client->do('general_lottery',json_encode($need));
                            }
                        }
                    }else{
                        $brush = 4;
			$the_awards = -1;
                    }
                }*/


		//$the_award = $task_client->do('general_lottery_test',json_encode($need));

		//$the_award = -1;
		//$the_award = json_encode(array('level' => 2,'type' => 2,'name' => 'sdfasdf','gift_number' => array('first_text' => 'asfsdf','second_text' => 'asdfsd','third_text' => 'com.baidu.ghjklzx')));
		//$the_award = array('level' => 1,'type' => 2,'name' => '四等奖 京东E卡','gift_number' => array('324234234','234234234'));
		//$the_awards = json_encode(array('pid' => 186,'name' => '四等奖  京东E卡一张','level' => 4,'imsi' => 460011114907782,'type' => 2,'gift_number' => array('first_text' => '5rgvqx 挑战自我','third_text' => 'org.jiaxxhaha.samegameym')));
		//$the_awards = json_encode(array('pid' => 184,'name' => '二等奖 迪奥香水一瓶','level' => 2,'imsi' => 460011114907782,'type' => 1));
		$num_where = array('imsi' => $imsi,'aid' => $aid);
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
		$log_data = array(
			'imsi' => $imsi,
			'device_id' => $_SESSION['DEVICEID'],
			'activity_id' => $aid,
			'ip' => $_SERVER['REMOTE_ADDR'],
			'sid' => $_GET['sid'],
			'award' => $msg_award,
			'time' => $time,
			'users' => '',
			'uid' => '',
			'msh' => $msg,
			//'brush' => $brush,
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
                            set_brush_byip($aid);
				$data = array($the_award['level'],$now_num,$the_award['name'],$the_award['type']);
				$log_data = array(
					'imsi' => $imsi,
					'device_id' => $_SESSION['DEVICEID'],
					'activity_id' => $aid,
					'ip' => $_SERVER['REMOTE_ADDR'],
					'sid' => $_GET['sid'],
					'time' => time(),
					'award_level' => $the_award['level'],
					'users' => '',
					'uid' => '',
					'award_id' => $the_award['insertid'],
					'key' => 'object_award'
				);

				permanentlog('activity_'.$aid.'.log', json_encode($log_data));
			}elseif($the_award['type'] == 2){
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
				$log_data = array(
					'imsi' => $imsi,
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
					'package' => $the_package,
					'gift' => $the_award['gift_number']['first_text'],
					'users' => '',
					'uid' => '',
					'lottery_type' => $page_result['lottery_style'],
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
