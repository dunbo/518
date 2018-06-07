<?php

//添加消息服务器接口 guokai add 2011-08-02
function get_task_client($config_key = '')
{
	static $client_map = array();
	$key = 'default';
	if (!empty($config_key)) {
		$key = 'gm_'. $config_key;
	}

	if (!isset($client_map[$key])) {
		$task_client = new GearmanClient();

		if ($key == 'default') {
			($task_server = load_config('task_server'))? True : $task_server = '127.0.0.1';
			($task_port = load_config('task_port'))? True : $task_port = '4730';
		} else {
			$task_server = load_config("gearman/{$config_key}/task_server");
			$task_port = load_config("gearman/{$config_key}/task_port");
		}

		$task_client->addServer($task_server, $task_port);
		$client_map[$key] = $task_client;
	}
	return $client_map[$key];
}

//获取worker任务服务器接口
function get_task_worker($config_key = '')
{
	static $client_map = array();
	$key = 'default';
	if (!empty($config_key)) {
		$key = 'gm_'. $config_key;
	}

	if (!isset($client_map[$key])) {
		$task_client = new GearmanWorker();

		if ($key == 'default') {
			($task_server = load_config('task_server'))? True : $task_server = '127.0.0.1';
			($task_port = load_config('task_port'))? True : $task_port = '4730';
		} else {
			$task_server = load_config("gearman/{$config_key}/task_server");
			$task_port = load_config("gearman/{$config_key}/task_port");
		}

		$task_client->addServer($task_server, $task_port);
		$client_map[$key] = $task_client;
	}
	return $client_map[$key];
}