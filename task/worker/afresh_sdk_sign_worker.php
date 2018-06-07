<?php
/*
 *   联运游戏生成二次签名包worker
 */
include dirname(__FILE__).'/../init.php';
include_once(dirname(__FILE__).'/../../tools/functions.php');

$static_data = '/data/att/m.goapk.com';
$static_dir = '/data3';
$java_path = '/usr/bin/';

$bin_path = '/data/www/wwwroot/new-wwwroot/config/gnu';
if (!is_file("{$bin_path}/aapt")) {
    $bin_path = '/data/www/wwwroot/config/gnu';
}
$own_sign = '-736551223,412919359';

$model = new GoModel();
$gift_base = array();
ini_set('default_socket_timeout', -1);
//邮件配置
$email = array(
    'wuqiaojun@anzhi.com',
	'yuanming@anzhi.com'
);
$name = '安智';
$subject = '联运游戏网游二次签名';

load_helper('task');
$worker = get_task_worker();
$worker->addFunction("afresh_sdk_sign", "make_afresh_sdk");
while ($worker->work());

function make_afresh_sdk($jobs){    

    global $java_path;
    global $static_data;
    global $static_dir;

    $jobs = $jobs->workload();
    $jobs = json_decode($jobs,true); 
	$sid = rand_code_md5();
	write_log(date('Y-m-d H:i:s',time()).json_encode($jobs), $sid);
	
    $url = $static_data.$jobs['url'];
    	
	$sign_url = '/data/att/m.goapk.com/data3/sdk_game_sign/anzhi.keystore';
	$sign_pwd = 'anzhi@)!$';
	$alias_name = 'newkey.keystore';

    $date = date('Y-m-d');
	$rand_path = "/tmp/afresh_sdk_sign/{$date}/". $sid;
	mkdir($rand_path, 0777, true);
	mkdir($rand_path.'/assets/',0777,true); 
	
    $apk_tmp = $rand_path.'/'.$jobs['apk_name'].'.apk.zip';
    $msg = "step1start cp {$url} {$apk_tmp}";
    write_log($msg, $sid);
    go_shell_exec("cp {$url} {$apk_tmp}");
	
	$msg = "step1end-step2start "."zip -d {$apk_tmp} META-INF/*";
	write_log($msg, $sid);	
    go_shell_exec("zip -d {$apk_tmp} META-INF/*");	
    	
    $apk_name = $rand_path.'/'.$jobs['id'].'.apk.zip';
	
	$cmd = "{$java_path}jarsigner -storepass '{$sign_pwd}' -verbose -keystore {$sign_url} -signedjar {$apk_name} {$apk_tmp} {$alias_name}";
	$msg = "step2end-step3start ".$cmd;
    write_log($msg, $sid);                     
	
	$rc = go_shell_exec($cmd);
	//unlink($apk_tmp);
	$msg = "step3end-setp4start";
	write_log($msg, $sid);
	
	if ($rc != 0 || !file_exists($apk_name) ) {
	    	write_log('正式包生成失败'."${cmd} could not be done.", $sid);			
	}else{
	    $jobs['url'] = $apk_name;
	   
	    update_sdk($jobs, $sid);
	}    
}

