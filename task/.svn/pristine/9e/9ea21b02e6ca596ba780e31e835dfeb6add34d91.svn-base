<?php
require_once(dirname(__FILE__).'/../init.php');
ini_set('displays_errors', true);
error_reporting(E_ALL);

$ip = getServerIp();
//只在18使用
if ($ip != '192.168.1.18' && $ip != '192.168.0.99') {
    exit;
}
load_helper('utiltool');
$_SERVER['HTTP_HOST'] = '518.anzhi.com';
$model = new GoModel();
$worker->addFunction('soft_undercarriage', 'soft_undercarriage_func');  
while ($worker->work());

function soft_undercarriage_func($job){
	global $model;
	$time = time();
	$push_server = 'pushdb';
	$string = $job->workload();
 	if ( !($data = json_decode($string, true)) ) {
		return false;
	}
	$msg = $data['msg'];
	$debut_arr =  isset($data['debut_arr']) ?  $data['debut_arr'] : '';
	$screen_arr =  isset($data['screen_arr']) ? $data['screen_arr'] : '';
	unset($data['msg'],$data['debut_arr'],$data['screen_arr']);
	$dev_id = array();
	$package = array();
	$softname = array();
	$undersids = array();
	foreach($data as $v){
		if($v['dev_id'] != 0){
			$dev_id[] =  $v['dev_id'];
			if($v['package']) $package[] = $v['package']; 
			$softname[$v['dev_id']][] = $v['softname'];
		}	
		//发送死链softid
		$undersids[] = $v['softid'];
	}

	if($dev_id){
		$option = array(
			'table' => 'pu_developer',
			'where' => array(
				'dev_id' => $dev_id
			),
			'field' => 'dev_id,email,dev_name'
		);
		$dever = $model->findAll($option);
		$dev_list = array();
		foreach($dever as $k => $v){
			$dev_list[$v['dev_id']] = $v;
		}
		unset($dever);
		$subject = "【安智提醒】_ 软件下架通知";
		$tm = date("Y-m-d",$time);
		foreach($softname as $devid => $v){
			$str = implode('、',$v);
			//发送安智提醒
			$msgs = "您的 &lt;{$str}&gt;软件于【{$tm}】被下架，下架原因：<br/>{$msg}";
			dev_remind_add($devid,$msgs);
			//发送邮件提醒
			$email_cont = get_email_content($str,$dev_list[$devid]['dev_name'],$tm,"被下架","下架原因：<br/>{$msg}");
			realsend($dev_list[$devid]['email'],$dev_list[$devid]['dev_name'],$subject,$email_cont);
		}
		$subject_debut = "【安智提醒】_ 取消首发通知";
		if($debut_arr){
			foreach($debut_arr as $devid => $v){
				$str1 = implode('、',$v);
				//下架取消首发发送提醒
				$msg_debut = "您的 &lt;{$str1}&gt;软件于【{$tm}】取消首发，取消原因:下架取消";
				dev_remind_add($devid,$msg_debut);
				//下架取消首发发送邮件
				$but_cont = get_email_content($str1,$dev_list[$devid]['dev_name'],$tm,"取消首发","取消原因：<br/>下架取消；");
				realsend($dev_list[$devid]['email'],$dev_list[$devid]['dev_name'],$subject_debut,$but_cont);
			}
		}	
		$subject_screen = "【安智提醒】_ 取消闪屏通知";		
		if($screen_arr){
			foreach($screen_arr as $devid => $v){
				$str2 = implode('、',$v);
				//下架取消闪屏发送提醒
				$msg_screen = "您的 &lt;{$str2}&gt;软件于【{$tm}】取消闪屏，取消原因:下架取消";
				dev_remind_add($devid,$msg_screen);
				//下架取消闪屏发送邮件
				$screen_cont = get_email_content($str2,$dev_list[$devid]['dev_name'],$tm,"取消闪屏","取消原因：<br/>下架取消；");
				realsend($dev_list[$devid]['email'],$dev_list[$devid]['dev_name'],$subject_screen,$screen_cont);
			}
		}		
	}
	//向WDJ发送死链
	//wdj_under_key($undersids);
	//更新pu_developer字段statistics_on
	update_developer($dev_id);	
}

