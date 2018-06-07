<?php
include 'init.php';

$content_id = $_GET['id'];
$type = $_GET['type'];

$img_prefix = getIconHost();
load_helper('utiltool');
$filter_option = getFilterOption();

if((!empty($type))&&($type==1)){
	$content_model = load_model('caijinews');
	$res = $content_model->getDetailById($content_id);
	$content_info = $res[$content_id];
	$tplObj->out['url'] = $content_info['content_wap_url'];
	$tplObj->out['title'] = $content_info['show_news_name'];
	$option = array(
		'where' => array(
			'id' => $content_info['website_id']
		),
		'table' => 'caiji_video_config',
	);
	$site_info = $content_model->findOne($option,'caiji');
}else{
	$content_model = load_model('coopNews');
	$res = $content_model->getDetailById($content_id);
	$content_info = $res[$content_id];
	$tplObj->out['url'] = $content_info['url'];
	$tplObj->out['title'] = $content_info['title'];
	
	$config_model = load_model('pu_config');
	$config_res = $config_model->getConfig(array('coop_chl_id' ));
	if ($config_res && $config_res['coop_chl_id']) {
		$channel = $config_res['coop_chl_id'][1];
		$filter_option['channel_soft_cid'] = $channel;
		$filter_option['channel'] = $channel;
	}
	$option = array(
		'where' => array(
			'id' => $content_info['site_id']
		),
		'table' => 'coop_site',
	);
	$site_info = $content_model->findOne($option);
	
}

$package = $site_info['package'];

$softlist = load_model('softlist');
$dev_model = load_model('dev');
$res = $softlist->getPkg2Id($package);
$softids = array_flip($res[$package]);
$softids = $dev_model->filterSoftId($softids, $filter_option);
$softid = array_pop($softids);

$res = $softlist->getSoftInfos($softid);
$softinfo = $res[$softid];

$tplObj->out['iconurl'] = $img_prefix.$softinfo['iconurl'];
$tplObj->out['softname'] = $softinfo['softname'];
$tplObj->out['score'] = $softinfo['score'];
$tplObj->out['softid'] = $softinfo['softid'];
$tplObj->out['package'] = $softinfo['package'];
$tplObj->out['filesize'] = formatFileSize('', $softinfo['filesize']);

$tplObj->display("news.html");
