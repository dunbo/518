<?php
/*
	安智门户页，游戏/应用 jsonp数据
*/

	include_once (dirname ( realpath ( __FILE__ ) ) . '/init.php');
	//游戏分类
	$game_categorylist = gomarket_action('soft.GoGetSoftCategoryList',array('TYPE' => 1,'VR' => 1));
	$game_category_all = $game_categorylist['DATA'][0]['CHILD_CATEGORY_GROUP'];
	$game_category = array_slice($game_category_all,0,7);
	$callback = $_GET['callback'];
	$json = json_encode($game_category);
	echo "{$callback}({$json})";