<?php
ini_set("display_errors", true);
error_reporting(E_ALL & ~E_NOTICE);
date_default_timezone_set('Asia/Shanghai');
define('GO_CONFIG_DIR', dirname(__FILE__).'/../devupload.anzhi.com/config');
//define('GO_MODEL_DIR', dirname(__FILE__).'/../devupload.anzhi.com/model');
//define('P_LOG_DIR', '/tmp/goapk_log/');
define('GO_UID_DEFAULT', 13176);
define('GO_APP_ROOT', dirname(realpath(__FILE__)).'/../devupload.anzhi.com/');
require_once(dirname(__FILE__).'/../GoPHP/Startup.php');
require GO_APP_ROOT . DS . '..'.DS.'tools'.DS.'functions.php';
$worker= new GearmanWorker();  
($task_server = load_config('task_server'))? True : $task_server = '127.0.0.1';
($task_port = load_config('task_port'))? True : $task_port = 4730;
$worker->addServer($task_server, $task_port);

$gearman_worker_list_key = 'gearman_worker_list';
