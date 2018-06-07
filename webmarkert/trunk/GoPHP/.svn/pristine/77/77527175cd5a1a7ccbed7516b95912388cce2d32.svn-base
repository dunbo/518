<?php
if (!defined("P_LOG_DIR")) define('P_LOG_DIR', '/data/att/permanent_log');

if (!defined("IS_PAD")) define('IS_PAD', false); //是否 是平板环境


$static_hosts = array("http://apk.goapk.com","http://cmcc.goapk.com", "http://192.168.0.99/testdata");


if (isset($_SERVER['REMOTE_ADDR'])) {
	$remote_addr = $_SERVER['REMOTE_ADDR'];
	$GLOBALS["GO_ENV"] = (strpos($remote_addr, "192.168.0.") === 0 or $remote_addr === "127.0.0.1") ? GO_ENV_DEV : GO_ENV_PRODUCT;

	if ($GLOBALS["GO_ENV"] == GO_ENV_DEV) {
		$config['full_static_host'] = $static_hosts[2];
	}  else {
		$config['full_static_host'] = $static_hosts[0];
	}

} else {
	$GLOBALS["GO_ENV"] = GO_ENV_CLI;
	$config['full_static_host'] = $static_hosts[0];
}

if ($GLOBALS["GO_ENV"] == GO_ENV_DEV) {
    $config['goapk_img_host'] = 'http://192.168.0.99/testdata';
    $config['goapk_apk_host'] = 'http://192.168.0.99/testdata';
    //图片cdn
    $config['web_market_img'] = "";//http://9.img.goapk.com/web
    $config['m_goapk_img']   = ""; //http://9.img.goapk.com/m
    $config['goapk_bbs_url'] = 'http://bbs.anzhi.com';
    $config['aapt_executable_path'] = '/data/www/wwwroot/config/gnu/aapt';
} else {
    $config['goapk_img_host'] = "http://apk.goapk.com";
    $config['goapk_apk_host'] = "http://apk.goapk.com";
    $config['web_market_img'] = "";
    $config['m_goapk_img']     = "";
    $config['goapk_bbs_url'] = 'http://bbs.anzhi.com';
    $config['aapt_executable_path'] = '/data/www/wwwroot/new-wwwroot/config/gnu/aapt';
}

$config['log_level'] = array(GO_INFO);
//$config['log_level'] = array();
if (strpos($remote_addr, "192.168.0.") === 0) {
    $config['log_path'] = '/data/att/gomarket.goapk.com';
    $config['uc_api'] = 'http://bbs.anzhi.com/api/';
    //$config['uc_api'] = 'http://bbs.anzhi.com/api/';
    
} elseif ($remote_addr == '127.0.0.1') {
	$config['log_path'] = '/data/att/gomarket.goapk.com';
	$config['uc_api'] = 'http://bbs.anzhi.com/api/';
	//$config['uc_api'] = 'http://bbs.anzhi.com/api/';
	
} else {
    $config['log_path'] = '/data/att/gomarket.goapk.com';
    $config['uc_api'] = 'http://bbs.anzhi.com/api/';
}


//$config['pay_host'] = "http://dev.pay.anzhi.com:9021";//礼券、礼包接口域名测试地址
$config['pay_host'] = "http://pay.anzhi.com";//礼券、礼包接口域名

$config['static_host'] = $config['full_static_host'];
$config['static_data'] = '/data/att/m.goapk.com';
$config['static_dir'] = '/data3';	//前面要带/
$config['cj_static_data'] = '/data/att/m.goapk.com/data3';//采集data3目录线上用 /data/caiji/gameinfo

$config['default_uid'] = 13176;
$config['default_mid'] = 0;
$config['hide_loose'] = 1;
$config['hide_upgrade'] = 2;

$config['action_install'] = 0;
$config['action_uninstall'] = 1;
$config['action_download'] = 2; //从手机客户端下载
$config['action_web_download'] = 3; //从web市场下载
$config['action_ptn_download'] = 4; //合作方



$config["split_dir"] = "splitapkfile";//分割后文件存储的根目录
$config["split_block"] = 524288;//分割的大小
$config["split_dirname_num"] = 2;//分割的目录名长度

$config['db']['master'] = array(
    'type' => 'mysql',
    'host' => '192.168.0.99',
    'port' => '3306',
    'username' => 'root',
    'password' => 'southpark',
    'database' => 'newgomarket',
    'database' => 'newgomarket', 
    'charset' => 'utf8',
);

