<?php

//刷sdk版本并检查是否是最新版sdk
include dirname(__FILE__).'/../init.php';


ini_set('display_errors', true);
error_reporting(E_ALL);
define('IMG_PATH', '/data/att/m.goapk.com');

//$time = time()-1800;

load_helper('task');
$worker = get_task_worker();
$worker->addFunction("sdk_version", "get_sdk_version");
while ($worker->work());

function get_sdk_version($jobs){
	sleep(5);
	//邮件配置
	$email = 'linhongqing@anzhi.com';
	$notice_email = array(
		'yuanming@anzhi.com',
		'linhongqing@anzhi.com',
		'wuqiaojun@anzhi.com'
	);
	$name = '安智';
	$subject = 'sdk版本检测异常';
	$jobs = $jobs->workload();
    $jobs = json_decode($jobs,true); 
	writelog('sdk_version_worker_log.log',date('Y-m-d H:i:s',time()).json_encode($jobs)."\n\n");
	$tmp_id = $jobs['id'];
	$model = new GoModel();
	//sdk最新版本
	$sdk_version = $model->findOne(array('table'=>'pu_config','where'=>array('status'=>1,'config_type'=>"sdk_version"),'field'=>'configcontent'));

	//sdk测试状态软件
	$option = array(
			'table'=>'sj_soft_tmp as a',
			'join'=>array('sj_soft_file_tmp as b'=>array('on'=>array('a.id','b.tmp_id'),'join_type'=>'left'),'sj_soft_whitelist as c'=>array('on'=>array('a.package','c.package'),'join_type'=>'right')),
			'where'=>array('a.id'=>$tmp_id,'b.package_status'=>1),
			'field'=>'a.id,a.package,b.url,c.email_num,c.email_tm'
		);
	$sdk_list = $model->findAll($option, 'master');//查询主库，防止数据同步延迟
	writelog('sdk_version_worker_log.log',date('Y-m-d H:i:s',time()).$model->getSql()."\n\n".print_r($sdk_list,true)."\n\n");
	$is_email  =  array();
	$today = strtotime(date('Ymd'));
	$notice_msg = '';
	if($sdk_list){
		foreach ($sdk_list as $k => $v) {
			$sdk_ver = check_sdk_ver(IMG_PATH . $v['url']);		
			$sdk_vers = implode(',', $sdk_ver);
			if(!$sdk_vers){
				$notice_msg .= "tmp_id为{$v['id']}的版本号为[{$sdk_vers}];";
			}
			writelog('sdk_version_worker_log.log',date('Y-m-d H:i:s',time()).$sdk_vers."\n\n");
			if (count($sdk_ver) == 1) {
				if ($sdk_ver[0] != $sdk_version['configcontent']) {
					if(empty($v['email_tm'])||($v['email_tm']==$today&&$v['email_num']<2)){
						//发邮件
						$is_email[$v['package']]['sdk_version'] = $sdk_vers;
						$is_email[$v['package']]['tmp_id'] = $v['id'];
						$is_email[$v['package']]['email_tm'] = $v['email_tm'];
					}
				}
			} else {
				if(empty($v['email_tm'])||($v['email_tm']==$today&&$v['email_num']<2)){
					//发邮件
					$is_email[$v['package']]['sdk_version'] = $sdk_vers;
					$is_email[$v['package']]['tmp_id'] = $v['id'];
					$is_email[$v['package']]['email_tm'] = $v['email_tm'];
				}
			}
			if (count($sdk_ver) != 0) {
				$where = array('id' => $v['id']);
				$data = array(
					'__user_table' => 'sj_soft_tmp',
					'sdk_version' => $sdk_vers
				);
				$res = $model->update($where, $data);
			}
		}
		$email_content = '';
		foreach ($is_email as $e_k => $e_v) {
			if ($e_v['sdk_version'] == '')
				$e_v['sdk_version'] = "空";
			$email_content .= 'tmp_id为' . $e_v['tmp_id'] . '包名为' . $e_k . 'sdk版本为' . $e_v['sdk_version'] . '<br>';
		}

		$message = $email_content;
		writelog('sdk_version_worker_log.log',date('Y-m-d H:i:s',time()).$message."\n\n");
		if($message!=''){
			$email = realsend($email, $name, $subject, $message);
			writelog('sdk_version_worker_log.log',date('Y-m-d H:i:s',time()).print_r($email,true)."\n\n");
			if($email){
				foreach($is_email as $i_k=>$i_v){
					$where = array('package' => $i_k);
					
					$data = array(
						'__user_table' => 'sj_soft_whitelist',
						'email_tm' => $today
					);
					if($i_v['email_tm']==$today){
						$data['email_num'] = array('exp','email_num + 1');
					}else{
						$data['email_num'] = 0;
					}
					$res = $model->update($where, $data);
				}
			}
		}
		if($notice_msg != ''){
			foreach($notice_email as $user){
				realsend($user, $name, $subject, $notice_msg);
			}	
		}
		
	}

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
		$content = "name:{$name} email:{$email} mag:{$message} {tm} ";
		writelog("{$tm}_sdkversion_sendEmail.log",$content);
	}
	return true;
}

