<?php
include_once (dirname(realpath(__FILE__)).'/../init.php');
$config = load_config('lottery_cache/redis','lottery');
if ($config) {
	$redis = new GoRedisCacheAdapter($config);
} else {
	$redis = GoCache::getCacheAdapter('redis');
}
$model = new GoModel();
$message = session_begin();
if($_SESSION['product']!=1){
    $_SESSION['VERSION_CODE']=6310;
}
$activity_host = "http://m.test.anzhi.com";

$imsi = $_SESSION['USER_IMSI'];
$aid = $_GET['aid'];
if(ctype_digit($aid)==false){
    exit(0);
}
$share = $_GET['share'];
$version_code = $_SESSION['VERSION_CODE'];
$alone_update = $_SESSION['ALONE_UPDATE'];
get_brush_bysn();


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
$page_result['ap_desc'] = htmlspecialchars_decode($page_result['ap_desc']);

if($alone_update == 1 && $version_code < $page_result['version_code']){
	$channel_option = array(
		'where' => array(
			'cid' => $cid,
			'status' => 1,
			'version_code' => array('exp',">={$page_result['version_code']}"),
			'limit_rules' => array('exp'," ='' or limit_rules is null "),
		),
		'cache_time' => 300,
		'table' => 'sj_market',
	);
	$channel_result = $model -> findAll($channel_option);

	if(!$channel_result){
		$channel_status = 100;
	}else{
		$channel_status = 200;
	}
	$tplObj -> out['channel_status'] = $channel_status;
}

if($_POST){
		$log_data = array(
			'imsi' => $imsi,
			'device_id' => $_SESSION['DEVICEID'],
			'activity_id' => $_POST['aid'],
			'ip' => $_SERVER['REMOTE_ADDR'],
			'sid' => $_GET['sid'],
			'time' => time(),
			'name' => $_POST['name_back'],
			'tel' => $_POST['tel_back'],
			'key' => 'userinfo_back'
		);
		permanentlog('activity_'.$_POST['aid'].'.log', json_encode($log_data));
}

if($imsi){
	if($alone_update==0 && $version_code < $page_result['version_code']){
		$version_status = 200;
		$log_data = array(
			'imsi' => $imsi,
			'device_id' => $_SESSION['DEVICEID'],
			'activity_id' => $aid,
			'ip' => $_SERVER['REMOTE_ADDR'],
			'sid' => $_GET['sid'],
			'time' => time(),
			'users' => '',
			'uid' => '',
			'key' => 'update_page'
		);
		permanentlog('activity_'.$aid.'.log', json_encode($log_data));
		$resultanzhi = gomarket_action('soft.GoGetSoftDetailPackage', array(
			'PACKAGE_NAME' => 'cn.goapk.market',
			'VR' => 3,
		));
		$tplObj -> out['version_status'] = $version_status;
		$tplObj -> out['resultanzhi'] = $resultanzhi;
	}else{

                $time=time();
                /*
                $home_key_today = 'gen_brush:home:imsi:'.$imsi.':aid:'.$aid.':'.date('Ymd',time());
                $redis->setnx($home_key_today,$time);
                $redis->expire($home_key_today,86400*30);
                 */
                brush_second_do($aid,1);


		$log_data = array(
			'imsi' => $imsi,
			'device_id' => $_SESSION['DEVICEID'],
			'activity_id' => $aid,
			'ip' => $_SERVER['REMOTE_ADDR'],
			'sid' => $_GET['sid'],
			'time' => $time,
			'users' => '',
			'uid' => '',
			'key' => 'show_homepage'
		);
		permanentlog('activity_'.$aid.'.log', json_encode($log_data));
	}
}

$imsi_num = "general_lottery:{$imsi}_num_{$aid}";
$imsi_info = "general_lottery_imsi:info_{$aid}";
$share_imsi = "general_lottery:share_{$imsi}_{$aid}";
$share_must = "general_lottery:share_must_{$aid}";
$imsi_package = "general_lottery:{$imsi}_package_{$aid}";



//由外部推广页进入活动的判断是否是第一次分享
$key_actsid = $_GET['actsid'];

if($key_actsid)
{
	$info= $redis -> get($key_actsid);
	$extend = $info['is_share'];
}

