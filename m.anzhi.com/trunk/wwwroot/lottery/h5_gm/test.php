<?php
include_once (dirname(realpath(__FILE__)).'/../../init.php');
$config = load_config('lottery_cache/redis',"lottery");
if ($config) {
	$redis = new GoRedisCacheAdapter($config);
} else {
	$redis = GoCache::getCacheAdapter('redis');
}
$prefix		=	"h5_gm";
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
		$url = $activity_host."/lottery/{$prefix}/index2.php?stop=1&aid=".$active_id;
		header("Location: {$url}");
	}
}

session_begin($sid);
$build_query = http_build_query($_GET);
if( $configs['is_test'] ) {
	$h_str = 'dev.';
}

$center_url = "http://".$h_str."i.anzhi.com/mweb/account/login?serviceId=014&serviceVersion=5410&serviceType=0&redirecturi=";
$login_url = $center_url.$activity_host."/lottery/{$prefix}/index2.php?".$build_query;
$tplObj -> out['login_url'] = $login_url;
if(isset($_SESSION['USER_UID']) && $_SESSION['USER_ID'] != 13176){//已登录
	$tplObj -> out['is_login'] = 1;
}else {//未登录
	$tplObj -> out['is_login'] = 2;
}
if($_POST['is_receive']){
	if(!$_SESSION['USER_UID']){
		exit(json_encode(array('code'=>0,'msg'=>'登录失效请重新登录！')));
	}
	$res = receive_coupon($_POST['level'],$_SESSION['USER_UID'],$_POST['pid']);
	exit($res);
}else{	
	if($_GET['my_prize'] == 1){
		my_prize();
	}else if($_GET['gift_list'] == 1){
		gift_list();
	}else if($_GET['gift_detail'] == 1){
		gift_detail();
	}else if($_GET['is_log'] == 1){
		//页面日志
		$log_data = array(
			"imsi" => $_SESSION['USER_IMSI'],
			"device_id" => $_SESSION['DEVICEID'],
			"activity_id" => $active_id,
			"ip" => $_SERVER['REMOTE_ADDR'],
			"sid" => $sid,
			"time" => $time,
			'package' => $_GET['package'],
			"user" => $_SESSION['USER_UID'] && $_SESSION['USER_ID'] != 13176 ? $_SESSION['USER_NAME'] : '',
			'uid'=> $_SESSION['USER_UID'] && $_SESSION['USER_ID'] != 13176 ? $_SESSION['USER_UID'] : '',
			//1点击礼包 2点击直接玩
			"type" => $_GET['type'],
			'key' => 'click'
		);
		permanentlog('activity_'.$active_id.'.log', json_encode($log_data));
		exit;	
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
	$tplObj -> out['product'] = $_SESSION['product'] ? $_SESSION['product'] : $_GET['product'] ;//1市场 13 sdk
	$tpl = "lottery/{$prefix}/test.html";	
	$tplObj -> out['special_gm'] = get_special_gm();
	$tplObj -> out['FIRMWARE'] = $_SESSION['FIRMWARE'] ;
	$tplObj -> display($tpl);
}

function gift_list(){
	global $prefix,$active_id,$tplObj,$configs,$time,$sid,$redis,$model;
	$list_gift_key = $prefix.":".$active_id.':list_gift';
	$list_gift = $redis->get($list_gift_key);	
	if($list_gift  == null){	
		$option = array(
			'where' => array(
				'aid' => $active_id,
				'status'=>1
			),
			'table' => 'valentine_draw_prize',
			'group'=>'`desc`',
			'field'=>'count(*) as total,`desc`'
		);
		$list = $model->findAll($option, 'lottery/lottery');
		$list_gift = array();
		$appname_arr = get_appname();
		foreach($list as $key => $val){
			$list_gift[$val['desc']]['total'] = $val['total'];	
			$list_gift[$val['desc']]['appname'] = $appname_arr[$val['desc']];	
		}			
		$redis->set($list_gift_key,$list_gift,86400);	
	}
	$tplObj -> out['list_gift'] = $list_gift;
	$tplObj -> out['aid'] = $active_id;
	$tplObj -> out['sid'] = $sid;
	$tplObj -> out['prefix'] = $prefix;
	$tplObj -> out['static_url'] = $configs['static_url'];
	$tplObj -> out['new_static_url'] = $configs['new_static_url'];
	$tplObj -> out['activity_share_url'] = $configs['activity_share_url'];
	$tplObj -> out['version_code'] = $_SESSION['VERSION_CODE'];
	$tplObj -> out['username'] = $_SESSION['USER_NAME'];
	$tpl = "lottery/{$prefix}/gift_list.html";
	$tplObj -> display($tpl);	
}
function gift_detail(){
	global $prefix,$active_id,$tplObj,$configs,$time,$sid,$redis,$model;
	$pkg = $_GET['pkg'] ? $_GET['pkg'] : 'lcby';
	$gift_detail_key = $prefix.":".$active_id.':list_gift_detail:'.$pkg;
	$gift_detail = $redis->get($gift_detail_key);	
	if($gift_detail  == null){	
		$option = array(
			'where' => array(
				'aid' => $active_id,
				'status'=>1,
				'desc'=>$pkg
			),
			'table' => 'valentine_draw_prize',
			'field'=>'`desc`,name,num,level,id'
		);
		$prize = $model->findAll($option, 'lottery/lottery');
		if(!$prize) return false;
		$gift_detail = array();
		foreach($prize  as $k => $v){
			$gift_detail[$v['id']] = $v;
		}
		$redis->set($gift_detail_key,$gift_detail,86400);	
	}
	$tplObj -> out['list_gift'] = $gift_detail;
	$tplObj -> out['receive_status'] = get_receive_status();
	$tplObj -> out['aid'] = $active_id;
	$tplObj -> out['sid'] = $sid;
	$tplObj -> out['pkg'] = $pkg;
	$tplObj -> out['prefix'] = $prefix;
	$tplObj -> out['static_url'] = $configs['static_url'];
	$tplObj -> out['new_static_url'] = $configs['new_static_url'];
	$tplObj -> out['activity_share_url'] = $configs['activity_share_url'];
	$tplObj -> out['version_code'] = $_SESSION['VERSION_CODE'];
	$tplObj -> out['username'] = $_SESSION['USER_NAME'];
	$tpl = "lottery/{$prefix}/gift_detail.html";
	$tplObj -> display($tpl);		
}
function my_prize(){
	global $prefix,$active_id,$tplObj,$configs,$time,$sid,$redis,$model;
	$uid = $_SESSION['USER_UID'];
	$tplObj -> out['prize_list'] = get_receive_status();
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
	$tpl = "lottery/{$prefix}/prize_list.html";
	$tplObj -> display($tpl);
}
function receive_coupon($level,$uid,$pid){
	global $redis,$prefix,$active_id,$time,$sid;
	$pkg = $_POST['pkg'];
	$receive_status_key = $prefix.":".$active_id.":receive_status:".$uid;
	$receive_status = $redis->get($receive_status_key);	
	$receive = $receive_status[$pid]['is_receive'];
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
	
	if($receive){
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
			'activityName' => "h5专题活动",
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
		$gift_detail_key = $prefix.":".$active_id.':list_gift_detail:'.$pkg;
		$gift_detail = $redis->get($gift_detail_key);
		$gift_detail[$pid]['num'] = $gift_detail[$pid]['num']-1;
		$redis->set($gift_detail_key,$gift_detail,86400);			
		$redis->delete($receive_status_key);			
		return $the_award;	
	}	
}
//我的礼包信息
function get_receive_status(){
	global $prefix,$active_id,$tplObj,$configs,$time,$sid,$redis,$model;
	$uid = $_SESSION['USER_UID'];
	$receive_status_key = $prefix.":".$active_id.":receive_status:".$uid;
	$receive_status = $redis->get($receive_status_key);	
	if($receive_status  == null){	
		$option = array(
			'where' => array(
				'aid' => $active_id,
				'status'=>1,
				'uid'=>$uid,
			),
			'table' => 'valentine_draw_award',
			'field'=>'pid,prizename'
		);
		$receive= $model->findAll($option, 'lottery/lottery');
		if(!$receive) return false;
		$receive_status = array();
		foreach($receive as $k => $v){
			list($softname,$gift_number) = explode(":",$v['prizename']);
			$receive_status[$v['pid']]['softname'] = $softname;
			$receive_status[$v['pid']]['gift_number'] = $gift_number;
			$receive_status[$v['pid']]['is_receive'] = 1;
		}
		$redis->set($receive_status_key,$receive_status,86400);	
	}
	return $receive_status;	
}
function get_special_gm(){
	$appname_arr = get_appname();
	//	兄弟聚首,再创霸业!
	$gm_conf = array(
		'jzsc' => array(
			'name' => $appname_arr['jzsc'],
			'short' => '寻昔日兄弟，战热血沙城',
		),
		'cqsjzzjtyh5' => array(
			'name' => $appname_arr['cqsjzzjtyh5'],
			'short' => '传奇世界正版授权，重塑传奇辉煌',
		),
		'lcby' => array(
			'name' => $appname_arr['lcby'],
			'short' => '经典传奇热血演绎，是兄弟战龙城！',
		),
		'xzsc' => array(
			'name' => $appname_arr['xzsc'],
			'short' => '寻昔日兄弟，一起血战沙城！',
		),
		'gwzrzhczz' => array(
			'name' => $appname_arr['gwzrzhczz'],
			'short' => '万人齐聚沙城,再战皇城荣耀之巅',
		),
	);
	//快来一起桃园结义！
	$gm_conf_1 = array(
		'ldsg' => array(
			'name' => $appname_arr['ldsg'],
			'short' => '加入诸侯纷争，一起战个痛快',
		),
		'htsg' => array(
			'name' => $appname_arr['htsg'],
			'short' => '上千名将任你差遣，多重阵法助你重写三国',
		),
		'ylcq' => array(
			'name' => $appname_arr['ylcq'],
			'short' => '腾讯正版授权，第一国战H5游戏',
		),
		'hxsg' => array(
			'name' => $appname_arr['hxsg'],
			'short' => '放置类策略三国，上线即开战！',
		),
	);	
	//	洞天福地，修仙天堂
	$gm_conf_2 = array(
		'xxwc' => array(
			'name' => $appname_arr['xxwc'],
			'short' => '老司机带你修仙带你飞',
		),
		'ysxzzwscq' => array(
			'name' => $appname_arr['ysxzzwscq'],
			'short' => '全新修真世界带来不一样的争霸乐趣',
		),
		'sssj' => array(
			'name' => $appname_arr['sssj'],
			'short' => '首款精品真实多人PK，同屏群战大作',
		),
		//'ssssslthbqc1' => array(
		//	'name' => $appname_arr['ssssslthbqc1'],
		//	'short' => '正版影视授权，白浅夜华邀你仙恋之旅',
		//),
	);	
	//	纯正小说改编
	$gm_conf_3 = array(
		'mhjzwjjy' => array(
			'name' => $appname_arr['mhjzwjjy'],
			'short' => '亿万粉丝翘首以待的纯正莽荒世界',
		),
		'hch5' => array(
			'name' => $appname_arr['hch5'],
			'short' => '沿袭小说经典人设，高度还原剧情',
		),
		'zzdzz' => array(
			'name' => $appname_arr['zzdzz'],
			'short' => '海量小说著名角色带你进入大千世界',
		),
		'xyjkdb' => array(
			'name' => $appname_arr['xyjkdb'],
			'short' => '天兵神将，听侯差遣！',
		),
		'tmld' => array(
			'name' => $appname_arr['tmld'],
			'short' => '唐门六大职业，聚首武定乾坤',
		),
		'hyrzrzds' => array(
			'name' => $appname_arr['hyrzrzds'],
			'short' => '加入鸣人的队伍，重燃热血火影梦',
		),
	);	
	//人人都有个大侠梦
	$gm_conf_4 = array(
		'dxgl' => array(
			'name' => $appname_arr['dxgl'],
			'short' => '国民武侠，你我的江湖梦！',
		),
		'ldjh5' => array(
			'name' => $appname_arr['ldjh5'],
			'short' => '还原经典，邀你体验陈近南之侠义',
		),
		'xkx' => array(
			'name' => $appname_arr['xkx'],
			'short' => '金庸正版授权，经典武侠大作',
		),
	);	
	//游戏风向标，精品推荐
	$gm_conf_5 = array(
		'zxy' => array(
			'name' => $appname_arr['zxy'],
			'short' => '一款神话题材MMORPG即时战斗游戏',
		),
		'xysf' => array(
			'name' => $appname_arr['xysf'],
			'short' => '穿越西游神话，运筹帷幄，晋升首富',
		),
		'sjonline' => array(
			'name' => $appname_arr['sjonline'],
			'short' => '即可交易又可撩妹的游戏',
		),
		'lmybl' => array(
			'name' => $appname_arr['lmybl'],
			'short' => '首款非回合制10P大乱斗，够污你就来！',
		),
		'mjsgj' => array(
			'name' => $appname_arr['mjsgj'],
			'short' => '奇迹之风再次起航,放置中感受经典',
		),
		'kdygdsb' => array(
			'name' => $appname_arr['kdygdsb'],
			'short' => '20年经典，口袋妖怪新作',
		),
		'crsw2' => array(
			'name' => $appname_arr['crsw2'],
			'short' => '首款Q版史诗级塔防策略游戏！',
		),
		'byhzg' => array(
			'name' => $appname_arr['byhzg'],
			'short' => '炫酷奥义，极品神器，称霸暗黑世界',
		),
		'ayzn' => array(
			'name' => $appname_arr['ayzn'],
			'short' => '拉开全服战场,跨服夺宝谁与争锋!',
		),
		'cwjl' => array(
			'name' => $appname_arr['cwjl'],
			'short' => '最经典的精灵，一个个萌萌哒',
		),
		'atmkdb' => array(
			'name' => $appname_arr['atmkdb'],
			'short' => '宇宙英雄奥特曼，全员出击等你战',
		),
		'fbyz' => array(
			'name' => $appname_arr['fbyz'],
			'short' => '告别单一自由搭配，呆萌可爱角色',
		),
		'wydsf' => array(
			'name' => $appname_arr['wydsf'],
			'short' => '赚钱！，就是这么简单！',
		),
		'qmcyzg' => array(
			'name' => $appname_arr['qmcyzg'],
			'short' => '穿越题材的古风冒险和爱恋养成游戏',
		),
		'jdzh' => array(
			'name' => $appname_arr['jdzh'],
			'short' => '动漫《游戏王》改编的卡牌对战手游',
		),
		'dddfw' => array(
			'name' => $appname_arr['dddfw'],
			'short' => '轻轻松松当土豪',
		),
	);

	return array($gm_conf,$gm_conf_1,$gm_conf_2,$gm_conf_3,$gm_conf_4,$gm_conf_5);	
}
function get_appname(){
	global $prefix,$active_id,$redis;
	$appname_key = $prefix.":".$active_id.':appname';
	$appname = $redis->get($appname_key);		
	if($appname  == null){	
		$appname = array(
			'jzsc'=>'决战沙城',
			'cqsjzzjtyh5'=>'传奇世界之仗剑天涯H5',
			'lcby'=>'龙城霸业',
			'ldsg'=>'乱逗三国',
			'htsg'=>'合体三国',
			'ylcq'=>'御龙传奇',
			'xxwc'=>'修仙外传',
			'ysxzzwscq'=>'异世修真之我是传奇',
			'mhjzwjjy'=>'莽荒纪之无尽疆域',
			'hch5'=>'幻城',
			'zzdzz'=>'至尊大主宰',
			'dxgl'=>'大侠归来',
			'ldjh5'=>'鹿鼎记H5',
			'zxy'=>'醉西游',
			'xysf'=>'西游首富',
			'sjonline'=>'世界Online',
			'lmybl'=>'荣耀与远征',
			'mjsgj'=>'魔剑士挂机',
			'kdygdsb'=>'口袋妖怪大师版',
			'crsw2'=>'超人守卫2',
			'byhzg'=>'冰与火之歌',
			'ayzn'=>'暗影之怒',
			'cwjl'=>'宠物精灵',
			'atmkdb'=>'奥特曼口袋版',
			'sssj'=>'蜀山世界',
			'fbyz'=>'风爆远征',
			'xyjkdb'=>'西游记口袋版',
			'wydsf'=>'我要当首富',
			'qmcyzg'=>'全民穿越之宫',
			'hxsg'=>'华夏三国',
			'xzsc'=>'血战沙城',
			'jdzh'=>'决斗之皇',
			'gwzrzhczz'=>'鬼武之刃之皇城至尊',
			'ssssslthbqc1'=>'三生三世十里桃花白浅传',
			'dddfw'=>'点点大富翁',
			'tmld'=>'唐门六道',
			'xkx'=>'侠客行',
			'hyrzrzds'=>'火影忍者',
		);
		$redis->set($appname_key,$appname,86400);	
	}
	return $appname;	
}