$config['db']['gostats'] = array(
    'type' => 'mysql',
    'host' => '192.168.0.99',
    'port' => '3306',
    'username' => 'root',
    'password' => 'southpark',
    'database' => 'newgomarket',
    'charset' => 'utf8',
);

$config['db']['slave'] = array(
    'type' => 'mysql',
    'host' => '192.168.0.99',
    'port' => '3306',
    'username' => 'root',
    'password' => 'southpark',
    'database' => 'newgomarket',
    'charset' => 'utf8',
);

$config['db']['lg'] = array(
	'type' => 'mysql',
    'host' => '192.168.0.99',
    'port' => '3306',
    'username' => 'root',
    'password' => 'southpark',
    'database' => 'lg_tv',
    'charset' => 'utf8',
);

$config['db']['send_num'] = array(
	'type' => 'mysql',
    'host' => '192.168.0.99',
    'port' => '3306',
    'username' => 'root',
    'password' => 'southpark',
    'database' => 'sendNum',
    'charset' => 'utf8',
);

$config['db']['settlement'] = array(
	'type' => 'mysql',
    'host' => '192.168.0.99',
    'port' => '3306',
    'username' => 'root',
    'password' => 'southpark',
    'database' => 'settlement',
    'charset' => 'utf8',
);
$config['db']['cpd_platform'] = array(
	'type' => 'mysql',
    'host' => '192.168.0.99',
    'port' => '3306',
    'username' => 'root',
    'password' => 'southpark',
    'database' => 'cpdPlatform',
    'charset' => 'utf8',
);

$config['db']['inotify'] = array(
	'type' => 'mysql',
    'host' => '192.168.0.99',
    'port' => '3306',
    'username' => 'root',
    'password' => 'southpark',
    'database' => 'operation',
    'charset' => 'utf8',
);

$config['db']['ucenter_daily'] = array(
	'type' => 'mysql',
    'host' => '192.168.0.99',
    'port' => '3306',
    'username' => 'root',
    'password' => 'southpark',
    'database' => 'ucenter',
    'charset' => 'utf8',
);

$config['db']['sphinx'] = array(
    'type' => 'sphinx',
    'host' => '127.0.0.1',
    'port' => '9312',
);

$config['cache']['memcached'] = array(
	array(
		'host' => '192.168.0.99',
		'port' => '11212',
		'read' => true,
		'write' => true,
	),
);

$config['cache']['model_memcached'] = array(
	array(
		'host' => '192.168.0.99',
		'port' => '11211',
		'read' => true,
		'write' => true,
	),
);

$config['cache']['dev'] = array(
	array(
		'host' => '192.168.0.99',
		'port' => '11211',
		'read' => true,
		'write' => true,
	),
);
$config['db']['mm_navi'] = array(
	'type' => 'mysql',
    'host' => '192.168.0.99',
    'port' => '3306',
    'username' => 'root',
    'password' => 'southpark',
    'database' => 'mm_navi',
    'charset' => 'utf8',
);

$config['memcached']['setting'] = array(
	'cron_server' => '192.168.0.99',
);
$config['cache']['redis'] = array(
	array(
		'host' => '192.168.0.99',
		'port' => '6379',
		'read' => true,
		'write' => true,
	),
);

$config['cache']['bi_redis'] = array(
    array(
		'host' => '172.16.1.192',
		'port' => '6381',
		'password' => 'az88Ai',
	),
);

$config['redis']['setting'] = array(
	'cron_server' => '192.168.0.99',
);
$config['cache']['mysql'] = array(
    'dbserver' => 'master',
    'table' => 'cache',
    'key_name' => 'key',
    'value_name' => 'value',
);

$prefix = (GO_SERVER_IP == "192.168.0.99") ? '119.57.50' : '192.168.1';
$config['web_clound'] = array(
    "${prefix}.12",
    "${prefix}.13",
    "${prefix}.14",
    "${prefix}.15",
    "${prefix}.17",
    "${prefix}.27",
);

