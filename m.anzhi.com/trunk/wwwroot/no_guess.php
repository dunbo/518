<?php
	
	//无竞猜次数页面
	include_once (dirname(realpath(__FILE__)).'/init.php');
	$redis = new GoRedisCacheAdapter();
	$model = new GoModel();
	if($_GET['sid'] && eregi('[0-9a-zA-z]', $_GET['sid']) && strlen($_GET['sid']) == 32){
		session_id($_GET['sid']);
	}
	session_start();
	if($_SESSION['USER_IMSI']){
		$imsi = $_SESSION['USER_IMSI'];
	}else{
		$imsi = 4;
	}
	$my_packages = $redis -> get($imsi);
	$packages_arr = json_decode($my_packages,true);
	$package_arr = explode(',',$packages_arr['package']);
	$all_package = $redis -> get('all_package');
	$my_all_package = json_decode($all_package,true);
	foreach($my_all_package as $key => $val){
		if(!in_array($val['package'],$package_arr)){
			$self_all_package[] = $val;
		}
	}

	$myself_package = array_rand($self_all_package,8);

	foreach($self_all_package as $key => $val){
		if(in_array($key,$myself_package)){
			$my_soft[] = $val;
		}
	}
	$tplObj -> out['aid'] = $_GET['aid'];
	$tplObj -> out['sid'] = $_GET['sid'];
	$tplObj -> out['img_url'] = "http://apk.goapk.com/";
	$tplObj -> out['my_soft'] = $my_soft;
	$tplObj -> display('no_guess.html');