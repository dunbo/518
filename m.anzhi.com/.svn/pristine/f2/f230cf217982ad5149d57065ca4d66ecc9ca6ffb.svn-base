<?php
/*
** 活动首页
*/
include_once(dirname(realpath(__FILE__)).'/init_page.php');

// 记录活动首页日志
$log_data = array(
    'imsi' => $imsi,
    'device_id' => $_SESSION['DEVICEID'],
    'activity_id' => $aid,
    'ip' => $_SERVER['REMOTE_ADDR'],
    'sid' => $sid,
    'time' => $now,
    'key' => 'show_homepage'
);
$rkey_imsi_is_first = "{$prefix}:{$aid}:{$imsi}:is_first"; //是否首次进入
if ($now > $a_end_time) {
	$tplObj->display("lottery/{$prefix}/end.html");
	exit;
} else {
    permanentlog($activity_log_file, json_encode($log_data));

    if(!$redis->exists($rkey_imsi_is_first)){
        $redis->set($rkey_imsi_is_first, 1, $r_cache_time);
        $tplObj->out['is_first'] = 1;
    }else{
        $tplObj->out['is_first'] = 0;
    }

    // 获取用户今日下载次数
    if(!$redis->exists($rkey_imsi_download_num)){
        $redis->set($rkey_imsi_download_num, 0, $r_cache_time);
        $download_num = 0;
    }else{
        $download_num = $redis->get($rkey_imsi_download_num);
        if($download_num>3){
            $download_num = 3;
        }
    }
    $tplObj->out['download_num'] = $download_num;
	$tplObj->display("lottery/{$prefix}/index.html");
	exit;
}