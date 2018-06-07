<?php
include 'init.php';

$content_id = $_GET['id'];

$img_prefix = getIconHost();
load_helper('utiltool');

if(empty($_GET['url']) || empty($_GET['title']) || empty($_GET['pkg'])) {
	exit('404');
}


$tplObj->out['url'] = $_GET['url'];
$tplObj->out['title'] = $_GET['title'];
$package = $_GET['pkg'];
$softlist = load_model('softlist');
$dev_model = load_model('dev');
$res = $softlist->getPkg2Id($package);
$softids = array_flip($res[$package]);
$filter_option =  array('channel_soft_cid' => 1577,'channel' => 1577);
$softids = $dev_model->filterSoftId($softids, $filter_option);
$softid = array_pop($softids);

$res = $softlist->getSoftInfos($softid);
$softinfo = $res[$softid];
if(empty($softinfo)) {
	exit('404');
}
$tplObj->out['iconurl'] = $img_prefix.$softinfo['iconurl'];
$tplObj->out['softname'] = $softinfo['softname'];
$tplObj->out['score'] = $softinfo['score'];
$tplObj->out['softid'] = $softinfo['softid'];
$tplObj->out['package'] = $softinfo['package'];
$tplObj->out['filesize'] = formatFileSize('', $softinfo['filesize']);

$tplObj->display("ucnews.html");
