<?php
require_once(dirname(__FILE__).'/../init.php');
load_helper('utiltool');
$worker->addFunction("log", "log_func");  
while ($worker->work());

function log_func($job)
{
    if ( !($p = unserialize($job->workload())) ) {
        return False;
    }
    if ( ($file       = $p['file']) && ($parameter  = $p['parameter']) && ($http_host  = $p['http_host']) ) {
        $_SERVER['HTTP_HOST'] = $http_host;
        permanentlog($file, json_encode($parameter)); 
    }
}
