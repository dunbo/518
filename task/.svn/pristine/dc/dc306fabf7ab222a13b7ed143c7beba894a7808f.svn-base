<?php
require_once(dirname(__FILE__).'/../init.php');
$worker->addFunction("set_resolution", "set_resolution_func");  
while ($worker->work());

function set_resolution_func($job)
{
    if ( !($p = unserialize($job->workload())) ) {
        return False;
    }
    $data = $p['data'];
    if (!$data['resolution']) {
        return False;
    }
    $user = load_model('user');
    list($width, $length) = explode('*', $data['resolution']);
    $option = array(
        'where' => array(
            'length' => $length,
            'width' => $width,
        ),
        'table' => 'sj_resolution'
    );
	if (!$user->findOne($option)) {
		$insert = array(
			'length' => $length,
			'width' => $width,
			'note' => $data['resolution'],
			'status' => 1,
			'upload_time' => time(),
			'last_refresh' => time(),
			'__user_table' => 'sj_resolution'
		);
		$user->insert($insert);
	}
}
