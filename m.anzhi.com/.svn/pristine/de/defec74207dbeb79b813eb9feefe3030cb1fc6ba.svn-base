<?php
include_once (dirname(realpath(__FILE__)).'/../../init.php');
$config = load_config('lottery_cache/redis',"lottery");
load_helper('ucenter');
if ($config) {
	$redis = new GoRedisCacheAdapter($config);
} else {
	$redis = GoCache::getCacheAdapter('redis');
}
$prefix		=	"recharge";
$active_id = $_GET['aid'] ? $_GET['aid'] : $_POST['aid'];
//ctype_digit  检查时候是只包含数字字符的字符串（0-9）
if(!ctype_digit($active_id)){
	exit;
}
$sid = $_GET['sid'] ? $_GET['sid'] : $_POST['sid'];
$tplObj -> out['prefix']	=	$prefix;
$tplObj->out['is_share']	=	$_GET['is_share'];
// 静态资源地址
$tplObj->out['new_static_url']		=	$configs['new_static_url'];
$tplObj->out['activity_share_url']	=	$configs['activity_share_url'];
//获取host
$activity_host = $configs['activity_url'];
$tplObj->out['activity_host']	=	$activity_host;
// 图片地址
$tplObj->out['imgurl'] = getImageHost();

if($_SESSION['VERSION_CODE']) {
	$is_version = 1;
}else {
	$is_version = 0;
}
$tplObj -> out['is_version'] = $is_version;


if($_GET['ios']==1 || strpos($_SERVER['HTTP_USER_AGENT'], 'iPhone')||strpos($_SERVER['HTTP_USER_AGENT'], 'iPad')){
	$tplObj->display("lottery/{$prefix}/ios.html");die;
}

if ($_GET['weixin']==1 || strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false ) {
	$tplObj->display("lottery/{$prefix}/weixin.html");die;
}

$model = new GoModel();
if($configs['is_test'] == 1 ){
	$time  = get_now_time();
}else {
	$time  = time();
}

if($_GET['stop'] != 1 ){
	$res = activity_is_stop($active_id);
	if(!$res){
		$url = $activity_host."/lottery/{$prefix}/index.php?stop=1&aid=".$active_id;
		header("Location: {$url}");
	}
}


//获取活动配置
function get_config($aid, $uid){
	global $model;
	$option = array(
			'where' => array(
					'id' => $aid,
			),
			'table' => 'sj_activity',
			'field' => 'id,name,start_tm,end_tm',
			'cache_time'	=>	600,
	);
	$activity = $model->findOne($option);
	return $activity;
}


/**
 * 通过活动Id获取软件信息
 * @param int $aid
 * @return array
 */
function getSoftInfoByAid($aid)
{
	global $prefix;
	global $redis;
	global $activity_host;
	
	$url		=	$activity_host."/phone.php?act=getlistbyaid&aid={$aid}";
	$soft_key	=	"{$prefix}:{$aid}_soft_info";
	$expire		=	1*86400;
	$soft_info	=	$redis -> get($soft_key);
	if( empty($soft_info) ) {
			$res	=	httpGet($url);
			$last	=	json_decode($res,true);
			if(!empty($last)) {
				$data = array();
				foreach ($last['DATA'] as $key => $val){
					$data[] = $val[7];
				}
				$redis	->	set($soft_key, $data, $expire);
				return	$data;
			}else{
				return	false;
			}
	}else {
		return	$soft_info;
	}
}

function get_ranking_data($js_data){
	global $configs;
	if($configs['is_test'] == 1){
		$host = load_config('pay_host');
	}else {
		$host = "http://pay.anzhi.com";	//线上地址
	}
	$url = '/pay/internal/order/find';
	$data = array(
			'serviceId'	=>	'031',
			'data'		=>	json_encode($js_data)
	);
	$vals	=	http_build_query($data);
	$res = httpGetInfo_ext($host.$url, $vals);
	$last = json_decode($res,true);
	if($last['code']!='200'){
		return false;
	}else{
		return json_decode($last['data'],true);
	}
}


