<?php

include_once (dirname(realpath(__FILE__)).'/../init.php');
$active_id = 185;
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
$a = array(array('0' => 1111),array('1' => 1111),array('2' => 1111),array('3' => 1111),array('4' => 1111),array('5' => 1111),);

$b = $redis -> getx('llen',"aaaaa");
var_dump($b);exit;


$imsi_gift_redis = "vacation_lottery:{$imsi}_gift_{$active_id}";
$imsi_have_package = "vacation_lottery:{$imsi}_have_{$active_id}";
$package_arr = array('com.brianbaek.popstar','com.happyelements.AndroidAnimal','com.sg.raiden.anzhi','com.joym.xiongdakuaipao','com.gameloft.android.ANMP.GloftDMCN','com.sxiaoao.moto3dOnline','com.xiaoao.car3d4.anzhi','com.feelingtouch.sniperzombie.anzhi','com.anben.yingcal.anzhi','com.fareast.kxbyttsjb.anzhi','wb.gc.xmxx.zxb.anzhi','com.moling.catchfish.ml','com.lxd.bwjy.anzhi','org.cocos2d.fishingjoy3.anzhi','com.coco.entertainment.immortalracer.anzhi','com.nhncorp.skundeadck','com.joym.armorhero.anzhi','com.gamedo.junglerunner.anzhi','com.sg.atmpk.anzhi','com.lcwx.xm.anzhi','com.trans.runcool.anzhi','com.soco.veggiesseason.anzhi','com.haishifish.anzhi','com.moling.buyu10002.anzhi');
$soft_arr = array('com.brianbaek.popstar' => 'PopStar!消灭星星官方正版','com.happyelements.AndroidAnimal' => '开心消消乐','com.sg.raiden.anzhi' => '雷电2014（雷霆版）','com.joym.xiongdakuaipao' => '熊出没之熊大快跑','com.gameloft.android.ANMP.GloftDMCN' => '神偷奶爸小黄人快跑','com.sxiaoao.moto3dOnline' => '3D暴力摩托','com.xiaoao.car3d4.anzhi' => '3D终极狂飙4','com.feelingtouch.sniperzombie.anzhi' => '僵尸前线3D','com.anben.yingcal.anzhi' => '天天爱闯关','com.fareast.kxbyttsjb.anzhi' => '开心捕鱼3(天天送金币)','wb.gc.xmxx.zxb.anzhi' => '消灭星星☆最新版','com.moling.catchfish.ml' => '街机千炮捕鱼','com.lxd.bwjy.anzhi' => '熊出没之保卫家园','org.cocos2d.fishingjoy3.anzhi' => '捕鱼达人3','com.coco.entertainment.immortalracer.anzhi' => '奔跑吧兄弟我是车神','com.nhncorp.skundeadck' => '亡灵杀手','com.joym.armorhero.anzhi' => '铠甲勇士之英雄传说','com.gamedo.junglerunner.anzhi' => '天天跑酷HD','com.sg.atmpk.anzhi' => '酷跑超人（奥特曼）','com.lcwx.xm.anzhi' => '熊出没之森林保卫战','com.trans.runcool.anzhi' => '开心酷跑','com.soco.veggiesseason.anzhi' => '燃烧的蔬菜季节版','com.haishifish.anzhi' => '天天街机捕鱼2','com.moling.buyu10002.anzhi' => '千炮捕鱼2');
$redis -> delete("vacation_lottery:soft_{$active_id}");
$redis -> sethash("vacation_lottery:soft_{$active_id}",$soft_arr);
//$aa = $redis -> gethash("vacation_lottery:soft_{$active_id}");

foreach($package_arr as $key => $val){
	$option = array(
		'where' => array(
			'status' => 0,
			'package' => $val
		),
		'field' => 'gift_num',
		'table' => 'vacation_gift_list'
	);
	$result = $model -> findAll($option,'lottery/lottery');
	$vacation_redis = "vacation_lottery:{$val}_{$active_id}";
	$redis -> delete($vacation_redis);
	$redis -> setlist($vacation_redis,$result);
	//$redis -> delete($vacation_redis);
}
//$aaa = $redis -> rpop("vacation_package:air.com.ghost.test.game.un_{$active_id}");
//echo $aaa;
//$bbb = $redis -> getlist("vacation_lottery:air.com.ghost.test.game.un_{$active_id}");
//var_dump($bbb);
//$ccc = $redis -> getlist("vacation_lottery:air.com.ghost.test.game.un_183");