$config['web_domain'] = array(
	'gomarket.goapk.com',
	'market.goapk.com',
	'www.goapk.com',
	'm.goapk.com',
	'goapk.yakergong.com',
	'www.anzhi.com',
	'anzhi.com',
	'm.anzhi.com',
	'wdj.anzhi.com',
	'uu.anzhi.com',
	'soar.anzhi.com',
	'mole.anzhi.com',
	'wdj.goapk.com',
	'uu.goapk.com',
	'soar.goapk.com',
	'mole.goapk.com',
	'bj.anzhi.com',
	'tcl.anzhi.com',
	'tencent.anzhi.com',
);
$config['download_log_path'] = array();
foreach($config['web_clound'] as $ip) {
	foreach($config['web_domain'] as $domain) {
		$url = "http://${v}:81/{$domain}/";
		$config['download_log_path'][] = $url;
	}
}

$config['merge_log_path'] = array( 
	'/data/www/log/'
);
$config['system'] = array(
    'write_db' => 'master',
    'read_db' => 'slave',
);

$config['cron'] = array(
    'write_db' => 'master',
    'read_db' => 'master',
	'insert_data' => true,
	'server_ip' => '192.168.0.99'
);


/***********************
 * 
 * @sphinx服务器配置
 *  
 */
$config['sphinx_server'] = '192.168.0.99';
$config['sphinx_port'] = '9313';

$config['task_server'] = '192.168.0.99';
$config['clear_worker_list'] =array('118.26.224.12','118.26.224.13','118.26.224.15','118.26.224.17');
$config['task_port'] = '4730';


