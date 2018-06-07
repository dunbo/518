<?php

//由于多应用情况下，目录由应用自己设置，核心系统就需要定义主程序的路径
define("SITE_PATH"	,	str_replace('define.inc.php','',str_replace('\\','/',__FILE__)));

//应用跳转到核心需要的路径


define('SITE_URL'	,	'http://'. $_SERVER['HTTP_HOST']);
//define('SITE_URL'	,	'http://test.newadmin.com');//wamp5
//define('SITE_URL'	,	'http://admin.goapk.local');//虚拟机



//市场url
define('__MARKETURL__'	,	'http://www.goapk.com');

//兼容旧系统
define('ROOT_PATH'	,	SITE_URL);

//ThinkPHP框架目录
define('THINK_PATH'	,	SITE_PATH.'../ThinkPHP');
//公共目录和风格目录
define('PUBLIC_PATH',	SITE_PATH."/Public");
define('PUBLIC_URL'	,	SITE_URL."/Public");
define('__PUBLIC__'	,	SITE_URL."/Public");

//设置附件目录
//define('ATTACHMENT_HOST','http://192.168.0.46/testdata');//虚拟机环境
define('ATTACHMENT_HOST','http://192.168.0.99/testdata');
//define('IMGATT_HOST','http://192.168.0.46/testdata');//虚拟机环境
define('IMGATT_HOST','http://192.168.0.99/testdata');
define('IMGATT_HOST_CDN','http://192.168.0.99/testdata');
//define('CAIJI_ATTACHMENT_HOST', 'http://caijigameinfo.goapk.com');
define('CAIJI_ATTACHMENT_HOST', 'http://192.168.0.99/testdata/data3');
define('GAMEINFO_ATTACHMENT_HOST', 'http://192.168.0.99/testdata');

//cdn 前缀
define('APK_HOST','http://gm.apk.anzhi.com');

//附件上传目录
define('UPLOAD_PATH',	"/data/att/m.goapk.com");	// 结尾有 /
define('CAIJI_UPLOAD_PATH', "/data/att/m.goapk.com/data3");   // 资讯采集列表编辑内容时的上传目录，线上已挂载在内网中的采集机器上，测试环境不变
define('UPLOAD_URL'	,	"/uploads/user/");		// 结尾有 /

// 广告位排期批量导入文件位置
//define('IMPORT_FILE_UPLOAD_PATH', '/data/att/518/import_file/'.date("Ym/d/",time()));

define("SERVER_ROOT", SITE_PATH . "../");
define('P_LOG_DIR', '/data/att/permanent_log');
define("APKINFO_CMD", 'python ' . SERVER_ROOT . 'config/gnu/apkinfo.py ');
define('GO_ENV_CLI', 2);

//模板目录
define('DIR_MODULE', '/data/www/wwwroot/m.goapk.com/module/');
define('STATIC_MODULE_DIR', '/data/www/wwwroot/m.goapk.com/static_module/');
if($_SERVER['SERVER_ADDR']=='192.168.0.99'){
	define('ACTIVITY_PAGE', '/data/www/wwwroot/m.goapk.com/activity/activity_page/');
}elseif($_SERVER['SERVER_ADDR']=='118.26.203.23'){
	define('ACTIVITY_PAGE', '/data/www/wwwroot/new-wwwroot/m.goapk.com/activity/activity_page/');
}elseif($_SERVER['SERVER_ADDR']=='192.168.1.18'){
	define('ACTIVITY_PAGE', '/data/www/wwwroot/new-wwwroot/m.anzhi.com/activity/activity_page/');
}

define('ACTIVITY_URL','http://118.26.203.23');

//字体模板目录
define('KEY_DIR_MODULE','/data/www/wwwroot/m.goapk.com/key_module/');
define('KEY_STATIC_MODULE', '/data/www/wwwroot/m.goapk.com/key_static_module/');
//字体模板图片目录
define('KEY_IMG_DIR',UPLOAD_PATH . '/key_static_module/img/');
//apk文件分割设置
define("SPLIT_DIR", "splitapkfile");//分割后文件存储的根目录
define("SPLIT_BLOCK", 524288);//分割的大小
define("SPLIT_DIRNAME_NUM", 2);//分割的目录名长度

//定义是否是正常访问
define('ROOT_WHICH',true);


//1 111 2 2222 //针对id 111的用户屏蔽渠道号为222的渠道显示 (filter_type 1,可以显示，，，2 不可以显示)
//针对source_type的对象（对象值为source_value）,在target_type的对象（值为target_value） 做 filter_type 类型的过滤

define('USER_FILTER_TYPE', 1);          //用户id		source_type
define('CHANNEL_FILTER_TYPE', 2);		//渠道显示		target_type
define('CHANNEL_TOTAL_FILTER_TYPE', 3);	//显示渠道用户总数		target_type
define('IMEI_FILTER_TYPE', 4);			//IMEI		source_type
define('IP_FILTER_TYPE', 5);			//IP		source_type
define('ADD_COMMENT_FILTER_TYPE', 6);	//评论过滤  (target_type)
define('CHANNEL_COEFFICIENT_TYPE', 7);	//渠道系数显示   target_type
define('CHANNEL_SHOW_TYPE', 8);			//渠道号显示	target_type
define('CHANNEL_SHOW_CONTROL', 9);
define('APK_SHOW',10);					//apk编辑显示 target_type
define('CHANNEL_SHOW_CONTROL_BY_CATEGORY', 11);	// 渠道过滤使用分类

define('NOTIFICATION_HOST', '127.0.0.1');
define('NOTIFICATION_PORT', 20012);
//轮播图跳转域名
define('SCROLL_PIC_HOST','http://www.anzhi.com');
//动态信息域名
define('SCROLL_DEV_INFORMATION','http://9.newdev.anzhi.com');
//智友配置
define('BBSLUNTAN_HOST','http://bbs.zhiyoo.com/forum.php?mod=post&action=edit');

?>
