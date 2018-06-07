<?php
function request_ucenter($api,$data_array,$need_crypt=true,$need_info=true,$method='POST')
{
	if(empty($data_array['data'])||empty($api)||!is_array($data_array['data'])){
		return false;
	}
	$start = microtime_float();
	$app = defined('APP_NAME') ? APP_NAME : 'www';
	$ucenter = load_config('ucenter/'.$app, 'uc');
	if ($_SESSION['ENV'] == 'FAKE_PROD') {
		$ucenter = load_config('ucenter/'.$app, 'uczb');
	} 
	if(empty($ucenter)){
		return false;
	}
	if($need_crypt===true){	//需要加密的数据要包在data层,否则无需
		$des = new GoDes($ucenter['privatekey']);
		$temp_data = $des->encrypt(json_encode($data_array['data']));
		$data = base64_encode($temp_data);
		$request_data = array('data'=>$data);
	}
	else{
		$request_data = $data_array['data'];
	}
	if($need_info===true){
		$request_data['serviceId'] = $ucenter['serviceId'];
		$request_data['serviceVersion'] = $ucenter['serviceVersion'];
		$request_data['serviceType'] = 0;	//	0 移动端， 1 Web端
		if(!empty($data_array['device'])){
			$request_data['device'] = json_encode($data_array['device']);
		}
	}
	$ch = curl_init();

	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

	$url = $ucenter['uri'].$api;

	if('POST'==$method){
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $request_data);
	}
	elseif('GET'==$method){
		$request_data = http_build_query($request_data);
		$url .= '?'.$request_data;
	}
	curl_setopt($ch, CURLOPT_URL, $url);
	$result = curl_exec($ch);
	$info = curl_getinfo($ch);
	curl_close($ch);
	$code = $info['http_code'];
	$msg = date('Y-m-d H:i:s');
	$day = date('Y-m-d');
	if(is_array($request_data)){	//	为记日志处理
		$request_data = json_encode($request_data);
	}
	if ($code == 200) {
		if (empty($result)) {
			$new_msg = $msg.$request_data." return empty \n";
			file_put_contents("/tmp/ucenter_error_{$day}.log", $new_msg, FILE_APPEND);
		}
		$ret = json_decode($result, true);
		$end = microtime_float();
		$s = $end - $start;
		if ($s > 0.5) {	//	此常量待定
			$rheader = explode("\r\n", $rheader);
			$ss = end($rheader);
			$new_msg = $msg.$request_data." spend {$s} {$ss}\n";
			file_put_contents("/tmp/ucenter_slow_{$day}.log", $new_msg, FILE_APPEND);
		}
		return $ret;
	}
	else {
		$msg .= $request_data." service abnormal \n";
		file_put_contents("/tmp/ucenter_error_{$day}.log", $msg, FILE_APPEND);
		return false;
	}
	return false;
}

