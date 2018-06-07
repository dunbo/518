<?php
	
	//错误：1,正常;2,电话格式错误;3,数据错误;
	include_once (dirname(realpath(__FILE__)).'/init.php');
	$redis = new GoRedisCacheAdapter();
	$model = new GoModel();
	$telphone = $_GET['telphone'];
	if($_GET['sid'] && eregi('[0-9a-zA-z]', $_GET['sid']) && strlen($_GET['sid']) == 32){
		session_id($_GET['sid']);
	}
	session_start();
	//if($_SESSION['USER_IMSI']){
		$imsi = $_SESSION['USER_IMSI'];
	//}else{
	//	$imsi = 10;
	//}
	
	if(!is_numeric($telphone) || strlen($telphone) != 11){
		echo 2;
		return 2;
		exit;
	}else{
		$data = array(
			'imsi' => $imsi,
			'mobile' => $telphone,
			'guess_num' => 2,
			'create_tm' => time(),
			'update_tm' => time(),
			'__user_table' => 'cup_user'
		);
		$result = $model -> insert($data,'world_cup');
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
		
		if($result){
			echo 1;
			return 1;
		}
	
	}