/*
$config['cdn'] = array(
	'goapk_apk_host' => 'http://apk.goapk.com',//普通频道
	'goapk_img_host' => 'http://img1.anzhi.com',
	
	'apk_host' => array(
		'wap' => array(
			'apktype' => 1,//apk频道类型，1则按照文件大小(11加密)，2按照分类(22加密)，3按照业务类型(33加密)
			
			'large_apk_host' => 'http://lw.apk.anzhi.com',//大apk文件频道
			'small_apk_host' => 'http://mw.apk.anzhi.com',//小apk文件频道
			
			'game_apk_host' => 'http://gw.apk.anzhi.com',//游戏apk频道
			'app_apk_host' => 'http://aw.apk.anzhi.com',//应用apk频道
			
			'logic_apk_host' => 'http://wap.apk.anzhi.com',
			
			
			'large_s_host' => 'http://lw.s.anzhi.com',//大apk文件频道
			'small_s_host' => 'http://mw.s.anzhi.com',//小apk文件频道
			
			'game_s_host' => 'http://gw.s.anzhi.com',//游戏apk频道
			'app_s_host' => 'http://aw.s.anzhi.com',//应用apk频道
			
			'logic_s_host' => 'http://wap.s.anzhi.com',
		),
		
		'gomarket' => array(
			'apktype' => 3,//apk频道类型，1则按照文件大小(11加密)，2按照分类(22加密)，3按照业务类型(33加密)
			
			'large_apk_host' => 'http://lm.apk.anzhi.com',//大apk文件频道
			'small_apk_host' => 'http://mm.apk.anzhi.com',//小apk文件频道
			
			'game_apk_host' => 'http://gm.apk.anzhi.com',//游戏apk频道
			'app_apk_host' => 'http://am.apk.anzhi.com',//应用apk频道
			
			'logic_apk_host' => 'http://market.apk.anzhi.com',
			'logic_apk_host' => 'http://192.168.0.99/testdata',
			
			'large_s_host' => 'http://lm.s.anzhi.com',//大apk文件频道
			'small_s_host' => 'http://mm.s.anzhi.com',//小apk文件频道
			
			'game_s_host' => 'http://gm.s.anzhi.com',//游戏apk频道
			'app_s_host' => 'http://am.s.anzhi.com',//应用apk频道
			
			'logic_s_host' => 'http://market.s.anzhi.com',
		),

		'www' => array(
			'apktype' => 2,//apk频道类型，1则按照文件大小(11加密)，2按照分类(22加密)，3按照业务类型(33加密)
			
			'large_apk_host' => 'http://l.apk.anzhi.com',//大apk文件频道
			'small_apk_host' => 'http://m.apk.anzhi.com',//小apk文件频道
			
			'game_apk_host' => 'http://g.apk.anzhi.com',//游戏apk频道
			'app_apk_host' => 'http://a.apk.anzhi.com',//应用apk频道
			
			'logic_apk_host' => 'http://www.apk.anzhi.com',
			
			'large_s_host' => 'http://l.s.anzhi.com',//大apk文件频道
			'small_s_host' => 'http://m.s.anzhi.com',//小apk文件频道
			
			'game_s_host' => 'http://g.s.anzhi.com',//游戏apk频道
			'app_s_host' => 'http://a.s.anzhi.com',//应用apk频道
			
			'logic_s_host' => 'http://www.s.anzhi.com',
		),		
		
	),
	
	'img_host' => array(
		//wap图片频道
		'wap' => 'http://wap.img.anzhi.com',
		//gomarket图片频道
		'gomarket' => 'http://market.img.anzhi.com',
		'gomarket' => 'http://192.168.0.99/testdata',
		//www图片频道
		'www' => array(
			'http://img1.anzhi.com',
			'http://img2.anzhi.com',
			'http://img3.anzhi.com',
			'http://img4.anzhi.com',
			'http://img5.anzhi.com'
        ),
	),
);
*/
$config['cdn'] = array(
	'goapk_apk_host' => 'http://apk.goapk.com',//普通频道
	'goapk_img_host' => 'http://img1.anzhi.com',
	
	'apk_host' => array(
		'wap' => array(
			'apktype' => 3,//apk频道类型，1则按照文件大小(11加密)，2按照分类(22加密)，3按照业务类型(33加密)
			
			'large_apk_host' => 'http://lw.apk.anzhi.com',//大apk文件频道
			'small_apk_host' => 'http://mw.apk.anzhi.com',//小apk文件频道
			
			'game_apk_host' => 'http://gw.apk.anzhi.com',//游戏apk频道
			'app_apk_host' => 'http://aw.apk.anzhi.com',//应用apk频道
			
			'logic_apk_host' => 'http://wap.apk.anzhi.com',
			'logic_apk_host' => 'http://192.168.0.99/testdata',
			
			
			'large_s_host' => 'http://lw.s.anzhi.com',//大apk文件频道
			'small_s_host' => 'http://mw.s.anzhi.com',//小apk文件频道
			
			'game_s_host' => 'http://gw.s.anzhi.com',//游戏apk频道
			'app_s_host' => 'http://aw.s.anzhi.com',//应用apk频道
			
			'logic_s_host' => 'http://wap.s.anzhi.com',
		),
		
		'gomarket' => array(
			'apktype' => 3,//apk频道类型，1则按照文件大小(11加密)，2按照分类(22加密)，3按照业务类型(33加密)
			
			'large_apk_host' => 'http://lm.apk.anzhi.com',//大apk文件频道
			'small_apk_host' => 'http://mm.apk.anzhi.com',//小apk文件频道
			
			'game_apk_host' => 'http://gm.apk.anzhi.com',//游戏apk频道
			'app_apk_host' => 'http://am.apk.anzhi.com',//应用apk频道
			
			'logic_apk_host' => 'http://market.apk.anzhi.com',
			'logic_apk_host' => 'http://192.168.0.99/testdata',
			
			'large_s_host' => 'http://lm.s.anzhi.com',//大apk文件频道
			'small_s_host' => 'http://mm.s.anzhi.com',//小apk文件频道
			
			'game_s_host' => 'http://gm.s.anzhi.com',//游戏apk频道
			'app_s_host' => 'http://am.s.anzhi.com',//应用apk频道
			
			'logic_s_host' => 'http://market.s.anzhi.com',
		),

		'www' => array(
			'apktype' => 3,//apk频道类型，1则按照文件大小(11加密)，2按照分类(22加密)，3按照业务类型(33加密)
			
			'large_apk_host' => 'http://l.apk.anzhi.com',//大apk文件频道
			'small_apk_host' => 'http://m.apk.anzhi.com',//小apk文件频道
			
			'game_apk_host' => 'http://g.apk.anzhi.com',//游戏apk频道
			'app_apk_host' => 'http://a.apk.anzhi.com',//应用apk频道
			
			'logic_apk_host' => 'http://www.apk.anzhi.com',
			'logic_apk_host' => 'http://192.168.0.99/testdata',
			
			'large_s_host' => 'http://l.s.anzhi.com',//大apk文件频道
			'small_s_host' => 'http://m.s.anzhi.com',//小apk文件频道
			
			'game_s_host' => 'http://g.s.anzhi.com',//游戏apk频道
			'app_s_host' => 'http://a.s.anzhi.com',//应用apk频道
			
			'logic_s_host' => 'http://www.s.anzhi.com',
		),		
		
	),
	
	'img_host' => array(
		//wap图片频道
		'wap' => 'http://wap.img.anzhi.com',
		'wap' => 'http://192.168.0.99/testdata',
		
		//gomarket图片频道
		'gomarket' => 'http://market.img.anzhi.com',
		'gomarket' => 'http://192.168.0.99/testdata',
		//www图片频道
		'www' => array(
			'http://img1.anzhi.com',
			'http://img2.anzhi.com',
			'http://img3.anzhi.com',
			'http://img4.anzhi.com',
			'http://img5.anzhi.com'
        ),
		'www' => 'http://192.168.0.99/testdata'
	),
);









