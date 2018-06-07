<?php

//VC=市场版本号&SDK_INT=系统版本号&DEVICE_ID=deviceid&MODEL=机型&ROM=rom版本&SessionID=SessionID
include_once (dirname(realpath(__FILE__)).'/init.php');


$id_str = $_POST['my_check'];
$id_arr = explode(',',$id_str);
foreach($id_arr as $key => $val){
	if($val){
		$id[] = $val;
	}
}

$pid = isset($_POST['pid']) ? $_POST['pid'] : 1;
$SID = $_POST['SID'];
$VC = $_POST['VC'];
$SDK_INT = $_POST['SDK_INT'];
$DEVICE_ID = $_POST['DEVICE_ID'];
$MODEL = $_POST['MODEL'];
$reason = $_POST['reason'];

//新版卸载理由修改了顺序
if ($pid == 1) {
	$reason_list = array(
		1 => "我想下某个软件结果下载了安智市场",
		2 => "手机出问题，卸载了重装试试",
		3 => "软件不全，好多软件找不到",
		4 => "通知栏推送太频繁，内容不感兴趣",
		5 => "有类似的软件市场了",
		6 => "下载的软件无法使用",
		7 => "下载太慢啦",
		8 => "软件更新太慢啦",
		9 => "用起来太卡了",
		10 => "其他",
	);	
}elseif($pid == 12){
	$reason_list = array(
		1 => "游戏不好玩",
		2 => "不知道如何使用",
		3 => "找不到一起玩的小伙伴",
		4 => "太费流量",
		5 => "内存占用太大，用起来太卡了",
	);	
}

foreach($id as $key => $val){
	if($reason){
		$the_id = 7;
		$my_reason = $reason;
		$my_select[$the_id] = $my_reason;
	} else {
		$the_id = $val;
		$my_reason = $reason_list[$the_id];
		$my_select[$the_id] = $my_reason;		
	}	
}

if (empty($SID) || empty($my_select)) exit("缺少参数");
$ip = onlineip();
$log = array(
	'KEY'=>'market_unintall',
	'TIME'=>time(),
	'REASON'=>$my_select,
	'IP'=>$ip,
	'SID' => $SID,
	'VC' => $VC,
	'SDK_INT' => $SDK_INT,
	'DEVICE_ID' => $DEVICE_ID,
	'MODEL' => $MODEL,
	'pid' => $pid,
);

permanentlog("market_unintall.log",json_encode($log));

if (in_array(2,$id)) {
	$fromjs = 1;
}else{
	$fromjs = 2;
}

echo $fromjs;

exit;