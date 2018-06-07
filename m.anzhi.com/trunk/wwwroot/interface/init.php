<?php
ini_set("display_errors", true);
error_reporting(E_ALL);
define('DS', DIRECTORY_SEPARATOR);
define('APP_NAME', 'www');
define("SERVER_ROOT", dirname(dirname(__FILE__)) . DS);
define('GO_APP_ROOT', dirname(realpath(__FILE__)));
define('GO_CONFIG_DIR', GO_APP_ROOT. DS.'..'.DS.'..'.DS. 'newgomarket.goapk.com'. DS. 'config');
define('GO_HELPER_DIR', GO_APP_ROOT. DS.'..'.DS.'..'.DS. 'newgomarket.goapk.com'. DS. 'helper');
define('P_LOG_DIR', '/data/att/permanent_log');
define("ACTION_WEB_DOWNLOAD", 3);
define("ACTION_PARTER_DOWNLOAD", 4);
define('GO_UID_DEFAULT', 13176);    
define('STATIC_IMG_HOST', load_config("goapk_img_host"));
define('FULL_STATIC_HOST', load_config("full_static_host"));  
include_once GO_APP_ROOT. DS.'..'.DS.'..'.DS. 'GoPHP'. DS. 'Startup.php';
include_once GO_APP_ROOT. DS. 'function.php';

date_default_timezone_set('Asia/Shanghai');
session_start();
$softObj = load_model('sjsoft');
$filter_option = array(
    'abi' => 3,
);
$filter_logic = pu_load_logic('filter', array('filter_option' => $filter_option) );
$softlist_logic = pu_load_logic('softlist', array('filter_logic' => $filter_logic));
$category_logic = pu_load_logic('category');
$soft_logic = pu_load_logic('softcheck', array('filter_logic' => $filter_logic));
$user_logic = pu_load_logic('user');
$comment_logic = pu_load_logic('comment');
$feedback_logic = pu_load_logic('feedback');
?>