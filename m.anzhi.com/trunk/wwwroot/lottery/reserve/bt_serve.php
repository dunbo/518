<?php
include_once (dirname(realpath(__FILE__)).'/../../init.php');
$config = load_config('lottery_cache/redis',"lottery");
if ($config) {
	$redis = new GoRedisCacheAdapter($config);
} else {
	$redis = GoCache::getCacheAdapter('redis');
}
$model = new GoModel();
$time = time();
$active_id = $_GET['aid'] ? $_GET['aid'] : $_POST['aid'];
//ctype_digit  检查时候是只包含数字字符的字符串（0-9）
if(!ctype_digit($active_id)){
	echo '活动id无效';
	exit;
}

$sid = $_GET['sid'] ? $_GET['sid'] : $_POST['sid'];
session_begin($sid);	
$prefix = 'reserve';
//日志
$log_data = array(
	"imsi"	=>	$_SESSION['USER_IMSI'],
	"device_id"		=>	$_SESSION['DEVICEID'],
	"chl_cid" => $_SESSION['CHL_CID'] ? $_SESSION['CHL_CID'] : '',
	"mac" 	=>  $_SESSION['MAC'],
	"ip"	=>	$_SERVER['REMOTE_ADDR'],
	"sid"	=>	$sid,
	"time"	=>	$time,
	"user"	=>	$_SESSION['USER_UID'] && $_SESSION['USER_ID'] != 13176 ? $_SESSION['USER_NAME'] : '',
	'uid'	=>	$_SESSION['USER_UID'] && $_SESSION['USER_ID'] != 13176 ? $_SESSION['USER_UID'] : '',
	'key'	=>	'show_homepage'
);
permanentlog('activity_'.$active_id.'.log', json_encode($log_data));

$tplObj -> out['aid'] = $active_id;
$tplObj -> out['now'] = $time;
$tplObj -> out['sid'] = $_GET['sid'];
$tplObj -> out['static_url'] = $configs['static_url'];
$tplObj -> out['new_static_url'] = $configs['new_static_url'];
$tplObj -> out['version_code'] = $_SESSION['VERSION_CODE'];
$tplObj -> out['img_url']  = getImageHost();
$soft_info = get_soft_info($configs['is_test']);
$tplObj -> out['soft_info']  =  $soft_info;
$tplObj -> out['is_test'] = $configs['is_test'];
$tplObj -> out['prefix'] = $prefix;
if($configs['is_test']){
	$gm_one = array('com.mindblocks.blocks.anzhi','com.dfgx.dpcq.anzhi','com.bbd.union.anzhi');
	$gm_two = array('com.dddwan.summer.anzhi','com.ijinshan.kbatterydoctor','org.wwlyl.food');
	$gm_three = array('cn.jj','com.netease.l10.anzhi','mgyly1.anzhi');
	$gm_four = array('com.knetp.lom2jni','com.syezon.lab.app_acceleratoron','com.vee.project.newspoint' ,'com.dsfreegame.jiebayidai1115');
}else{
	$gm_one = array('com.jiayou.mhsy.anzhi','com.yl.zytl.cyqq.anzhi','com.yhjh.igame.anzhi' );
	$gm_two = array('com.yxgc.fsgj.bt.anzhi','com.shsy.tqsg.cyqq.anzhi','com.shsx.xhsgbt.cyqq.anzhi');
	$gm_three = array('com.Pangaea.fscq.anzhi','com.ailiyou.bzmj.anzhi','com.njxgwl.xxzywqnbt.cyqq.anzhi');
	$gm_four = array('com.kdsmbl.igame.anzhi','com.ssw.igame.anzhi','com.jiayou.xzne.anzhi' ,'com.jmj.smszw.anzhi');	
}
$tplObj -> out['gm_one'] = $gm_one;
$tplObj -> out['gm_two'] = $gm_two;
$tplObj -> out['gm_three'] = $gm_three;
$tplObj -> out['gm_four'] = $gm_four;
$tpl = "lottery/reserve/bt_serve.html";	
$tplObj -> display($tpl);
function get_soft_info($is_test){
	global $model,$redis,$prefix,$active_id;
	$soft_info_key = $prefix.":".$active_id.':soft_info';
	$soft_info = $redis->get($soft_info_key);	
	if($soft_info === null){
		if($is_test){
			$pkg_arr = array(
				'com.mindblocks.blocks.anzhi' => '新回合网游力作 非RMB玩家首选',
				'com.dfgx.dpcq.anzhi' => '秉承沙巴克玩法,不花钱一样牛逼',
				'com.bbd.union.anzhi' => '盛世江湖 上线送VIP5、元宝*8888、铜币*100W',
				'com.dddwan.summer.anzhi' => '神将觉醒，跨服决战！登陆即送顶级神将！来就送V5',
				'com.ijinshan.kbatterydoctor' => '全新三国题材的3D手游，上线送V6',
				'org.wwlyl.food' => 'BT公益服 Q版卡牌RPG游戏',
				'cn.jj' => '上线即送满V10，元宝X66666，金币X100W',
				'com.netease.l10.anzhi' => '暴走游戏官方首款萌系战斗手游',
				'mgyly1.anzhi' => '经典ARPG续作来袭，最纯正的超级格斗手游',			
				'com.knetp.lom2jni' => '亚古兽大战太一，登陆送高V',			
				'com.syezon.lab.app_acceleratoron' =>'犬夜叉正版手游，上线送VIP6 海量钻石',			
				'com.vee.project.newspoint' => '专为宅男订制 上线送VIP15！',			
				'com.dsfreegame.jiebayidai1115' => '上线送VIP4 66666钻石',			
			);
		}else{
			$pkg_arr = array(
				//梦幻神语
				'com.jiayou.mhsy.anzhi' => '上线送12888元宝，极品神宠',
				//斩月屠龙
				'com.yl.zytl.cyqq.anzhi' => '充值比例1:300，上线送VIP4，20000元宝',
				//永恒江湖 
				'com.yhjh.igame.anzhi' => '上线送VIP5，8888元宝，100万铜币',
				//风色轨迹BT 
				'com.yxgc.fsgj.bt.anzhi' => '上线送VIP6，绑钻3888，金币888888',
				//铁骑三国 
				'com.shsy.tqsg.cyqq.anzhi' => '上线送VIP6，9999元宝，20万金币',
				//嘻哈三国BT
				'com.shsx.xhsgbt.cyqq.anzhi' => '上线送VIP12，10万元宝，200万银币',
				//封神传奇 
				'com.Pangaea.fscq.anzhi' => '上线送VIP4，5000元宝',
				//暴走萌将 
				'com.ailiyou.bzmj.anzhi' => '充值比例1:300，上线送1万钻石，500万金币，VIP6',
				//行侠仗义五千年 
				'com.njxgwl.xxzywqnbt.cyqq.anzhi' => '上线送VIP6，8888钻石，100万金币，300点体力',
				//口袋数码暴龙 
				'com.kdsmbl.igame.anzhi' => '上线送VIP6，5000钻石，5万功勋，50万金币',
				//杀生丸-寻玉之旅 
				'com.ssw.igame.anzhi' => '上线送VIP6，6000钻石，百万金币',
				//X战娘二 
				'com.jiayou.xzne.anzhi' => '上线送18888钻石，500万银币，100万金币',
				//数码兽之王BT 
				'com.jmj.smszw.anzhi' => '上线送VIP4，66666元宝',
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
			$v['short'] = $pkg_arr[$v['package']];
			$soft_info[$v['package']] = $v;
		}
		$redis->set($soft_info_key,$soft_info,30*60);	
	}
	return $soft_info;
}