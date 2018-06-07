<?php
require_once(dirname(__FILE__).'/../init.php');
#ini_set('displays_errors', true);
#error_reporting(E_ALL);
load_helper('utiltool');
load_helper('task');
$_SERVER['HTTP_HOST'] = 'x_channel';

$worker->addFunction('x_channel_downlaod', 'x_channel_downlaod_func');
while ($worker->work());

function x_channel_downlaod_func($job)
{
	$string = $job->workload();
	if ( !($data = json_decode($string, true)) ) {
		return false;
	}
	$sid = $data['sid'];
	$param = $data['param'];

	$day = date('Ymd');
	$hour = date('G');
	$filter_redis_config = load_config('filter/redis_write');
	$filter_redis = new GoRedisCacheAdapter($filter_redis_config);
	$filter_redis->pingConn();
	$num = $filter_redis->setx('hincrby', "X:{$day}:PACKAGE:HOUR", $hour, -1);
	//echo "X:{$day}:PACKAGE:HOUR $hour $num \n";
	if ($num >=0) {
		$all = $filter_redis->setx('hgetall', "X:{$day}:PACKAGE");
		$need_package = array();
		foreach ($all as $package => $num) {
			if ($num >0) {
				$need_package[$package] = $package;
			}
		}
		if ($need_package) {
			$package = array_rand($need_package);
			$num = $filter_redis->setx('hincrby', "X:{$day}:PACKAGE", $package, -1);
			//echo "X:{$day}:PACKAGE $package $num \n";

			if ( $num >= 0) {
				startDownload($sid, $param, $package);
			}
		}
	}
}

function startDownload($sid, $param, $package)
{
	$model = new GoModel();
	session_write_close();
	session_id($sid);
	session_start();
	if (empty($_SESSION['MODEL_CID'])) {//session失效的话
		return false;
	}

	$option = array(
		'table' => 'sj_soft',
		'where' => array(
			'package' => $package
		),
		'order' => 'softid desc',
		'field' => 'softid, package, softname,version_code'
	);
	$uuid = false;
	if ($soft = $model->findOne($option)) {
		$option = array(
			'table' => 'sj_soft_file',
			'where' => array(
				'softid' => $soft['softid']
			),
			'order' => 'id desc',
		);
		if ($file = $model->findOne($option)) {
			$category_types = array('top_1_hot' => 1, 'top_2_hot' => 1, 'top_new' => 1);
			$category_type = array_rand($category_types);

			$type = rand(1, 2);

			$content = file_get_contents(dirname(__FILE__). '/data/'. $category_type. '_'. $type. '.txt');
			$arr = explode("\n", $content);

			$download_roll = mt_rand(1,100);
			$install_roll = mt_rand(1,100);

			foreach ($arr as $val) {
				$json = json_decode($val, true);
				if (!$json) continue;
				if ($json['KEY'] == 'POST_DOWNLOAD_STATE') {
					if ($download_roll < 10) {
						continue;
					}
					sleep(rand(1, 3));
				}

				if ($json['KEY'] == 'SOFT_INSTALL') {
					if ($install_roll < 5) {
						continue;
					}
					sleep(rand(1, 3));
				}

				if (isset($json['ID'])) {
					$json['ID'] = $soft['softid'];
				}
				if (isset($json['TID'])) {
					$json['TID'] = $soft['softid'];
				}
				if (isset($json['PACKAGE_NAME'])) {
					$json['PACKAGE_NAME'] = $package;
				}
				if (isset($json['PARENT_PKG'])) {
					$json['PARENT_PKG'] = $package;
				}
				if (isset($json['APK_MD5'])) {
					$json['APK_MD5'] = $file['md5_file'];
				}

				if (isset($json['VCODE'])) {
					$json['VCODE'] = $soft['version_code'];
				}

				if (isset($json['MD5'])) {
					$json['MD5'] = $file['md5_file'];
				}
				if (isset($json['APP_SIZE'])) {
					$json['APP_SIZE'] = $file['filesize'];
				}		
				if (isset($json['INTEGERATE_URL'])) {
					$json['INTEGERATE_URL'] = 'http://am.apk.anzhi.com'. $file['url'];
				}		
				if (isset($json['TASK_SIGN'])) {
					if (!$uuid) $uuid = create_uuid();
					$json['TASK_SIGN'] = $uuid;
				}

				if (isset($json['DOWNLOAD_URL'])) {
					$json['DOWNLOAD_URL'] = 'http://am.apk.anzhi.com'. $file['url'];
				}
				if (isset($json['REAL_URL'])) {
					$json['REAL_URL'] = 'http://am.apk.anzhi.com'. $file['url'];
				}

				request($json, $_SESSION['RC4_CRYPT_KEY'], $sid);

				if ($json['KEY'] == 'SOFT_INSTALL') {
					//imei, imsi, mac, model, sim_sn, android_id, romversion, resolution(分辨率), sdk(版本名), package_name(下载的包名), download_url(下载地址), used(记录使用状态，默认0)，时间

					$data = array(
						'imei' => $param['DEVICEID'],
						'imsi' => $param['IMSI'],
						'mac' => $param['MAC'],
						'model' => $param['MODEL_NO'],
						'sim_sn' => !empty($param['ICCID']) ? $param['ICCID'] : '',
						// 'android_id' => $param['android_id'],
						// 'romversion' => $param['romversion'],
						'resolution' => $param['RESOLUTION'],
						'sdk' => $param['FIRMWARE'],
						'package_name' => $package,
						'download_url' => 'http://am.apk.anzhi.com'. $file['url'],
						'used' => 0,
						'create_at' => time(),
						'chl' => $param['SUPPLIERS'],
						'__user_table' => 'x_download'
					);
					$model->insert($data);
				}
			}
		}

	}
}