function request_task($api,$data_array,$extra=array(),$need_crypt=true,$need_info=true,$method='POST')
{
	$start = microtime_float();
	$app = defined('APP_NAME') ? APP_NAME : 'gomarket';
	$ucenter = load_config('ucenter/'.$app, 'uc');
	$day = date('Y-m-d');
	$api_version = isset($extra['version'])&&!empty($extra['version']) ? $extra['version'] : $ucenter[$api['prefix'].'_version'] ;
	$url = $ucenter[$api['prefix'].'_uri'].$api_version.$api['apiname'];
	file_put_contents("/tmp/ucenter_task_request_{$day}.log", date('[Y-m-d H:i:s]')."\t".session_id()."\t".json_encode($data_array)."\t{$url}\n", FILE_APPEND);
	$des = new GoDes($ucenter['task_privatekey']);
	$temp_data = $des->encrypt(json_encode($data_array['data']));
	$data = base64_encode($temp_data);
	$request_data = array('data'=>$data);
	$real_serviceId = $api['passthrough'] === true ? $ucenter['client_serviceId'] : ($api['passthrough'] === false ? $ucenter['serviceId'] : 0);
	if(empty($real_serviceId)){
		return false;
	}
	if($api['prefix']=='task'){
		$request_data['clientInfo'] = array(
			'serviceId'=>$real_serviceId,
			'clientVersion' => $_SESSION['VERSION_CODE'],
		);
	}
	$request_data['serviceId'] = $ucenter['serviceId'];
	$request_data['serviceVersion'] = $ucenter['serviceVersion'];
	$request_data['serviceType'] = 0;	//	0 移动端， 1 Web端
	if(isset($api['sid'])&&!empty($api['sid'])){
		$request_data['sid'] = $api['sid'];
	}
	if(!empty($data_array['device'])){
		$request_data['device'] = $data_array['device'];
	}
	$request_header = isset($extra['header']['appchannel']) && !empty($extra['header']['appchannel']) ? array('appchannel'=>$extra['header']['appchannel']) : (isset($ucenter['appchannel']) && !empty($ucenter['appchannel']) ? array('appchannel'=>$ucenter['appchannel']) : array());
	if(!empty($data_array['header'])){
		$request_header = array_merge($request_header,$data_array['header']);
	}
	$request_data['header'] = $request_header;
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json; charset=utf-8'));
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($request_data));
	curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
	$result = curl_exec($ch);
	$host_pre = "{$ucenter[$api['prefix'].'_uri']}v65/api/tms/task/";

	$log_last = str_replace($host_pre,'',$url);
	if($log_last == 'reSubmitTask'||$log_last == 'getUserReSoftTaskList'||$log_last == 'getUserSoftOrActivityList'){
		$log = "/tmp/v65_{$log_last}_{$day}.log";
		file_put_contents($log, date('[Y-m-d H:i:s]')."\t".json_encode($data_array)."\r\n".json_encode($request_data)."\r\n{$url}"."\r\n{$result}\r\n", FILE_APPEND);
	}else{
		file_put_contents("/tmp/ucenter_task_request_{$day}.log", date('[Y-m-d H:i:s]')."\t".session_id()."\t".json_encode($data_array)."\t{$url}\t{$result}\n", FILE_APPEND);
	}

	$info = curl_getinfo($ch);
	curl_close($ch);
	$code = $info['http_code'];
	$msg = date('Y-m-d H:i:s');
	if(is_array($request_data)){	//	为记日志处理
		$request_data = json_encode($request_data);
	}
	if ($code == 200) {
		if (empty($result)) {
			$new_msg = $msg.$request_data." return empty \n";
			file_put_contents("/tmp/ucenter_task_error_{$day}.log", $new_msg, FILE_APPEND);
		}
		$temp_data = json_decode($result, true);
		if($temp_data['code']==200){
			$respone_data = base64_decode($temp_data['data']);
			$decrypt_privatekey = $api['passthrough'] === true ? $ucenter['task_client_privatekey'] : $ucenter['task_privatekey'];
			$des = new GoDes($decrypt_privatekey);
			$ret = json_decode($des->decrypt($respone_data),true);
		}
		else{
			$ret = $temp_data;
		}
		$end = microtime_float();
		$s = $end - $start;
		if ($s > 0.5) {	//	此常量待定
			$rheader = explode("\r\n", $rheader);
			$ss = end($rheader);
			$new_msg = $msg.$request_data." spend {$s} {$ss}\n";
			file_put_contents("/tmp/ucenter_task_slow_{$day}.log", $new_msg, FILE_APPEND);
		}
		if($log_last == 'reSubmitTask'||$log_last == 'getUserReSoftTaskList'||$log_last == 'getUserSoftOrActivityList'){
			$log = "/tmp/v65_{$log_last}_{$day}.log";
			file_put_contents($log, date('[Y-m-d H:i:s]')."\t".json_encode($ret)."\r\n", FILE_APPEND);
		}
		return $ret;
	}
	else {
		$msg .= $request_data." service abnormal \n";
		file_put_contents("/tmp/ucenter_task_error_{$day}.log", $msg, FILE_APPEND);
		return false;
	}
	return false;
}

