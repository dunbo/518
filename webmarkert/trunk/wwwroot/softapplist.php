<?php 
	$category_id = array('CATEGORY_ID'=>1);
	$limt = array('LIST_INDEX_START'=>0,'LIST_INDEX_SIZE'=>15);
	$sub_cat_id = array('sub_category_id'=>(int)$_GET['sub_cat_id']);
	$result1 = gomarket_action('soft.GoGetCategoryAllSoftList',$sub_cat_id,$limt);
	$result = gomarket_action('soft.GoGetSoftCategoryList',$category_id);
	$tplObj -> out['applist'] = $result['DATA'];
	$tplObj -> out['softapplist'] = $result1;
	var_dump ($result1);
	// 客户端　手机版、HD版soft.GoGetSoftDetailCategory
	$anzhi = gomarket_action('soft.GoGetSoftDetailPackage', array('PACKAGE_NAME' => 'cn.goapk.market', 'VR' => 1));
	$tplObj->out['anzhi'] = $anzhi;		// 手机版
	$tplObj -> display('item_list.html');
?>