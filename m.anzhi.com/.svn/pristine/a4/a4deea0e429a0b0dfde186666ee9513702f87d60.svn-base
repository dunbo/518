<?php

	include_once (dirname(realpath(__FILE__)).'/init.php');
	$redis = new GoRedisCacheAdapter();
	$model = new GoModel();
	if($_GET['sid'] && eregi('[0-9a-zA-z]', $_GET['sid']) && strlen($_GET['sid']) == 32){
		session_id($_GET['sid']);
	}
	session_start();
	$status = $_GET['status'];
	if($status == 100 || $status == 200){
		$all_package = $redis -> get('all_package');
		$my_all_package = json_decode($all_package,true);
		$my_package = array_rand($my_all_package,8);

		foreach($my_all_package as $key => $val){
			if(in_array($key,$my_package)){
				$my_soft[] = $val;
			}
		}

		echo json_encode($my_soft);
		return json_encode($my_soft);
	}elseif($status == 300){
		//if($_SESSION['USER_IMSI']){
			$imsi = $_SESSION['USER_IMSI'];
		//}else{
		//	$imsi = 1;
		//}
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
				$my_softs[] = $val;
			}
		}
		echo json_encode($my_softs);
		return json_encode($my_softs);
	}