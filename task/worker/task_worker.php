<?php
/* 
 * ��ȡ������أ�����ʽ���»���
 */
require_once(dirname(__FILE__).'/../init.new.php');

$worker = new GoGearmanWorker(); //ʵ�����ɲ�ָ�����ã�����Ĭ��gearman������
$worker->addFunction('ucenter_callback', 'refresh_cache');
$worker->run();

function refresh_cache($job)
{
	$params = json_decode($job->workload(),true);
	$type = $params['type'];
	$log_id = $params['log_id'];
	$file = '/data/www/wwwroot/new-wwwroot/cron_market'. $params['file'];
	$result = shell_exec("/usr/local/php-5.2.17/bin/php $file $type $log_id");
	return $result;
}
