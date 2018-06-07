<?php
require_once(dirname(__FILE__).'/../init.php');
#ini_set('displays_errors', true);
#error_reporting(E_ALL);
load_helper('utiltool');
load_helper('task');
$_SERVER['HTTP_HOST'] = 'ad_channel';

$channel_conf = array(
    'GDT' => array(
        4590,
        4591,
        4592,
        4550,
        4551,
        4552,
        4518,
        4519,
        4520,
        4521,
        4522,
        4513,
        4514,
        4515,
        4516,
        4517,
        4870,
        4871,
        4872,
        4873,
        4874,
        4886,
        4887,
        4888,
        4889,
        4892,
        4893,
    ),
    'JRTT' => array(
        4526,
        4527,
        4528,
        4529,
        4530,
        4531,
        4532,
        4533,
        3794,
        3507,
        3114,
        1487,
    ),
);
$channel = array();
foreach ($channel_conf as $c => $cids) {
    foreach ($cids as $cid) {
        $channel[$cid] = $c;
    }
}
$model = new GoModel();
$worker->addFunction('ad_channel_stat', 'ad_channel_stat_func');
while ($worker->work());

function ad_channel_stat_func($job)
{
	global $model;
    global $channel;
	$redis = new GoRedisCacheAdapter(load_config("ad_channel/redis"));
	$string = $job->workload();
	if ( !($data = json_decode($string, true)) ) {
		return false;
	}
	#var_dump($data);
    $cid = $data['cid'];
    $submit_tm = time();
    switch ($channel[$cid])
    {
        // 广点通处理流程
        case 'GDT':
            $unid = md5(strtolower($data['imei']));
            $key = "AD_CLICK:GDT:${unid}";
            $cache_data = $redis->get($key);
            if ($cache_data)
            {
                $click_id = $cache_data['click_id'];
                $client_ip = $data['IP'];
                $url = crypt_gdt_url($click_id, $unid, $submit_tm, $client_ip);
            }
            else
            {
                return false;
            }
            break;

        // 今日头条处理流程
        case 'JRTT':
            $unid = md5($data['imei']);
            $key = "AD_CLICK:JRTT:${unid}";
            $cache_data = $redis->get($key);
            if ($cache_data)
            {
                $url = $cache_data['callback_url'];
                $click_id = $cache_data['click_id'];
            }
            else
            {
                return false;
            }
            break;

        default:
            return false;
    }
    $option = array(
        'table' => 'channel_activation',
        'where' => array(
            'cid' => $cid,
            'unid' => $unid,
        ),
    );
    $result = $model->findOne($option, 'ad_channel');
    if ($result)
        return false;
    $ret = require_url($url);
    $ret_data = json_decode($ret, true);
    $log_data = array(
        'cid' => $cid,
        'unid' => $unid,
        'update_tm' => $submit_tm,
        'imei' => $data['imei'],
        'imsi' => $data['imsi'],
        'mac' => $data['mac'],
        'ip' => $data['ip'],
        'click_id' => $click_id,
        'callback_url' => $url,
    );
    if ($ret && ($ret_data['ret'] === 0) )
    {
        $db_data = $log_data;
        $db_data['__user_table'] = 'channel_activation';
        $ret = $model->insert($db_data, 'ad_channel');
        if (!$ret)
            return false;
        $redis->delete($key);
        $log_data['callback_status'] = 'success';
        permanentlog('add_channel_stat.log', json_encode($log_data));
    }
    else
    {
        $log_data['callback_status'] = 'failed';
        $log_data['ret_str'] = $ret;
        permanentlog('add_channel_stat.log', json_encode($log_data));
    }
}

function crypt_gdt_url($click_id, $muid, $conv_time, $client_ip)
{
    //广点通提供参数
    $advertiser_id = "5387535";
    $sign_key = '94adb679b16a7658';
    $encrypt_key = 'BAAAAAAAAAAAUjUP';
    $base_url = 'http://t.gdt.qq.com/conv/app/1101166194/conv?';

    //加密流程
    $query_string = "muid={$muid}&conv_time={$conv_time}&client_ip={$client_ip}";
    $page = $base_url . $query_string;
    $encode_page = urlencode($page);
    $property = "${sign_key}&GET&${encode_page}";
    $signature = md5($property);
    $base_data = "${query_string}&sign=${signature}";
    $data = urlencode(base64_encode(simple_xor($base_data, $encrypt_key)));
    $attachment = "conv_type=MOBILEAPP_ACTIVITE&app_type=ANDROID&advertiser_id=${advertiser_id}";
    $url = "${base_url}v=${data}&{$attachment}";
    return $url;
}

function simple_xor($a, $b)
{
    $ret = '';
    $j = 0;
    for($i = 0; $i < strlen($a); $i++)
    {
         $ch = $a[$i];
         $ret = $ret . chr(ord($ch)^ord($b[$j]));
         $j++;
         $j = $j % (strlen($b));
    }
    return $ret;
}
