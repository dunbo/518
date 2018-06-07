<?php
require_once(dirname(__FILE__).'/../init.new.php');

$worker = new GoGearmanWorker('sendNum'); //实例化可不指定配置，调用默认gearman服务器
$worker->addFunction('test', 'test');
$worker->run();

function test(GearmanJob $job) {
	//不能使用sleep()
	echo("do something here\n");
}
