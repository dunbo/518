<?php
include_once ('./init.php');
$config = load_config('lottery_cache/redis',"lottery");
if ($config) {
	$redis = new GoRedisCacheAdapter($config);
} else {
	$redis = GoCache::getCacheAdapter('redis');
}
$model = new GoModel();
$sid = $_GET['sid'] ? $_GET['sid'] : $_POST['sid'];
session_begin($sid);
$prefix = "competitive_competition";
$time  = time();


if($_GET['logout']==1){
    session_destroy();
    header("Location: http://m.test.anzhi.com/competitive_competition.php");
}

//获取host
$activity_host = $configs['activity_url'];
$build_query = http_build_query($_GET);
if( $configs['is_test'] ) {
	$h_str = 'dev.';
}
//是否为手机端
$ismobile = isMobile();
if($ismobile){
	$m_str = 'm';
	$serviceType = '0';
}else{
	$serviceType = '1';
}
$center_url = "http://".$h_str."i.anzhi.com/".$m_str."web/account/login?serviceId=014&serviceVersion=5410&serviceType=".$serviceType."&redirecturi=";
$center2_url = "http://".$h_str."i.anzhi.com/web/account/register?serviceId=005&serviceVersion=1.0&serviceType=1&redirecturi=";
$center3_url = "http://".$h_str."i.anzhi.com/".$m_str."web/account/logout?serviceId=005&serviceVersion=1.0&serviceType=".$serviceType."&redirecturi=";
$login_url = $center_url.$activity_host."/{$prefix}.php?".$build_query;
$register_url = $center2_url.$activity_host."/{$prefix}.php?".$build_query;
$out_url = $center3_url.$activity_host."/{$prefix}.php?".$build_query;
$tplObj -> out['login_url'] = $login_url;
$tplObj -> out['register_url'] = $register_url;
$tplObj -> out['out_url'] = $out_url;
$tplObj -> out['activity_host'] = $activity_host;

if($_POST['is_log']){
	$log_data = array(
		"ip" => $_SERVER['REMOTE_ADDR'],
		"time" => $time,
		"user" => $_SESSION['USER_UID'] && $_SESSION['USER_ID'] != 13176 ? $_SESSION['USER_NAME'] : '',
		'uid'=> $_SESSION['USER_UID'] && $_SESSION['USER_ID'] != 13176 ? $_SESSION['USER_UID'] : '',
		'ismobile' => $ismobile,//true手机端
		'type' => $_POST['type'],//1立即下载2立即报名3四人赛4单人赛 copy复制分享 tsina分享到新浪 qzone分享到QQ空间 sqq分享到QQ好友 weixin 分享到微信
		'key' => 'click'
	);
	permanentlog($prefix.'.log', json_encode($log_data));
	exit;
}else if($_POST['info'] == 1){
	if(!$_SESSION['USER_UID']) exit(json_encode(array('code'=>2,'msg'=> $login_url)));
	$json_str = json_encode($_POST);
	$log_data = array(
		"ip" => $_SERVER['REMOTE_ADDR'],
		"time" => $time,
		"user" => $_SESSION['USER_UID'] && $_SESSION['USER_ID'] != 13176 ? $_SESSION['USER_NAME'] : '',
		'uid'=> $_SESSION['USER_UID'] && $_SESSION['USER_ID'] != 13176 ? $_SESSION['USER_UID'] : '',
		'data' => $json_str,//type 3四人赛4单人赛
		'ismobile' => $ismobile,//true手机端		
		'key' => 'info'
	);
	permanentlog($prefix.'.log', json_encode($log_data));
	//用户信息添加
	add_info($json_str);
	exit(json_encode(array('code'=>1)));
}else if($_POST['is_open']){
	list($down,$open,$receive) = save_pkg_status($_SESSION['USER_UID'],$_POST['level'],0,1);
	exit(json_encode(array('down'=>$down,'open'=>$open,'receive'=>$receive)));
}else{
	user_loging_new();
	if($_GET['type'] == 3 || $_GET['type'] == 4){
		info();
	}else{
		index();
	}
}
function index(){
	global $prefix,$tplObj,$configs,$time,$redis,$ismobile;
	//日志
	$log_data = array(
		"ip" => $_SERVER['REMOTE_ADDR'],
		"time" => $time,
		"user" => $_SESSION['USER_UID'] && $_SESSION['USER_ID'] != 13176 ? $_SESSION['USER_NAME'] : '',
		'uid'=> $_SESSION['USER_UID'] && $_SESSION['USER_ID'] != 13176 ? $_SESSION['USER_UID'] : '',
		'ismobile' => $ismobile,//true手机端
		'key' => 'show_homepage'
	);
	permanentlog($prefix.'.log', json_encode($log_data));	
	if(isset($_SESSION['USER_UID']) && $_SESSION['USER_ID'] != 13176){//已登录
		//登录日志
		$log_data = array(
			'uid' => $_SESSION['USER_UID'],
			'ip' => $_SERVER['REMOTE_ADDR'],
			'time' => $time,
			'ismobile' => $ismobile,//true手机端
			'key' => 'login'
		);
		permanentlog($prefix.'.log', json_encode($log_data));
		$uid = $_SESSION['USER_UID'];
		$tplObj -> out['username'] = $_SESSION['USER_NAME'];
		$tplObj -> out['is_login'] = 1;
		$tplObj -> out['uid'] = $uid;
	}else {//未登录
		$tplObj -> out['is_login'] = 2;
	}
	if($ismobile){
		$tpl = "{$prefix}/index_wap.html";
	}else{
		$tpl = "{$prefix}/index.html";
	}
	$tplObj -> out['is_share'] = $_GET['is_share'];
	$tplObj -> out['stop'] = $_GET['stop'];
	$tplObj -> out['prefix'] = $prefix;
	$tplObj -> out['static_url'] = $configs['static_url'];
	$tplObj -> out['new_static_url'] = $configs['new_static_url'];
	$tplObj -> out['activity_share_url'] = $configs['activity_share_url'];
	$tplObj -> out['version_code'] = $_SESSION['VERSION_CODE'];
	$tplObj -> out['is_test'] = $configs['is_test'];
	$tplObj -> out['is_weixin'] = is_weixin();
	$tplObj -> out['qq_group'] = get_qq_group();	
	$tplObj -> display($tpl);
}

