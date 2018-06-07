<?php
include_once (dirname(realpath(__FILE__)).'/../../init.php');
$config = load_config('lottery_cache/redis',"lottery");
if ($config) {
	$redis = new GoRedisCacheAdapter($config);
} else {
	$redis = GoCache::getCacheAdapter('redis');
}
$prefix		=	"seckill";
$active_id	=	$_GET['aid'] ? $_GET['aid'] : $_POST['aid'];
//ctype_digit	检查时候是只包含数字字符的字符串（0-9）
if(!ctype_digit($active_id)){
	exit;
}
$sid = $_GET['sid'] ? $_GET['sid'] : $_POST['sid'];

$model = new GoModel();
if($configs['is_test'] == 1 ) {
	//$time  = get_now_time();
	$time  = time();
}else {
	$time  = time();
}

//获取host
$activity_host = $configs['activity_url'];
if($_GET['stop'] != 1 ) {
	$activity_tm = activity_is_stop($active_id);
	if(!$activity_tm) {
		$url = $activity_host."/lottery/{$prefix}/receive_coupon.php?stop=1&aid=".$active_id;
		header("Location: {$url}");
	}
}

session_begin($sid);
$build_query = http_build_query($_GET);
if( $configs['is_test'] ) {
	$h_str = 'dev.';
}

$center_url = "http://".$h_str."i.anzhi.com/mweb/account/login?serviceId=014&serviceVersion=5410&serviceType=0&redirecturi=";
$login_url = $center_url.$activity_host."/lottery/{$prefix}/receive_coupon.php?".$build_query;
$tplObj -> out['login_url'] = $login_url;
if($_POST['is_receive']){
	if(!$_SESSION['USER_UID']){
		exit(json_encode(array('code'=>0,'msg'=>'登录失效请重新登录！')));
	}
	$res = receive_coupon($_POST['level'],$_SESSION['USER_UID']);
	exit($res);
}else if($_POST['is_down'] == 1){
	list($down,$open,$receive) = save_pkg_status($_SESSION['USER_UID'],$_POST['level'],1);
	exit(json_encode(array('down'=>$down,'open'=>$open,'receive'=>$receive)));
}else if($_POST['is_open']){
	list($down,$open,$receive) = save_pkg_status($_SESSION['USER_UID'],$_POST['level'],0,1);
	exit(json_encode(array('down'=>$down,'open'=>$open,'receive'=>$receive)));
}else{
	if($_GET['my_prize'] == 1){
		my_prize();
	}else{
		index();
	}
}
function index(){
	global $prefix,$active_id,$tplObj,$configs,$time,$sid,$redis;
	//日志
	$log_data = array(
		"imsi" => $_SESSION['USER_IMSI'],
		"device_id" => $_SESSION['DEVICEID'],
		"activity_id" => $active_id,
		"ip" => $_SERVER['REMOTE_ADDR'],
		"sid" => $sid,
		"time" => $time,
		'product' => $_SESSION['product'],//1市场 13 sdk
		"user" => $_SESSION['USER_UID'] && $_SESSION['USER_ID'] != 13176 ? $_SESSION['USER_NAME'] : '',
		'uid'=> $_SESSION['USER_UID'] && $_SESSION['USER_ID'] != 13176 ? $_SESSION['USER_UID'] : '',
		'key' => 'show_homepage'
	);
	permanentlog('activity_'.$active_id.'.log', json_encode($log_data));	
	user_loging_new();
	if(isset($_SESSION['USER_UID']) && $_SESSION['USER_ID'] != 13176){//已登录
		//登录日志
		$log_data = array(
			'uid' => $_SESSION['USER_UID'],
			'imsi' => $_SESSION['USER_IMSI'],
			'device_id' => $_SESSION['DEVICEID'],
			'activity_id' => $active_id,
			'ip' => $_SERVER['REMOTE_ADDR'],
			'sid' => $sid,
			'time' => $time,
			'key' => 'login'
		);
		permanentlog('activity_'.$active_id.'.log', json_encode($log_data));
		$uid = $_SESSION['USER_UID'];
		$tplObj -> out['username'] = $_SESSION['USER_NAME'];
		$tplObj -> out['is_login'] = 1;
		$tplObj -> out['uid'] = $uid;
	}else {//未登录
		$tplObj -> out['is_login'] = 2;
	}
	if($_GET['stop'] == 1){
		$tpl = "lottery/{$prefix}/receive_coupon_end.html";
	}else{
		$soft_info = get_soft_info($configs['is_test']);
		list($config,$tm_config) = get_config($configs['is_test']);
		$tplObj -> out['tm_config_arr']  =  $tm_config;
		$tplObj -> out['config_arr']  =  $config;
		$tplObj -> out['day']  =  date("Ymd",$time);
		$tplObj -> out['tomorrow']  =  date("Ymd",$time+86400);
		$tplObj -> out['soft_info']  =  $soft_info;
		$tplObj -> out['json_soft_info']  =  json_encode($soft_info);
			
		$tpl = "lottery/{$prefix}/receive_coupon.html";
	}
	$tplObj -> out['aid'] = $active_id;
	$tplObj -> out['is_share'] = $_GET['is_share'];
	$tplObj -> out['sid'] = $_GET['sid'];
	$tplObj -> out['stop'] = $_GET['stop'];
	$tplObj -> out['prefix'] = $prefix;
	$tplObj -> out['static_url'] = $configs['static_url'];
	$tplObj -> out['new_static_url'] = $configs['new_static_url'];
	$tplObj -> out['activity_share_url'] = $configs['activity_share_url'];
	$tplObj -> out['version_code'] = $_SESSION['VERSION_CODE'];
	$tplObj -> out['is_test'] = $configs['is_test'];
	$tplObj -> out['chl_cid'] = $configs['is_test'] ? '4fb52a893294' : '4c6cbe964779';
	$tplObj -> out['product'] = $_SESSION['product'] ? $_SESSION['product'] : $_GET['product'] ;//1市场 13 sdk
	$tplObj -> display($tpl);
}

