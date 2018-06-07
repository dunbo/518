<?php
function filter_softlist($key, $offset, $size, $filter_option = array(), $cust_block_size = false, $is_return_id = true,$extra_option = array())
{
	$data = array(
		'key' => 'list',
		'list_type' => $key,
		'offset' => $offset,
		'size' => $size,
		'is_return_id' => $is_return_id,
		'cust_block_size' => $cust_block_size,
		'filter' => $filter_option,
        'extra_option'=>$extra_option,
	);
	return get_filter_data($data);
}

function filter_softids($softids, $filter_option = array(), $is_return_id = true, $extra_option = array())
{
	$data = array(
		'key' => 'softids',
		'softids' => $softids,
		'is_return_id' => $is_return_id,
		'filter' => $filter_option,
		'extra_option' => $extra_option,
	);
if (isset($softids[0])) {
	file_put_contents('/tmp/filter_error.log', go_trace(), FILE_APPEND);
}
	return get_filter_data($data);
}

function filter_search($search_key, $offset, $size, $filter_option = array(),$extra_option = array())
{
	$data = array(
		'key' => 'search',
		'search_key' => $search_key,
		'offset' => $offset,
		'size' => $size,
		'filter' => $filter_option,
		'extra_option' => $extra_option,
	);
	return get_filter_data($data);
}

function filter_suggest($package, $offset, $size, $filter_option = array(),$bi_option = array())
{
	$data = array(
		'key' => 'suggest',
		'package' => $package,
		'offset' => $offset,
		'size' => $size,
		'filter' => $filter_option,
		'bi_option'=>$bi_option,
	);
	return get_filter_data($data);
}

function filter_recommend($filter_option = array(),$bi_option = array())
{
	$data = array(
		'key' => 'recommend',
		'filter' => $filter_option,
		'bi_option'=>$bi_option,
	);
	return get_filter_data($data);
}


function get_filter_data($data)
{
	$start = microtime_float();
	$url = load_config('filter_api_uri');
	$data['referer'] = $_POST['KEY'];
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
	$json = json_encode($data);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
    //curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
	$header = array(
		'Host:filter.anzhi.com',
		'Content-Type: application/json',
		'Content-Length:'.strlen($json),
	);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $header);  


    $result = curl_exec($ch);
	$info = curl_getinfo($ch);
	$code = $info['http_code'];
	if ($code == 200) {
		if (empty($result)) {
			$msg = date('Y-m-d H:i:s');
			$day = date('Y-m-d');
			$msg .= "{$json} return empty \n";
			file_put_contents("/tmp/filter_api_error_{$day}.log", $msg, FILE_APPEND); 
		}
		$result = json_decode($result, true);
		$end = microtime_float();
		$s = $end - $start;
		if ($s >0.5) {
			$msg = date('Y-m-d H:i:s');
			$day = date('Y-m-d');
			$msg .= "{$json} spend {$s}\n";
			file_put_contents("/tmp/filter_api_slow_{$day}.log", $msg, FILE_APPEND);
		}
		return $result;
	} else {
		$msg = date('Y-m-d H:i:s');
		$day = date('Y-m-d');
		$msg .= "{$json} return empty \n";
		file_put_contents("/tmp/filter_api_error_{$day}.log", $msg, FILE_APPEND);	
		return false;
	}
	return true;
}



