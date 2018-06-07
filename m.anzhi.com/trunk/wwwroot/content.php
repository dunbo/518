<?php 
include_once(dirname(realpath(__FILE__)).'/init.php');

$model = new GoModel();

$morelist = isset($_GET['morelist']) && !empty($_GET['morelist']) ? $_GET['morelist'] : 0;
$limit = 10; //精彩内容每页数量

$option = array(
	'where' => array(
		'status' => 1,
		'passed' => 2,
		'title' => array('exp', "!=''"),
	),
	'order' => 'id desc',
	'cache_time' => '600',
	'offset' => $morelist*$limit,
	'limit' => $limit,
	'field' => 'id,package,title,show_style,explicit_pic,title2',
	'table' => 'sj_soft_content_explicit'
);
$result = $model->findAll($option);
foreach($result as $key => $val){
    $explicit_pic = json_decode($val['explicit_pic'], true);
    if($val['show_style'] == 2){
        $explicit_pic = array_slice($explicit_pic, 1);
    }
    $result[$key]['explicit_pic'] = $explicit_pic;
}
$tplObj->out['ImageHost'] = getImageHost();
$tplObj->out['content'] = $result;
if ($_GET['morelist'] >= 1){
    $tplObj->out['morelist'] = $_GET['morelist'];
    $tplObj->display("content_ajax.html");
} else {
    $tplObj->display("content.html");
}