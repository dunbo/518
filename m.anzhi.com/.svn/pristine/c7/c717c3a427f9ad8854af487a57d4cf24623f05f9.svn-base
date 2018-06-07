<?php
include_once (dirname(realpath(__FILE__)).'/../init.php');
$config = load_config('lottery_cache/redis','lottery');
if ($config) {
	$redis = new GoRedisCacheAdapter($config);
} else {
	$redis = GoCache::getCacheAdapter('redis');
}
$model = new GoModel();
session_begin();
$aid = $_GET['aid'];
//ctype_digit  检查时候是只包含数字字符的字符串（0-9）
if(!ctype_digit($aid)){
	exit;
}
$version_code = $_SESSION['VERSION_CODE'];
if($aid)
{
	$key="routine_rebate_active_".$aid;
	//$redis -> delete($key);
	$result	= $redis -> get($key);
	if(!$result)
	{
		$option = array(
			'where' => array(
				'id' => $aid
			),
			'table' => 'sj_activity'
		);
		$active_result = $model -> findOne($option);
		if($active_result['end_tm'] < time())
		{
			$pid = $active_result['activity_end_id'];
		}
		else
		{
			$pid = $active_result['activity_page_id'];
		}
	}
}
elseif($_GET['pid'])
{
	$pid = $_GET['pid'];
}
if($active_result['activity_type'] == 7 || $pid)
{
	$page_option = array(
		'where' => array(
		'ap_id' => $pid
		),
	'table' => 'sj_activity_page'
	);
	$result = $model -> findOne($page_option);
	$result['name'] =$active_result['name'];
	$info_arr=array(
		'pid'=>$pid,   	                             //生成活动页面id
		'name'=>$result['name'],
		'ap_imgurl'=>$result['ap_imgurl'],           //活动头图
		'ap_type'=>$result['ap_type'],  	//类型1活动页面2获奖名单3活动预告4等待名单
		'activate_type'=>$result['activate_type'],                          //活动类型,1:普通活动,2:多软件活动3广告活动4通用活动5集赞活动7常规充值活动
		'ap_desc'=>$result['ap_desc'],   	         //活动说明
		'soft_bg'=>$result['soft_bg'],               //推荐游戏背景图
		'ap_notice'=>$result['ap_notice'],           //软件名称文字颜色	
		'winning_comment'=>$result['winning_comment'],     //打开按键文案
		'warning_bgcolor'=>$result['warning_bgcolor'],     //打开按键颜色
		'download_comment'=>$result['download_comment'],   //安装按键文案
		'bg_color'=>$result['bg_color'],   	               //安装按键颜色
		'button_comment'=>$result['button_comment'],       //下载按钮文案
		'download_bgcolor'=>$result['download_bgcolor'],   //下载按钮颜色
		'share_text'=>$result['share_text'],	           //换一换按键文案
		'share_bgcolor'=>$result['share_bgcolor'],	       //换一换按键颜色	
		'ap_imgurl_bg'=>$result['ap_imgurl_bg'],    	   //返利说明图
		'download_config'=>$result['download_config'],     //充值流程图
		'bottom_color'=>$result['bottom_color'],           //轮播图背景图片
		'share_other_pic'=>$result['share_other_pic'],     //换一换字颜色
		'lost_no_desc' =>$result['lost_no_desc'],          //下载字体颜色
		'lose_yes_desc' =>$result['lose_yes_desc'],        //安装字体颜色
		'last_lottery_desc'=>$result['last_lottery_desc'], // 打开字体颜色 
	);
	$save_info=$redis -> set('routine_rebate_active_'.$aid,$info_arr,3600);
	//$redis->expire('routine_rebate_active_'.$aid,3600);
}

$imsi = $_SESSION['USER_IMSI'];
$log_data = array(
	'imsi' => $imsi,
	'device_id' => $_SESSION['DEVICEID'],
	'activity_id' => $aid,
	'ip' => $_SERVER['REMOTE_ADDR'],
	'sid' => $_GET['sid'],
	'time' => time(),
	'users' => '',
	'uid' => '',
	'key' => 'show_homepage'
);
permanentlog('activity_'.$aid.'.log', json_encode($log_data));
$tplObj -> out['aid'] = $aid;
$tplObj -> out['sid'] = $_GET['sid'];

$tplObj -> out['result'] = $result;
$tplObj -> out['img_url'] = getImageHost();
$tplObj -> out['static_url'] = $configs['static_url'];
$tplObj -> out['version_code'] = $version_code;

$tplObj -> display("routine_rebate_index.html");
?>