<?php
include_once ('./init.php');
session_begin();
$imei = $_SESSION['DEVICEID'];
if(empty($imei)){
    echo '页面已失效，请退出活动重新进入';
    exit(0);
}

$cityid = $_GET['cityid'];

$tplObj -> out['static_url'] = $configs['static_url'];

$tpl = "lottery/store/buy_school.html";


$activity_option = array(
	'where' => array(
		'id' => 1
	),
	'cache_time' => 3600,
	'table' => 'store_config'
);
$config_info = $model -> findOne($activity_option,'lottery/lottery');
$config_info['explain'] = str_replace("\n","<br/>",$config_info['explain']);
$tplObj -> out['config_info'] = $config_info;

$activity_option = array(
	'where' => array(
		'pname' => $cityid,
		'status' => 1,
	),
	'cache_time' => 3600,
	'table' => 'store_school'
);
$school = $model -> findAll($activity_option,'lottery/lottery');

if($_POST){
    $active_id = 359;
    //添加记录
    $data = array(
            'imei' => $imei,
            'mobile' => $_POST['mobile'],
            'city' => $_POST['cityid'],
            'school' => $_POST['school'],
            'create_tm' => time(),
            '__user_table' => 'store_tmp_user'
    );
    $model->insert($data,'lottery/lottery');

    //日志
    $data_tmp['mobile'] = $_POST['mobile'];
    $data_tmp['city'] = $_POST['cityid'];
    $data_tmp['school'] = $_POST['school'];

            $log_data = array(
                    'imsi' => $_SESSION['USER_IMSI'],
                    'device_id' => $_SESSION['DEVICEID'],
                    'activity_id' => $active_id,
                    'ip' => $_SERVER['REMOTE_ADDR'],
                    'sid' => $_GET['sid'],
                    'time' => time(),
                    'telphone' => $_POST['mobile'],
                    'city' => $_POST['cityid'],
                    'school' => $_POST['school'],
                    'key' => 'store_user_add'
            );
            permanentlog('activity_'.$active_id.'.log', json_encode($log_data));


    echo json_encode($data_tmp);
    exit(0);
}

$tplObj -> out['mobile'] = $_GET['mobile'];
$tplObj -> out['school'] = $school;
$tplObj -> out['cityid'] = $cityid;
$tplObj -> display($tpl);
