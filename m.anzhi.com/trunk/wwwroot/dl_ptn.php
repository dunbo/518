<?php
include_once (dirname(realpath(__FILE__)).'/init.php');

$map = array(
    //KK直播 http://m.anzhi.com/dl_ptn.php?package=com.melot.meshow&vcode=91
    'com.melot.meshow' => array(
        '91' => array('http://wap.apk.anzhi.com/data3/apk/201703/20/MESHOW_5.5.0.apk', '2ca084a89df76aa96c5f82c9a171aaba'),
    ),

    //优果 http://m.anzhi.com/dl_ptn.php?package=com.ugirls.app02&vcode=33
    'com.ugirls.app02' => array(
        // '33' => array('http://wap.apk.anzhi.com/data3/apk/201703/20/app-anzhihezuo2-v2.1.2-20170314.apk', 'd60e3c7231f73e1eebb2b54e1ae2716c'),
        // 'anzhihezuo3' => array('http://wap.apk.anzhi.com/data3/apk/201703/22/app-anzhihezuo3-v2.1.2-20170314_18c4c.apk', 'ea91dc384d789fc15e164bbd0f81552e'),
        '33' => array('http://wap.apk.anzhi.com/data3/apk/201704/13/app-v2.2.1-anzhihezuo2.apk', '51c98ff7e1a9c349ced80cb1b6d365e6'),
        'anzhihezuo3' => array('http://wap.apk.anzhi.com/data3/apk/201704/13/app-v2.2.1-anzhihezuo3.apk', '5196ff3b6ce4606232b0e3c3f8ed7b8b'),
        
        'anzhihezuo4' => array('http://wap.apk.anzhi.com/data3/apk/201704/27/app-v2.2.1-anzhihezuo4.apk', '79513cb6047f7aa578db62067d0314c3'),
        'anzhihezuo5' => array('http://wap.apk.anzhi.com/data3/apk/201704/27/app-v2.2.1-anzhihezuo5.apk', '106dd84ddb8cf29a156ebd8066ad1167'),
        'anzhihezuo6' => array('http://wap.apk.anzhi.com/data3/apk/201704/27/app-v2.2.1-anzhihezuo6.apk', '0b6d954ec774bdff6287f6485ba2671f'),
        'anzhihezuo7' => array('http://wap.apk.anzhi.com/data3/apk/201704/27/app-v2.2.1-anzhihezuo7.apk', 'aedb1063e86df751731334323f939ecc'),
        'anzhihezuo8' => array('http://wap.apk.anzhi.com/data3/apk/201704/27/app-v2.2.1-anzhihezuo8.apk', 'bdfbaad8d3860b7611273d5b1b797bec'),
    ),   
);


if (!isset($_GET['package']) || !isset($_GET['vcode'])) {
    exit;
}

$package = $_GET['package'];
$vcode = $_GET['vcode'];

if (!preg_match('/^[a-z0-9\.\_\$]+$/', $package)) {
    exit;
}

if ($vcode == 'zhiyoo' || $vcode == 'kxhy') {
    $map[$package][$vcode] = array("http://m.anzhi.com/download.php?gcid={$vcode}&package={$package}");
}

$url = $map[$package][$vcode][0];

$tolog = array(
	'action' => 300,//第三方下载数据
	'submit_tm' => time(),
	'package' => $package,
	'ip' => onlineip(),
	'refer' => $_SERVER['HTTP_REFERER'],
    'gcid' => $_GET['vcode']
);
$h = date('H');
permanentlog('install_log_'.$h.'.json', json_encode($tolog));

header("Location:".$url);
exit;