function prize_info(){
	global $prefix,$active_id,$tplObj,$configs,$time,$sid,$redis,$model;
	$prize_info_key = $prefix.":".$active_id.':prize_info';
	$prize_info = $redis->get($prize_info_key);		
	if($prize_info  === null){	
		$option = array(
			'where' => array(
				'status' => 1,
				'aid' => $active_id,
			),
			'table' => 'valentine_draw_prize',
			'field' => 'id,`desc`',
		);
		$list = $model->findAll($option, 'lottery/lottery');
		if(!$list) return false;
		$prize_info = array();
		foreach($list as $k => $v){
			$prize_info[$v['id']] = json_decode($v['desc'],true);
		}
		$redis->set($prize_info_key,$prize_info,86400);		
	}
	return 	$prize_info;
}
function my_prize(){
	global $prefix,$active_id,$tplObj,$configs,$time,$sid,$redis,$model;
	$uid = $_SESSION['USER_UID'];
	$my_prize_key = $prefix.":".$active_id.':my_prize:'.$uid;
	$my_prize = $redis->get($my_prize_key);	
	if($my_prize  === null){
		$prize_info = prize_info();	
		$option = array(
			'where' => array(
				'uid' => $uid,
				'aid' => $active_id,
			),
			'table' => 'valentine_draw_award',
		);
		$list = $model->findAll($option, 'lottery/lottery');
		$my_prize = array();
		foreach($list as $key => $val){
			$my_prize[$key]['prizename'] = $val['prizename'];
			$my_prize[$key]['appDomainRemark'] = $prize_info[$val['pid']]['appDomainRemark'];//适用范围
			$my_prize[$key]['couponAmount'] = $prize_info[$val['pid']]['couponAmount'];//礼券金额
			$my_prize[$key]['memo'] = $prize_info[$val['pid']]['memo'];
		}			
		$redis->set($my_prize_key,$my_prize,30*60);	
	}
	$tplObj -> out['my_prize'] = $my_prize;
	$tplObj -> out['aid'] = $active_id;
	$tplObj -> out['is_share'] = $_GET['is_share'];
	$tplObj -> out['sid'] = $sid;
	$tplObj -> out['stop'] = $_GET['stop'];
	$tplObj -> out['prefix'] = $prefix;
	$tplObj -> out['static_url'] = $configs['static_url'];
	$tplObj -> out['new_static_url'] = $configs['new_static_url'];
	$tplObj -> out['activity_share_url'] = $configs['activity_share_url'];
	$tplObj -> out['version_code'] = $_SESSION['VERSION_CODE'];
	$tplObj -> out['username'] = $_SESSION['USER_NAME'];
	$tpl = "lottery/{$prefix}/receive_coupon_prize.html";
	$tplObj -> display($tpl);
}
function pkg_cofing(){
	global $active_id;	
	if($active_id == 1075 || $active_id == 1086){
		$pkg_arr = array(
			4 => 'com.netease.wdsj.yyxx.anzhi',
			5 => 'com.games.hc.acing.anzhi',
			6 => 'com.tencent.tmgp.sgame',
			7 => 'com.eglsgamea.ah2.anzhi',
			8 => 'com.pokercity.bydrqp.anzhi',
			9 => 'com.jiguang.dtszj.anzhi',
		);
	}else if($active_id == 1125){
		$pkg_arr = array(
			4 => 'com.netease.wdsj.yyxx.anzhi',
			5 => 'com.games.hc.acing.anzhi',
			6 => 'com.tencent.tmgp.sgame',
			7 => 'com.eglsgamea.ah2.anzhi',
			8 => 'com.pokercity.bydrqp.anzhi',
			9 => 'com.jiguang.dtszj.anzhi',
		);		
	}
}
function get_config($is_test){
	global $model,$redis,$prefix,$active_id;
	$uid = $_SESSION['USER_UID'];
	$config_key = $prefix.":".$active_id.':config:'.$uid;
	$config = $redis->get($config_key);	
	$tm_config_key = $prefix.":".$active_id.':tm_config:'.$uid;
	$tm_config = $redis->get($tm_config_key);	
	if($config === null || $tm_config === null){	
		$option = array(
			'where' => array(
				'id' => $active_id,
			),
			'table' => 'sj_activity',
			'field' => 'name,activity_page_id,activity_end_id,start_tm,end_tm,channel_id',
			//'cache_time' => 30*60
		);
		$activity = $model->findOne($option);
		$pkg_arr = pkg_cofing();
		$config = array(
			4 => array(
				'day' => date('Ymd',$activity['start_tm']),
				'pkg' => $is_test ? 'com.mindblocks.blocks.anzhi' : $pkg_arr[4],
				'is_receive' => 0,
				'is_down' => 0,
				'is_open' => 0,
			),
			5 => array(
				'day' => date('Ymd',$activity['start_tm']),
				'pkg' => $is_test ? 'com.dfgx.dpcq.anzhi' : $pkg_arr[5],
				'is_receive' => 0,		
				'is_down' => 0,
				'is_open' => 0,				
			),
			6 => array(
				'day' => date('Ymd',$activity['start_tm']+86400),
				'pkg' => $is_test ? 'com.bbd.union.anzhi' : $pkg_arr[6],
				'is_receive' => 0,		
				'is_down' => 0,
				'is_open' => 0,				
			),
			7 => array(
				'day' => date('Ymd',$activity['start_tm']+86400),
				'pkg' => $is_test ? 'com.dddwan.summer.anzhi' : $pkg_arr[7],		
				'is_receive' => 0,	
				'is_down' => 0,
				'is_open' => 0,				
			),
			8 => array(
				'day' => date('Ymd',$activity['start_tm']+2*86400),
				'pkg' => $is_test ? 'com.ijinshan.kbatterydoctor' : $pkg_arr[8],
				'is_receive' => 0,			
				'is_down' => 0,
				'is_open' => 0,				
			),
			9 => array(
				'day' => date('Ymd',$activity['start_tm']+2*86400),
				'pkg' => $is_test ? 'org.wwlyl.food' : $pkg_arr[9],
				'is_receive' => 0,		
				'is_down' => 0,
				'is_open' => 0,				
			),
		);	
		$redis->set($config_key,$config,30*86400);	
		$tm_config = array(
			1 => array(
				'day' => date('Ymd',$activity['start_tm']),
				'pic' => '3元礼券',		
				'is_receive' => 0,					
			), 
			2 => array(
				'day' => date('Ymd',$activity['start_tm']+86400),
				'pic' => '15元礼券',
				'is_receive' => 0,					
			),
			3 => array(
				'day' => date('Ymd',$activity['start_tm']+2*86400),
				'pic' => '45元礼券',
				'is_receive' => 0,					
			),
		);
		$redis->set($tm_config_key,$tm_config,30*86400);	
	}
	return array($config,$tm_config);
}
function get_soft_info($is_test){
	global $model,$redis,$prefix,$active_id;
	$soft_info_key = $prefix.":".$active_id.':soft_info';
	$soft_info = $redis->get($soft_info_key);	
	if($soft_info === null){
		if($is_test){
			$pkg_arr = array(
				'com.mindblocks.blocks.anzhi' => 4,
				'com.dfgx.dpcq.anzhi' => 5,
				'com.bbd.union.anzhi' => 6,
				'com.dddwan.summer.anzhi' => 7,
				'com.ijinshan.kbatterydoctor' => 8,
				'org.wwlyl.food' => 9,
			);
		}else{
			$pkg_arr = pkg_cofing();
			$pkg_arr = array(
				$pkg_arr[4] => 4,
				$pkg_arr[5] => 5,
				$pkg_arr[6] => 6,
				$pkg_arr[7] => 7,
				$pkg_arr[8] => 8,
				$pkg_arr[9] => 9,
			);
		}
		$option = array(
			'table' => 'sj_soft AS A',
			'where' => array(
				'A.status' => 1,
				'A.hide' => 1,
				'A.package' => array_keys($pkg_arr),
				'B.package_status' => 1,
			),
			'join' => array(
				'sj_soft_file AS B' => array(
					'on' => array('A.softid','B.softid'),
				)
			),
			'field' => 'A.softid,A.softname,A.package,A.version_code,A.version_code,A.total_downloaded,B.iconurl_125,B.filesize',
			'order' => 'A.softid desc',
			'group' => 'A.package',
		);	
		$softinfo = $model->findAll($option);

		$soft_info = array();
		foreach($softinfo as $k => $v){
			$v['iconurl'] = getImageHost().$v['iconurl_125'];
			$v['level'] = $pkg_arr[$v['package']];
			$soft_info[$v['package']] = $v;
		}
		$redis->set($soft_info_key,$soft_info,30*60);	
	}
	return $soft_info;
}
function receive_coupon($level,$uid){
	global $redis,$prefix,$active_id,$time,$sid;
	$receive_key	= "{$prefix}:{$active_id}:receive:{$uid}:{$level}";
	$receive = $redis->setnx($receive_key,1,30*86400);
	//用户领取日志
	$log_data = array(
		'time'	=>	$time,
		'imsi'	=>	$_SESSION['USER_IMSI'],
		'uid'	=>	$uid,
		'sid' => $sid,	
		'username'	=>	$_SESSION['USER_NAME'],
		'device_id'	=>	$_SESSION['DEVICEID'],
		"DEVICE_SN" => $_SESSION['DEVICE_SN'],
		'activity_id'	=>	$active_id,
		'level'	=>	$level,
		'key'	=>	'receive'
	);
	if(!$receive){
		$log_data['msg'] = '已领过请不要重复领取';
		permanentlog('activity_'.$active_id.'.log', json_encode($log_data));	
		return json_encode(array('code'=>0,'msg'=>'已领过请不要重复领取'));
	}else{
		load_helper('task');
		$task_client = get_task_client();
		$new_array=array(
			'prefix' => $prefix,
			'uid'	 =>	$uid,
			'aid'  => $active_id,
			'username' => $_SESSION['USER_NAME'],
			'position' => $level,
			'activityName' => "领礼券活动",
			'table' => 'valentine_draw_prize',
			'table_award' => 'valentine_draw_award',
		);
		$the_award = $task_client->do('lssue_prize', json_encode($new_array));
		$res = json_decode($the_award,true);
		if($res['code'] == 0){
			$log_data['msg'] = $res['msg'];
			permanentlog('activity_'.$active_id.'.log', json_encode($log_data));	
			exit(json_encode(array('code'=>0,'msg'=>$res['msg'])));
		}		
		if($level > 3){
			$config_key = $prefix.":".$active_id.':config:'.$uid;
			$config = $redis->get($config_key);
			$config[$level]['is_receive'] = 1;
			$redis->set($config_key,$config,30*86400);				
		}else{
			$tm_config_key = $prefix.":".$active_id.':tm_config:'.$uid;
			$tm_config = $redis->get($tm_config_key);
			$tm_config[$level]['is_receive'] = 1;
			$redis->set($tm_config_key,$tm_config,30*86400);				
		}
		permanentlog('activity_'.$active_id.'.log', json_encode($log_data));
		//用户领取成功日志
		$log_data = array(
			'time'	=>	$time,
			'imsi'	=>	$_SESSION['USER_IMSI'],
			'uid'	=>	$uid,
			'sid'	=>	$sid,
			'username'	=>	$_SESSION['USER_NAME'],
			'device_id'	=>	$_SESSION['DEVICEID'],
			"DEVICE_SN" => $_SESSION['DEVICE_SN'],
			'activity_id'	=>	$active_id,
			'level'	=>	$level,	
			'type'	=>	$res['type'],	
			"award_name" => $res['prizename'],
			'key'	=>	'receive_success'
		);
		permanentlog('activity_'.$active_id.'.log', json_encode($log_data));	
		$my_prize_key = $prefix.":".$active_id.':my_prize:'.$uid;
		$redis->delete($my_prize_key);			
		return $the_award;	
	}	
}
function save_pkg_status($uid,$level,$down=0,$open=0){
	global $redis,$prefix,$active_id;
	$config_key = $prefix.":".$active_id.':config:'.$uid;
	$config = $redis->get($config_key);
	if($down == 1) $config[$level]['is_down'] = 1;
	if($open == 1) $config[$level]['is_open'] = 1;
	$redis->set($config_key,$config,30*86400);
	return array($config[$level]['is_down'],$config[$level]['is_open'],$config[$level]['is_receive']);	
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