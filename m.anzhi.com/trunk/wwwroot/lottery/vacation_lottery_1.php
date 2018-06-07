<?php

include_once (dirname(realpath(__FILE__)).'/../init.php');
$active_id = 186;
$config = load_config('lottery_cache/redis',"lottery");
if ($config) {
	$redis = new GoRedisCacheAdapter($config);
} else {
	$redis = GoCache::getCacheAdapter('redis');
}
$model = new GoModel();
if($_GET['sid'] && eregi('[0-9a-zA-z]', $_GET['sid']) && strlen($_GET['sid']) == 32){
	session_id($_GET['sid']);
}
session_start();
if($_SESSION['USER_IMSI']){
	$imsi = $_SESSION['USER_IMSI'];
}
$redis -> delete("vacation_lottery_second:award_{$active_id}");
$aaa = $redis -> sethash("vacation_lottery_second:award_{$active_id}",array(0,1,1,30,30,50,50,100));
$package_arr_1 = array('com.elextech.alert.anzhi','com.pokercity.mxddz.anzhi','net.crimoon.sgz15.anzhi','com.yinhan.shenmo.anzhi','com.youkia.death.anzhi','com.cyou.cx.mtlbb.anzhi');
$package_arr_2 = array('sh.lilith.dgame.anzhi','com.babletime.fknsango_anzhi','com.tianmashikong.qmqj.anzhi','com.cyou.cx.mtlbb.anzhi','com.netease.TXHD.anzhi');
$package_arr_3 = array('com.ahzs.anzhi','com.youzu.nslm.anzhi','net.crimoon.pm.anzhi','com.camelgames.fantasyland_cn','com.lk.yxzj.anzhi','com.tianmashikong.qmqj.anzhi');
$package_arr_4 = array('com.heitao.mhj.anzhi','com.wanmei.mini.condor.anzhi','com.yinhan.hunter.anzhi','com.snailgame.panda.anzhi','com.ztgame.ztmobiletest.anzhi','com.pokercity.mxddz.anzhi');
$soft_arr = array('com.yinhan.hunter.anzhi' => '时空猎人','sh.lilith.dgame.anzhi' => '刀塔传奇','com.cyou.cx.mtlbb.anzhi'=>'天龙八部3D','com.netease.ldxy.anzhi'=>'乱斗西游','com.supercell.boombeach.anzhi'=>'海岛奇兵','com.babletime.fknsango_anzhi'=>'放开那三国','com.elextech.alert.anzhi'=>'红警4 大国崛起','net.crimoon.pm.anzhi'=>'去吧皮卡丘','com.ztgame.ztmobiletest.anzhi'=>'征途','com.youkia.death.anzhi'=>'我是死神','com.tianmashikong.qmqj.anzhi'=>'全民奇迹','com.yinhan.shenmo.anzhi'=>'神魔','com.heitao.mhj.anzhi'=>'莽荒纪','net.crimoon.sgz15.anzhi'=>'三国志15','com.snailgame.panda.anzhi'=>'太极熊猫','com.camelgames.fantasyland_cn'=>'小小帝国','com.youzu.nslm.anzhi'=>'女神联盟','com.wanmei.mini.dod.anzhi'=>'暗黑黎明','com.wanmei.mini.condor.anzhi'=>'神雕侠侣','cc.thedream.qinsmoon.anzhi'=>'秦时明月','com.lk.yxzj.anzhi'=>'英雄之剑','com.ahzs.anzhi'=>'暗黑战神','com.zhangqu.game.football.anzhi'=>'热血足球经理','com.netease.TXHD.anzhi'=>'天下HD','com.pokercity.mxddz.anzhi' => '单机斗地主(明星版)','com.tianmashikong.qmqj.anzhi' => '全民奇迹','com.cyou.cx.mtlbb.anzhi' => '天龙八部3D');
$redis -> delete("vacation_lottery_second:soft_{$active_id}");
$redis -> sethash("vacation_lottery_second:soft_{$active_id}",$soft_arr);
$aa = $redis -> gethash("vacation_lottery_second:soft_{$active_id}");

foreach($package_arr_1 as $key => $val){
	$option = array(
		'where' => array(
			'status' => 0,
			'package' => $val
		),
		'field' => 'gift_num',
		'table' => 'vacation_lottery_gift'
	);
	$result = $model -> findAll($option,'lottery/lottery');
	$vals = array();
	foreach($result as $k => $v){
		$vals[$val] = $v['gift_num'];
		$aa1[] = $vals;
	}
}

$vacation_redis1 = "vacation_lottery_second:award_gift_first_{$active_id}";
$redis -> delete($vacation_redis1);
$redis -> setlist($vacation_redis1,$aa1);

foreach($package_arr_2 as $key => $val){
	$option = array(
		'where' => array(
			'status' => 0,
			'package' => $val
		),
		'field' => 'gift_num',
		'table' => 'vacation_lottery_gift'
	);
	$result = $model -> findAll($option,'lottery/lottery');
	$vals = array();
	foreach($result as $k => $v){
		$vals[$val] = $v['gift_num'];
		$aa2[] = $vals;
	}
}

$vacation_redis2 = "vacation_lottery_second:award_gift_second_{$active_id}";
$redis -> delete($vacation_redis2);
$redis -> setlist($vacation_redis2,$aa2);

foreach($package_arr_3 as $key => $val){
	$option = array(
		'where' => array(
			'status' => 0,
			'package' => $val
		),
		'field' => 'gift_num',
		'table' => 'vacation_lottery_gift'
	);
	$result = $model -> findAll($option,'lottery/lottery');
	$vals = array();
	foreach($result as $k => $v){
		$vals[$val] = $v['gift_num'];
		$aa3[] = $vals;
	}
}

$vacation_redis3 = "vacation_lottery_second:award_gift_third_{$active_id}";
$redis -> delete($vacation_redis3);
$redis -> setlist($vacation_redis3,$aa3);

foreach($package_arr_4 as $key => $val){
	$option = array(
		'where' => array(
			'status' => 0,
			'package' => $val
		),
		'field' => 'gift_num',
		'table' => 'vacation_lottery_gift'
	);
	$result = $model -> findAll($option,'lottery/lottery');
	$vals = array();
	foreach($result as $k => $v){
		$vals[$val] = $v['gift_num'];
		$aa4[] = $vals;
	}
}

$vacation_redis4 = "vacation_lottery_second:award_gift_forth_{$active_id}";
$redis -> delete($vacation_redis4);
$redis -> setlist($vacation_redis4,$aa4);
