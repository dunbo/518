<?php
require_once(dirname(__FILE__) . '/push_common.php');

$worker = get_task_worker('push1');
$start = time();
$worker->addFunction('add_push_receipt', 'add_push_receipt_func');
while ($worker->work());
