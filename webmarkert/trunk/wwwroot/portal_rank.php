<?php
/*
	安智门户页，游戏/应用 jsonp数据
*/

	include_once (dirname ( realpath ( __FILE__ ) ) . '/init.php');
	
	//应用/游戏排行
	$apps_rank = gomarket_action('soft.GoGetCategoryAllSoftList', array('LIST_INDEX_START' => 0, 'LIST_INDEX_SIZE' => 8,'VR' => 1, 'ORDER'=> 1, 'ID' => 1));
	$games_rank = gomarket_action('soft.GoGetCategoryAllSoftList', array('LIST_INDEX_START' => 0, 'LIST_INDEX_SIZE' => 8,'VR' => 1, 'ORDER'=> 1, 'ID' => 2,));
	$rank = array('apps' => $apps_rank,'games' => $games_rank);
	$callback = $_GET['callback'];
	$json = json_encode($rank);
	echo "{$callback}({$json})";