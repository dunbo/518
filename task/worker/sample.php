<?php
require_once(dirname(__FILE__).'/../init.new.php');

$worker = new GoGearmanWorker('sendNum'); //ʵ�����ɲ�ָ�����ã�����Ĭ��gearman������
$worker->addFunction('test', 'test');
$worker->run();

function test(GearmanJob $job) {
	//����ʹ��sleep()
	echo("do something here\n");
}
