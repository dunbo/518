<?php

function filter_softlist($cache_key, $filter = array(), $offset = 0, $count = 20, $order_key = 0, $is_return_id = true)
{       
    if(empty($cache_key)) return false; 
	$data = array(
		'key' => 'list',
		'list_type' => $cache_key,
		'offset' => $offset,
		'count' => $count,
		'filter' => $filter,
		'order_key' => $order_key,
		'is_return_id' => $is_return_id
	);
	return get_filter_data($data);
}

function filter_packagelist($package_list, $filter = array(), $offset = 0, $count = 20, $order_key = 0, $is_return_id = true)
{       
    if(empty($package_list)) return false; 
	$data = array(
		'key' => 'list',
		'package_list' => $package_list,
		'offset' => $offset,
		'count' => $count,
		'filter' => $filter,
		'order_key' => $order_key,
		'is_return_id' => $is_return_id
	);
	return get_filter_data($data);
}
function filter_softlist_defined_list($new_defined_list, $filter = array(), $offset = 0, $count = 20, $is_return_id = true)
{       
    if(empty($new_defined_list)) return false;
	$data = array(
		'key' => 'list',
		'new_defined_list' => $new_defined_list,
		'offset' => $offset,
		'count' => $count,
		'filter' => $filter,
		'is_return_id' => $is_return_id
	);
	return get_filter_data($data);
}

function filter_search($search_key, $filter = array(), $offset = 0, $count = 20, $is_return_id = true)
{       
    if(empty($search_key)) return false;
	$data = array(
		'key' => 'search',
		'search_key' => $search_key,
		'offset' => $offset,
		'count' => $count,
		'filter' => $filter,
		'order_key' => 'SEARCH_KEY_SOFTID_ORDER',
		'is_return_id' => $is_return_id
	);
	return get_filter_data($data);
}

function get_filter_data($data)
{
	$url = load_config('filter_api_uri');
	
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
	$json = json_encode($data);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
    curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
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
		$result = json_decode($result, true);
		return $result;
	} else {
		
		return false;
	}
	return true;
}

