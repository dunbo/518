<?php
/*
	安智门户页，游戏/应用 jsonp数据
*/
	include_once (dirname ( realpath ( __FILE__ ) ) . '/init.php');
		
	//软件
	$HomeFeature = gomarket_action('softv2.GoGetExtentHomeFeatureOld', array('LIST_INDEX_START' => 0, 'LIST_INDEX_SIZE' => 72,'VR' => 13, 'FULL_LIST' => 1,'GET_COUNT' => true,'EXTRA_OPTION_FIELD' => array('parentid','iconurl_96')));

	foreach($HomeFeature['DATA'] as $key => $val){
		if($val['parentid'] == 2){
			$portal_game_all[] = $val;
		}
	}
	$portal_game = array_slice($portal_game_all,0,12);
	$callback = $_GET['callback'];
	$json = json_encode($portal_game);
	echo "{$callback}({$json})";


	
	
	