function uc_getUserinfo($pid,$sid,$device_arr=array())
{
	$result = request_ucenter('/api/account/queryUserBySid',array('data'=>array('pid'=>$pid,'sid'=>$sid),'device'=>$device_arr));
	return $result;
}

function uc_getUserinfoByAccount($account,$device_arr=array())
{
	$result = request_ucenter('/api/account/queryUserInfo',array('data'=>array('account'=>$account),'device'=>$device_arr));
	return $result;
}

function uc_getUserinfoBySid($sid,$device_arr=array(),$extra_infos=array())
{
	$request_data = array('sid'=>$sid);
	if(!empty($extra_infos)){
		foreach($extra_infos as $key=>$value){
			$request_data[$key] = $value;
		}
	}
	$result = request_ucenter('/api/account/queryUserBySid',array('data'=>$request_data,'device'=>$device_arr));
	return $result;
}

function uc_register($loginName,$email,$password,$telephone=0,$device_arr=array())
{
	$data = array('loginName'=>$loginName,'email'=>$email,'password'=>$password);
	if(!empty($telephone)){
		$data = array_merge($data,array('telephone'=>$telephone));
	}
	$request_data = array('data'=>$data,'device'=>$device_arr);
	$result = request_ucenter('/api/account/registerByLoginName',$request_data);
	return $result;
}

function uc_login($account,$password,$device_arr=array())
{
	$result = request_ucenter('/api/account/login',array('data'=>array('account'=>$account,'password'=>$password),'device'=>$device_arr));
	return $result;
}

function uc_checkOnline($sid,$device_arr=array())
{
	$result = request_ucenter('/api/account/checkOnline',array('data'=>array('sid'=>$sid),'device'=>$device_arr));
	return $result;
}

function uc_logout($sid,$device_arr=array())
{
	$result = request_ucenter('/api/account/logout',array('data'=>array('sid'=>$sid),'device'=>$device_arr));
	return $result;
}

function uc_resetPassword($pid,$password,$timestamp,$device_arr=array())
{
	$result = request_ucenter('/api/account/resetPassword',array('data'=>array('pid'=>$pid,'password'=>$password,'timestamp'=>$timestamp),'device'=>$device_arr));
	return $result;
}

function uc_checkPassword($pid,$password,$device_arr=array())
{
	$result = request_ucenter('/api/account/checkPassword',array('data'=>array('pid'=>$pid,'password'=>$password),'device'=>$device_arr));
	return $result;
}

function uc_uploadAlbum($pid,$binary,$device_arr=array())
{
	$result = request_ucenter('/api/common/uploadAlbum',array('data'=>array('pid'=>$pid,'albumStr'=>base64_encode($binary)),'device'=>$device_arr),false);
	return $result;
}

function uc_getAlbum($pid,$size,$device_arr=array())
{
	$app = defined('APP_NAME') ? APP_NAME : 'www';
	$imguri = load_config('ucenter/gomarket/imguri', 'uc');
	$result = request_ucenter($imguri,array('data'=>array('pid'=>$pid,'size'=>$size),'device'=>$device_arr),false,false,'GET');
	return $result;
}

//	直接返回用户中心接口url，那边会处理为302跳转
function uc_getAlbumUrl($pid,$size)
{
	if(empty($pid)||empty($size)){
		return false;
	}
	$app = defined('APP_NAME') ? APP_NAME : 'www';
	$imguri = load_config('ucenter/gomarket/imguri', 'uc');
	if(empty($imguri)){
		return false;
	}
	return $imguri."?pid=$pid&size=$size";
}

function uc_getAllTask($device_arr=array(),$passthrough=true,$request_serviceId=null,$extra=array())
{
	$app = defined('APP_NAME') ? APP_NAME : 'gomarket';
	$ucenter = load_config('ucenter/'.$app, 'uc');
	if($request_serviceId===null){
		$request_serviceId = $ucenter['client_serviceId'];
	}
	$result = request_task(array('prefix'=>'task','apiname'=>'/api/tms/task/getAllList','passthrough'=>$passthrough),array('data'=>array('serviceId'=>$request_serviceId,'page'=>1,'pageSize'=>'10000'),'device'=>$device_arr),$extra);
	return $result;
}

