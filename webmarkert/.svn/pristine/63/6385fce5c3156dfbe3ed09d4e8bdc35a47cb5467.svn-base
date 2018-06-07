<?php
include_once(dirname(realpath(__FILE__)).'/init.php');
$tplObj->out['type'] = 'zt_index';
$result = gomarket_action('soft.GoGetSoftSubject',array('LIST_INDEX_START'=>0,'LIST_INDEX_SIZE' =>16, 'VR' => 1));
$feature_list = $result['DATA'];
// 客户端　手机版、HD版soft.GoGetSoftDetailCategory
$anzhi = gomarket_action('soft.GoGetSoftDetailPackage', array('PACKAGE_NAME' => 'cn.goapk.market', 'VR' => 1));
$tplObj->out['anzhi'] = $anzhi;		// 手机版
$no_subject = array_values($default_subject);
$fids = array();
foreach($feature_list as $key => $val){
	if($val[1] > 1000000){
		$val[1] = intval($val[1] / 1000000);
	}
	$fid = $val[1];
	$fids[] = $fid;
	if(!in_array($fid,$no_subject)){
		$feature_arr_go[$val[1]] = $val;
	}
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
	!empty($val['title']) && $feature_arr_go[$fid][2] = $val['title'];
	!empty($val['text']) && $feature_arr_go[$fid][5] = $val['text'];
}

$feature_arr = array_slice($feature_arr_go,3,7);
//$best_app = gomarket_action('soft.GoGetSoftSubjectAllList', array('ID' => $feature_list[4][1], 'TYPE' => 1, 'VR' => 1));
//$best_game = gomarket_action('soft.GoGetSoftSubjectAllList', array('ID' => $feature_list[2][1], 'TYPE' => 1, 'VR' => 1));

$feature_mine = array_slice($feature_arr_go,0,3);
foreach($feature_mine as $key => $val){
	$feature_soft = gomarket_action('soft.GoGetSoftSubjectAllList', array('ID' => $val[1], 'TYPE' => 1, 'VR' => 1));
	$val['soft'] = $feature_soft['DATA'];
	$feature_mine[$key] = $val;
}
$tplObj -> out['feature_mine'] = $feature_mine;

//装机必备
$bibei = gomarket_action('soft.GoGetNecessaryExtent',array("LIST_INDEX_START" => 0,"LIST_INDEX_SIZE"=>15));
$bibei_arr_go = $bibei['DATA'];
foreach($bibei_arr_go as $key => $val){
	if(count($val['CHILD_GROUP']) > 1){
		$bibei_arr_need[$key] = $val;
	}
}
foreach($bibei_arr_need as $key => $val){
	if(count($val['CHILD_GROUP']) > 2){
		shuffle($val['CHILD_GROUP']);
		$rand = array_slice($val['CHILD_GROUP'],0,2);
		$val['CHILD_GROUP'] = array();
		foreach($rand as $k => $v){
			$val['CHILD_GROUP'][] = $v;
		}
	}
	$bibei_arr[] = $val;
}
$cid = 0;
foreach($bibei_arr as $key => $val){
	foreach($val['CHILD_GROUP'] as $k => $v){
		$v['download'] = num_format($v[9],2);
		$val['CHILD_GROUP'][$k] = $v;	
	}
	$bibei_arr[$key] = $val;
}
foreach($bibei_arr as $key => $val){
	$val['cid'] = $cid++;
	$bibei_arr[$key] = $val;
}
// 专题轮播图
if($channel == 'qqhelper'){
	get_pic_subject(4);
}else{
	get_pic_subject(50);
}
$tplObj->out['GOAPK_IMG_HOST'] = GOAPK_IMG_HOST;
	
//$app_arr = $best_app['DATA'];
//$game_arr = $best_game['DATA'];
$count = count($bibei_arr);
$tplObj -> out['count'] = $count;
$tplObj -> out['bibei_arr'] = $bibei_arr;
//$tplObj -> out['game_arr'] = $game_arr;
//$tplObj -> out['app_arr'] = $app_arr;
$tplObj -> out['feature_arr'] = $feature_arr;
$tplObj -> out['default_subject'] = $default_subject;
display('zt_index.html');

?>
