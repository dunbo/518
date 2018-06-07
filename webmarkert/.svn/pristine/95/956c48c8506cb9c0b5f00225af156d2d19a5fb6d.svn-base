<?php
include_once (dirname(realpath( __FILE__ )) . '/init.php');
//安智市场最新版
$newanzhi = gomarket_action('soft.GoGetSoftDetailPackage', array('PACKAGE_NAME' => 'cn.goapk.market', 'VR' => 1));
$anzhiqrimg = get_qrimg($newanzhi['ID'], 'cn.goapk.market', $newanzhi['SOFT_PROMULGATE_TIME'], $newanzhi['ICON']);
$anzhiqrimg = "<img src='{$anzhiqrimg}'/>";
$tplObj -> out['newanzhi'] = $newanzhi;
$tplObj -> out['anzhiqrimg'] = $anzhiqrimg;
$tplObj -> display('/tpl2/anzhi_qrimg.html');