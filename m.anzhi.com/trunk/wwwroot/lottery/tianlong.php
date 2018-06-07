<?php

include_once (dirname(realpath(__FILE__)).'/../init.php');
$soft_result = gomarket_action ( 'soft.GoGetSoftDetailPackage', array ('PACKAGE_NAME' => 'com.cyou.cx.mtlbb.anzhi'));

$tplObj -> out['soft_result'] = $soft_result;
$tplObj -> out['PACKAGE_NAME'] = 'com.cyou.cx.mtlbb.anzhi';
$tplObj -> display('tianlong.html');