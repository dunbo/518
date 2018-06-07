<?php
if (time() >= strtotime('2013-02-25') || time() < strtotime('2013-02-24') )
	exit('-99');
include_once('../../init.php');
session_start();
//ini_set('display_errors', 1);
//error_reporting(E_ALL);
define('MAX_LOTTERY', 10);
$key = array(
	'softlist' => 'getRandomSoft',
	'download' => 'checkDownload',
	'phoneinfo' => 'getPhoneInfo',
	'submitphone' => 'checkPhoneNumber',
);
if (empty($_GET['action']))
{
	exit(false);
}
$action = $_GET['action'];
if (!isset($key[$action]))
{
	exit(false);
}
$key[$action]();

function getRandomSoft()
{
	$prefix = "LANTERN_SOFT_DETAIL_";
	//if (empty($_SESSION['phone']))
	//{
	//	exit('-2');
	//}
	$model = new GoModel();
	$option = array(
		'table' => 'activity_lantern_soft',
		'where' => array(
			'status' => 1
		),
		'field' => 'package',
		'cache_time' => 600,
	);
	$softlist = $model->findAll($option, 'activity');
	if (empty($softlist))
	{
		exit;
	}
	//var_dump($softlist);
	$num = count($softlist);
	$randomsoft = array();
	$cache = new GoMemcachedCacheAdapter();
	while (count($randomsoft) < 20 && count($randomsoft) < $num)
	{
		$rnum = rand(0, $num - 1);

		if (isset($randomsoft[$rnum]))
			continue;
		//$softinfo = $cache->delete($prefix . $softlist[$rnum]['package']);
		$softinfo = $cache->get($prefix . $softlist[$rnum]['package']);
		if (empty($softinfo))
		{
			//echo '"' . $softlist[$rnum]['package'] . '"';
			$softinfo = gomarket_action('soft.GoGetSoftDetailPackage', array('PACKAGE_NAME' => $softlist[$rnum]['package']));
			if (!empty($softinfo))
				$cache->set($prefix . $softlist[$rnum]['package'], $softinfo, 600);
			else
			{
				$cache->set($prefix . $softlist[$rnum]['package'], array('error' => 'false'), 600);
				$softinfo = array();
			}
		}
//		var_dump($softinfo);
//		exit;
		if (empty($softinfo['SOFT_NAME']) || ($softinfo['SOFT_NAME'] == 'f'))
			continue;

		$randomsoft[$rnum] = array(
			'package'  => $softlist[$rnum]['package'],
			'softname' => $softinfo['SOFT_NAME'],
			'icon'     => $softinfo['ICON'],
		);
	}
	exit(json_encode(array_values($randomsoft)));
}

function checkDownload()
{
	$prefix = "LANTERN_SOFT_DETAIL_";
	if (empty($_SESSION['phone']))
	{
		exit('-2');
	}
	$phone = $_SESSION['phone'];
	
	if (empty($_GET['package']))
	{
		exit(false);
	}
	$redis = new GoMemcachedCacheAdapter();
	$package = $_GET['package'];
	//$softinfo = gomarket_action('soft.GoGetSoftDetailPackage', array('PACKAGE_NAME' => $package));
	$softinfo = $redis->get($prefix . $package);
	if (empty($softinfo))
	{
		$softinfo = gomarket_action('soft.GoGetSoftDetailPackage', array('PACKAGE_NAME' => $package));
		$redis->set($prefix . $package, $softinfo, 600);
	}
//		var_dump($softinfo);
//		exit;
	if (empty($softinfo))
		exit('-1');
	$softid = $softinfo['ID'];
	$model = new GoModel();
	$option = array(
		'table' => 'activity_lantern_soft',
		'where' => array(
			'package'   => $package,
			'status'    => 1
		),
		'field' => '*',
		'cache_time' => 600
	);
	$result = $model->findOne($option, 'activity');
	if (empty($result))
	{
		exit('-1');
	}
	
	$option = array(
		'table' => 'activity_lantern_phone',
		'where' => array(
			'phone_num' => $phone,
		),
		'field' => 'phone_download',
	);
	$result = $model->findOne($option, 'activity');
	$download = $result['phone_download'];
	if (!empty($result) && $download >= MAX_LOTTERY)
	{
		exit('-3');
	}
	
	$option = array(
		'table' => 'activity_lantern_download',
		'where' => array(
			'phone_num' => $phone,
			'package'   => $package
		),
		'field' => 'id',
		'cache_time' => 600,
	);
	$result = $model->findOne($option, 'activity');
	if (empty($result))
	{
		$download++;
		
		$model->query("START TRANSACTION", 'activity');
		$data = array(
			'__user_table' => 'activity_lantern_download',
			'phone_num'    => $phone,
			'softid'       => $softid,
			'package'      => $package,
			'submit_tm'    => time(),
		);
		$result = $model->insert($data, 'activity');
		if (empty($result))
		{
			$model->query("ROLLBACK", 'activity');
			exit('-1');
		}
		$data = array(
			'__user_table' => 'activity_lantern_phone',
			'phone_download' => $download,
		);
		$where = array(
			'phone_num' => $phone,
		);
		$result = $model->update($where, $data, 'activity');
		if (empty($result))
		{
			$model->query("ROLLBACK", 'activity');
			exit('-1');
		}
		$model->query("COMMIT", 'activity');
	}
	
	$return = array(
		'phone_num' => $phone,
		'phone_download' => (int)$download,
		'phone_left' => MAX_LOTTERY - $download
	);
	exit(json_encode($return));
}

function getPhoneInfo()
{
	//var_dump($_SESSION);
	if (empty($_SESSION['phone']))
	{
		exit('-2');
	}
	
	$phone = $_SESSION['phone'];
	$model = new GoModel();
	$option = array(
		'table' => 'activity_lantern_phone',
		'where' => array(
			'phone_num' => $phone
		),
		'field' => 'phone_num,phone_download',
	);
	$result = $model->findOne($option ,'activity');
	if (empty($result))
	{
		exit('-1');
	}
	else
	{
		$result['phone_download'] = (int)$result['phone_download'];
		$result['phone_left'] = MAX_LOTTERY - $result['phone_download'];
		exit(json_encode($result));
	}
}

function checkPhoneNumber()
{
	//var_dump($_SESSION);
	if (empty($_GET['phone']))
	{
		exit(false);
	}
	$phone = $_GET['phone'];
	if (!preg_match('/^1\d{10}$/', $_GET['phone']))
	{
		exit('-3');
	}
	$model = new GoModel();
	$option = array(
		'table' => 'activity_lantern_phone',
		'where' => array(
			'phone_num' => $phone
		),
		'field' => '*',
	);
	$result = $model->findOne($option, 'activity');
	if (!empty($result))
	{
		$_SESSION['phone'] = $phone;
		$_SESSION['actid'] = 1;
		getPhoneInfo();
	}
	else
	{
		$data = array(
			'__user_table' => 'activity_lantern_phone',
			'phone_num' => $phone,
			'create_at' => time(),
		);
		$result = $model->insert($data, 'activity');
		if (empty($result))
		{
			exit(-1);
		}
		else
		{
			$_SESSION['phone'] = $phone;
			$_SESSION['actid'] = 1;
			getPhoneInfo();
		}
	}
}

?>
