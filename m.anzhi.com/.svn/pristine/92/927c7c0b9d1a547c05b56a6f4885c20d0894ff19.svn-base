<?php
include_once (dirname(realpath(__FILE__)).'/../../init.php');
$model = new GoModel();
$active_id = $_GET['actionid'];

$tplObj -> out['aid'] = $active_id;
/*@
pageid 1个人中心2红包任务中心3红包任务详情4叠叠乐5翻翻乐
6九宫格7红包雨8签到抽奖9桌面红包10我的消息列表
11礼包列表12专题详情（评论）13软件详情（评论）14活动详情（限分享指定活动）15红包明细
16分享指定软件17叠叠乐叠加红包18邀请码任务19通知栏
*/

//key为pageid  val为actiontype
// $actiontype_arr = array(
	// 4 => 8,
	// 5 => 7,
	// 6 => 5,
	// 7 => 6,
// );
$pageid = $_GET['pageid'];
$tplObj -> out['pageid'] = $pageid;
//$tplObj -> out['actiontype'] = $actiontype_arr[$pageid];
$tplObj -> out['actiontype'] = $_GET['actiontype'];
if(in_array($pageid,array(4,5,6,7))){
	$option = array(
		'where' => array(
			'id' => $active_id,
		),
		'table' => 'sj_activity',
		'field' => 'status,name,start_tm,end_tm,red_start_tm,red_end_tm,activity_type,red_at_desc',
		'cache_time' => 10*60
	);
	$list = $model->findOne($option);
	$time = time();	
	if($list['status'] != 1){
		// 下线、审核
		$tplObj -> out['is_expired'] = 1;
	}else{
		$tplObj -> out['is_expired'] = 0;
	}	
}
$task_con = array(
	'T1' => '每日签到',
	'T2' => '金币精选软件',
	'T3' => '邀请安装安智市场',
	'T4' => '绑定手机号',
	'T5' => '金币分享软件',
	'T6' => '评论软件',
	'T7' => '领礼包',
	'T8' => '收藏软件',
	'T9' => '下载任意软件',
	'T10' => '升级安智市场',
	'T11' => '填写邀请码',
	'T12' => '回复大家说',
	'T13' => '评论专题',
	'T14' => '浏览合作资讯',
	'T15' => '分享指定软件',
	'T16' => '分享指定活动',
	'T41' => '签到抽奖安装并打开',
	'T42' => '签到抽奖安装并登录',
	'T43' => '签到抽奖安装并体验时长',
	'T50' => '普通软件第三方注册',
	'T51' => '普通软件安装并打开',
	'T52' => '普通软件安装并登录',
	'T53' => '普通软件安装并体验时长',
	'T55' => '普通软件安装并充值',
	'T61' => '成长任务安装并打开',
	'T63' => '成长任务安装并体验时长',
	'T71' => '红包活动安装并打开',
	'T72' => '红包活动安装并体验时长',
	'T73' => '红包活动安装并登录',
);
$tplObj -> out['tasktype'] = $task_con[$_GET['tasktype']];
$tplObj -> out['taskname'] = $_GET['taskname'];
$tplObj -> out['activityName'] = $_GET['actionname'];
$tplObj -> out['appname'] = $_GET['appname'];
$tplObj -> out['money'] = $_GET['money'];
$tplObj -> out['prizename'] = $_GET['prizename'];
$tplObj -> out['azinvitecode'] = $_GET['azinvitecode'];
$tplObj -> out['taskid'] = $_GET['taskid'];
$tplObj -> out['list_get'] = $_GET;
$tplObj -> out['azinviteusername'] = $_GET['azinviteusername'];//邀请人
if($configs['is_test'] == 1 ) {
	$tplObj -> out['user_pic'] = "http://dev.i.anzhi.com/api/obtain/album?pid=".$_GET['pid'];
}else{
	$tplObj -> out['user_pic'] = "http://image.anzhi.com/header?pid=".$_GET['pid'];
}
$tplObj -> out['username'] = $_GET['username'];
$tplObj -> out['static_url'] = $configs['static_url'];
$tplObj -> out['new_static_url'] = $configs['new_static_url'];
$tplObj -> out['activity_share_url'] = $configs['activity_share_url'];
if(strpos($_SERVER['HTTP_USER_AGENT'], 'iPhone')||strpos($_SERVER['HTTP_USER_AGENT'], 'iPad')){
	$tplObj -> out['is_ios'] = 1;
}
$tpl = "lottery/red_ffl/share.html";
$tplObj -> out['is_weixin'] = is_weixin();
$tplObj -> out['activity_host'] = $configs['activity_url'];
$tplObj -> display($tpl);