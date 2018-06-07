<?php
require_once(dirname(__FILE__).'/push_common.php');

$worker = get_task_worker('push1');
$start = time();
$worker->addFunction('add_push_log', 'add_push_log_func');
while ($worker->work());