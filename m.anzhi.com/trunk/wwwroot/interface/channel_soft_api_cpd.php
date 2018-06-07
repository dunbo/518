<?php
include_once('./distribute/init.php');
// cpd对外接口
// 这里的chl和后面的下载用到一个，跟数据库username用同一个
$model = new GoModel();
$chl = 'ydmm';
$chl = isset($_GET['chl']) ? $_GET['chl'] : 'yezi';
$date = date('Y-m-d');
$time = time();

$option = array(
    'table' => 'tf_channel',
    'where' => array(
        'status' => 1
    ),
);
$secret_keys = array();
$tf_channel_all = $model->findAll($option, 'toufang/toufang');
foreach($tf_channel_all as $v){
    $secret_keys[$v['username']] = $v['secret_keys'];
}

$secret_key =  isset($secret_keys[$chl]) ? $secret_keys[$chl] : '';
$access_key_get = $_GET['access_key'];
$a_time = $_GET['a_time'];

if ( $access_key_get != md5($secret_key . $a_time) ){
    $retarr =  array(
        'code' => 1,
        'msg' => "No permissions!",
    );
    exit(json_encode($retarr));
}

if ($time - $a_time > 600) {
    $retarr =  array(
        'code' => 1,
        'msg' => "Be overdue!",
    );
    exit(json_encode($retarr));
}

$softmodel = load_model('softlist');
$dev_model = load_model('dev');

$rett = $model->findOne($option, 'toufang/toufang');
$channel = getChannel($chl);

if (empty($channel)) {
    $retarr =  array(
        'code' => 2,
        'msg' => "No channel data",
    );
    exit(json_encode($retarr));
}

$channel_id = $channel['id'];

$channelSoft = getChannelPackage($channel_id, $date);
$softDetail = getChannelPackageDetail($channelSoft);
if ( empty($softDetail) ) {
    $retarr =  array(
        'code' => 2,
        'msg' => "NOT DATA",
    );
    exit(json_encode($retarr));
}

$new_arr = formatSoftDetail($softDetail, $channel, $date);

$retarr = array(
	'code' => 0,
	'msg' => "success",
	'data' => $new_arr,
);

$to_log = array(
	'k' => 'softlist',
	'ts' => $time,
	'channel_id' => $channel_id,
	'ip' => onlineip(),
);
writeLog($to_log);

exit(json_encode($retarr));
