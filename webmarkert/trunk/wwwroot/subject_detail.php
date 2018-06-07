<?php
include_once(dirname(realpath(__FILE__)).'/init.php');

$result = gomarket_action('soft.GoGetSoftSubject',array('LIST_INDEX_START'=>0,'LIST_INDEX_SIZE' =>10, 'VR' => 1));

$feature_list = $result['DATA'];

$default_subject_flip = array_flip($default_subject);
$tplObj -> out['default_subject'] = $default_subject;
$fids = array();
foreach($feature_list as $key => $val){
	if($val[1] > 1000000){
		$val[1] = intval($val[1] / 1000000);
	}
	$fid = $val[1];
	$fids[] = $fid;
	//if(!isset($default_subject_flip[$val[1]])){
		$feature_arr_go[$fid] = $val;
	//}
}
$id = $_GET['id'];


if ($id > 1000000){
	$id = intval($id / 1000000);
}
if($id == $ctc_subject){
	$tplObj->out['type_two'] = 'ctc_subject';
}

$page = (int)$_GET['page']?$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) *$limit;
if($id =='999999'){
	$pack_name = get_white_list();
	$intro = array();
	foreach($pack_name as $key=>$val){
		$tmp = gomarket_action('soft.GoGetSoftDetailPackage', array('PACKAGE_NAME' => $key, 'VR' => 1, 'EXTRA_OPTION_FIELD' => array('A.upload_tm','B.min_firmware','A.intro','A.category_name','subname','A.category_id','A.costs','tags','min_firmware','max_firmware', 'status', 'hide', 'iconurl_72', 'parent_name','A.update_content')));
		if ($tmp) {
			$intro[$key] = $tmp;
			$intro[$key]['size'] = formatFileSize($tmp['SOFT_SIZE'],1);
			$intro[$key]['qrimg'] = get_qrimg($tmp['ID'],$tmp[$key],$val[15],$tmp['ICON']);
			$intro[$key]['info'] = mb_substr($tmp['intro'],0,50,'utf-8');;		
		}
	}
	
}

$feature_soft_go = gomarket_action('soft.GoGetSoftSubjectAllList', array('ID' => $id,'LIST_INDEX_START' => $offset ,'LIST_INDEX_SIZE' => $limit, 'TYPE' => 1, 'VR' => 1, 'GET_INFO' => 1,'GET_COUNT' => true));
$feature_soft_arr = $feature_soft_go['DATA'];
foreach($feature_soft_arr as $key => $val){
	$val['size'] = formatFileSize($val[9],1);
	$val['qrimg'] = get_qrimg($val[0],$val[7],$val[15],$val[1]);
	$feature_soft_arr[$key] = $val;
}

$fids[] = $id;
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
	if ($fid == $id) {
		!empty($val['title']) && $feature_soft_go['NAME'] = $val['title'];
		!empty($val['text']) && $feature_soft_go['SUB_DES'] = $val['text'];
	}
	if (isset($feature_arr_go[$fid])) {
		!empty($val['title']) && $feature_arr_go[$fid][2] = $val['title'];
		!empty($val['text']) && $feature_arr_go[$fid][5] = $val['text'];
	}
}
$flist = array();
foreach ($feature_arr_go as $val) {
	$flist[] = $val;
}

$count = $feature_soft_go['COUNT'];
$tplObj->out['page'] =  pagination_arr($page, $count, $limit, 10);
$tplObj -> out['feature'] = $feature_soft_go;
$tplObj -> out['feature_soft_arr'] = $feature_soft_arr;
$tplObj -> out['intro'] = $intro;
$tplObj -> out['feature_arr'] = $flist;

if($page == 1){
	$tplObj -> out['type'] = 'zt_detail';	
}elseif($page > 1){
	$tplObj -> out['type'] = 'zt_detail_page';
}

if($id =='999999'){
    $tplObj -> out['seo_title_name'] = 'zt_detail_white';
	display("zt_detail_white.html");
}else{
	display('zt_detail.html');
}
