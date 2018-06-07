<?php
$seccodedata['type'] = 0; //0英文图片验证码 1中文图片验证码 2Flash 验证码 3语音验证码
$seccodedata['width'] = 150;
$seccodedata['height'] = 60;
$seccodedata['background'] = 1;
$seccodedata['adulterate'] = 1;
$seccodedata['ttf'] = 1;
$seccodedata['angle'] = 1;
$seccodedata['color'] = 1;
$seccodedata['size'] = 1;
$seccodedata['shadow'] = 1;
$seccodedata['animator'] = 1;
$timestamp = time();
$_DCOOKIE = array();
$seccode = 0;
$charset = "UTF-8";
$GLOBALS['auth_key'] = "duyipeng";
define("_SITE_ROOT_",dirname(__FILE__)."/");
define("_SESSION_PATH_",_SITE_ROOT_."session");
//session_save_path(_SESSION_PATH_);
session_start();
//这里定义session回收命令


function formcheck($secc,$flag=0){
	$seccode = $_SESSION['seccode'];
	seccodeconvert($seccode);
	$tmp = substr($seccode, 0, 1);	
	if(strtoupper($secc) != $seccode) {
       return false;
     }
	if(!$flag) $_SESSION['seccode'] = $seccode = random(6, 1) + $tmp * 1000000;
	return true;
}
function random($length, $numeric = 0) {
	PHP_VERSION < '4.2.0' ? mt_srand((double)microtime() * 1000000) : mt_srand();
	$seed = base_convert(md5(print_r($_SERVER, 1) . microtime()), 16, $numeric ? 10 : 35);
	$seed = $numeric ? (str_replace('0', '', $seed) . '012340567890') : ($seed . 'zZ' . strtoupper($seed));
	$hash = '';
	$max = strlen($seed) - 1;
	for($i = 0; $i < $length; $i++) {
		$hash .= $seed[mt_rand(0, $max)];
	} 
	return $hash;
} 

function dheader($string, $replace = true, $http_response_code = 0) {
	$string = str_replace(array("\r", "\n"), array('', ''), $string);
	if (empty($http_response_code) || PHP_VERSION < '4.3') {
		@header($string, $replace);
	} else {
		@header($string, $replace, $http_response_code);
	} 
	if (preg_match('/^\s*location:/is', $string)) {
		exit();
	} 
}

function seccodeconvert(&$seccode) {
	global $seccodedata,$charset;
	$seccode = substr($seccode, -6);
	if($seccodedata['type'] == 1) {
		include_once language('seccode');
		$len = strtoupper($charset) == 'GBK' ? 2 : 3;
		$code = array(substr($seccode, 0, 3), substr($seccode, 3, 3));
		$seccode = '';
		for($i = 0; $i < 2; $i++) {
			$seccode .= substr($lang['chn'], $code[$i] * $len, $len);
		}
		return;
	} elseif($seccodedata['type'] == 3) {
		$s = sprintf('%04s', base_convert($seccode, 10, 20));
		$seccodeunits = 'CEFHKLMNOPQRSTUVWXYZ';
	} else {
		$s = sprintf('%04s', base_convert($seccode, 10, 24));
		$seccodeunits = 'BCEFGHJKMPQRTVWXY2346789';
	}
	$seccode = '';
	for($i = 0; $i < 4; $i++) {
		$unit = ord($s{$i});
		$seccode .= ($unit >= 0x30 && $unit <= 0x39) ? $seccodeunits[$unit - 0x30] : $seccodeunits[$unit - 0x57];
	}
}
function fileext($filename) {
	return trim(substr(strrchr($filename, '.'), 1, 10));
}
?>
