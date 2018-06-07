<?php 
include_once(dirname(realpath(__FILE__)).'/init.php');

$model = new GoModel();

$cur_page = $_GET['page'] ? $_GET['page'] : 1;
$pre_page = 36; //精彩内容每页数量
$show_page = 35; //页签数量

$option1 = array(
	'where' => array(
		'status' => 1,
		'passed' => 2,
		'title' => array('exp', "!=''"),
	),
	'order' => 'create_tm desc',
	'cache_time' => '600',
	'field' => 'id',
	'table' => 'sj_soft_content_explicit'
);
$result1 = $model->findAll($option1);

$page = pagination_arr($cur_page, count($result1), $pre_page, $show_page, $page_url_str = 'page', $request_uri = '/news/news.php');
$tplObj->out['page'] = $page;

$option2 = array(
	'where' => array(
		'status' => 1,
		'passed' => 2,
		'title' => array('exp', "!=''"),
	),
	'order' => 'id desc',
	'cache_time' => '600',
	'offset' => ($cur_page-1)*$pre_page,
	'limit' => $pre_page,
	'field' => 'id,package,title,title2',
	'table' => 'sj_soft_content_explicit'
);
$result2 = $model->findAll($option2);
$tplObj->out['content'] = $result2;
$tplObj->display('content.html');