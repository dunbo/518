<?php

include_once (dirname(realpath(__FILE__)).'/init.php');
//6:验证成功;7:未获取电话或验证码;8:验证错误;9:更新数据错误;
$model = new GoModel();
$checkcode = $_GET['checkcode'];
$telphone = $_GET['telphone'];
$aid = $_GET['aid'];
$IMSI = $_GET['IMSI'];
$my_time = time() - 600;
if($checkcode && $telphone){
	$option = array(
		'where' => array(
			'telphone' => $telphone,
			'checkcode' => $checkcode,
			'create_tm' => array('exp',">{$my_time}"),
		),
		'table' => 'sj_noflux_activity'
	);
	$result = $model -> findOne($option);
	if($result){
		setCookie("NOFLUX_{$aid}",$aid,time()+3600*24*6);
		setCookie("TELPHONE_{$aid}",$telphone,time()+3600*24*6);
		setCookie("IMSI_{$aid}",$IMSI,time()+3600*24*6);

		$data = array(
			'update_tm' => time(),
			'status' => 1,
			'__user_table' => 'sj_noflux_activity'
		);
		$where = array(
			'telphone' => $telphone,
			'checkcode' => $checkcode
		);
		$my_result = $model -> update($where,$data);
		if($my_result){
			echo 6;
			return 6;
		}else{
			echo 9;
			return 9;
		}
	}else{
		echo 8;
		return 8;
	}
}else{
	echo 7;
	return 7;
}