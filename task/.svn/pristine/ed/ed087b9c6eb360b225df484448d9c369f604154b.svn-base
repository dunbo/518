<?php  
require_once(dirname(__FILE__).'/../init.php');
$worker->addFunction("refresh_lack", "refresh_lack_func");  
while ($worker->work());  
function refresh_lack_func($job)  
{  
    if ( !($p = json_decode($job->workload(), true)) ) {
        return False;
    }

	$softid = trim($p['softid']);
	if (empty($softid)) {
		return False;
	}
	$server = 'master';
	$model = new GoModel();

	$where = array(
		'softid' => $softid
	);
	$option = array(
		'where' => $where,
		'table' => 'sj_soft',
		'field' => 'package,version,version_code'
	);

	$soft = $model->findOne($option, $server);

	$option = array(
		'where' => array(
			'package' => $soft['package']
		),
		'table' => 'sj_soft_lack',
		'field' => 'package,type, user_version_code'
	);
	$lack = $model->findOne($option, $server);

	if ($lack) {
		$delete = false;
		if ($lack['type'] == 1) {
			$delete = true;
		} elseif($lack['type'] == 2 && $soft['version_code'] >= $lack['user_version_code']) {
			$delete = true;
		}
		if ($delete == true) {
			$where = array(
				'package' => $lack['package'],
				'__user_table' => 'sj_soft_lack',
			);
			$model->delete($where, $server);	
		}
	}
}
