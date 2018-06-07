<?php
require_once(dirname(__FILE__).'/../init.php');
ini_set('displays_errors', true);
error_reporting(E_ALL);

load_helper('utiltool');
$_SERVER['HTTP_HOST'] = '518.anzhi.com';
$model = new GoModel();
$start = time();
$worker->addFunction('developer_notice_send_mail', 'developer_send_mail_func');  
while ($worker->work());

function developer_send_mail_func($job)
{
	global $model;
	$push_server = 'pushdb';
	$string = $job->workload();
 	if ( !($data = json_decode($string, true)) ) {
		return false;
	}
	//支持开发者为多条件筛选
	if(is_array($data['send_obj'])){
		$dever=get_developere_email($data['send_obj']);
	}else{
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
	}
	
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
function get_developere_email($data){
	// send_obj2 0公司1个人3全部
	// whitelist_type	0全部或1联运或2非联运
	// soft_parent_category 0全部或子类
	// soft_two_category 0全部或具体
	global $model;
	$opts = array(
		'table' => 'pu_developer',
		'where' => array(
			'email_verified' => 1,
			'status' => 0,
			'type'=>$data['send_obj2'],	
		),
		'field' => 'dev_id, dev_name, email'
	);
	$dever = $model->findAll($opts);
	$dev_id=array();
	foreach($dever as $k=>$v){
		$dev_id[]=$v['dev_id'];
	}

	$opts = array(
		'table' => 'sj_soft_whitelist',
		'where' => array(
			'status' => 1,
		),
		'field' => 'id,dev_id'
	);
	$whitelist_data = $model->findAll($opts);
	$whitelist_dev_id=array();
	foreach($whitelist_data as $k=>$v){
		$whitelist_dev_id[]=$v['dev_id'];
	}
	

	if($data['whitelist_type']==1){
		$dev_id=array_intersect($dev_id,$whitelist_dev_id);
	}else if($data['whitelist_type']==2){
		$dev_id=array_diff($dev_id,$whitelist_dev_id);
	}

	//过滤游戏分类
	$need_dev_id=array();
	if($data['soft_parent_category']!=0){
		//获取游戏分类
		$cate_data=array();
		if($data['soft_two_category']==0){
			$cate_datas=array();
			$opts = array(
				'table' => 'sj_category',
				'where' => array(
					'parentid' => $data['soft_parent_category'],
					'status' => array('exp','>=0'),
				),
				'field' => 'category_id, name'
			);
			$categorys = $model->findAll($opts);
			foreach($categorys as $k=>$v){
				$cate_datas[]=$v['category_id'];
			}
			$opts = array(
				'table' => 'sj_category',
				'where' => array(
					'parentid' => $cate_datas,
					'status' => array('exp','>=0'),
				),
				'field' => 'category_id, name'
			);
			$categorys = $model->findAll($opts);
			foreach($categorys as $k=>$v){
				$cate_data[]=','.$v['category_id'].',';
			}
		}else{
			// $cate_data[]=$data['soft_two_category'];
			$opts = array(
				'table' => 'sj_category',
				'where' => array(
					'parentid' => $data['soft_two_category'],
					'status' => array('exp','>=0'),
				),
				'field' => 'category_id, name'
			);
			$categorys = $model->findAll($opts);
			foreach($categorys as $k=>$v){
				$cate_data[]=','.$v['category_id'].',';
			}
		}
		foreach($dev_id as $k=>$v){
			$opts = array(
				'table' => 'sj_soft',
				'where' => array(
					'category_id' =>$cate_data,
					'status' => 1,
					'dev_id' => $v,
					// 'hide' => 1,
				),
				'field' => 'softid,category_id'
			);
			$softs = $model->findAll($opts);
			if($softs){
				$need_dev_id[]=$v;
			}
		}	
	}else{
		$need_dev_id=$dev_id;
	}
	$need_dever=array();
	foreach($dever as $k=>$v){
		if(in_array($v['dev_id'], $need_dev_id)){
			$need_dever[]=$v;
		}
	}
	return $need_dever;
}
