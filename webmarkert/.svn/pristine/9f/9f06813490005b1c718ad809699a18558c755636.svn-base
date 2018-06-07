<?php
include_once (dirname ( realpath ( __FILE__ ) ) . '/init.php');

$id = $_GET['id'];
if ($id > 1000000){
	$id = intval($id / 1000000);
}
$size = isset($_GET['size']) ? $_GET['size'] : 10;

if($channel == 'ZX' || $channel == 'wdj' || $channel == 'qqhelper' || $channel == '360_app' || $channel == '360_game') $size = 8;
$theme = isset($_GET['theme']) ? $_GET['theme'] : 1;
$theme_config = array(
	'1' => 'widget_subject_h.html',
	'2' => 'widget_subject_v.html',
);

$param = array(
	'ID' => $id,
	'VR' => 1,
	'LIST_INDEX_START' => 0,
	'LIST_INDEX_SIZE' => $size,
	'EXTRA_OPTION_FIELD' => array('isoffice')
);

$feature_soft_go = gomarket_action('soft.GoGetSoftSubjectAllList', $param);
$feature_soft_arr = $feature_soft_go['DATA'];
foreach($feature_soft_arr as $key => $val){
	$val['qrimg'] = get_qrimg($val[0],$val[7],$val[15],$val[1]);
	$val['size'] = formatFileSize($val[9],1);
	$feature_soft_arr[$key] = $val;
}

$tplObj -> out['feature_soft_arr'] = $feature_soft_arr;	

display($theme_config[$theme]);