function update_sdk($data, $sid){
    global $model;    
    global $static_data;
    global $static_dir;   
	global $own_sign;
	global $email;
	global $name;
	global $subject;
    $dir_apk = $static_data.$static_dir.'/apk/'.date('Ym/d').'/';
	$msg = "step4end-setp5start ".$dir_apk;
	write_log($msg, $sid);
	
    if(!is_dir($dir_apk)) {
        mkdir($dir_apk,0777,true);
		chmod($dir_apk,0777);
    }
	$msg = "step5end-setp6start";
	write_log($msg, $sid);
	
    $apk_name = $dir_apk.rand_code_md5().'.apk';    
    
    
    $msg = "step7end-setp8start cp {$data['url']} {$apk_name}";
	write_log($msg, $sid);
    $cmd = "cp {$data['url']} {$apk_name}";
    go_shell_exec($cmd);
    //unlink($data['url']);
    
	$msg = "step8end-setp9start";
	write_log($msg, $sid);
    $md5_file = md5_file($apk_name);
    $filesize = filesize($apk_name);
    $sign = getSignFromApk($apk_name);//获得签名
    
    $apk_name = str_replace($static_data,'',$apk_name);

	if($apk_name){
		//将旧信息插入sj_soft_file_tmp_old表
		$where = array( 'id' => $data['id']); 
		$old_option = array(
			'table' => 'sj_soft_file_tmp',
			'where' => $where
		);
		$old_info = $model->findOne($old_option);
		$insert_old = array(			
			'tmp_id' => $old_info['tmp_id'],
			'file_tmp_id' => $old_info['id'],
			'apk_name' => $old_info['apk_name'],
			'url' => $old_info['url'],
			'filesize' => $old_info['filesize'],
			'sign' => $old_info['sign'],
			'md5_file' => $old_info['md5_file'],
			'add_tm' => time(),
			'__user_table' => 'sj_soft_file_tmp_old',
		);
		$model->insert($insert_old);
		$msg = "step9end-setp10start".$model->getSql();
		write_log($msg, $sid);
		
		//更新新的url
		$option = array(
				'url' => $apk_name,
				'md5_file' => $md5_file,
				'filesize' => $filesize, 
				'sign' => $sign,   
				'last_refresh' => time(),
				'__user_table' => 'sj_soft_file_tmp'
		);
		$model -> update($where,$option);
		
		$msg = "step10end-setp11start".$model->getSql();
		write_log($msg, $sid);
		
		$where = array('package' => $data['apk_name']);
		if($sign==$own_sign){
			$where = array('package'=> $data['apk_name']); 
			$option = array(
				'status' => 1,
				'sign' => $sign,
				'__user_table' => 'sj_sdk_sign'
			);
			$model -> update($where,$option);
			$msg = "step11end-setp12start".$model->getSql();
			write_log($msg, $sid);
		}else{
			$message = "包名为{$data['apk_name']}，tmp_id为{$old_info['tmp_id']},file_tmp_id为{$data['id']}的游戏二次签名失败";
			foreach ($email as $user) {
				realsend($user, $name, $subject, $message);
			}
		}
		
	}
    
}



function rand_code_md5() {
    return md5(rand(1, 100000) . microtime().uniqid());
}

function go_shell_exec($cmd) {
    $ret = shell_exec("${cmd}; echo $?");
    if ($ret != 0)
        file_put_contents('/tmp/go_shell_exec.err', "${cmd}\n", FILE_APPEND);
    return intval($ret);
}

function write_log($msg, $sid){
	$log = "/data/att/permanent_log/afresh_sdk_sign_log/".date('Y-m-d').'/';
	if(!is_dir($log)) {
		mkdir($log,0777,true) ;
	}
	$t = date('[Y-m-d H:i:s]');
file_put_contents($log.'afresh_sdk_sign.log', "{$t} {$sid}: {$msg}\n", FILE_APPEND);
}

//发送邮件
function _http_post_email($vals) {
		$url = 'http://192.168.1.143/service.php';
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
	//调用发邮件接口

	$data = array(
		'email'=>$email,
		'name'=>$name,
		'subject'=> $subject,
		'content'=>$message
	);
	$tmp = _http_post_email($data);	
	if($tmp['http_code']!=200) {
		return array(
			'error' => 5,
			'msg' => '和邮件服务器通讯失败！',
		);
	} else {
		$ret = json_decode($tmp['ret'],true);
		if($ret['code']<0) {//进入发送队列失败
			return array(
				'error' => $ret['code'],
				'msg' => $ret['msg'],
			);
		}
		$tm  = date("Y-m-d",time());
		$content = "name:{$name} email:{$email} mag:{$message} {$tm} ";
		write_log($content);
	}
	return true;
}