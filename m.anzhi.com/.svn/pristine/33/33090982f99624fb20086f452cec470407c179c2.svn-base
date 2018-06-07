<?php
include_once (dirname(realpath(__FILE__)).'/../init.php');

if($_GET['sid'] && eregi('[0-9a-zA-z]', $_GET['sid']) && strlen($_GET['sid']) == 32){
	session_id($_GET['sid']);
}
if($_GET['package']){

    $phone = $_GET['phone'];
    $package = $_GET['jsondata'];
    $aid = $_GET['aid'];
    $sid = $_GET['sid'];

    session_start();

    $data = array(
            'activity_id' => $aid,
            'device_id' => $_SESSION['DEVICEID'],
            'ip' => $_SERVER['REMOTE_ADDR'],
            'sid' => $sid,
            'mobile_phone' => $phone,
            'time' => time()
    );
    permanentlog('activity_'.$aid.'.log', json_encode($data));


    /*
    $model = new GoModel();
    $my_option = array(
            'where' => array(
                    'status' =>1,
                    'hide' =>1
            ),
            'field' => 'softid,softname,version_code,min_firmware,max_firmware',
            'order' => 'softid',
            'table' => 'sj_soft'
    );
    $my_result = $model -> findAll($my_option,'lottery/lottery');*/

    //$softinfo = gomarket_action('soft.GoGetSoftDetailPackage', array('PACKAGE_NAME' => $_GET['package'],'EXTRA_OPTION_FIELD' => array('min_firmware','max_firmware')));
    $softinfo = array(
        'ID'=>2143491,
        'SOFT_NAME'=>'新仙剑奇侠传',
        'SOFT_VERSION_CODE'=>1,
        'SOFT_SIZE'=>123149026,
        'min_firmware'=>9,
        'max_firmware'=>0
    );
    echo json_encode($softinfo);

}else{
    $tplObj -> out['sid'] = $_GET['sid'];
    $tplObj -> out['static_url'] = $configs['static_url'];
    $tplObj -> display('xianjian_index.html');
}
