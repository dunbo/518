<?php
include 'init.php';

$is_over = $double->redis->get('double_color_ball:isover');

if(!$_POST){
	if($is_over==1){
		$build_query = http_build_query($_GET);
	    $a_url = $configs['activity_url']."lottery/{$prefix}/index.php?".$build_query;
		header('Location:'.$a_url.'');exit(0);
	}
}


if(!empty($a->uid))
{
	$res = get_azb($a->uid,$a->aid);
	$azb_data = json_decode($res['data'],true);
	$isHasPayPwd = $azb_data['isHasPayPwd'];
}
$a->tplObj -> out['isHasPayPwd'] = $isHasPayPwd;

if($_POST['type']==1)
{

	$is_over = $double->redis->get('double_color_ball:isover');

	if($is_over==1){
		echo 3;exit;
	}

	$orderarr = array();
	$numbers = explode(',', $_POST['nums']);

	if(empty($numbers)){
		exit(0);
	}
	$reds = explode(',', $_POST['reds']);
	$blues = explode(',', $_POST['blues']);
	$buynumber=0;
	foreach ($numbers as $key => $value) {
		$orderinfo = array();
		$tmp_arr = array();
		for ($i=0; $i<6 ; $i++) { 
			$tmp = array_shift($reds);
			array_push($tmp_arr,$tmp);
		}
			$tmp = array_shift($blues);
			array_push($tmp_arr,$tmp);

			$orderinfo['number']=$tmp_arr;
			$orderinfo['buynumber']=$value;
			$orderarr[] = $orderinfo;
			$buynumber +=$value;
	}

	$orderid = $double->add_order($a->uid,$a->puid,$orderarr,$buynumber);
	if($orderid!=false){
		//支付安智币
		$rs_xf = consume_azb($a->aid,$a->uid,$_POST['xf_pwd'],$buynumber*20,$double->issue,'',$orderid);
		//permanentlog('double_color_pay_return_'.$a->aid.'.log', json_encode($rs_xf));
		if($rs_xf['code']==200){

	        // $log_data = array(
	        //     "issue" => $double->issue,
	        //     "buynumber" => $buynumber,
	        //     "azb_num" => $buynumber*20,
	        //     "activity_id" => $a->aid,
	        //     'key' => 'consume_azb'
	        // );
	        // permanentlog('activity_'.$a->aid.'.log', json_encode($log_data)); 

	        $log_data = array(
	            "imsi"          =>  $_SESSION['USER_IMSI'],
	            "device_id"     =>  $_SESSION['DEVICEID'],
	            "activity_id"   =>  $a->aid,
	            "ip"            =>  $_SERVER['REMOTE_ADDR'],
	            "sid"           =>  $a->sid,
	            "time"          =>  time(),
	            "user"          =>  $_SESSION['USER_NAME'],
	            'azbAmount' => $buynumber*20,
	            "issue" => $double->issue,
	            "buynumber" => $buynumber,
	            'uid'           =>  $a->uid,
	            'key'           =>  'azb_consume_success',
	        );
	        permanentlog('activity_'.$a->aid.'.log', json_encode($log_data));


			$xf_rs = $double->update_pay_status($orderid,$a->puid,$buynumber*20);//修改订单状态

			if($xf_rs==true){
				echo 1;exit(0);
			}else{
				permanentlog('double_color_paysucc_dbfail_'.$a->aid.'.log', json_encode($rs_xf));
			}

		}else if($rs_xf['code']=='41017'){
			$jump_url = $u->get_cz_url();
			$rs_xf['jump_url']=$jump_url;
		}
		echo json_encode($rs_xf);exit(0);
	}

	echo 2;exit(0);
}
if($_GET['page']=='user')
{
	$tplObj->display("lottery/{$prefix}/buy_user.html");	
	exit(0);
}else{
	$rand_number_json = $_GET['rand_number_json'];
	$tplObj->out['rand_number_json']= $rand_number_json;
	$tplObj->out['is_has_rad']= !empty($rand_number_json)?1:2;
	$tplObj->display("lottery/{$prefix}/buy.html");	
}

