<?php
	include_once ('./fun.php');
	include_once ('./aes.php');
	$aes = new AES();
	//设置密钥
	if( $configs['is_test'] ) {
		$secret_key = "FhbYDSZuzJiXpwy4";
	}else{
		$secret_key = "FXIpNWC3vz8DMMCZ";
	}	
	$aes->__set("key",$secret_key);	
	
	$orderid = $_GET['orderid'];
	$imsi = $_GET['imsi'];
	if($orderid){
		$return = array(
			'result' => 1
		);
	}else{
		$return = array(
			'result' => 0
		);
	}
	$json_str = json_encode($return);
	$post_str = file_get_contents("php://input");
	parse_str($post_str,$res);
	$res_str = $res['data'];
	$endata= $aes->decrypt($res_str);	
	$res_arr = json_decode($endata,true);	
	$price = intval($res_arr['denominationprice']);
	$log = array(
		'time' => $time,
		'imsi' => $imsi,
		'orderid' => $orderid,
		'post_res' =>$endata,
	);
	permanentlog('callback_data_'.$active_id.'.log', json_encode($log));	
	//用户提取流量日志
	$log_data = array(
		'time'	=>	$time,
		'imsi'	=>	$imsi,
		'sid' => $sid,	
		'orderid' => $orderid,	
		'device_id'	=>	$_SESSION['DEVICEID'],
		"DEVICE_SN" => $_SESSION['DEVICE_SN'],
		'activity_id'	=>	$active_id,
		'price'=>$price,//单位M
		'key'	=>	'use_flow'
	);	
	if($res_arr['result'] != 1){
		$grant_flow_key = $prefix.":".$active_id.":grant_flow:".$imsi;
		$redis->delete($grant_flow_key);			
		$where = array('orderid' => $orderid);
		$data = array(
			'status' => 2,
			'update_tm' => $time,
			'__user_table' => 'recharge_flow_bill',
		);
		$model->update($where, $data, 'lottery/lottery');
		//失败后回滚
		$is_callback_key = $prefix.":".$active_id.":is_callback:".$orderid;
		$is_callback = $redis->setnx($is_callback_key,1);
		$redis->expire($is_callback_key,86400*2);	
		if(!$is_callback){
			//判断当前订单是否已经回滚过了如已经回滚不执行下面的操作
			return array('code'=>0,'msg'=>'提取失败');	
		}
		$log_data['msg'] = "提取失败";
		permanentlog('activity_'.$active_id.'.log', json_encode($log_data));		
		return array('code'=>0,'msg'=>'提取失败');	
	}
	permanentlog('activity_'.$active_id.'.log', json_encode($log_data));
	
	$where = array('orderid' => $orderid);
	$data = array(
		'status' => 1,
		'update_tm' => $time,
		'__user_table' => 'recharge_flow_bill',
	);
	$model->update($where, $data, 'lottery/lottery');	

	echo $json_str;exit;