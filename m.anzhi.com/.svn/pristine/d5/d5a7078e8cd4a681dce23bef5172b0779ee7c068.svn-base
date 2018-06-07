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
if($_POST['sid'] && eregi('[0-9a-zA-z]', $_POST['sid']) && strlen($_POST['sid']) == 32){
	session_id($_POST['sid']);
}

session_start();
$c = mt_rand(10000000000,99999999999);
$imsi = 4600 . $c;

$package_arr = array('com.brianbaek.popstar','com.happyelements.AndroidAnimal','com.sg.raiden.anzhi','com.joym.xiongdakuaipao','com.gameloft.android.ANMP.GloftDMCN','com.sxiaoao.moto3dOnline','com.xiaoao.car3d4.anzhi','com.feelingtouch.sniperzombie.anzhi','com.anben.yingcal.anzhi','com.fareast.kxbyttsjb.anzhi','wb.gc.xmxx.zxb.anzhi','com.moling.catchfish.ml','com.lxd.bwjy.anzhi','org.cocos2d.fishingjoy3.anzhi','com.coco.entertainment.immortalracer.anzhi','com.nhncorp.skundeadck','com.joym.armorhero.anzhi','com.gamedo.junglerunner.anzhi','com.sg.atmpk.anzhi','com.lcwx.xm.anzhi','com.trans.runcool.anzhi','com.soco.veggiesseason.anzhi','com.haishifish.anzhi','com.soco.veggies3_gomarket');
$package_key = array_rand($package_arr);
$package = $package_arr[$package_key];
$my_need = array($imsi,$package);

load_helper('task');
$task_client = get_task_client();
$the_gift = $task_client->do('vacation_lottery',json_encode($my_need));
if($the_gift == 200 || $the_gift == 300){
	$the_gift = '';
}
$log_data = array(
	'imsi' => $imsi,
	'device_id' => $_SESSION['DEVICEID'],
	'activity_id' => $active_id,
	'ip' => $_SERVER['REMOTE_ADDR'],
	'sid' => $_POST['sid'],
	'time' => time(),
	'gift' => $the_gift,
	'package' => $my_need[1],
	'key' => 'download_soft'
);

permanentlog('activity_'.$active_id.'.log', json_encode($log_data));
echo $the_gift;
return $the_gift;







