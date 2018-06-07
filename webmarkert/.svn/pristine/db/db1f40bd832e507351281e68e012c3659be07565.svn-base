<?php
function permanentlog($name, $log, $now="", $define_host="") {
	if($now=="") $now = time();
	/*$switch_host = array(
		'118.26.224.21' => 'gomarket.goapk.com',
		'118.26.235.21' => 'www.anzhi.com',
		'118.26.235.12' => 'gomarket.goapk.com'
	);*/		
	$host = strtolower($_SERVER['HTTP_HOST']);
	if (strstr($host, ":")) {
		$host = substr($host, 0, strrpos($host, ":"));
	}
	/*if (isset($switch_host[$host])) {
		$host = $switch_host[$host];
	}*/
	empty($host) && $host = 'unknown';

	if (defined(APP_NAME)) {
	    if (APP_NAME == 'gomarket') {
            $host = 'gomarket.goapk.com';
        } else if (APP_NAME == 'game') {
            $host = 'c.azyx.com';
        } else if (APP_NAME == 'www') {
            if (preg_match("/^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$/", $host)) {
                $host = 'www.anzhi.com';
            }
        }
	}
	
	if ($define_host) {
	    $host = $define_host;
	}
	
	if (load_config('use_remote_log')) {
		$start = microtime_float();
		$data = array(
			'f' => $name,
			'l' => $log,
			'h' => $host,
			't' => $now,
		);
		load_helper('task');
		$end = microtime_float();
		$spend = $end - $start;
        $task_client = get_task_client();
        $task_client->doBackground("rlog", json_encode($data));
		if ($spend > 0.2) {
			$day = date('Y-m-d', time());
			$time = date('Y-m-d H:i:s', time());
			file_put_contents('/tmp/task_slow-'. $day. '.log', "{$time} {$name} {$spend}\n", FILE_APPEND);
		}
	}

	$full_path = P_LOG_DIR . "/" . $host . "/" . date("Y-m-d", $now) . "/" . $name;
	$dir = dirname($full_path);
	if (!is_dir($dir)) {
		if (!mkdir($dir, 0755, true)) {
			go_log(GO_ERROR, 'cannot add permanent_log');
			return false;
		}
	}

	$re = file_put_contents($full_path, $log. "\n", FILE_APPEND);
	
	if ($re === false) {
	    $day = date('Y-m-d', time());
	    file_put_contents('/tmp/permanentlog-'. $day. '.error.log', $log. "\n", FILE_APPEND);
	}
	
	return true;
}

function get_http_response($theURL) {
    $headers = get_headers($theURL, 1);
    $length = $headers['Content-Length'];
    return array(
    	'code' => substr($headers[0], 9, 3),
    	'length' => $length,
    );
}


function getSplitUrl($info, $fn){
	if (!is_array($info)) {
		$info = array('filesize' => $info);
	}
	$apksize = $info['filesize'];
	$apk_host = getApkHost($info, false);
	
	$block_num = ceil($apksize/load_config('split_block'));
	$split_url = array();
	$base_name = basename($fn);
	$base_dir = '';
	if ($block_num == 0) return $split_url;

	if (preg_match('/^\/apk\/\d{6}\/\d{2}/', $fn)) {
		$base_dir = $apk_host. str_replace(DS. $base_name, '', $fn);
	} else {
		$base_dir = $apk_host. DS .load_config('split_dir'). DS . substr($base_name, 0,load_config('split_dirname_num'));
	}
	for($i=0;$i<$block_num;$i++){
		$name = sprintf("${base_name}.%04d", $i);
		$split_url[] = encrypt_apk_url($base_dir. DS . $name);
	}

	return $split_url;
}

function getFilterOption($reload = false)
{
	static $filter;
	if (empty($filter) || $reload) {
		$product = isset($_SESSION['product']) ? $_SESSION['product'] : 1;
		$filter = array(
			'device' => $_SESSION['MODEL_DID'],
			'firmware' => $_SESSION['FIRMWARE'],
			'channel' => $_SESSION['MODEL_CID'],
			'wchannel' => $_SESSION['MODEL_CID'],
			//'authorized' => $_SESSION['MODEL_AUTHORIZED'],
			'auth' => $_SESSION['MODEL_AUTHORIZED'],
			'abi' => $_SESSION['ABI'],
			'product' => $product,
			'has_channel_soft' => $_SESSION['has_channel_soft'],
		);
		if(isset($_SESSION['app2sd'])){
			 $filter['app2sd'] = 1;
		}
		if (isset($_SESSION['USER_IMSI']))
			$filter['operator'] = substr($_SESSION['USER_IMSI'], 0, 5);
		if (isset($_SESSION['MODEL_OID']))
			$filter['model_oid'] = $_SESSION['MODEL_OID'];
        
		if (empty($_SESSION['device:has_rule'])){
			unset($filter['device']);
		}

		if (empty($_SESSION['channel:has_rule'])){
			unset($filter['channel']);
			unset($filter['wchannel']);
		}
	}
	return $filter;
}

function fuzzy($n) {
	$n = intval($n);
	$min = 10;
	$max = 1000000;
	if ($n < $min)
		return " < ${min} ";

	if ($n > $max)
		return " > ${max} ";
	$i = 1;
	$base = 10;
	while ($i < $n) {
		$i = $i * $base;
	}
	$step = $i / $base;
	$f = intval($n / $step) * $step;
	$t = $f + $step - 1;
	return "${f} - ${t}";
}
function toLocation($url) {
    header("content-type:text/html; charset=utf-8");
    header("Location: {$url}");
    exit;
}
function exitOnError($msg, $url = "") {
    $msg = strip_tags($msg);
    $jump = "";
    if ($url) $jump = "window.self.location='{$url}';";
    echo "<script>alert(\"{$msg}\"); $jump </script>";
    exit;
}
