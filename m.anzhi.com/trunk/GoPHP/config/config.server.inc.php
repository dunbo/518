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

$config['log_level'] = array();
if (strpos($remote_addr, "192.168.0.") === 0) {
    $config['log_path'] = '/data/att/gomarket.goapk.com';
    //$config['uc_api'] = 'http://forum.anzhi.com/api/';
    $config['uc_api'] = 'http://9.bbs.goapk.com/api/';
} elseif ($remote_addr == '127.0.0.1') {
	$config['log_path'] = '/data/att/gomarket.goapk.com';
	$config['uc_api'] = 'http://9.bbs.goapk.com/api/';
	//$config['uc_api'] = 'http://forum.anzhi.com/api/';
} else {
    $config['log_path'] = '/data/att/gomarket.goapk.com';
    $config['uc_api'] = 'http://bbs.anzhi.com/api/';
}


$config['static_host'] = $config['full_static_host'];
$config['static_data'] = '/data/att/m.goapk.com';

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
	'host' => 'dbm.goapk.com',
	'port' => '3306',
	'username' => 'newgomarket',
	'password' => 'newgomarket!@#',
	'database' => 'newgomarket',
	'charset' => 'utf8',
);

//从库设置
$config['db']['slave'] = array(
	'type' => 'mysql',
	'host' => 'dbs.goapk.com',
	'port' => '3306',
	'username' => 'newgomarket',
	'password' => 'newgomarket!@#',
	'database' => 'newgomarket',
	'charset' => 'utf8',
);

//sphinx 设置
$config['db']['sphinx'] = array(
	'type' => 'sphinx',
	'host' => 'sph.goapk.com',
	'port' => '9312',
);

//memcache 设置
$config['cache']['memcached'] = array(
	array(
        'host' => '192.168.1.12',
        'port' => '11211',
        'read' => true,
        'write' => true,
        'local' => true,
    ),
    array(
        'host' => '192.168.1.13',
        'port' => '11211',
        'read' => true,
        'write' => true,
        'local' => true,
    ),
    array(
        'host' => '192.168.1.14',
        'port' => '11211',
        'read' => true,
        'write' => true,
        'local' => true,
    ),
    array(
        'host' => '192.168.1.15',
        'port' => '11211',
        'read' => true,
        'write' => true,
        'local' => true,
    ),
    array(
        'host' => '192.168.1.17',
        'port' => '11211',
        'read' => true,
        'write' => true,
        'local' => true,
    ),
    array(
        'host' => '192.168.1.10',
        'port' => '11211',
        'read' => true,
        'write' => true,
    ),
);

$config['memcached']['setting'] = array(
    'cron_server' => '192.168.1.18',
);
$config['cache']['mysql'] = array(
    'dbserver' => 'master',
    'table' => 'cache',
    'key_name' => 'key',
    'value_name' => 'value',
);

