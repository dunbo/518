<?php
ini_set('display_errors', 'on');
error_reporting(E_ERROR);

!defined('GO_APP_ROOT') && exit('请先在入口文件定义GO_APP_ROOT变量!!'); 

!defined('DS') && define('DS', DIRECTORY_SEPARATOR);
!defined('GOPHP_ROOT') && define('GOPHP_ROOT', dirname(realpath(__FILE__)));
!defined('GOPHP_TOP') && define('GOPHP_TOP', realpath(dirname(realpath(__FILE__)). DS. '..'));

if(array_key_exists("SERVER_ADDR", $_SERVER)) {
    $server = $_SERVER["SERVER_ADDR"];
} else { #cli mode
    $server = shell_exec("/sbin/ifconfig -a | grep 'inet ' | cut -d':' -f2 | cut -d' ' -f1 | grep -v '127.0.0.1' | grep '192.168' |head -n 1");
    $server = trim($server);
}
$client_ip = $_SERVER['REMOTE_ADDR'];
if (preg_match('/^192/', $client_ip) && isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
	$_SERVER['REMOTE_ADDR'] = $_SERVER['HTTP_X_FORWARDED_FOR'];
}


$pinyins = array();

$UC2GBTABLE='';


define("GO_SERVER_IP", $server);

date_default_timezone_set('Asia/Shanghai');

define('GO_CORE_LIBRARY', GOPHP_ROOT. DS. 'lib');

define('GO_DEBUG', 'DEBUG');
define('GO_ERROR', 'ERROR');
define('GO_INFO', 'INFO');
define('GO_FILTER', 'FILTER');

#GO_ENV_* 运行环境, 开发、生产、命令行
define('GO_ENV_DEV', 0);
define('GO_ENV_PRODUCT', 1);
define('GO_ENV_CLI', 2);

!defined('GO_MODEL_DIR') && define('GO_MODEL_DIR', GO_APP_ROOT. DS. 'model');
!defined('GO_CONFIG_DIR') && define('GO_CONFIG_DIR', GO_APP_ROOT. DS. 'config');
!defined('GO_HELPER_DIR') && define('GO_HELPER_DIR', GO_APP_ROOT. DS. 'helper');
!defined('GO_LIBRARY_DIR') && define('GO_LIBRARY_DIR', GO_APP_ROOT. DS. 'lib');
!defined('GO_LOGIC_DIR') && define('GO_LOGIC_DIR', GO_APP_ROOT. DS. 'logic');

!defined('GO_TOP_MODEL_DIR') && define('GO_TOP_MODEL_DIR', GOPHP_ROOT. DS. 'model');
!defined('GO_TOP_CONFIG_DIR') && define('GO_TOP_CONFIG_DIR', GOPHP_ROOT. DS. 'config');
!defined('GO_TOP_HELPER_DIR') && define('GO_TOP_HELPER_DIR', GOPHP_ROOT. DS. 'helper');
!defined('GO_TOP_LIBRARY_DIR') && define('GO_TOP_LIBRARY_DIR', GOPHP_ROOT. DS. 'lib');
!defined('GO_TOP_LOGIC_DIR') && define('GO_TOP_LOGIC_DIR', GOPHP_ROOT. DS. 'logic');


require_once GOPHP_ROOT. DS . 'lib' . DS. 'Common.func.php';

load_config();

spl_autoload_register('go_autoload');

load_core('GoDB');
load_core('GoModel');
load_core('GoPu_model');
load_core('GoCache');
