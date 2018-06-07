<?php
include_once (dirname(realpath(__FILE__)).'/init.php');

$model = new GoModel();
$report_option = array(
	'where' => array(
		'status' => 1
	),
	'order' => 'rank',
	'table' => 'sj_report_question'
);

$report_result = $model -> findAll($report_option);

$tplObj -> out['http_url'] = HTTP_URL;
$tplObj -> out['report_result'] = $report_result;
$tplObj -> out['report_results'] = json_encode($report_result);

if ($_GET['pid'] == 20) {
    $tplObj -> display('report_smzdw.html');
}

if (isset($_GET['tpl_ver']) && $_GET['tpl_ver'] == 'v6') {
	$tplObj -> display('report_v6.html');
} else {
	$tplObj -> display('report.html');
}