function request($data, $key, $sid)
{
    $version = 6010;
    $vr = 3;
    $plain_body = array(
        'VR' => $vr,
        'EN' => 2
    );
    $plain_body['SID'] = $sid;
    $key = $key;
    $post_data = json_encode($data);
    $post_data = gzencode($post_data);

    $plain_txt = json_encode($plain_body);
    $post_data = rc4_crypt($key, $post_data);
    $plain_body['RL'] = strlen($post_data);
    $plain_txt = json_encode($plain_body);

    $plain_size = strlen($plain_txt);
    $request = 'AnZhiM';
    $request .= int2bin($version, 4);
    $request .= int2bin($plain_size, 4);
    $request .= $plain_txt;
    $request .= $post_data;
    $raw = post($request);
}


function post($post_data)
{
    static $x ;
    $tmpurl = 'http://127.0.0.1/api.php';

    $ch = curl_init();
    $header = array('host:api.goapk.com');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);   
    curl_setopt($ch, CURLOPT_URL, $tmpurl);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $content = curl_exec($ch);
    $info = curl_getinfo($ch);
    // var_dump($content, $info['http_code']);
    //echo "{$info['http_code']} {$info['size_download']}\n";
    // var_dump($info);
    curl_close($ch);
    //file_put_contents("1.log", $post_data);
    return $content;
}

function rc4_crypt($key, $msg) {
    //return rc4_crypt_native($key, $msg);
    //return $msg;
    $td = mcrypt_module_open('arcfour', '' , 'stream', '');
    mcrypt_generic_init($td, $key, null);
    $encrypted = mcrypt_generic($td, $msg);
    mcrypt_generic_deinit($td);
    mcrypt_module_close($td);
    return $encrypted;
}

function int2bin($val, $align) {
    $str = "";
    $n = 0;
    do {
        $chr = $val % 256;
        $str .= chr($chr);
        $val = $val >> 8;
        $n += 1;
    } while ($val != 0);
    for (; $n < $align; $n++)
        $str .= "\0";
    return strrev($str);
}
