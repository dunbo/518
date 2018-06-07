<?php
require_once(dirname(__FILE__).'/../init.php');
#ini_set('displays_errors', true);
#error_reporting(E_ALL);
load_helper('utiltool');
load_helper('task');
$_SERVER['HTTP_HOST'] = 'x_log';

$worker->addFunction('x_log', 'x_log_func');
while ($worker->work());

function x_log_func($job)
{
	$string = $job->workload();
	if ( !($data = json_decode($string, true)) ) {
		return false;
	}

    $act_redis = new GoRedisCacheAdapter(load_config("activation_status/redis"));
    $k = 'IMEI:X:'. strtoupper(trim($data['imei']));

    $act_redis->pingConn();

    $act_redis->set($k, $data['type']);

    if ($data['type'] == 2) {
        permanentlog('x_log.log', $string);
    }
}