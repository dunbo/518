<?php
echo "1";exit;
include_once(dirname(realpath(__FILE__)).'/init.php');
header('content-type:text/html;charset=utf-8');
$chl_360 = '1eb231ac5c073ba76a2d0ba6df5d634c9750449c';
if (!$softid = (int)$_GET['s']) {
    exit;
}
$r = get_download_url($softid,'','1', false);
if ($r == DOWNLOAD_NO_SOFT) {
    exit;
}
list($downurl, $package, $ipbanned) = $r;
if ($r == DOWNLOAD_IPBANNED) {
	echo '0';
	exit;
}
echo "1";
