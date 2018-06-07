<?php
/*
** 软件下载增加抽奖机会接口
*/

include_once (dirname(realpath(__FILE__)).'/aprilstrip_init.php');

if (!$imsi_status) {
	echo 0;
	exit;
}

// 下载的包，重复下载同一个包无效
$package = $_POST['pkgname'];

// 查看此包是否在mysql中
$option = array(
    'where' => array(
        'package' => $package,
        'page_id' => $page_id,
        'status' => 1
    ),
    'cache_time' => 1200,
    'table' => 'sj_actives_soft'
);
$package_find_result = $model -> findOne($option);

// 该用户已下载过的包列表
$my_package = $redis -> gethash($rkey_imsi_package_list, $package);
$log_num = 0;
if ($package_find_result && !empty($my_package)) {
	$log_num = 1;
    // 把此包添加进此用户的已下载过的包列表
    $ret = $redis -> sethash($rkey_imsi_package_list,array($package=>1));
    // 可抽奖次数加1
    $now_num = $redis -> setx('incr',$rkey_imsi_lottery_num,1);
    // 入库
    $where = array(
        'imsi' => $imsi
    );
    // 写回库里
    $data = array(
        'lottery_num' => $now_num,
        'time' => time(),
        '__user_table' => 'aprilstrip_lottery_num'
    );
    $model -> update($where,$data,'lottery/lottery');
} else {
    $now_num = $redis -> get($rkey_imsi_lottery_num);
}

// 记日志
$log_data = array(
    'imsi' => $imsi,
	'imei' => $_SESSION['USER_IMEI'],
    'device_id' => $_SESSION['DEVICEID'],
    'activity_id' => $aid,
    'ip' => $_SERVER['REMOTE_ADDR'],
    'sid' => $sid,
    'time' => time(),
    'num' => $log_num,
    'package' => $package,
    'key' => 'aprilstrip_lottery_download'
);
permanentlog($activity_log_file, json_encode($log_data));

echo $now_num;
exit;