<?php

/**
 * 加密 / 解密函数
 * @todo	本函数存在很大问题，但是为来兼容旧版本，留来下来
 * Enter description here ...
 * @param unknown_type $str
 * @param unknown_type $name
 * @param unknown_type $timestamp
 */
function go_crypt_linear($str, $name, $timestamp) {
	$name = (string) $name;
	$timestamp = (string) $timestamp;

	if (function_exists('go_crypt_linear_native')) {
		//file_put_contents('/tmp/crypt.log', date('Y-m-d H:i:s') . "\n");
		return go_crypt_linear_native($str, $name, $timestamp);
	}

	
	$name_len = strlen($name);
	$timestamp_len = strlen($timestamp);
	$name_idx = 0;
	$timestamp_idx = 0;
	$len = strlen($str);
	$ret = array();
	for ($idx = 0;$idx < $len;$idx+= 1) {
		$ch = substr($str, $idx, 1);
		$digit = ord($ch) + 128 * 2 - ord($name[$name_idx]) - ord($timestamp[$timestamp_idx]);
		$digit = $digit % 256;
		$ret[] = chr($digit);
		$name_idx = ($name_idx + 1) % $name_len;
		$timestamp_idx = ($timestamp_idx + 1) % $timestamp_len;
	}
	$encrypted = implode('', $ret);
	return $encrypted;
}

function go_decrypt_linear($str, $name, $timestamp) {
	$name = (string) $name;
	$timestamp = (string) $timestamp;
	
	$name_len = strlen($name);
	$timestamp_len = strlen($timestamp);
	$name_idx = 0;
	$timestamp_idx = 0;
	$len = strlen($str);
	$ret = array();
	for ($idx = 0;$idx < $len;$idx+= 1) {
		$ch = substr($str, $idx, 1);
		$digit = ord($ch) + ord($name[$name_idx]) + ord($timestamp[$timestamp_idx]);
		$digit = $digit % 256;
		$ret[] = chr($digit);
		$name_idx = ($name_idx + 1) % $name_len;
		$timestamp_idx = ($timestamp_idx + 1) % $timestamp_len;
	}
	$encrypted = implode('', $ret);
	return $encrypted;
}
