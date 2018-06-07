<?php
/** 软件扫描回调 */
//ini_set('display_errors', true);
//error_reporting(E_ALL);
define('DS', DIRECTORY_SEPARATOR);
define('GO_APP_ROOT', dirname(realpath(__FILE__)));
define('GO_CONFIG_DIR', GO_APP_ROOT. DS. '..'. DS. 'newgomarket.goapk.com'. DS. 'config');
define('GO_HELPER_DIR', GO_APP_ROOT. DS. '..'. DS. 'newgomarket.goapk.com'. DS. 'helper');
include_once GO_APP_ROOT. DS. '..'. DS. 'GoPHP'. DS. 'Startup.php';
include_once '../model/scanSoft.php';
include_once '../model/scanlib/scanSoft.adapter.php';
include_once '../model/scanlib/scanSoft.lbe.php';
date_default_timezone_set('Asia/Shanghai');
load_helper("utiltool");

$companyObj = scanSoftAdapter::init();
$companyObj->http_response();
$model = new GoModel();
$db_server = 'master';
$companyObj->responseAftermath($model, $db_server);
$companyObj->work_end();
?>
