<?php
require_once dirname(__FILE__) . '/init.php';
define('_APP_NAME', 'perfect');
define('_APP_SITE', 'm.anzhi.com');
define('_APP_SITE_PUBLIC', _APP_SITE.'/'._APP_NAME.'/public');
$tplObj->out['public_url'] = 'http://'._APP_SITE_PUBLIC;
$tplObj -> out['new_static_url'] = $configs['new_static_url'];
$model = new GoModel();


$softid = $_GET['softid'];
if (empty($softid))
        exit;


$option = array(
    'where' => array('softid' => $softid,'status' => 1,'hide' => 1),
    'table' => 'sj_soft',
    'field' => 'softid,softname,package,score,version,intro',
);
$data = $model->findOne($option);
if (empty($data))
    exit;


$option = array(
    'where' => array('softid' => $softid,'package_status' => 1),
    'table' => 'sj_soft_file',
    'field' => 'softid,filesize,iconurl_72,iconurl_125',
);
$data_file = $model->findOne($option);

$tplObj->out['softid'] = $data['softid'];
$tplObj->out['softname'] = $data['softname'];
$tplObj->out['package'] = $data['package'];

$score_light=array();
$t_score=ceil($data['score']/2);
for($i=1;$i<6;$i++){
    if($i<$t_score){
        $score_light[$i]=1;
    }else if($i==$t_score){
        if($data['score']<$i*2){
            $score_light[$i]=2;
        }else{
            $score_light[$i]=1;
        }
    }else{
        $score_light[$i]=0;
    }
}
$tplObj->out['score_light'] = $score_light;
$tplObj->out['version'] = $data['version'];
$tplObj->out['intro'] = $data['intro'];

$tplObj->out['filesize'] = formatFileSize('',$data_file['filesize']);

$option = array(
    'where' => array('softid' => $softid,'status' => 1),
    'table' => 'sj_soft_thumb',
    'field' => 'softid,image_thumb,image_raw',
);
$thumb_file = $model->findAll($option);
$tplObj->out['thumb_file'] = $thumb_file;
$img_url = getImageHost();

$tplObj->out['img_url'] = $img_url;
$option = array(
    'where' => array('softid' => $softid,'status' => 1),
    'table' => 'sj_icon',
    'field' => 'softid,iconurl_72,iconurl_125, iconurl_512',
);
$data_icon = $model->findOne($option);

$tplObj->out['iconurl_125'] = $data_icon['iconurl_125'];

if($data_icon['iconurl_512']){
   $tplObj->out['iconurl_125'] = $data_icon['iconurl_512'];
}

if(!$data_icon['iconurl_512']){
   $tplObj->out['iconurl_125'] = $data_file['iconurl_512'];
}


// 展示模版数据
$tplObj->display('soft_expand/soft_expand_tpl.html');


