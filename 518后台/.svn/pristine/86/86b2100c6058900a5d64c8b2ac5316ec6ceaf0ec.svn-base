<?php
ini_set('display_errors', true);
error_reporting(E_ERROR | E_PARSE);
date_default_timezone_set('Asia/Shanghai');
var_dump($_POST['do']);
if($_POST['do']=='save') {
	$dir = $_POST['static_data'].'/img/'.date('Ym/d').'/';
	if(!is_dir($dir)) mkdir($dir,0777,true);
	$rt = array();
	if($_FILES) {
		foreach($_FILES as $key=>$val) {
			list($msec,$sec) = explode(' ',microtime());
			$msec = substr($msec,2);
			$dst = $dir.$key.'_'.$msec.'.jpg';
			$src = $val['tmp_name'];
			if(move_uploaded_file($src,$dst)) {
				$rt[$key] = str_replace($_POST['static_data'],'',$dst);
			} else {
				$rt[$key] = 'failed';
			}
		}
	}
	exit(json_encode($rt));
}
