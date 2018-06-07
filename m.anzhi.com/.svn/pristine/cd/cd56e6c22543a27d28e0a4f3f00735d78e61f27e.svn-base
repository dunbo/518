<?php
include_once ('./fun.php');

if($_POST){
	if($_POST['is_user'] == 1){
		$data = array(
			'uid' => $uid,
			'username' => $_SESSION['USER_NAME'],
			'phone' => $_POST['mobile_phone'],
			'contact_name' => $_POST['lxname'],
			'address' => $_POST['address'],
		);
		$ret = add_user($data);
		if($ret){
			//用户修改信息日志
			$log_data = array(
				"imsi" => $_SESSION['USER_IMSI'],
				"device_id" => $_SESSION['DEVICEID'],
				"mac" => $_SESSION['MAC'],				
				'uid'=>$uid,
				'sid' => $sid,	
				'username' => $_SESSION['USER_NAME'],
				'tel' => $_POST['mobile_phone'],
				'name' => $_POST['lxname'],
				'address' => $_POST['address'],
				'time' => $time,
				"mid"	=>	$m_arr['id'],
				'key' => 'info_edit'
			);
			permanentlog($log_key, json_encode($log_data));			
			exit(json_encode(array('code'=>1,'msg'=>'成功')));
		}else{
			exit(json_encode(array('code'=>0,'msg'=>'失败')));
		}
	}else if($_POST['is_czk_phone'] == 1){
		//充值卡填写手机号
		$czk_phone = $_POST['czk_phone'];
		$id = $_POST['id'];
		$ret = save_czk_phone($uid,$id,$_POST['is_expired'],$czk_phone);
		if($ret){
			//用户充值卡手机号日志
			$log_data = array(
				"id" => $id,
				"imsi" => $_SESSION['USER_IMSI'],
				"device_id" => $_SESSION['DEVICEID'],	
				"mac" => $_SESSION['MAC'],				
				'uid'=>$uid,
				'sid' => $sid,	
				'username' => $_SESSION['USER_NAME'],
				'tel' => $czk_phone,
				'time' => $time,
				"mid"	=>	$m_arr['id'],
				'key' => 'czk_info_edit'
			);
			permanentlog($log_key, json_encode($log_data));			
			exit(json_encode(array('code'=>1,'msg'=>'成功')));
		}else{
			exit(json_encode(array('code'=>0,'msg'=>'失败')));
		}		
	}
}else{
	$prize_list = get_my_prize($uid,$_GET['is_expired']);
	if($_GET['from'] == 1){
		$type = $_GET['type'];
		$id = $_GET['id'];
		if($type == 1){
			//实物奖用户信息
			$userinfo = get_user_info($uid);
			$userinfo['phone'] = $userinfo['phone'] ? substr_replace($userinfo['phone'],'****',3, -4) : '';  	
			$tplObj -> out['user_info'] = $userinfo;
			$prize_info = $prize_list[$id];
		}else if($type == 2 || $type == 3 || $type == 5){
			//充值卡、礼券、礼包直发
			$prize_info = $prize_list[$id];
			if($type == 3 || $type == 5){
				$ext = json_decode($prize_info['ext'],true);
				$prize_info['package'] = $ext['package'];
				$prize_info['instructions'] = $ext['instructions'];
				$prize_info['application'] = $ext['application'];
				$prize_info['remark'] = $ext['remark'];
				if($type == 3){
					$prize_info['money'] = ($ext['money']/100)."元";
				}else{
					$prize_info['gift_number'] = $ext['giftCode'];
				}
			}else{
				$prize_info['phone'] = $prize_info['phone'] ? substr_replace($prize_info['phone'],'****',3, -4) : '';  
			}
		}else if($type == 4){
			//礼包
			$prize_info = $prize_list[$id];
			$ext = json_decode($prize_info['ext'],true);
			$prize_info['package'] = $ext['package'];
			$prize_info['gift_number'] = $ext['giftCode'];
		}
		if($type == 4 || $type == 5){
			//礼包软件信息
			$tplObj -> out['img_url']  = getImageHost();
			$tplObj -> out['soft_info'] = get_soft_info($prize_info['package']);
		}else if($type == 1 || $type == 2){
			$tplObj -> out['is_pub'] = get_is_pub($id);
		}
		$tplObj -> out['prize_info'] = $prize_info;
		$tpl = "{$prefix}/award_info.html";
	}else{
		$tplObj -> out['prize_list'] = $prize_list;
		$tplObj -> out['img_url'] = getImageHost();
		//我的奖品日志
		$log_data = array(
			"imsi" => $_SESSION['USER_IMSI'],
			"device_id" => $_SESSION['DEVICEID'],
			"mac" => $_SESSION['MAC'],				
			'uid'=>$uid,
			'sid' => $sid,	
			'username' => $_SESSION['USER_NAME'],
			'time' => $time,
			"mid"	=>	$m_arr['id'],
			'key' => 'my_prize'
		);
		permanentlog($log_key, json_encode($log_data));			
		$tpl = "{$prefix}/prize_list.html";
	}
	// $array =  array(
		// 'code' => '200',
		// 'giftCode' => '29348823',
		// 'msg' => '成功',
		// 'package' => 'com.baidu.BaiduMap',
		// 'money' =>500,
		// 'expiry_date' => '2016-05-05~2018-01-01',
		// 'instructions' => '礼券使用说明',
		// 'application' => '礼券适用范围',
	// );
	// echo json_encode($array);
	$tplObj -> out['m_arr'] = $m_arr;
	$tplObj -> out['is_test'] = $configs['is_test'];
	$tplObj -> out['is_expired'] = $_GET['is_expired'];
	$tplObj -> out['sid'] = $sid;	
	$tplObj -> out['prefix'] = $prefix;	
	$tplObj -> out['static_url'] = $configs['static_url'];
	$tplObj -> out['new_static_url'] = $configs['new_static_url'];	
	$tplObj -> display($tpl);	
}