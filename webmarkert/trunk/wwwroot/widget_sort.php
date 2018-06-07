<?php
include_once (dirname ( realpath ( __FILE__ ) ) . '/init.php');

$id = $_GET['id'];

$order = isset($_GET['order']) ? (int)$_GET['order'] : 1;
$mine = isset($_GET['mine']) ? $_GET['mine'] : 1;
$order_name = ($order == 1) ? '热门' : '最新';
$theme = isset($_GET['theme']) ? $_GET['theme'] : 1;
$theme_config = array(
	'1' => array('widget_sort_1.html', $soft_num),//www
	'2' => array('widget_sort_2.html', 10),//www
	'3' => array('widget_childcate.html',12),//www
	'4' => array('widget_sort_1.html', 10),//中兴 wdj
	'5' => array('widget_sort_2.html', 10),//中兴 wdj
	'6' => array('widget_childcate.html', 10),//中兴
	'7' => array('widget_sort_2.html', 8),//360
	'8' => array('widget_sort_1.html', 20),//360
	'9' => array('widget_sort_3.html', 8),//360
	'10' => array('widget_sort_2.html', 12),//腾讯
	'11' => array('widget_childcate.html', 12),//腾讯
	'12' => array('widget_sort_1.html', 10),//腾讯
	'13' => array('widget_sort_1.html', 10),//手机助手
	'14' => array('widget_childcate.html', 10),//手机助手
	'15' => array('widget_subject_h.html', $order),
	'16' => array('widget_subject_v.html', $order),
	'17' => array('widget_subject_v.html', $order),
	'18' => array('widget_sort_1.html', 5),
);
$api_key = 'soft.GoGetCategoryAllSoftList';
if ($theme == 17) {
	$api_key = 'v53.GoGetHomeSoaring';
}
if ($theme == 15 || $theme == 16 || $theme == 17) {
	$order = 1;
}

$apps = gomarket_action($api_key, array('LIST_INDEX_START' => 0, 'LIST_INDEX_SIZE' => $theme_config[$theme][1],'VR' => 1, 'ORDER'=> $order, 'ID' => $id,'EXTRA_OPTION_FIELD' => array('isoffice')));
foreach($apps['DATA'] as $key => $val){
	$val['qrimg'] = get_qrimg($val[0],$val[7],$val[15],$val[1]);
	$val['size'] = formatFileSize($val[9],1);
	$apps['DATA'][$key] = $val;
}

$tplObj -> out['channel'] = $_GET['channel'];
$tplObj->out['apps'] = $apps['DATA'];
$tplObj->out['feature_soft_arr'] = $apps['DATA'];
$tplObj->out['order'] = $order;
$tplObj->out['parentid'] = $id;
$tplObj->out['mine'] = $mine;
$tplObj->out['order_name'] = $order_name;
display($theme_config[$theme][0]);

