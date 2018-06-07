<?php
include_once ('./init.php');
session_begin();
$imei= $_SESSION['DEVICEID'];
$ret = $redis->get('azstore:sale:register:'.$imei);


function http_post_mobile($vals, $class='changty') {
	//global $action;
        $action ='sendSms';
	if(preg_match('/^192\.168\.0/i',$_SERVER['SERVER_ADDR']) || $_SERVER['SERVER_ADDR']=='10.0.3.15'|| $_SERVER['SERVER_ADDR']=='114.247.222.131') {
		$url = 'http://118.26.224.18/service.php.php?do='.$action.'&class='.$class;
		$host = 'Host: smsapi.goapk.com';
	} else {
		$url = 'http://192.168.1.18/service.php?do='.$action.'&class='.$class;
		$host = 'Host: smsapi.goapk.com';
	}

	$url .= '&key=46c6fe2c705f8fb48ed1e4b537ae4dd0&rand='.microtime(true);

	$res = curl_init();
	curl_setopt($res, CURLOPT_URL, $url);
	curl_setopt($res, CURLOPT_TIMEOUT, 15);
	curl_setopt($res, CURLOPT_HTTPHEADER, array($host,'Expect:'));
	curl_setopt($res, CURLOPT_POST, true);
	curl_setopt($res, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($res, CURLOPT_POSTFIELDS, $vals);
	$result = curl_exec($res);
	$http_code = curl_getinfo($res,CURLINFO_HTTP_CODE);
	$errno = curl_errno($res);
	$error = curl_error($res);
	curl_close($res);
	return array(
		'ret' => $result,
		'http_code' => $http_code,
	);
}
if($_POST){
    if(empty($imei)){
        echo 3;exit(0);
    }

    //用户名已存在
    $activity_option = array(
        'where' => array(
                'username' => $_POST['username'],
                'status' => 1
        ),
        'table' => 'store_user'
    );
    $res= $model -> findOne($activity_option,'lottery/lottery');
    if(!empty($res)){
        echo 2;exit(0);
    }

    $now_num = $redis -> get('azstore:tjryzmnum:imei:'.$imei.date('Ymd',time()));
    if($now_num>=10){
        echo 4;exit(0);
    }

    $redis -> setx('incr','azstore:tjryzmnum:imei:'.$imei.date('Ymd',time()),1);

    $redis->set('azstore:sale:register:'.$imei,$_POST);

    $re_rs = $redis->get('azstore:sale:register:'.$imei);
    if(empty($re_rs)){
        echo 3;exit(0);
    }

    //发验证码
    $vercode = rand(100000,999999);
                    $award_data = array(
                            'code' => $vercode,
                            'imei' => $imei,
                            'mobile' => $_POST['mobile'],
                            'tj_name' => $_POST['recommend'],
                            'create_tm' => time(),
                            'username' => $_POST['username'],
                            '__user_table' => 'store_recommend_vercode'
                    );
    $ret = $model -> insert($award_data,'lottery/lottery');

    $content = '卖家注册验证码'.$vercode.'，有效期30分钟';
    
    $send_arr = array('phone'=>$_POST['mobile'],'content'=>$content);
    http_post_mobile($send_arr);

    //发短信
    echo 1;exit(0);
}


$activity_option = array(
	'cache_time' => 1200,
	'table' => 'store_recommend'
);
$recommendlist = $model -> findAll($activity_option,'lottery/lottery');





$tplObj -> out['ret'] = $ret;
$tplObj -> out['recommendlist'] = $recommendlist;
$tpl = "lottery/store/register.html";
$tplObj -> out['static_url'] = $configs['static_url'];
$tplObj -> display($tpl);
