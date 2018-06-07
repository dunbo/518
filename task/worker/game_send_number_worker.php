<?php
include dirname(__FILE__).'/../init.php';
ini_set('display_errors',true);
error_reporting(E_ALL);
$db = 'sendnum';
$worker->addFunction("game_send_number", "game_send_number_func");  
while ($worker->work());
function game_send_number_func($jobs){
	global $type_map;
	if ( !($p = json_decode($jobs->workload(),true)) ) {
        return False;
        }
	//传参
	//file_put_contents('/tmp/lipeng0427.log','start_tm '.date('Y-m-d H:i:s',time())."\n",FILE_APPEND);
	$uid = $p['uid'];//论坛uid
	$active_id = $p['active_id']; //活动id
	$ip = $p['ip']; //参与者的ip
	$now_time = $p['take_tm'];
	$date = date('Y-m-d',$now_time);
	$num_key = "active{$active_id}_uid{$uid}_num";	//号码key
	//活动信息
	//file_put_contents('/tmp/lipeng0427.log','query_tm '.microtime().' '.__LINE__."\n",FILE_APPEND);
	$active_info = get_active_info($active_id);
	//file_put_contents('/tmp/lipeng0427.log','query_tm '.microtime().' '.__LINE__."\n",FILE_APPEND);
	if($active_info['status'] == 0){//已停用
		writeCache($num_key,3);
		return;
	}
	if($active_info['start_tm'] > $now_time){ //未开始
		writeCache($num_key,"ready".date("Y-m-d H:i:s",$active_info['start_tm']));
		return;
	}elseif($active_info['end_tm'] < $now_time){//已结束
		writeCache($num_key,2);
		return;
	}
	//file_put_contents('/tmp/lipeng0427.log','query_tm '.microtime().' '.__LINE__."\n",FILE_APPEND);
	//该活动是否还有号码
	if($active_info['used_cnt'] >= $active_info['num_cnt']){
		writeCache($num_key,4);
		return;
	}
	//该用户是否还有号码配额
	//file_put_contents('/tmp/lipeng0427.log','query_tm '.microtime().' '.__LINE__."\n",FILE_APPEND);
	$type = $type_map[$active_info['active_type']];
	if($type == 'day'){	
		$key = "day_active{$active_id}_date{$date}_usedcnt"; //按天限量
	}else if($type == 'ip'){
		$key = "ip_active{$active_id}_ip{$ip}_usedcnt"; //按ip限量
	}else if($type == 'sum'){
		$key = "sum_active{$active_id}_usedcnt"; //按总量限量
	}else if($type == "uid"){
		$key = "uid_active{$active_id}_u{$uid}_usedcnt";//按用户id限量
	}
	$params = array('key' => $key,'type' => $type, 'date' => $date,'now_time' => $now_time,'ip' => $ip,'uid' => $uid);
	$usecnt = get_active_usedcnt($active_id,$params);
	//file_put_contents('/tmp/lipeng0427.log','query_tm '.microtime().' '.__LINE__."\n",FILE_APPEND);
	if($active_info['conf_cnt'] > 0){
		if($usecnt >= $active_info['conf_cnt']){
			writeCache($num_key,5);
			return;
		}
        }
	$params = array('key' => $key,'num_key' => $num_key,'type' => $type,'ip' => $ip,'take_tm' => $now_time,'uid' => $uid, 'date' => $date, 'now_time' => $now_time);
	get_game_number($active_id,$params,$active_info); //获取号码	
	//file_put_contents('/tmp/lipeng0427.log','end_tm '.date('Y-m-d H:i:s',time())."\n",FILE_APPEND);
} 
//获取活动信息
function get_active_info($active_id){
	global $db;
	$model = new GoModel();
	$option = array(
		'table' => 'sendnum_active',
		'where' => array(
			'id' => $active_id,
		),
	);
	$result = $model -> findOne($option,$db);
	return $result;
}
//用户本活动中获取号码的次数
function get_active_usedcnt($active_id,$params){
	global $db;
	extract($params);
	$option = array(
		'table' => 'sendnum_number',
		'where' => array(
			'active_id' => $active_id,
			'status' => 1,
		),
		'field' => 'count(id) as cnt',
	);
	if($type == 'day'){	
		$option['where']['take_tm'] = array('exp','>='.strtotime(date('Y-m-d',$now_time)).' and take_tm <= '.strtotime(date('Y-m-d',$now_time).' 23:59:59'));  	
	}else if($type == 'ip'){
		$option['where']['ip'] = $ip;
	}else if($type == "uid"){
		$option['where']['uid'] = $uid;
	}
	$usedcnt = getCache($key);
	if($usedcnt) return $usedcnt;
	$model = new GoModel();
	$result = $model -> findOne($option,$db);
	writeCache($key,$result['cnt']);
	file_put_contents('/tmp/lpcache54.log',$key.':'. $result['cnt'].'\n',FILE_APPEND);
	return $result['cnt'];
}
//获取号码
function get_game_number($active_id,$params,$active_info=array()){
	global $db;
	extract($params);
	$model = new GoModel();
	$option = array(
		'table' => 'sendnum_number',
		'where' => array(
			'active_id' => $active_id,
			'status' => 0,
		),
		'order' => 'id desc',
	
	);
	$info = $model -> findOne($option,$db);
	if(!$info) {
		writeCache($num_key,5);
		return;
	}
	$affect = $model -> update(array('id' => $info['id']), array('status' => '1','take_tm' => $take_tm,'ip' => $ip,'uid' => $uid,'__user_table' =>'sendnum_number'),$db);
	if($affect){
		$affect = $model -> query('update sendnum_active set used_cnt = used_cnt + 1 where id = '.$active_id,$db);
		if($affect){
			$cnt = getCache($key);
			if($cnt){
				$cnt++;
				writeCache($key,$cnt);
			}else{
				global $type;
				$cnt = get_active_usedcnt($active_id,$params);
				writeCache($key,$cnt);
			}
		}
		writeCache($num_key,$active_info['active_name'] . ':' . $info['active_num']);
	}else{
		writeCache($num_key,5); //获取号码失败
	}
}

function getCache($key)
{
    $config = load_config('cache/sendnum_memcached');
	$cacheObj = new GoMemcachedCacheAdapter($config);
	return $cacheObj -> get($key);
}

function writeCache($key, $val,$time=600) {
    $config = load_config('cache/sendnum_memcached');
	$cacheObj = new GoMemcachedCacheAdapter($config);
	return $cacheObj -> set($key,$val,$time);
}
