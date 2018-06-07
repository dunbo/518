<?php
/*
	校园行活动m站推广页
*/
include_once (dirname(realpath(__FILE__)).'/../init.php');

$intro = gomarket_action('soft.GoGetSoftDetailPackage', array(
	'PACKAGE_NAME' => 'cn.goapk.market',
	'VR' => 3,
	'EXTRA_OPTION_FIELD' => array(
	'A.category_id','A.category_name','A.hide','A.status','A.update_content'
	),
));
$intro['SOFT_PROMULGATE_TIME'] = date('Y-m-d',strtotime($intro['SOFT_PROMULGATE_TIME']));
$intro['soft_sizes']= formatFileSize('',$intro['SOFT_SIZE']);
$tplObj -> out['intro'] = $intro;
$tplObj -> display('m_school.html');