<?php
include_once (dirname ( realpath ( __FILE__ ) ) . '/init.php');

$count = 100;
$page = (int)$_GET['page'];
$num = 15;
$area = 10;
$start = ($page <= 1) ?  0 : ($page - 1) * 10;
$LIST_INDEX_SIZE = 15;
if( $start == 0 && ($channel == '360_app' || $channel == '360_game' || $channel == 'qqhelper')){
		$anzhi = gomarket_action('soft.GoGetSoftDetailPackage', array('PACKAGE_NAME' => 'cn.goapk.market', 'VR' => 1));
		$pkg360_1 = gomarket_action('soft.GoGetSoftDetailPackage', array('PACKAGE_NAME' => 'com.qihoo360.mobilesafe', 'VR' => 1));
		$pkg360_1['PACKAGE'] = 'com.qihoo360.mobilesafe';
		$pkg360_2 = gomarket_action('soft.GoGetSoftDetailPackage', array('PACKAGE_NAME' => 'com.qihoo360.mobilesafe.opti.powerctl', 'VR' => 1));
		$pkg360_2['PACKAGE'] = 'com.qihoo360.mobilesafe.opti.powerctl';
		$pkg360_3 = gomarket_action('soft.GoGetSoftDetailPackage', array('PACKAGE_NAME' => 'com.qihoo360.mobilesafe.opti', 'VR' => 1));
		$pkg360_3['PACKAGE'] = 'com.qihoo360.mobilesafe.opti';
		$tplObj -> out['360_special'] = array($pkg360_1,$pkg360_2,$pkg360_3);	
		$tplObj -> out['360_special'] = array($pkg360_1,$pkg360_2,$pkg360_3);
		$tplObj -> out['anzhi'] =  $anzhi;
}
if($channel == '360_app' || $channel == '360_game') $LIST_INDEX_SIZE = 12;

$HomeFeature = gomarket_action('soft.GoGetExtentHomeFeatureOld', array('LIST_INDEX_START' => $start, 'LIST_INDEX_SIZE' => $LIST_INDEX_SIZE,'VR' => 1, 'FULL_LIST' => 1,'GET_COUNT' => true,'EXTRA_OPTION_FIELD' => array('intro','category_id')));
$tplObj->out['homeFeature'] = $HomeFeature['DATA'];
$count = $HomeFeature['COUNT'];
$tplObj->out['page'] =  pagination_arr($page, $count, $num, $area);

$tplObj -> out['channel'] = $_GET['channel'];
display('jp_recommend.html');










