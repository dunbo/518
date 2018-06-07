<?php  
require_once(dirname(__FILE__).'/../init.php');
$worker->addFunction("update_db", "update_db_func");  
while ($worker->work());  
function update_db_func($job)  
{  
    if ( !($p = json_decode($job->workload(), true)) ) {
        return False;
    }
	if (empty($p['where'])) return false;
	if (empty($p['table'])) return false;
	if (empty($p['data'])) return false;
	
	$model = new GoModel();
	$p['data']['__user_table'] = $p['table'];
	$model->update( $p['where'], $p['data'] );
	return true;
}
