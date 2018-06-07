<?php  
require_once(dirname(__FILE__).'/../init.php');
$worker->addFunction("device_user_package", "device_user_package_func");  
while ($worker->work());  
function device_user_package_func($job)  
{  
    if ( !($p = unserialize($job->workload())) ) {
        return False;
    }
    $session = $p['session'];
    $installed_packages = $p['installed_packages'];
	
	$device = load_model('deviceUser');
	$option = array(
		'field' => 'A.id, B.id AS packages_id',
		'where' => array(
			'imei' => $session['USER_IMEI'],
		),
		'table' => 'sj_device_user AS A',
		'join' => array(
			'sj_device_user_package AS B' => array(
				'type' => 'left',
				'on' => array('A.id', 'B.id')
			)
		)
	);
	$info = $device->findOne($option);
	if (!empty($info['id'])) {
	   if ($info['packages_id']) {
			$device->update(array('id' => $info['id']), array('packages' => json_encode($installed_packages), '__user_table' => 'sj_device_user_package'));
		} else {
			$device->insert(array('id' => $info['id'], 'packages' => json_encode($installed_packages), '__user_table' => 'sj_device_user_package'));
		}
	}
}