$prefix = (GO_SERVER_IP == "192.168.0.99") ? '119.57.50' : '192.168.1';
$config['download_log_path'] = array(    
    "http://${prefix}.14:81/gomarket.goapk.com/",
    "http://${prefix}.15:81/gomarket.goapk.com/",
    "http://${prefix}.12:81/gomarket.goapk.com/",
    "http://${prefix}.13:81/gomarket.goapk.com/",
	"http://${prefix}.17:81/gomarket.goapk.com/",
    
    
    "http://${prefix}.14:81/market.goapk.com/",
    "http://${prefix}.15:81/market.goapk.com/",
    "http://${prefix}.12:81/market.goapk.com/",
    "http://${prefix}.13:81/market.goapk.com/",
	"http://${prefix}.17:81/market.goapk.com/",
        
    "http://${prefix}.14:81/www.goapk.com/",
    "http://${prefix}.15:81/www.goapk.com/",
    "http://${prefix}.12:81/www.goapk.com/",
    "http://${prefix}.13:81/www.goapk.com/",
	"http://${prefix}.17:81/www.goapk.com/",

    
    "http://${prefix}.14:81/m.goapk.com/",
    "http://${prefix}.15:81/m.goapk.com/",
    "http://${prefix}.12:81/m.goapk.com/",
    "http://${prefix}.13:81/m.goapk.com/",
	"http://${prefix}.17:81/m.goapk.com/",
    
    "http://${prefix}.14:81/goapk.yakergong.com/",
    "http://${prefix}.15:81/goapk.yakergong.com/",
    "http://${prefix}.12:81/goapk.yakergong.com/",
    "http://${prefix}.13:81/goapk.yakergong.com/",
	"http://${prefix}.17:81/goapk.yakergong.com/",

    "http://${prefix}.14:81/www.anzhi.com/",
    "http://${prefix}.15:81/www.anzhi.com/",
    "http://${prefix}.12:81/www.anzhi.com/",
    "http://${prefix}.13:81/www.anzhi.com/",
	"http://${prefix}.17:81/www.anzhi.com/",

    "http://${prefix}.14:81/anzhi.com/",
    "http://${prefix}.15:81/anzhi.com/",
    "http://${prefix}.12:81/anzhi.com/",
    "http://${prefix}.13:81/anzhi.com/",
	"http://${prefix}.17:81/anzhi.com/",
		
	"http://${prefix}.14:81/m.anzhi.com/",
	"http://${prefix}.15:81/m.anzhi.com/",
	"http://${prefix}.12:81/m.anzhi.com/",
	"http://${prefix}.13:81/m.anzhi.com/",
	"http://${prefix}.17:81/m.anzhi.com/",
		
	"http://${prefix}.12:81/wdj.anzhi.com/",
	"http://${prefix}.13:81/wdj.anzhi.com/",
	"http://${prefix}.14:81/wdj.anzhi.com/",
	"http://${prefix}.15:81/wdj.anzhi.com/",
	"http://${prefix}.17:81/wdj.anzhi.com/",

	"http://${prefix}.12:81/uu.anzhi.com/",
	"http://${prefix}.13:81/uu.anzhi.com/",
	"http://${prefix}.14:81/uu.anzhi.com/",
	"http://${prefix}.15:81/uu.anzhi.com/",
	"http://${prefix}.17:81/uu.anzhi.com/",

	"http://${prefix}.12:81/soar.anzhi.com/",
	"http://${prefix}.13:81/soar.anzhi.com/",
	"http://${prefix}.14:81/soar.anzhi.com/",
	"http://${prefix}.15:81/soar.anzhi.com/",
	"http://${prefix}.17:81/soar.anzhi.com/",

	"http://${prefix}.12:81/mole.anzhi.com/",
	"http://${prefix}.13:81/mole.anzhi.com/",
	"http://${prefix}.14:81/mole.anzhi.com/",
	"http://${prefix}.15:81/mole.anzhi.com/",
	"http://${prefix}.17:81/mole.anzhi.com/",

	"http://${prefix}.12:81/wdj.goapk.com/",
	"http://${prefix}.13:81/wdj.goapk.com/",
	"http://${prefix}.14:81/wdj.goapk.com/",
	"http://${prefix}.15:81/wdj.goapk.com/",
	"http://${prefix}.17:81/wdj.goapk.com/",

	"http://${prefix}.12:81/uu.goapk.com/",
	"http://${prefix}.13:81/uu.goapk.com/",
	"http://${prefix}.14:81/uu.goapk.com/",
	"http://${prefix}.15:81/uu.goapk.com/",
	"http://${prefix}.17:81/uu.goapk.com/",

	"http://${prefix}.12:81/soar.goapk.com/",
	"http://${prefix}.13:81/soar.goapk.com/",
	"http://${prefix}.14:81/soar.goapk.com/",
	"http://${prefix}.15:81/soar.goapk.com/",
	"http://${prefix}.17:81/soar.goapk.com/",

	"http://${prefix}.12:81/mole.goapk.com/",
	"http://${prefix}.13:81/mole.goapk.com/",
	"http://${prefix}.14:81/mole.goapk.com/",
	"http://${prefix}.15:81/mole.goapk.com/",
	"http://${prefix}.17:81/mole.goapk.com/",
);


$config['system'] = array(
    'write_db' => 'master',
    'read_db' =>  'slave',
);
$config['sphinx_server'] = 'sph.goapk.com';

$config['task_server'] = 'sph.goapk.com';
$config['task_port'] = '4730';

$config['cache']['redis'] = array(
    array(
        'host' => '192.168.1.12',
        'port' => '6379',
        'read' => true,
        'write' => true,
        'local' => true
    ),
    array(
        'host' => '192.168.1.13',
        'port' => '6379',
        'read' => true,
        'write' => true,
        'local' => true
    ),
    array(
        'host' => '192.168.1.14',
        'port' => '6379',
        'read' => true,
        'write' => true,
        'local' => true
    ),
    array(
        'host' => '192.168.1.15',
        'port' => '6379',
        'read' => true,
        'write' => true,
        'local' => true
    ),
    array(
        'host' => '192.168.1.17',
        'port' => '6379',
        'read' => true,
        'write' => true,
        'local' => true
    ),
);

$config['redis']['setting'] = array(
    'cron_server' => '192.168.1.18',
);

$config['cron'] = array(
    'write_db' => 'master',
    'read_db' => 'master',
);

return $config;

