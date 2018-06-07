<?php
include_once (dirname ( realpath ( __FILE__ ) ) . '/init.php');

$tag_id = $_GET['tag_id'];
$cat_id = $_GET['cat_id'];
$order = isset($_GET['order']) ? (int)$_GET['order'] : 1;
$limit = 12;

$parameters = array(
	'LIST_INDEX_START' => 0,
	'LIST_INDEX_SIZE' => $limit,
	'ORDER'=> $order,
	'CATEGORY_ID' => $cat_id,
	'TAG_ID' => $tag_id,
	'VR' => 24,
);
$apps = gomarket_action('v53.GoGetTagSoftList',$parameters);

foreach($apps['DATA'] as $key => $val){
	$val['qrimg'] = get_qrimg($val[0],$val[7],$val[15],$val[1]);
	$val['size'] = formatFileSize($val[9],1);
	$val[11] = num_format($val[11], 2);
	$apps['DATA'][$key] = $val;
}

$tplObj->out['apps'] = $apps['DATA'];
display('widget_childcatetag.html');
