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
	//$post_str = "sign=d44b02c7f86959031c30069655b40b5d&time=1512525669584&partid=1000000000000109&data=53LzMQ0%2BMWbVzkDnr%2F2v3%2Fic3bQY2yBQwMIVW5%2BFZ9f%2BbJdN4Kr%2Bb4ObgAwWTjg9osJbzApSIhxI%2FWMElKq5%2FQxIEwl0CJTGCdU8NktTyA5x4cGm0fsN4BOYNncKRX%2BmfOuh3q4TRJrFwfaKCO04GrAmwcWiGHgzHs3bqEBBJ6saKFwRL0443OdprwZ5MPBIN1sNrcy77f42jlR4GWKdyBwT4rsP3%2BiJi35wGCTDUEE6UoWO4KXOzQS7r4VyY2vIgBhittH2w5j30zVTK7n4vsguIaQ1QY6i0Y%2B9lIryixs%3D";

	
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
	permanentlog('callback_data.log', json_encode($log));	
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
		$log_data['msg'] = "提取失败";
		permanentlog('activity_'.$active_id.'.log', json_encode($log_data));
		//失败后回滚
		$is_callback_key = $prefix.":".$active_id.":is_callback:".$orderid;
		$is_callback = $redis->setnx($is_callback_key,1);
		$redis->expire($is_callback_key,86400*2);	
		if(!$is_callback){
			//判断当前订单是否已经回滚过了如已经回滚不执行下面的操作
			return array('code'=>0,'msg'=>'提取失败');	
		}
		//所有用户的流量总数
		$use_num_key = $prefix.":".$active_id.":use_num:".$today;
		$redis->setx('incr',$use_num_key,-$price);
		//当月可提取的次数
		$mon_use_num_key = $prefix.":".$active_id.":mon_use_num:".$imsi;
		$redis->setx('incr',$mon_use_num_key,-1);
		//当月限可提取500M
		$mon_price_num_key = $prefix.":".$active_id.":mon_price_num:".$imsi;
		$redis->setx('incr',$mon_price_num_key,-($price));	
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