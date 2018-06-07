<?php  
require_once(dirname(__FILE__).'/../init.php');
$deviceUserModel = load_model('deviceUser');
$worker->addFunction("device_user", "device_user_func");  
while ($worker->work());  
function device_user_func($job)  
{  
	global $deviceUserModel;
	$start = microtime_float();
	
    if ( !($p = unserialize($job->workload())) ) {
        return False;
    }
    $session = $p['session'];
    $data = $p['data'];
    $ip = $p['ip'];
    $last_refresh = $p['last_refresh'];
    $did = $p['did'];
    $userid = $p['userid'];
    $deviceUserData = array(
        'firmware' => $session['FIRMWARE'],
		'did' => $did,
		'imei' => $session['USER_IMEI'],
    	'imsi' => $session['USER_IMSI'],
    	'mac' => $session['MAC'],
		'cid' => $session['MODEL_CID'],
        'ip' => $ip,
		'version_code' => $data['version_code'],
		'last_refresh' => $last_refresh,
    );
    $where = array(
    	'imei' => $session['USER_IMEI'],
    );
    $affected_rows = $deviceUserModel->update($where, $deviceUserData);
    if (!$affected_rows) {
		$deviceUserData['submit_tm'] = time();
		$deviceUserData['imei'] = $session['USER_IMEI'];
        $deviceUserData['userid'] = $userid;
        $deviceUserModel->insert($deviceUserData);
    }
	$end = microtime_float();
	$sql = $deviceUserModel->getSql();
	echo $end - $start, "__{$sql}\n";
}
