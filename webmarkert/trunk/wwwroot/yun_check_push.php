<?php
	include_once(dirname(realpath(__FILE__)).'/init.php');
	$redis= new  GoRedisCacheAdapter(load_config('yun/redis'));
	$key = 'YUN_PUSH_'.$_GET['DID'].'_'.$_GET['SID'].'_'.$_GET['SEC'];
	$result = $redis->get($key);
   	if($result)
	{
		$status = 200;
	}else{
		$status = 0;
	}
	exit(json_encode(array('status'=>$status)));