function uc_getAllSoftTask($apiname,$device_arr=array(),$passthrough=true,$request_serviceId=null,$extra=array(),$transfer_data)
{
	$app = defined('APP_NAME') ? APP_NAME : 'gomarket';
	$ucenter = load_config('ucenter/'.$app, 'uc');
	if($request_serviceId===null){
		$request_serviceId = $ucenter['client_serviceId'];
	}
	$data=array();
	$data['serviceId']=$request_serviceId;
	
	$data=array_merge($data, $transfer_data);
	$result = request_task(array('prefix'=>'task','apiname'=>$apiname,'passthrough'=>$passthrough),array('data'=>$data,'device'=>$device_arr),$extra);
	return $result;
}
function uc_TaskDetailList($sid, $data, $device_arr=array(), $header=array(), $extra = array())
{
	$app = defined('APP_NAME') ? APP_NAME : 'gomarket';
	$ucenter = load_config('ucenter/'.$app, 'uc');
	$request_serviceId = $ucenter['client_serviceId'];
	$apiname = !empty($extra['apiname']) ? $extra['apiname'] : '/api/tms/task/getUserReSubSoftTaskDetailList';
	$api_info = array(
		'prefix'=>'task',
		'apiname'=> $apiname,
		'passthrough'=> true,
		'sid'=> $sid
	);

	$data['serviceId'] = $request_serviceId;
	$data_array = array(
		'data'=> $data,
		'device'=>$device_arr,
		'header'=>$header
	);

	$result = request_task($api_info,$data_array, $extra);
	return $result;
}
function sort_soft($a,$b)
{
	$a_sortNo = intval($a['sortNo']);
	$b_sortNo = intval($b['sortNo']);
	if($a_sortNo===$b_sortNo){
		$a_updateTime = intval(strtotime($a['updateTime']));
		$b_updateTime = intval(strtotime($b['updateTime']));
		return $a_updateTime<$b_updateTime ? 1 : -1;
	}
	return $a_sortNo<$b_sortNo ? -1 : 1;
}

function uc_getSoftByPage($taskId,$page=1,$pageSize='10000',$device_arr=array(),$passthrough=true,$request_serviceId=null,$redis=null,$extra=array())
{
	if(empty($redis)){
		$redis = GoCache::getCacheAdapter('redis');
	}
	if($taskId===0){
		$well_chosen = $redis->get('UCENTER_TASK_ID:T2');
		$taskId = $well_chosen['taskId'];
	}
	$app = defined('APP_NAME') ? APP_NAME : 'gomarket';
	$ucenter = load_config('ucenter/'.$app, 'uc');
	if($request_serviceId===null){
		$request_serviceId = $ucenter['client_serviceId'];
	}
	$result = request_task(array('prefix'=>'task','apiname'=>'/api/tms/task/getSoftByPage','passthrough'=>$passthrough),array('data'=>array('serviceId'=>$request_serviceId,'taskId'=>$taskId,'page'=>$page,'pageSize'=>10000),'device'=>$device_arr),$extra);
	if(isset($result['code'])){	//	异常情况直接返回提示
		return $result;
	}
	$now = time();
	$return = array();
	foreach($result as $single){
		if(intval(strtotime($single['shelvesTime']))<=$now&&intval(strtotime($single['underTime']))>=$now){
			$return[] = $single;
		}
	}
	usort($return,'sort_soft');
	return $return;
}

function uc_submitTask($sid, $data, $device_arr=array(), $header=array(), $extra = array())
{
	$app = defined('APP_NAME') ? APP_NAME : 'gomarket';
	$ucenter = load_config('ucenter/'.$app, 'uc');
	$request_serviceId = $ucenter['client_serviceId'];
	$apiname = !empty($extra['apiname']) ? $extra['apiname'] : '/api/tms/usertask/submitTask';
	$api_info = array(
		'prefix'=>'task',
		'apiname'=> $apiname,
		'passthrough'=> true,
		'sid'=> $sid
	);

	$data['serviceId'] = $request_serviceId;
	$data_array = array(
		'data'=> $data,
		'device'=>$device_arr,
		'header'=>$header
	);

	$result = request_task($api_info,$data_array, $extra);
	return $result;
}