$config['first_icon'] = '%s/img/201205/30/%s_first.png';
$config['cn_icon'] = '%s/img/201205/30/%s_hanzify.png';

//邮件服务器配置
$config['mail'] = array(
        'host' => 'mail.anzhi.com.cn',
        'username' => 'service@anzhi.com.cn',
        'password' => 'azbbs2012',
        'from' => 'service@anzhi.com.cn',
        'fromname' => 'service',
);
//对webmarket 和 wapmarket 安智市场进行特殊处理
$config['market_info'] = array(
		'softname' => '安智市场 V4.0.1',
		'ename' => 'GoMarket v4.0.1',
		'version' => '4.0.1',
		'version_code' => '4002',
		'upload_tm'=>strtotime('2012-08-23'),
		'last_refresh'=>strtotime('2012-08-23'),
);
//对webmarket 和 wapmarket 安智市场进行特殊处理
$config['market_info'] = array(
		'softname' => '安智市场 V4.0.1',
		'ename' => 'GoMarket v4.0.1',
		'version' => '4.0.1',
		'version_code' => '4002',
		'upload_tm'=>strtotime('2012-08-23'),
		'last_refresh'=>strtotime('2012-08-23'),
		'intro' => "
			更新内容如下：
			1.修复之前4.0版本部分机型无法下载的问题；
			2.根据安卓4.0系统设计的全新界面；
			3.优化专题页面，内容更加完整；
			4.优化分类页面，寻找软件更加容易；
			5.新增搜索热词榜和语音搜索功能；
			6.新增'移动到SD卡'功能；
			7.新增手势操作功能；
			8.优化管理界面，管理软件更省心。
		",
		'filesize' => 2029700,
);
$config['filter_api_uri'] = 'http://127.0.0.1/api.php';
$config['db']['lg'] = array(
	'type' => 'mysql',
    'host' => '192.168.0.99',
    'port' => '3306',
    'username' => 'root',
    'password' => 'southpark',
    'database' => 'lg_tv',
    'charset' => 'utf8',
);

$config['db']['newgomarket'] = array(
	'type' => 'mysql',
    'host' => '192.168.0.99',
    'port' => '3306',
    'username' => 'root',
    'password' => 'southpark',
    'database' => 'newgomarket',
    'charset' => 'utf8',
);

//孙涛测试配置
$config['db']['caiji']  = array(
    'type' => 'mysql',
    'host' => '192.168.0.99',
    'port' => '3306',
    'username' => 'root',
    'password' => 'southpark',
    'database' => 'caiji',
    'charset' => 'utf8',
);

// 智友
$config['db']['zhiyoo']  = array(
    'type' => 'mysql',
    'host' => '192.168.0.99',
    'port' => '3306',
    'username' => 'root',
    'password' => 'southpark',
    'database' => 'zhiyoo',
    'charset' => 'utf8',
);

$config['db']['BBS_DB'] = array(
	'type' => 'mysql',
	'host' => '192.168.0.87',
	'port' => '3306',
	'username' => 'azbbs',
	'password' => 'bbsAZ@)!@)#@&goapk',
	'database' => 'goapk',
	'charset' => 'utf8',
);
$config['db']['DB_ZHIYOO'] = array(
	'type' => 'mysql',
	'host' => '192.168.0.99',
	'port' => '3306',
	'username' => 'root',
	'password' => 'southpark',
	'database' => 'zhiyoo',
	'charset' => 'utf8',
);

$config['ip_prefix'] = '192.168.0.';

//下载量数据格式 1 为 数字 2 为 xx万+
$config['num_format'] = array(
	'num' => 1,
	'format' => 2,
);