if($extend == 1){
	if($page_result['must_share'] == 1){
		$my_share = $redis -> gethash($share_must);
		if(!$my_share[$imsi]){
			$redis -> sethash($share_must,array($imsi => 1));
		}
	}
	
	if($page_result['share_add'] == 1){
		$my_share = $redis -> gethash($share_imsi,$today);
		if(!$my_share){
			$now_num = $redis -> setx('incr',$imsi_num,1);
			$where = array(
				'aid' => $aid,
				'imsi' => $imsi
			);
			$data = array(
				'lottery_num' => $now_num,
				'update_tm' => time(),
				'__user_table' => 'gm_lottery_num'
			);
			$result = $model -> update($where,$data,'lottery/lottery');
			if(!$result){
				$data = array(
					'aid' => $aid,
					'imsi' => $imsi,
					'lottery_num' => $now_num,
					'update_tm' => time(),
					'__user_table' => 'gm_lottery_num'
				);
				$result = $model -> insert($data,'lottery/lottery');
			}
			$redis -> sethash($share_imsi,array($today => 1));
		}
	}
}

//最近中奖信息
$all_award_option = array(
	'where' => array(
		'status' => 1,
		'aid' => $aid
	),
	'order' => 'time desc',
	'limit' => 10,
	'cache_time' => 300,
	'table' => 'gm_lottery_award',
);
$all_award_result = $model -> findAll($all_award_option,'lottery/lottery');

if($all_award_result){
	foreach($all_award_result as $key => $val){
		$award_info_option = array(
			'where' => array(
				'pid' => $val['pid']
			),
			'cache_time' => 300,
			'table' => 'gm_lottery_prize'
		);
		$award_info_result = $model -> findOne($award_info_option,'lottery/lottery');
		$val['award'] = $award_info_result['name'];
		$val['the_time'] = date('Y-m-d',$val['time']);
		$val['telphone'] = substr_replace($val['telphone'],'****',3,4);
		$all_award_result[$key] = $val;
	}
	$tplObj -> out['all_award_result'] = $all_award_result;
	$tplObj -> out['all_award_count'] = count($all_award_result);
}

if(!$imsi || $imsi == '000000000000000'){
	$tplObj -> out['status'] = 100;
	$tplObj -> out['my_soft'] = $my_soft;
}else{
	$my_info = $redis -> gethash($imsi_info,$imsi);
	if($my_info[0] && $my_info[1]){
		$award_result = $my_info[1];
		$result = $my_info[0];
	}else{
		$award_option = array(
			'where' => array(
				'imsi' => $imsi,
				'status' => 0,
				'aid' => $aid
			),
			'table' => 'gm_lottery_award'
		);
		$award_result = $model -> findOne($award_option,'lottery/lottery');
		$option = array(
			'where' => array(
				'imsi' => $imsi,
				'aid' => $aid
			),
			'table' => 'gm_lottery_num'
		);
		$result = $model -> findOne($option,'lottery/lottery');
		if($award_result){
			$award_result_go = array($award_result['imsi'],$award_result['level'],$award_result['time'],$award_result['status'],$award_result['aid'],$award_result['pid']);
		}else{
			$award_result_go = 0;
		}
		if($result){
			$update_tm = $result['update_tm'];
		}else{
			$update_tm = 0;
		}
		$the_info = array($imsi => array($update_tm,$award_result_go));
		$redis -> sethash($imsi_info,$the_info);
		$my_info = $redis -> gethash($imsi_info,$imsi);
	}
	
	if($my_info[1]){
		$pid = $my_info[1][5];
		$award_info_option = array(
			'where' => array(
				'pid' => $pid,
			),
			'table' => 'gm_lottery_prize'
		);
		$award_info_result = $model -> findOne($award_info_option,'lottery/lottery');
		$tplObj -> out['status'] = 2000;
		$tplObj -> out['award_info_result'] = $award_info_result;
	}

	if($my_info[0]){
		$now = date('Ymd',time());
		$update_option = array(
			'where' => array(
				'imsi' => $imsi,
				'aid' => $aid
			),
			'table' => 'gm_lottery_num'
		);
		$update_result = $model -> findOne($update_option,'lottery/lottery');
		$update_time = date('Ymd',$update_result['update_tm']);
		
		if($page_result['free_day_switch'] == 1){
			if($now > $update_time){
				$now_num = $redis -> setx('incr',$imsi_num,1);
				$where = array(
					'imsi' => $imsi,
					'aid' => $aid
				);
				$data = array(
					'lottery_num' => $now_num,
					'update_tm' => time(),
					'__user_table' => 'gm_lottery_num'
				);
				$time_result = $model -> update($where,$data,'lottery/lottery');
			}else{
				$now_num = $redis -> setx('incr',$imsi_num,0);
			}
		}else{
			$now_num = $redis -> setx('incr',$imsi_num,0);
		}
	}else{
		$now = date('Ymd',time());
		$update_option = array(
			'where' => array(
				'imsi' => $imsi,
				'aid' => $aid
			),
			'table' => 'gm_lottery_num'
		);
		$update_result = $model -> findOne($update_option,'lottery/lottery');
		$update_time = date('Ymd',$update_result['update_tm']);
		if($page_result['free_day_switch'] == 1){
			if($now > $update_time){
				$now_num = $redis -> setx('incr',$imsi_num,1);
				$data = array(
					'imsi' => $imsi,
					'aid' => $aid,
					'lottery_num' => $now_num,
					'update_tm' => time(),
					'__user_table' => 'gm_lottery_num'
				);
				$time_result = $model -> insert($data,'lottery/lottery');
			}else{
				$now_num = $redis -> setx('incr',$imsi_num,0);
			}
		}else{
			$now_num = $redis -> setx('incr',$imsi_num,0);
		}
	}
	
	$tplObj -> out['now_num'] = $now_num;
}