function uc_unlogin_userinfo($device_arr=array(),$passthrough=true,$request_serviceId=null,$extra=array())
{
	$app = defined('APP_NAME') ? APP_NAME : 'gomarket';
	$ucenter = load_config('ucenter/'.$app, 'uc');
	if($request_serviceId===null){
		$request_serviceId = $ucenter['client_serviceId'];
	}
	$result = request_task(array('prefix'=>'task','apiname'=>'/api/tms/usertask/getUseCenterInfoNoLogin','passthrough'=>$passthrough),array('data'=>array('serviceId'=>$request_serviceId),'device'=>$device_arr),$extra);
	return $result;
}

function uc_userdraw_getTopWinners($device_arr=array(),$passthrough=true,$request_serviceId=null,$extra=array())
{
	$app = defined('APP_NAME') ? APP_NAME : 'gomarket';
	$ucenter = load_config('ucenter/'.$app, 'uc');
	if($request_serviceId===null){
		$request_serviceId = $ucenter['client_serviceId'];
	}
	$result = request_task(array('prefix'=>'task','apiname'=>'/api/tms/userdraw/getTopWinners','passthrough'=>$passthrough),array('data'=>array('serviceId'=>$request_serviceId),'device'=>$device_arr),$extra);
	return $result;
}

function uc_vc2prefix($version_code,$force=false)
{
	$prefix = '';
	$newest_prefix = 'v6';
	if($version_code>=6000){
		$prefix = 'v6';
	}
	if($force===true){
		$prefix = $newest_prefix;
	}
	return $prefix;
}

function uc_submitTask_variable_url($params)
{
	$app = defined('APP_NAME') ? APP_NAME : 'gomarket';
	$ucenter = load_config('ucenter/'.$app, 'uc');
	if($request_serviceId===null){
		$request_serviceId = $ucenter['client_serviceId'];
	}
	$request_data = array(
			'api'=>array('prefix'=>'task','sid'=>$_SESSION['UCENTER_SID'],'passthrough'=>true)
			,'data_array'=>array('data'=>array('serviceId'=>$request_serviceId),'device'=>array(),'header'=>array())
			,'extra'=>array()
			);
	foreach($params as $out_key=>$out_value){
		if($out_key==='data_array'){
			foreach($out_value as $inner_key=>$inner_value){
				if(isset($request_data[$out_key][$inner_key])&&is_array($request_data[$out_key][$inner_key])){
					$request_data[$out_key][$inner_key] = array_merge($request_data[$out_key][$inner_key],$params[$out_key][$inner_key]);
				}
				else{
					$request_data[$out_key][$inner_key] = $params[$out_key][$inner_key];
				}
			}
		}
		else{
			$request_data[$out_key] = array_merge($request_data[$out_key],$params[$out_key]);
		}
	}
	$result = request_task($request_data['api'],$request_data['data_array'],$request_data['extra']);
	return $result;
}

/**
 * 获取用户vip等级
 * @param  [type] $pid [description]
 * @return [type]      [description]
 */
function uc_getVipLevel($pid)
{
	$app = defined('APP_NAME') ? APP_NAME : 'www';
	$ucenter = load_config('ucenter/'.$app, 'uc');

	$ch = curl_init();

	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$url = $ucenter['uri']. '/common/account/checkVipInfo?pid='. $pid;
	curl_setopt($ch, CURLOPT_URL, $url);
	$result = curl_exec($ch);
	curl_close($ch);
	$json = json_decode($result, true);
	$vipLevel = intval($json['vipLevel']);
	return $vipLevel;
}



/**
 * 检查用户是否为渠道用户
 * @param  string $pid [用户PID]
 * @param  string $app_key 
 * @return bool      [true:CPS用户,false:非CPS用户]
 */
