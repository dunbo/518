<?php

include_once (dirname(realpath(__FILE__)).'/../init.php');
$active_id = 187;
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
$fp = fopen("user.txt", 'r');
//$user_arr1 = explode("\r\n",$user_str);

$user_arr4 = array();
//foreach($user_arr1 as $key => $val){
while(!feof($fp)){
	$val = trim(fgets($fp));
	if (empty($val)) continue;
	$user_arr2 = explode(',',$val);
	if($user_arr2[1] && $user_arr2[1] != "NULL"){
		$user_arr4[md5($user_arr2[1])] = $user_arr2[0];
	}
	if($user_arr2[2] && $user_arr2[2] != "NULL"){
		$user_arr4[md5($user_arr2[2])] = $user_arr2[0];
	}
	if($user_arr2[3] && $user_arr2[3] != "NULL"){
		$user_arr4[md5($user_arr2[3])] = $user_arr2[0];
	}
}
fclose($fp);

$pice = 1000;
$info_count = 0;
$id2ids = array();
foreach ($user_arr4 as $idx => $val) {
	$id2ids[$idx] = $val;
	$info_count++;
	if($info_count % $pice == 0) {
		$redis->sethash("vacation_lottery_third:user_{$active_id}_T", $id2ids);
		$id2ids = array();
		echo $info_count, " vacation_lottery_third:user_{$active_id}_T\n";
	}
}
if (!empty($id2ids)) {
    $redis->sethash("vacation_lottery_third:user_{$active_id}_T", $id2ids);
}
$redis->setx('rename', "vacation_lottery_third:user_{$active_id}_T", "vacation_lottery_third:user_{$active_id}");
unset($id2ids);
exit;


//$redis -> delete("vacation_lottery_third:user_account_{$active_id}");exit;
//$redis -> sethash("vacation_lottery_third:user_account_{$active_id}",$have_lottert_user);
$redis -> delete("vacation_lottery_third:award_{$active_id}");
$redis -> sethash("vacation_lottery_third:award_{$active_id}",array(0,2,1,20,200,130006));
//$ffff = $redis -> gethash("vacation_lottery_third:award_{$active_id}");
//var_dump($ffff);exit;
//$have_lottery_user = $redis -> gethash("vacation_lottery_third:user_account_{$active_id}");
//VAR_DUMP($have_lottery_user);EXIT;
//$have_lottert_user = $redis -> delete("vacation_lottery_third:user_account_{$active_id}");
//$have_lottery_imsi = $redis -> delete("vacation_lottery_third:user_imsi_{$active_id}");
//$have_lottery_imei = $redis -> delete("vacation_lottery_third:user_imei_{$active_id}");exit;
//$my_user = array('qqqq@anzhi.com' => 1,'wwww@anzhi.com' => 1,'eeee@anzhi.com' => 1,'rrrr@anzhi.com' => 1,'tttt@anzhi.com' => 1,'yyyy@anzhi.com' => 1,'uuuu@anzhi.com' => 1,'iiii@anzhi.com' => 1,'oooo@anzhi.com' => 1,'pppp@anzhi.com' => 1,'aaaa@anzhi.com' => 1,'ssss@anzhi.com' => 1,'dddd@anzhi.com' => 1,'ffff@anzhi.com' => 1,'gggg@anzhi.com' => 1,'hhhh@anzhi.com' => 1,'jjjj@anzhi.com' => 1,'kkkk@anzhi.com' => 1,'llll@anzhi.com' => 1,'zzzz@anzhi.com' => 1,'xxxx@anzhi.com' => 1,'cccc@anzhi.com' => 1,'13269705879' => 1,'anzhitestt@163.com' => 1);
//$my_user_length = $redis -> setx('hlen',"vacation_lottery_third:user_{$active_id}");
//$redis -> delete("vacation_lottery_third:user_{$active_id}");exit;
//$redis -> sethash("vacation_lottery_third:user_{$active_id}",$my_user);
//$redis -> sethash("vacation_lottery_third:user_{$active_id}",array('ererd' => 3));
//$ccc = $redis -> gethash("vacation_lottery_third:user_{$active_id}");
//var_dump($ccc);
//$bbb = $redis -> gethash("vacation_lottery_third:user_{$active_id}","ererd");
//var_dump($bbb);
//exit;
//$aaa = $redis -> sethash("vacation_lottery_third:award_{$active_id}",array(0,2,1,20,200,50,50,100));
//exit;
$package_arr = array('com.zhangqu.game.tank3D.anzhi','com.crisisfire.android.anzhi','sh.lilith.dgame.anzhi','com.babletime.fknsango_anzhi','com.elextech.alert.anzhi','com.SmartSpace.TheSoulOfSwordFury.Android.anzhi','com.netease.ldxy.anzhi','com.heitao.mhj.anzhi','com.pokercity.mxddz.anzhi','net.crimoon.pm.anzhi','com.crisisfire.android.anzhi','com.youkia.death.anzhi');

$soft_arr = array('com.zhangqu.game.tank3D.anzhi' => '3D坦克世界','com.crisisfire.android.anzhi' => '全民枪战','sh.lilith.dgame.anzhi'=>'刀塔传奇','com.babletime.fknsango_anzhi'=>'放开那三国','com.elextech.alert.anzhi'=>'红警4 大国崛起','com.SmartSpace.TheSoulOfSwordFury.Android.anzhi'=>'剑魂之刃','com.netease.ldxy.anzhi'=>'乱斗西游','com.heitao.mhj.anzhi'=>'莽荒纪','com.pokercity.mxddz.anzhi'=>'明星斗地主','net.crimoon.pm.anzhi'=>'去吧皮卡丘','com.crisisfire.android.anzhi'=>'全民枪战','com.youkia.death.anzhi'=>'我是死神');
//$redis -> sethash("vacation_lottery_third:soft_{$active_id}",$soft_arr);
//$aa = $redis -> gethash("vacation_lottery_third:soft_{$active_id}");
//var_dump($aa);
foreach($package_arr as $key => $val){
	$option = array(
		'where' => array(
			'status' => 0,
			'package' => $val
		),
		'field' => 'gift_num',
		'table' => 'vacation_third_gift'
	);
	$result = $model -> findAll($option,'lottery/lottery');
	$vals = array();
	foreach($result as $k => $v){
		$vals[$val] = $v['gift_num'];
		$aa[] = $vals;
	}
	
}

$vacation_redis = "vacation_lottery_third:award_gift_{$active_id}";
$redis -> delete("vacation_lottery_third:award_gift_{$active_id}");
$redis -> setlist("vacation_lottery_third:award_gift_{$active_id}",$aa);
$ddd = $redis -> getlist("vacation_lottery_third:award_gift_{$active_id}");
var_dump($ddd);
//$aaa = $redis -> rpop("vacation_lottery_second:award_gift_forth__{$active_id}");
//var_dump($aaa);
//$bbb = $redis -> getlist("vacation_lottery_second:org.bxsd.dhxx_wan_{$active_id}");
//var_dump($bbb);