$config['filter']['memcache'] = array(
    array(
        'host' => '192.168.0.99',
        'port' => '11211',
        'read' => true,
        'write' => true,
    ),
);
$config['filter']['redis'] = array(
    array(
        'host' => '192.168.0.99',
        'port' => '6379',
        'read' => true,
        'write' => true,
    ),
);

$config['filter']['redis_read'] = array(
    array(
        'host' => '192.168.0.99',
        'port' => '6689',
        'read' => true,
        'write' => true,
    ),
);
$config['filter']['redis_write'] = array(
    array(
        'host' => '192.168.0.99',
        'port' => '6689',
        'read' => true,
        'write' => true,
    ),
);



$config['suggest']['prefix'] = 'http://172.16.1.201:9999/se';
$config['recommend']['prefix'] = 'http://172.16.1.201:9999/discovery';



/***************************************[SESSION配置 start]*****************************************/

//当前机房
$config['session']['server_address'] = 'a';
//SESSION超时时间
$config['session']['expire'] = 300;
$config['session']['memcached']['a'] = array(
		array(
				'host' => '192.168.0.99',
				'port' => '11212',
				'read' => true,
				'write' => true,
		),
);


$config['session']['memcached']['b'] = array(
		array(
				'host' => '192.168.0.99',
				'port' => '11211',
				'read' => true,
				'write' => true,
		),
);


$config['client_session'] = array();
$config['client_session']['code_key'] = array(0 => '1$%.lk',1 => '2$%.lk',2 => '3$%.lk');
$config['client_session']['map'] = array
(
		1 => array (
				'key' => 'VERSION_CODE',
				'type' => 'int',
				'len'	=> 2
		),
		2 => array (
				'key' => 'channel_filter_search',
				'type' => 'string'
		),
		3 => array (
				'key' => 'MODEL_CID',
				'type' => 'int',
				'len' => 2
		),
		4 => array (
				'key' => 'MODEL_AUTHORIZED',
				'type' => 'int',
				'len' => 1
		),
		5 => array (
				'key' => 'NET_TYPE',
				'type' => 'string'
		),
		6 => array (
				'key' => 'USER_IMEI',
				'type' => 'string',
		),
		7 => array (
				'key' => 'MODEL_OID',
				'type' => 'string'
		),
		8 => array (
				'key' => 'DEVICEID',
				'type' => 'string'
		),
		9 => array (
				'key' => 'FIRMWARE',
				'type' => 'int',
				'len' => 1
		),
		10 => array (
				'key' => 'RESOLUTION',
				'type' => 'string'
		),
		11 => array (
				'key' => 'ABI',
				'type' => 'int',
				'len' => 1
		),
		12 => array (
				'key' => 'MAC',
				'type' => 'string'
		),
		13 => array (
				'key' => 'channel_soft_cid',
				'type' => 'int',
				'len' => 1
		),
		14 => array (
				'key' => 'channel:has_rule',
				'type' => 'int',
				'len' => 1
		),
		15 => array (
				'key' => 'model_end',
				'type' => 'string'
		),
		16 => array (
				'key' => 'product',
				'type' => 'string'
		),

		17 => array (
		 		'key' => 'USER_IMSI',
		 		'type' => 'string'
		),

		18 => array (
				'key' => 'RES',
				'type' => 'string'
		),
		19 => array (
				'key' => 'MODEL_DID',
				'type' => 'int',
				'len' => 2
		),
		20 => array (
				'key' => 'device:has_rule',
				'type' => 'string'
		),
		21 => array (
				'key' => 'RC4_CRYPT_KEY',
				'type' => 'string'
		),
		22 => array (
				'key' => 'TIME_STAMP',
				'type' => 'int',
				'len' => 4
		),
		23 => array (
				'key' => 'CRYPT_KEY',
				'type' => 'string'
		),
		24 => array (
				'key' => '__lifetime',
				'type' => 'int',
				'len' => 4
		),
		25 => array (
				'key' => 'USER_ID',
				'type' => 'int',
				'len' => 4
		)


);
//生成用key转换
$config['client_session']['key'] = array();
foreach ($config['client_session']['map'] as $k => $v){
	$config['client_session']['key'][$v['key']] = $k;
}
/***************************************[SESSION配置 end]*****************************************/


return $config;