function get_softname(){
	$pkg_arr = array(
		'com.netease.hyxd.yyxx.anzhi' => "荒野行动-空降开黑",
	);
	return $pkg_arr;
}

function info(){
	global $prefix,$tplObj,$configs,$time,$redis,$model,$ismobile;
	$uid = $_SESSION['USER_UID'];
	$type = $_GET['type'];
	$pkg = $_GET['pkg'];
	$info = get_info();
	$tplObj -> out['info'] = $info;
	$softname = get_softname();
	$tplObj -> out['softname'] = $softname[$pkg];
	$tplObj -> out['type'] = $type;
	$tplObj -> out['pkg'] = $pkg;
	$tplObj -> out['prefix'] = $prefix;
	$tplObj -> out['static_url'] = $configs['static_url'];
	$tplObj -> out['new_static_url'] = $configs['new_static_url'];
	$tplObj -> out['activity_share_url'] = $configs['activity_share_url'];
	$tplObj -> out['version_code'] = $_SESSION['VERSION_CODE'];
	$tplObj -> out['username'] = $_SESSION['USER_NAME'];
	if($_GET['type'] == 3){
		if($ismobile){
			$tpl = "{$prefix}/info_wap.html";
		}else{
			$tpl = "{$prefix}/info.html";
		}
	}else{
		if($ismobile){
			$tpl = "{$prefix}/info2_wap.html";
		}else{
			$tpl = "{$prefix}/info2.html";
		}
	}
	$school_config = array(
		1 => '山西工商学院',
		2 => '太原科技大学',
		3 => '晋中职业技术学院',
	);
	$tplObj -> out['school_config'] = $school_config;
	$tplObj -> out['qq_group'] = json_encode(get_qq_group());
	$area_arr = array(
		'bj' => '北京',
		'sh' => '上海',
		'gz' => '广州',
		'tj' => '天津',
	);
	$tplObj -> out['area_config'] = $area_arr;
	if(isset($_SESSION['USER_UID']) && $_SESSION['USER_ID'] != 13176){//已登录
		$tplObj -> out['is_login'] = 1;
	}else {//未登录
		$tplObj -> out['is_login'] = 2;
	}	
	$area_arr = array(
		'bj' => '北京',
		'sh' => '上海',
		'gz' => '广州',
		'tj' => '天津',
		'sx' => '山西',
	);
	$tplObj -> out['area_config'] = $area_arr;
	if(isset($_SESSION['USER_UID']) && $_SESSION['USER_ID'] != 13176){//已登录
		$tplObj -> out['is_login'] = 1;
	}else {//未登录
		$tplObj -> out['is_login'] = 2;
	}	
	$tplObj -> display($tpl);
}
function get_qq_group(){
	$qq_group = array(
		1 => array(
			'status' => '可预约',
			'province' => '山西',
			'city' => '太原',
			'address' => '海淀区上地西路8号上地科技大厦西区802',
			'time' => '2017年12月7日',
			'time_wap' => '2017.12.7',
			'title' => 'AET-山西工商学院',
			'idkey' => 'f17b4e0ac1262c5ab8eb1da891ebc08678ff7fddbafe2dd46b052daf05024bc4',
			'qq' => '636086583',
		),
		2 => array(
			'status' => '可预约',
			'province' => '山西',
			'city' => '太原',	
			'address' => '海淀区上地西路8号上地科技大厦西区802',	
			'time' => '2017年12月7日',			
			'time_wap' => '2017.12.7',		
			'title' => 'AET-太原科技大学',
			'idkey' => 'e28881eee43505990ee9262119421215f88d1b522aa4b6e624aff7251a29b5a2',
			'qq' => '636611031',
		),
		3 => array(
			'status' => '可预约',
			'province' => '山西',
			'city' => '晋中',		
			'address' => '海淀区上地西路8号上地科技大厦西区802',	
			'time' => '2017年12月7日',
			'time_wap' => '2017.12.7',
			'title' => '晋中职业技术学院',
			'idkey' => '9fe9ed2ba4042d000be2d17969104a5f7ab88ef9cefb56a10285c742e9391fc0',
			'qq' => '170641868',
		),
    );	
	return $qq_group;
}
function get_info(){
	global $prefix,$time,$redis,$model;
	$table = "competitive_competition";
	$uid = $_SESSION['USER_UID'];
	$type = $_GET['type'];
	$pkg = $_GET['pkg'];
	$info_key = $prefix.":".$pkg.":info:".$uid.":".$type;
	$info = $redis->get($info_key);		
	if(!$info){
		$where = array(
			'uid' => $uid,
			'package' => $pkg,
			'type' => $type,
		);		
		$option = array(
			'where' => $where,
			'table' => $table,
		);
		$res = $model->findOne($option,'lottery/lottery');
		if(!$res) return false;
		$info = $res['info'];	
		$info = $redis->set($info_key,$info,10*86400);	
	}
	return json_decode($info,true);
}

