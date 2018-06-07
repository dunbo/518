<?php
define('DS', DIRECTORY_SEPARATOR);
define("SERVER_ROOT", dirname(dirname(__FILE__)) . DS);
define('GO_APP_ROOT', dirname(realpath(__FILE__)));
define('GO_CONFIG_DIR', GO_APP_ROOT. DS.'..'.DS.'..'.DS. 'newgomarket.goapk.com'. DS. 'config');
define('GO_HELPER_DIR', GO_APP_ROOT. DS.'..'.DS.'..'.DS. 'newgomarket.goapk.com'. DS. 'helper');
define('P_LOG_DIR', '/data/att/permanent_log');
define("ACTION_WEB_DOWNLOAD", 3);
define("ACTION_PARTER_DOWNLOAD", 4);
define('GO_UID_DEFAULT', 13176);
define('STATIC_IMG_HOST', getImageHost());
define('FULL_STATIC_HOST', load_config("full_static_host"));
include_once GO_APP_ROOT. DS.'..'.DS.'..'.DS. 'GoPHP'. DS. 'Startup.php';
date_default_timezone_set('Asia/Shanghai');
$softObj = load_model('sjsoft');
?>