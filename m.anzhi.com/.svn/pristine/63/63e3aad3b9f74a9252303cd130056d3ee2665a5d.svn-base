<?php
include_once (dirname(realpath(__FILE__)).'/../../init.php');
$config = load_config('lottery_cache/redis',"lottery");
if ($config) {
	$redis = new GoRedisCacheAdapter($config);
} else {
	$redis = GoCache::getCacheAdapter('redis');
}
$model = new GoModel();
$aid = $active_id = $_GET['aid'];
$option = array(
	'where' => array(
		'id' => $active_id,
	),
	'table' => 'sj_activity',
	'field' => 'status,name,start_tm,end_tm,red_start_tm,red_end_tm,activity_type,red_at_desc,imgurl',
	'cache_time' => 10*60
);
$activity = $model->findOne($option);
$time = time();	
if($activity['start_tm'] > $time){
	//echo '未开始';
	$status = 0;
}else if($activity['end_tm'] <= $time){
	//echo '结束';
	$status = 2;
}else{
	$day = date("Y-m-d");
	$red_start_tm = strtotime($day." ".$activity['red_start_tm']);	
	$red_end_tm = strtotime($day." ".$activity['red_end_tm']);
	if($red_start_tm && $red_start_tm > $time){
	//	echo '未开始';
		$status = 0;
	}else if($red_end_tm && $red_end_tm <= $time){
		//echo '结束';
		$status = 2;		
	}else{
		//echo '进行中';
		$status = 1;
	}
	$tplObj -> out['end_tm'] = $day." ".$activity['red_end_tm'];
}
//未领取列表
if(isset($_SESSION['USER_UID']) && $_SESSION['USER_ID'] != 13176){
		$uid = $_SESSION['USER_UID'];
		$key = "red_package:".$aid.":uid:".$uid.":red_package_list";//未领取红包列表
		$temp_list = $redis->gethash($key);

		foreach($temp_list as $k=>$v){
			if(time()-$v['time']>=3600){
				$redis->hdel($key,$k);
			}
		}
		$nogetlist = $redis->gethash($key);
		krsort($nogetlist);
		$tplObj -> out['nogetlist'] = $nogetlist;
		if(!empty($nogetlist)){
			$tplObj -> out['noget'] = 1;
		}
}

$tplObj -> out['aid'] = $active_id;

$activity['red_at_desc'] = str_replace(array("\r\n", "\r", "\n"), "", htmlspecialchars_decode($activity['red_at_desc']));
$activity['red_at_desc'] = str_replace("'",'"',$activity['red_at_desc']);

switch ($activity['activity_type']){
    case 5:
        $title='九宫格抽奖';
        break;
    case 6:
        $title='天降红包雨';
        break;
    case 7:
        $title='红包翻翻乐';
        break;
    case 8:
        $title='红包叠叠乐';
        break;
}
$tplObj -> out['is_share'] = $_GET['is_share'];
$tplObj -> out['sid'] = $_GET['sid'];
$tplObj -> out['title'] = $title;
$tplObj -> out['list'] = $activity;
$tplObj -> out['status'] = $status;
$tplObj -> out['static_url'] = $configs['static_url'];
$tplObj -> out['new_static_url'] = $configs['new_static_url'];
$tplObj -> out['activity_share_url'] = $configs['activity_share_url'];
$tplObj -> out['img_url']  = getImageHost();
$tpl = "lottery/red_ffl/currency.html";
$tplObj -> display($tpl);