function httpGetInfo_ext($url, $vals) {
	$res = curl_init();
	curl_setopt($res, CURLOPT_URL, $url);
	curl_setopt($res, CURLOPT_POST, true);
	curl_setopt($res, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($res, CURLOPT_POSTFIELDS, $vals);
	$result = curl_exec($res);
	$http_code = curl_getinfo($res, CURLINFO_HTTP_CODE);
	$errno = curl_errno($res);
	$error = curl_error($res);
	curl_close($res);
	writelog('ranking_http.log',"{$http_code}|{$errno}|{$error}\n" .$url. print_r($vals, true) . "\n" . print_r($result, true) . "\n\n");
	return $result;
}

//日志
function writelog($filename,$msg){
	$now = time();
	$path = "/data/att/permanent_log/promotion.anzhi.com/".date("Y-m-d", $now);
	if(!file_exists($path)){
		mkdir($path, 0755, true);
	}
	$path_log = $path."/".$filename;
	$msg = date('Y-m-d H:i:s', $now). " {$msg}\n";
	file_put_contents($path_log, $msg, FILE_APPEND);
}


function get_azb_info($uid, $active_id)
{
	$info = get_azb($uid, $active_id);
	if($info['code']==200) {
		$data = json_decode($info['data'], true);
		if(isset($data['azmoney']) && $data['azmoney']){
			return $data['azmoney'];
		}else {
			return 0;
		}
	}else {
		return 0;	
	}
}

function get_xf_info($data)
{
	$info = get_ranking_data($data);
	if(isset($info[0]['payAmount']) && $info[0]['payAmount']){
		return $info[0]['payAmount'];
	}else {
		return 0;
	}
}


/**
 * 添加用户、修改用户信息
 */
function add_user($data,$time){
	global $model;
	global $redis;
	global $prefix;
	
	$option = array(
			'where' => array(
					'uid' => $data['uid'],
					'aid' => $data['aid']
			),
			'table' => 'valentine_draw_userinfo',
	);
	$userinfo = $model->findOne($option,'lottery/lottery');
	if($userinfo){
		$new_data = array(
				'uid' => $data['uid'],
				'username' => $_SESSION['USER_NAME'],
				'phone' => $data['phone'] ? $data['phone'] : $userinfo['phone'] ,
				'contact_name' => $data['contact_name'] ? $data['contact_name'] : $userinfo['contact_name'],
				'address' => $data['address'] ? $data['address'] : $userinfo['address'],
				'update_tm' => $time,
				'__user_table' => 'valentine_draw_userinfo'
		);
		if($data['draw_data_num']){
			$new_data['draw_data_num'] = $data['draw_data_num'];
		}else{
			$new_data['draw_data_num'] = $userinfo['draw_data_num'];
		}
		$where = array(
				'uid' => $data['uid'],
				'aid' =>$data['aid'],
		);
		$ret =  $model->update($where, $new_data,'lottery/lottery');
		if($ret){
			//剩余抽奖次数
			$rest = intval($new_data['draw_data_num']-$userinfo['deduction_num']);
			if($rest < 0) {
				$rest = 0;
			}
			$redis->set("{$prefix}:{$data['aid']}_rest_lottery_num:".$data['uid'], $rest, 10*86400);
		}
	}else {//新增
		$new_data = array(
				'uid' => $data['uid'],
				'aid' => $data['aid'],
				'username' => $_SESSION['USER_NAME'],
				'phone' => $data['phone'] ? $data['phone'] : $userinfo['phone'] ,
				'contact_name' => $data['contact_name'] ? $data['contact_name'] : $userinfo['contact_name'],
				'address' => $data['address'] ? $data['address'] : $userinfo['address'],
				'update_tm' => $time,
				'create_tm' => $time,
				'os_from' => 2,
				'__user_table' => 'valentine_draw_userinfo'
		);
		if($data['draw_data_num']){
			$new_data['draw_data_num'] = $data['draw_data_num'];
		}
		$ret =  $model->insert($new_data,'lottery/lottery');
		if($ret){
			$redis->set("{$prefix}:{$data['aid']}_rest_lottery_num:".$data['uid'], intval($data['draw_data_num']),  30*60);
		}
	}
	$redis->set("{$prefix}:{$data['aid']}_userinfo:".$data['uid'], $new_data, 86400*10);
	return 	$ret;
}

function get_now_time(){
	global $model;
	$option = array(
			'where' => array(
					'status'  => 1,
					'conf_id' => 280
			),
			'table' => 'pu_config',
			'field' => 'configcontent',
	);
	$list = $model->findOne($option);
	return strtotime($list['configcontent']);
}


function get_by_username($uid){
	$result = request_ucenter('/api/account/queryUserByUid',array('data'=>array('uid'=>$uid),'device'=>array()));
	if($result['code']==200) {
		return isset($result['data']['userInfo']['loginName'])?$result['data']['userInfo']['loginName']:'';
	}else {
		return '';	
	}
}

function str_replace_cn($str, $start, $enlengthd ){
	if(preg_match("/[\x7f-\xff]/", $str)){
		if(is_utf8($str)){
			return substr_replace($str,'***',$start*3, -1*3);
		}else{
			return substr_replace($str,'***',$start*2, -1*2);
		}
	}else{
		return substr_replace($str,'***',$start*2, $enlengthd);
	}
}

function is_utf8($word){
	if(preg_match("/^([".chr(228)."-".chr(233)."]{1}[".chr(128)."-".chr(191)."]{1}[".chr(128)."-".chr(191)."]{1}){1}/",$word) == true || preg_match("/([".chr(228)."-".chr(233)."]{1}[".chr(128)."-".chr(191)."]{1}[".chr(128)."-".chr(191)."]{1}){1}$/",$word) == true || preg_match("/([".chr(228)."-".chr(233)."]{1}[".chr(128)."-".chr(191)."]{1}[".chr(128)."-".chr(191)."]{1}){2,}/",$word) == true) {
		return true;
	}else {
		return false;
	}
}