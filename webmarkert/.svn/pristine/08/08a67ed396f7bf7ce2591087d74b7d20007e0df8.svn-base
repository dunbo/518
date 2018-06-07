<?php
include_once(dirname(realpath(__FILE__)).'/init.php');

$page = (int)$_GET['page']?$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) *$limit; 
$params = array(
	'VR' => 1,
	'LIST_INDEX_START' => $offset,
	'LIST_INDEX_SIZE' => $limit,
	'GET_COUNT' => true
);
$result = gomarket_action('soft.GoGetSoftSubject', $params);
$fids = array();
foreach($result['DATA'] as $key => $val){
	if($val[1] > 1000000){
		$val[1] = intval($val[1] / 1000000);
	}
	$fid = $val[1];
	$fids[] = $fid;
	$feature_list[$fid] = $val;
}
$option = array(
	'table' => 'sj_webmarket_feature_text',
	'where' => array(
		'feature_id' => $fids,
		'status' => 1
	),
	'field' => 'feature_id,title,text',
	'cache_time' => 1800,
);
$model = new GoModel();
$web_data = $model->findAll($option);
foreach ($web_data as $val) {
	$fid = $val['feature_id'];
	!empty($val['title']) && $feature_list[$fid][2] = $val['title'];
	!empty($val['text']) && $feature_list[$fid][5] = $val['text'];
}
$flist = array();
foreach ($feature_list as $val) {
	$flist[] = $val;
}
$count = $result['COUNT'];
$area = 10;
$tplObj->out['page'] =  pagination_arr($page, $count, $limit, $area);
$tplObj -> out['feature_list'] = $flist;
$tplObj -> out['type'] = 'zt_list';
$tplObj -> display('zt_list.html');
