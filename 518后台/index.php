<?php

//  同时分析CPU和Mem的开销 
// $start_tm = gettimeofday(true);
// $enable = true;
// if ($enable) {
	// xhprof_enable(XHPROF_FLAGS_CPU + XHPROF_FLAGS_MEMORY);
	// register_shutdown_function('xhprof_debug');
// }
//处理上传代码在ff中对session的处理
if(isset($_POST['PHPSESSID'])){
	session_id($_POST['PHPSESSID']);
}
if(isset($_GET['sessid'])) session_id($_GET['sessid']);	//上传apk用
//载入全局定义

require 'define.inc.php';

//载入Think模式定义文件
define('S_ROOT',str_replace('index.php','',str_replace('\\','/',__FILE__)));
define('SU_ROOT',str_replace('/index.php','',str_replace('\\','/',__FILE__)));
define('APP_NAME'	, '.');
define('APP_PATH'	, SITE_PATH.'/'.APP_NAME);
define('APP_URL'	, SITE_URL.'/'.APP_NAME);
define('APP_ROOT'	, SITE_URL);
define('NO_CACHE_RUNTIME',TRUE);
//载入核心文件
require(THINK_PATH."/ThinkPHP.php");

if(function_exists('get_magic_quotes_gpc')) ini_set('magic_quotes_gpc',0);
//开启错误报告
error_reporting(E_ALL);
ini_set('display_errors', 'on');
//实例化一个网站应用实例
$App = new App();
$App->run();

function xhprof_debug(){
	global $start_tm;
	if (function_exists("xhprof_enable") && (mt_rand(1, 10000) == 1 || isset($_GET['XHPROF_DEBUG']))){ 
		$data = xhprof_disable();   //返回运行数据
		$dh = '/data/www/wwwroot/test.com/xhprof';
		include_once $dh."/xhprof_lib/utils/xhprof_lib.php";
		include_once $dh."/xhprof_lib/utils/xhprof_runs.php";  
		$dir = '/data/xhprof/';
		$dir = $dir.date('Y-m-d', time());
		
		if(!file_exists($dir)) 	mkdir($dir, 0755, true);	
		$objXhprofRun = new XHProfRuns_Default($dir); 
		$run_id =  ACTION_NAME .':'.MODULE_NAME . ":" .uniqid();
		$objXhprofRun->save_run($data, "xhprof",$run_id);
	}																
}

?>