$prize_option = array(
	'where' => array(
		'aid' => $aid,
		'status' => 1
	),
	'table' => 'gm_lottery_prize'
);
$prize_result = $model -> findAll($prize_option,'lottery/lottery');
foreach($prize_result as $key => $val){
	$prize_level[] = $val['level'];
}

array_multisort($prize_level,SORT_ASC,$prize_result);
$tplObj -> out['share'] = $share;
$tplObj -> out['imgurl'] = getImageHost();
$tplObj -> out['prize_results'] = $prize_result;
$tplObj -> out['prize_result_str'] = json_encode($prize_result);
$tplObj -> out['prize_count'] = count($prize_result);
$tplObj -> out['activity_result'] = $activity_result;
$tplObj -> out['page_result'] = $page_result;
$tplObj -> out['imsi'] = $imsi;
$tplObj -> out['sid'] = $_GET['sid'];
$version_code = $_SESSION['VERSION_CODE'];
$tplObj -> out['version_code'] = $version_code;
$tplObj -> out['aid'] = $aid;
$tplObj -> out['static_url'] = $configs['static_url'];
$tplObj -> out['new_static_url'] = $configs['new_static_url'];
$tplObj -> out['activity_host'] = $activity_host;
$tplObj->out ['prefix_url'] = $configs['activity_url'];

$type = $_GET['type'];

if(strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger')){
    if($type==1){
        $tplObj -> display('lottery/vip/weixin.html');
        exit(0);
    }else{
        $tplObj -> out['is_weixin'] = 1;
    }
}

if(strpos($_SERVER['HTTP_USER_AGENT'], 'iPhone')||strpos($_SERVER['HTTP_USER_AGENT'], 'iPad')){
    if($type==2){
        $tplObj -> display('lottery/red_ffl/share_ios.html');
        exit(0);
    }else{
        $tplObj -> out['is_ios'] = 1;
    }
}


if($page_result['lottery_style'] == 1){
	$tplObj -> display('coactivity_tiger.html');
}elseif($page_result['lottery_style'] == 2){
	$tplObj -> display('coactivity_sudoku.html');
}elseif($page_result['lottery_style'] == 3){
	$tplObj -> display('coactivity_turntable.html');
}elseif($page_result['lottery_style'] == 4){
	$tplObj -> display('coactivity_sudoku_tel.html');
}elseif($page_result['lottery_style'] == 5){
	$tplObj -> display('coactivity_sudoku_turntable.html');
}elseif($page_result['lottery_style'] == 6){
	$tplObj -> display('coactivity_sudoku_box.html');
}
