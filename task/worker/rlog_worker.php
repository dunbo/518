<?php
require_once(dirname(__FILE__).'/../init.php');
load_helper('utiltool');
$worker->addFunction("rlog", "rlog_func");  
while ($worker->work());

function rlog_func($job)
{
    if ( !($data = json_decode($job->workload(), true)) ) {
        return False;
    }
	//$gzjson = gzcompress($job->workload());
	//$r = requestPost('http://118.26.224.30/log.php', $gzjson, 4);
	if ( isset($data['f']) && isset($data['l']) && isset($data['h']) && isset($data['t']) ) {
		putrlog($data);
	}
}

function putrlog($data) {
    $host = $data['h'];

    $full_path = P_LOG_DIR . DS . $host . DS . date("Y-m-d", $data['t']) . DS. $data['f'] ;
    $dir = dirname($full_path);
    if (!is_dir($dir)) {
        if (!mkdir($dir, 0755, true)) {
            return false;
        }
    }
    file_put_contents($full_path, $data['l']. "\n", FILE_APPEND);
    return true;
}