function add_info($json_str){
	global $prefix,$time,$redis,$model;
	$table = "competitive_competition";
	$uid = $_SESSION['USER_UID'];
	$type = $_POST['type'];
	$pkg = $_POST['pkg'];
	$where = array(
		'uid' => $uid,
		'package' => $pkg,
		'type' => $type,
	);	
	$option = array(
		'where' => $where,
		'table' => $table,
	);
	$res = $model->findOne($option,'lottery/lottery');	
	if(!$res){
        $new_data = array(
			'uid' => $uid,
			'info' => $json_str,
			'package' => $pkg ,
			'type' => $type ,
			'add_tm' => $time,
			'__user_table' => $table
        );	
        $ret =  $model->insert($new_data,'lottery/lottery');		
	}else{
        $new_data = array(
			'info' => $json_str,
			'update_tm' => $time,
			'__user_table' => $table
        );
        $ret =  $model->update($where, $new_data,'lottery/lottery');		
	}
	$info_key = $prefix.":".$pkg.":info:".$uid.":".$type;
	$info = $redis->set($info_key,$json_str,10*86400);		
}

/*移动端判断*/
function isMobile(){ 
    // 如果有HTTP_X_WAP_PROFILE则一定是移动设备
    if (isset ($_SERVER['HTTP_X_WAP_PROFILE'])){
        return true;
    } 
    // 如果via信息含有wap则一定是移动设备,部分服务商会屏蔽该信息
    if (isset ($_SERVER['HTTP_VIA'])){ 
        // 找不到为flase,否则为true
        return stristr($_SERVER['HTTP_VIA'], "wap") ? true : false;
    } 
    // 脑残法，判断手机发送的客户端标志,兼容性有待提高
    if (isset ($_SERVER['HTTP_USER_AGENT'])){
        $clientkeywords = array ('nokia',
            'sony',
            'ericsson',
            'mot',
            'samsung',
            'htc',
            'sgh',
            'lg',
            'sharp',
            'sie-',
            'philips',
            'panasonic',
            'alcatel',
            'lenovo',
            'iphone',
            'ipod',
            'blackberry',
            'meizu',
            'android',
            'netfront',
            'symbian',
            'ucweb',
            'windowsce',
            'palm',
            'operamini',
            'operamobi',
            'openwave',
            'nexusone',
            'cldc',
            'midp',
            'wap',
            'mobile'
            ); 
        // 从HTTP_USER_AGENT中查找手机浏览器的关键字
        if (preg_match("/(" . implode('|', $clientkeywords) . ")/i", strtolower($_SERVER['HTTP_USER_AGENT']))){
            return true;
        } 
    } 
    // 协议法，因为有可能不准确，放到最后判断
    if (isset ($_SERVER['HTTP_ACCEPT'])){ 
        // 如果只支持wml并且不支持html那一定是移动设备
        // 如果支持wml和html但是wml在html之前则是移动设备
        if ((strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') !== false) && (strpos($_SERVER['HTTP_ACCEPT'], 'text/html') === false || (strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') < strpos($_SERVER['HTTP_ACCEPT'], 'text/html')))){
            return true;
        } 
    } 
    return false;
} 
