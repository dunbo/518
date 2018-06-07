<?php  
require_once(dirname(__FILE__).'/../../init.php');
$worker->addFunction("yingyongbao_report", "yingyongbao_report_func");  
while ($worker->work());  
function yingyongbao_report_func($job)  
{  
    if ( !($p = json_decode($job->workload(), true)) ) {
        return False;
    }	
	if (empty($p[0])) return false;
	if (empty($p[1])) return false;
	$j = $job->workload();

	$r = request($p[1], $p[0]);
	load_helper('utiltool');
	permanentlog('yingyongbao_report.log', $j."|ret:".$r);

	return json_encode($r);
}

/**
 * 接口请求函数
 *
 */
function request($url, $data)
{
    $url .= '&fr='.time();
	$start = microtime_float();
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);   
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1);
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
	curl_setopt($ch, CURLOPT_TIMEOUT, 10);
	
    curl_setopt($ch, CURLOPT_HTTPHEADER, array("Expect:"));  

	$content = curl_exec($ch);
	$info = curl_getinfo($ch);
	///echo $url;
	curl_close($ch);
	$end = microtime_float();
	$spend = $end - $start;
	$info = json_encode($info);
	if ($spend > 0.5) {
		$now = time();
		$time = date('Y-m-d H:i:s', $now);
		$day = date('Y-m-d', $now);
		$sid = session_id();
		file_put_contents("/tmp/yingyongbao_slow_{$day}.log", "{$start} : {$url}: {$spend} {$sid} {$info}\n", FILE_APPEND);
	}
	return $content;
}
