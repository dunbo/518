<?php
$configs = array();
$configs['static_url'] = '/../static'; // 资源存储路径
$configs['new_static_url'] = '/../static'; // 资源存储路径（与线上统一，上面的static_url废弃，线上此地址应指向静态目录images的上一层目录）
$configs['activity_url'] = 'http://m.test.anzhi.com/'; // 资源存储路径
$configs['activity_share_url'] = 'http://m.test.anzhi.com/';//分享用
$configs['activity_video_url'] = 'http://m.test.anzhi.com/cmcc';//播放视频地址用
$configs['goserver_url'] = 'http://api.test.anzhi.com/goserv.php';//goserver地址
//$configs['goserver_url'] = 'http://dev.gomarket.goapk.com/goserv.php';//goserver线上地址
$configs['chl_package'] = array('com.tencent.weishi','com.tencent.weishi','com.tencent.weishi');
$configs['chl_package_url'] = array(
        'com.tencent.weishi'=>'http://apk.goapk.com/data3/apk/201506/23/ceaa8c612074e846e4e528100d5e52bb_12889700.apk',
        'com.tencent.weishi2'=>'http://apk.goapk.com/data3/apk/201506/23/ceaa8c612074e846e4e528100d5e52bb_12889700.apk',
        'com.tencent.weishi3'=>'http://apk.goapk.com/data3/apk/201506/23/ceaa8c612074e846e4e528100d5e52bb_12889700.apk'
        );
$configs['is_test'] = 1;//是否测试环境
if($_SERVER['SERVER_ADDR']=='124.243.198.97') {	//518test
	//dummy
	$configs['dummy'] = array(
		'host' => '124.243.198.97',
		'host_dam' => 'Host: dummy.goapk.com',
	);

} else {	//线上
	//dummy
	$configs['dummy'] = array(
		'host' => '192.168.0.99',
		'host_dam' => 'Host: 9.dummy.goapk.com',
	);

}

return $configs;
?>
