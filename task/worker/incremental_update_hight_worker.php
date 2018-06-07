<?php
require_once(dirname(__FILE__).'/soft_incremental_common.php');

$worker = new GoGearmanWorker(); //实例化可不指定配置，调用默认gearman服务器
$worker->addFunction("incremental_update_high", "incremental_update_func");
$worker->run();