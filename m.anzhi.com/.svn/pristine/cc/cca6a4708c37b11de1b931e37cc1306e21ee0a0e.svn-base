<?php

include_once (dirname(realpath(__FILE__)).'/init.php');

$softid = $_GET['softid'];
if (!empty($softid)) {
	$tplObj->out['softid'] = $softid;
	$softinfo = $model->getsoftinfos($softid, getFilterOption());
	if (!empty($softinfo)) {
		$softinfo = $softinfo[$softid];
		$softinfo['down_url'] = "/download.php?softid={$softid}";
		
		$tplObj->out['softname'] = $softinfo['softname'];
		$tplObj->out['iconurl'] = $img_host . $softinfo['iconurl'];
		$tplObj->out['down_url'] = "/download.php?softid={$softid}";
	}
}

$tplObj->display('lottery/defendwar/jump.html');
exit;