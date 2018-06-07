<?php
require_once(dirname(__FILE__).'/../init.php');
ini_set('displays_errors', true);
error_reporting(E_ALL);

load_helper('utiltool');
$_SERVER['HTTP_HOST'] = '518.anzhi.com';
$model = new GoModel();
$start = time();
$worker->addFunction('send_mail_log', 'send_mail_log_func');  
while ($worker->work());

function send_mail_log_func($job)
{
	global $model;
	$push_server = 'pushdb';
	$string = $job->workload();
 	if ( !($data = json_decode($string, true)) ) {
		return false;
	}
	$opts = array(
		'table' => 'pu_developer',
		'where' => array(
			'email_verified' => 1,
			'status' => 0,
			'type'=>$data['send_obj'],	
		),
		'field' => 'dev_id, dev_name, email'
	);
	$dever = $model->findAll($opts);
	//var_dump($data);
	//file_put_contents('aaa.txt',;);exit;
	foreach ($dever as $val) {
		if (!$val['dev_id'] || !$val['dev_name'] || !$val['email']) {
			continue;	
		}
		$a = 	realsend($val['email'], $val['dev_name'], $data['subject'], $data['msgs']);
		//var_dump($a);ifxus_close_slob
		if ($a) {
			permanentlog("dev_sendEmail.log", "dev_id:" . $val['dev_id'] ." "."email:".$val['email']." ". "msg:".$data['msgs']." " . date('Y-m-d H:i:s'));
		}
	}
}

/**
 * sendmail
 */
function _http_post_email($vals) {
	$url = 'http://124.243.198.92/service.php';
	//$url = 'http://118.26.203.22/service.php';
	$host = 'Host: mail.goapk.com';
	$url .= '?key=f3778b2d59c276233de4f73b2ebf46ea';
	$res = curl_init();
	curl_setopt($res, CURLOPT_URL, $url);
	curl_setopt($res, CURLOPT_TIMEOUT, 5);
	curl_setopt($res, CURLOPT_HTTPHEADER, array($host));
	curl_setopt($res, CURLOPT_POST, true);
	curl_setopt($res, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($res, CURLOPT_POSTFIELDS, $vals);
	$result = curl_exec($res);
	$http_code = curl_getinfo($res,CURLINFO_HTTP_CODE);
	curl_close($res);
	return array(
		'ret' => $result,
		'http_code' => $http_code,
	);
}
function realsend($email, $name, $subject, $message) {
	$data = array(
		'email'=>$email,
		'name'=>$name,
		'subject'=> $subject,
		'content'=>$message
	);
	//测试
	$is_test = true;
	if ($is_test) {
		$email_array = array('610655166@qq.com','467947645@qq.com','qingfeng130227@qq.com','158796378@qq.com','yuanming@anzhi.com','anzhi_test_1@163.com','527159802@qq.com','249024553@qq.com','atesta004@163.com',   'yuesaisai@anzhi.com');
		
		if(!in_array($email,$email_array)){
			return false;
		}
		//$data['interior_send'] = 1;
	}
	$tmp = _http_post_email($data);
	
	if($tmp['http_code']!=200) {
		return array(
			'error' => 5,
			'msg' => '发送失败!'
		);
	} else {
		$ret = json_decode($tmp['ret'],true);
		if($ret['code']<0) {
			return array(
				'error' => $ret['code'],
				'msg' => $ret['msg'],
			);
		}
	}
	return true;
}

