<?php

require_once(dirname(__FILE__).'/../init.php');

$worker->addFunction("restart_worker", "restart_worker_func");  
while ($worker->work());  

function restart_worker_func($job)  
{  
    if ( !($p = unserialize($job->workload())) ) {
        return false;
    }
    if (!isset($p['name']) || empty($p['name']))
        return false;
    $name = $p['name'];
    $count = isset($p['count']) ? $p['count'] : 1;
    // XXX:
    $pdir = realpath(dirname(__FILE__). '/..');
    $env = trim(shell_exec("dirname `which php`"));
    $start_cmd = "export PATH=\$PATH:$env; python ${pdir}/start_worker.py ${name} ${count}";
    $stop_cmd = "export PATH=\$PATH:$env; python ${pdir}/stop_worker.py ${name}";
    //
    shell_exec($stop_cmd);
    shell_exec($start_cmd);
}

