<?php
require_once(dirname(__FILE__).'/../init.php');

$ip = getServerIp();
if ($ip == load_config('redis/setting/cron_server')) {
    exit;
}
$start = time();
$worker->addFunction("cache_list_{$ip}", "cache_list_func");  
load_helper('mysqlcache');
while ($worker->work());

function cache_list_func($job)
{
	$string = $job->workload();
    if ( !($p = unserialize($string)) ) {
        return False;
    }
    $now = date('Y-m-d H:i:s',time());
    $start_this = microtime_float();
	$func = strtolower("get_{$p['key']}_func");
	$param = $p['param'];
	$filter = $p['filter'];
	$key = strtoupper($p['key']) . ':'. strtoupper(md5($string));
    $total = 0;
	if (function_exists($func)) {
		$list = call_user_func($func, $param);
		
		$softlist = load_model('softlist', '', false);
		$list = $softlist->filterSoftId($list, $filter);
		
        $redis = GoCache::getCacheAdapter('redis');
		//$redis->setx('del', $key);
        $redis->set($key, $list, 300);
        $total = count($list);
		unset($softlist);
		unset($list);
	}
    $spend = microtime_float() - $start_this;
    echo "{$now} {$string} {$key} {$total} <{$spend}>\n";
	unset($string);
	unset($p);
}

function get_home_new_func($param)
{
	static $result;
	$pid = !empty($param['pid']) ? $param['pid'] : 0;

	if (!isset($result[$pid]) || ( time() - $result[$pid]['time'] > 600) ) {	
		if (!empty($pid)) {
			$list = getMysqlCache('new_list'.$pid);
		} else {
			$list = getMysqlCache('new_list');
			
			//最新软件添加候补	
			$new_candidate = getMysqlCache('new_candidate');
			if (!empty($new_candidate)) {
		//		shuffle($new_candidate);
				$list = array_merge($new_candidate, $list);
			}
		}
		$result[$pid]['data'] = $list;
		$result[$pid]['time'] = time();
	} else {
		$list = $result[$pid]['data'];
	}
	return $list;
}

function get_home_hot_func($param)
{
	static $result;
	$pid = !empty($param['pid']) ? $param['pid'] : 0;
	if (!isset($result[$pid]) || ( time() - $result[$pid]['time'] > 600) ) {
		if (!empty($pid)) {
			$list = getMysqlCache('hot_list'.$pid);
		} else {
			$list = getMysqlCache('hot_list');
		}
		$result[$pid]['data'] = $list;
		$result[$pid]['time'] = time();
	} else {
		$list = $result[$pid]['data'];
	}
	return $list;
}

function get_category_list_func()
{
}