function get_email_content($softname,$dev_name,$tm,$replace,$reason){
	$content = "亲爱的：{$dev_name}<br><br>
				您的&lt;{$softname}&gt; 软件于【{$tm}】{$replace}，期待您继续发布更多优秀的软件；<br>\n {$reason}<br><br>如有疑问，请与安智客服联系（http://dev.anzhi.com/contact_us.php）<br>安智开发者联盟敬上<br>http://dev.anzhi.com<br>日期：{$tm}<br>(系统邮件,请勿回复)" ;
	return 	$content;		
}
/**
 * sendmail
 */
function _http_post_email($vals) {
	$url = 'http://42.62.4.183/service.php';
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
		$email_array = array('1216063767@qq.com','467947645@qq.com','qingfeng130227@qq.com','158796378@qq.com','yuanming@anzhi.com','anzhi_test_1@163.com','527159802@qq.com','249024553@qq.com');
		if(!in_array($email,$email_array)){
			return false;
		}
		$data['interior_send'] = 1;
	}
	$tmp = _http_post_email($data);
	
	if($tmp['http_code']!=200) {
		writelog("send_mail.log", var_export($data,true)."\n".var_export($tmp,true)."发送邮件失败");
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
//消息提醒入库
function dev_remind_add($dev_id,$content,$remind_id = ''){
	global $model;
	$data = array(
		'__user_table' => 'dev_remind',
		'dev_id' => $dev_id,
		'content' => $content,
		'create_tm' => time(),
		'read_status' => 0,
		'status' => 1,
		'remind_id' => $remind_id
	);
	$remindres = $model->insert($data);		
	if(!$remindres){
		writelog("dev_remind.log",$model->getsql()."提醒消息ID为{$dev_id}内容为{$content}插入失败");
	}
}
//软件操作日志
function writelog($filename,$msg){
	$now = time();
	$path = "/data/att/permanent_log/admin_task_log/".date("Y-m-d", $now);
	if(!file_exists($path)){
		mkdir($path, 0755, true);
	}	
	$path_log = $path."/".$filename;
	$msg = date('Y-m-d H:i:s', $now). " {$msg}\n";
	file_put_contents($path_log, $msg, FILE_APPEND);
}


//向wdj发送死链接请求 
function wdj_under_key($undersid){
	$server = getServerIp();
	$under_key = "625f650b2ffc7e7ad508fa48b75380d3";
	$wdj_dead_link = "http://appapi.wandoujia.com/app/getBrokenLink.php?key=625f650b2ffc7e7ad508fa48b75380d3";
	if($server !='192.168.0.99' && $server != '10.0.3.15') {
		if (!empty($undersid)) {
				//发送死链通知  
				$underinfo['counts'] = 1;
				$underinfo['urls']['url'][] = array(
					'id' => $undersid,
					'link' => "http://wdj.anzhi.com/soft_" . $undersid . ".html",
					'returnCode' => 403,
				);
				$under_data['key'] = $under_key; //死链key
				$under_data['data'] = json_encode($underinfo);
				$http_result = requestPost($wdj_dead_link, $under_data);
				if ($http_result) {				
					$content ='=> Took  method ' . 'POST' . ' to send a request to ' . $wdj_dead_link . "in to softid " . $undersid . " " . $http_result. " \n";
					writelog("curl.log",$content);
				}
			//发送死链通知结束
		}
	}
}
//更新统计statistics_on字段（上架的数据）
function update_developer($dev_id){
	global $model;
	$where = array();
	$where['status'] = 1; 
	$where['hide'] = 1; 
	$where['channel_id'] = ''; 
	$where['claim_status'] = 2;
	if(is_array($dev_id)){
		$dev_id = array_unique($dev_id);
		foreach($dev_id as $val){
			$where['dev_id'] = $val; 
			$option = array(
				'table' => 'sj_soft',
				'where' => $where,
				'field' => 'count(*) as counts',
			);
			$total = $model->findOne($option);
			$count = $total['counts'];
			
			$where_p = array(
				'dev_id' => $val
			);
			$option = array(
				'__user_table' => 'pu_developer',
				'statistics_on'=>$count,
			);
			$model->update($where_p,$option);
		}
	}else{	
		$where['dev_id'] = $dev_id; 
		$option = array(
			'table' => 'sj_soft',
			'where' => $where,
			'field' => 'count(*) as counts',
		);
		$total = $model->findOne($option);
		$count = $total['counts'];
		
		$where_p = array(
			'dev_id' => $dev_id
		);
		$option = array(
			'__user_table' => 'pu_developer',
			'statistics_on'=>$count,
		);
		$model->update($where_p,$option);
	}
	
}