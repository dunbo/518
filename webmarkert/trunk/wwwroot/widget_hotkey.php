<?php
include_once (dirname ( realpath ( __FILE__ ) ) . '/init.php');

$search_hot_result = gomarket_action('soft.GoGetHotWords');

$theme = isset($_GET['theme']) ? $_GET['theme'] : 1;

$theme_config = array(
	'1' => array('widget_hotkey.html', 10),//www,tcl
	'2' => array('widget_hotkey.html', 8),//other
);
$size = $theme_config[$theme][1];

$hot = array_slice($search_hot_result['DATA'],0,$size);
foreach($hot as $key => $val){
	$val['rank'] = $key + 1;
	$hot[$key] = $val;
}
$tplObj -> out['hot'] = $hot;
$tplObj->display($theme_config[$theme][0]);