function uc_getChannel($pid ,$app_key)
{

	$app = defined('APP_NAME') ? APP_NAME : 'www';
	$ucenter = load_config('ucenter/'.$app, 'uc');

	$ch = curl_init();

	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$url = $ucenter['uri']. '/common/account/checkChannelInfo?pid='. $pid .'&appKey='. $app_key;
	curl_setopt($ch, CURLOPT_URL, $url);
	$result = curl_exec($ch);
	//var_dump($url);exit;
	//var_dump($result);exit;
	curl_close($ch);
	$json = json_decode($result, true);
	$channel = intval($json['channelCode']);
	return $channel;
}

/**
 * 检查用户VIP等级及渠道
 * @param  string $pid [用户PID]
 * @return array      [vipLevel:VIP级别,"vipType":VIP渠道0普通、1召回、2渠道]
 */
function uc_getVipInfo($pid)
{

	$app = defined('APP_NAME') ? APP_NAME : 'www';
	$ucenter = load_config('ucenter/'.$app, 'uc');
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$url = $ucenter['uri']. '/common/account/checkVipInfo?pid='. $pid;
	curl_setopt($ch, CURLOPT_URL, $url);
	$result = curl_exec($ch);
	curl_close($ch);
	$json = json_decode($result, true);
	
	if($json['code'] != 200) {
		return null;
	}
	
	return $json;
}



function uc_getShareTask($device_arr=array(),$type=1,$passthrough=true,$request_serviceId=null,$extra=array()){
	$app = defined('APP_NAME') ? APP_NAME : 'gomarket';
	$ucenter = load_config('ucenter/'.$app, 'uc');
	if($request_serviceId===null){
		$request_serviceId = $ucenter['client_serviceId'];
	}
	$result = request_task(array('prefix'=>'task','apiname'=>'/api/tms/retask/getsoftactivity','passthrough'=>$passthrough),array('data'=>array('serviceId'=>$request_serviceId,'type'=>$type),'device'=>$device_arr),$extra);
	return $result;
}

function uc_getOtherTask($device_arr=array(),$type=1,$passthrough=true,$request_serviceId=null,$extra=array()){
	$app = defined('APP_NAME') ? APP_NAME : 'gomarket';
	$ucenter = load_config('ucenter/'.$app, 'uc');
	if($request_serviceId===null){
		$request_serviceId = $ucenter['client_serviceId'];
	}
	$result = request_task(array('prefix'=>'task','apiname'=>'/api/tms/retask/getlist ','passthrough'=>$passthrough),array('data'=>array('serviceId'=>$request_serviceId,'listDestination'=>$type),'device'=>$device_arr),$extra);
	return $result;
}

function uc_getUserTaskInfo($sid, $data, $device_arr=array(), $header=array(), $extra = array()){
	$app = defined('APP_NAME') ? APP_NAME : 'gomarket';
	$ucenter = load_config('ucenter/'.$app, 'uc');
	$request_serviceId = $ucenter['client_serviceId'];
	$apiname = $extra['apiname'];
	$api_info = array(
		'prefix'=>'task',
		'apiname'=> $apiname,
		'passthrough'=> true,
		'sid'=> $sid
	);

	$data['serviceId'] = $request_serviceId;
	$data_array = array(
		'data'=> $data,
		'device'=>$device_arr,
		'header'=>$header
	);
	$result = request_task($api_info,$data_array, $extra);
	return $result;
}

/**
 * 获取用户会员等级
 * @param  [int] $pid [用户pid]
 * @return [int]      [用户等级]
 */
function uc_getMemberLevel($pid,$appkey,$channel)
{
	$app = defined('APP_NAME') ? APP_NAME : 'www';
	$ucenter = load_config('ucenter/'.$app, 'uc');
	$url = $ucenter['uri']. '/common/account/checkMemberInfo';
	$request_data = array();
	$request_data['pid'] = $pid ;
	$request_data['appkey'] = $appkey ;
	$request_data['channel'] = $channel ;
	$ch = curl_init();	
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $request_data);	
	curl_setopt($ch, CURLOPT_URL, $url);
	$result = curl_exec($ch);
	curl_close($ch);
	$json = json_decode($result, true);
	$Level = intval($json['memberLevelForSDK']);
	return $Level;
}
