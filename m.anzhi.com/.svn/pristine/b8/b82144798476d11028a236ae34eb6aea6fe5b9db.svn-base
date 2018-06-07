<?php
include_once (dirname(realpath(__FILE__)).'/init.php');
$pid = isset($_GET['pid']) ? $_GET['pid'] : 1;
$sub = isset($_GET['sub']) ? $_GET['sub'] : 0;

$model = new GoModel();
$options = array(
    'where' => array(
        'status' => 1,
        'pid' => $pid,
        'show_pid_page' => $sub
    ),
    'order' => 'rank',
    'table' => 'sj_helptext'
);

$results = $model -> findAll($options);

$tplObj -> out['result'] = $results;

if (isset($_GET['tpl_ver']) && $_GET['tpl_ver'] == 'v6') {
	$tplObj -> display('help_v6.html');
} else {
	$tplObj -> display('help.html');
}