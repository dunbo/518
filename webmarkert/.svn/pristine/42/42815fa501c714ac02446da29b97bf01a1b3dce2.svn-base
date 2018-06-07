<?php 
include_once(dirname(realpath(__FILE__)).'/../init.php');

$map_pre = 'http://www.anzhi.com/';

/** 主页 */
$map_config['index'] = array(
	'热门应用' => 'list_1_1_hot.html',
	'热门游戏' => 'list_2_1_hot.html',
	'最新应用' => 'list_1_1_new.html',
	'最新游戏' => 'list_2_1_new.html',
	'精彩内容' => 'news/',
);

/**
 * 网站导航
 */
$filter_option = array(
	'abi' => 3
);

include_once('web_map.php');

$web_map = new web_map();

$web_map->tpl = $tplObj;

$web_map->showMap();