function writelog($filename,$msg){
	$now = time();
	$path = "/data/att/permanent_log/admin_cron_log/".date("Y-m-d", $now);
	if(!file_exists($path)){
		mkdir($path, 0755, true);
	}	
	$path_log = $path."/".$filename;
	$msg = date('Y-m-d H:i:s', $now). " {$msg}\n";
	file_put_contents($path_log, $msg, FILE_APPEND);
}

function check_sdk_ver($file) {
	$r = array();
	$cmd = "/data/www/wwwroot/config/gnu/aapt  d xmltree ".$file." AndroidManifest.xml|grep ANZHIUSERCENTE_VERSIONS -A1|grep -v ANZHIUSERCENTE_VERSIONS";
	$ver = trim(shell_exec($cmd));
	if(empty($ver)){
		$cmd = "/data/www/wwwroot/config/gnu/aapt  d xmltree ".$file." AndroidManifest.xml|grep ANZHI_SINGLE_SDK_VERSIONS -A1|grep -v ANZHI_SINGLE_SDK_VERSIONS";
		$ver = trim(shell_exec($cmd));
	}
	if(preg_match('/Raw: "([^"]+)"/',$ver,$matches)){
		$r[] = $matches[1];
		return $r;
	}
	if(strpos($ver,'type 0x10')){
		$version_str = substr($ver,strrpos($ver,')')+1);
		$r[] =  hexdec($version_str);
		return $r;
	}
	if(strpos($ver,'type 0x4')){
		$version_str = substr($ver,strrpos($ver,')')+1);
		$r[] = round(hexToDecFloat($version_str),1);
		return $r;
	}
	
	$tmp_dir = '/tmp';
	$classes = 'classes.dex';
	$cmd = "unzip -jo -d {$tmp_dir} \"{$file}\" {$classes} 2>/dev/null";
	shell_exec($cmd);	
	$vers = array(
		//'1.0',
		'3.1.1',
		'3.1.2',
		'3.1.3',
	);
	$i = 0;
	foreach ($vers as $ver) {
		$pver = preg_quote($ver);
		$cmd = "strings {$tmp_dir}/{$classes}|grep '^{$pver}\$'";
		$line = trim(shell_exec($cmd));
		if ($ver == $line) {
			$r[] = $ver;
			$i++;
		}
	}
	return $r;
}

function hexToDecFloat($strHex) {
	$v = hexdec($strHex);
	$x = ($v & ((1 << 23) - 1)) + (1 << 23) * ($v >> 31 | 1);
	$exp = ($v >> 23 & 0xFF) - 127;
	return $x * pow(2, $exp - 23);
}
