<?php

require_once dirname(__FILE__) . '/init.php';

$id = $_GET['id'];
if (empty($id))
        exit;
$model = new GoModel();
$where = array(
    'id' => $id,
	'push_type' => 4,
    'status' => 1,
);
$option = array(
    'where' => $where,
    'table' => 'sj_sdk_push',
    'field' => '*',
);
$data = $model->findOne($option);
if (empty($data))
    exit;
$data['item_content'] = htmlspecialchars_decode($data['item_content']);
$img_url = getImageHost();
$data['item_content'] = str_replace('http://118.26.224.18/cmcc', $img_url, $data['item_content']);
$data['item_content'] = str_replace('<!--{ANZHI_IMAGE_HOST}-->', $img_url, $data['item_content']);

$tplObj->out['title'] = $data['title'];
$tplObj->out['item_content'] = $data['item_content'];
// 展示模版数据
$tplObj->display('sdk_push_notice_tpl.html');