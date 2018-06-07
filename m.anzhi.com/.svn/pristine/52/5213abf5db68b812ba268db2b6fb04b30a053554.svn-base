<?php
//百度回调接口
$dir = '/data/att/permanent_log/baidu/'. date('Y-m-d');
if (is_dir($dir)) {
	mkdir($dir, 0777, true);
}

$file = $dir. '/docid.log';
file_put_contents($file, file_get_contents('input://php'));
echo json_encode(array('ret' => 0, 'memo' => ''));