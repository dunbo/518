
<?php
require_once dirname(__FILE__) . '/init.php';

$id = $_GET['id'];
if (empty($id))
        exit;
$model = new GoModel();
$where = array(
    'id' => $id,
    'status' => 2,
);
$option = array(
    'where' => $where,
    'table' => 'sj_olgame_news',
    'field' => '*',
);
$data = $model->findOne($option);
if (empty($data))
    exit;
$tplObj->out['id'] = $id;
$tplObj->out['random'] = uniqid();
$tplObj->out['title'] = $data['news_name'];
$tplObj->out['author'] = $data['author'];
$data['module_content'] = htmlspecialchars_decode($data['module_content']);
$img_url = getImageHost();
$data['module_content'] = str_replace('http://118.26.224.18/cmcc', $img_url, $data['module_content']);
$data['module_content'] = str_replace('<!--{ANZHI_IMAGE_HOST}-->', $img_url, $data['module_content']);
$tplObj->out['module_content'] = $data['module_content'];
$tplObj->out['release_tm'] = date(load_config('date_format'), $data['release_tm']);
$tplObj->out['visit_count'] = $data['visit_count'];
// 展示模版数据
$tplObj->display('onlinegame_news_tpl.html');