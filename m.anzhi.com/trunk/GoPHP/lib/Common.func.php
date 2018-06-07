<?php
/**
 * 加载model
 * @param string $model
 * @param boolean $single
 * @return GoModel $model
 */
function load_model($model, $alias = '', $single = true)
{
    $global_key = 'model_'. $model. $alias;
    if ($single == true && isset($GLOBALS[$global_key])) {
        return $GLOBALS[$global_key];
    }

    $model_file = GO_TOP_MODEL_DIR. DS. $model. '.php';
    $is_top = False;
    if (file_exists($model_file)) {
        go_require_once($model_file);
        $is_top = True;
    }
    $model_file = GO_MODEL_DIR. DS. $model. '.php';
    if (file_exists($model_file)) {
        go_require_once($model_file);
        $modelName = $is_top? "Go". ucfirst($model) ."Model_sub" : "Go". ucfirst($model) ."Model";
    } else {
        $modelName = "Go". ucfirst($model) ."Model";
    }
    if ($single == true) {
        $model = GoModel::getInstance($modelName, $alias);
        $GLOBALS[$global_key] = $model;
        return $model;
    } else {
        return new $modelName;
    }
}

//取得一个model的数据
function pu_load_model_data($model, $index = '')
{
    $data = pu_load_model($model, $index, 'data_info');
    GoPu_model::unsetModel($model, $index);
    return $data;
}

//加载一个model
function pu_load_model_obj($model, $index = '')
{
    return pu_load_model($model, $index, 'object');
}

//加载一个新的model
function pu_load_model_new_obj($model)
{
    $obj = pu_load_model($model, array(), 'object');
    $obj->is_new = True;
    return $obj;
}

/**
 * 新的加载model方式
 * @param string $model: 要加载新的model
 * @param string $index: 通过索引取得的model数据
 * @param string $return_mode: 返回的形式 'object' 为返回model的实例化类 'data_info' 为返回model的数据存储 (可选，默认为'object')
 * @return GoPu_model OR array
 */

function pu_load_model($model, $index = '', $return_mode = 'object')
{
    $model_file = GO_TOP_MODEL_DIR. DS. $model. '.php';
    if (file_exists($model_file)) {
        go_require_once($model_file);
    } else {
        //return False;
    }
    $model_file = GO_MODEL_DIR. DS. $model. '.php';
    if (file_exists($model_file)) {
        go_require_once($model_file);
        $modelName = "Go". ucfirst($model) ."Model_sub" ;
    } else {
        $modelName = "Go". ucfirst($model) ."Model";
    }
    if (is_array($index) && key($index) === 0) {
        return GoPu_model::getInstance_arr($modelName, $index, $return_mode);
    } else {
        return GoPu_model::getInstance($modelName, $index, array(), $return_mode);
    }
}

//加载logic
function pu_load_logic($logic, $parameter = array())
{
    $logic_file = GO_TOP_LOGIC_DIR. DS. $logic. '.php';
    $is_top = False;
    if (file_exists($logic_file)) {
        go_require_once($logic_file);
        $is_top = True;
    }
    if (defined('GO_LOGIC_DIR')) {
        $logic_file = GO_LOGIC_DIR. DS. $logic. '.php';
        if (file_exists($logic_file)) {
            go_require_once($logic_file);
            $logicName = $is_top? "Go". ucfirst($logic) ."Logic_sub" : "Go". ucfirst($logic) ."Logic";
        }
    }
    if ( !isset($logicName) ) { $logicName = "Go". ucfirst($logic) ."Logic"; }
    return new $logicName($parameter);
}
/**
 * 获取配置项
 *
 * @param string $string
 */
function load_config($string = '', $file = '')
{
    if(!empty($string)){
        $config_key = empty($file) ? 'go_config' : 'go_config_'. $file;
        $cache_key = $config_key . '_' . $string . '_cache';
        if (!isset($GLOBALS[$cache_key])) {
            if(empty($GLOBALS[$config_key])) {
                $config_file = empty($file) ? 'config.inc.php' : $file. '.inc.php';
                $top_config = go_require_once(GO_TOP_CONFIG_DIR. DS. $config_file);
                if (!is_array($top_config)) $top_config = array();

                $app_config_file = GO_CONFIG_DIR. DS. $config_file;
                if (file_exists($app_config_file)) {
                if ($file == 'dev')var_dump($string, $file, $top_config);
                    $app_config = go_require_once($app_config_file);
                    if (is_array($app_config)) {
                        $top_config = config_merge($top_config, $app_config);
                    }
                }
                $GLOBALS[$config_key] = $top_config;
            }

            $keys = explode('/', $string);
            $item = $GLOBALS[$config_key];
            foreach ($keys as $key) {
                if ( !isset($item[$key]) ) {  return false; }
                $item = $item[$key];
            }
            $GLOBALS[$cache_key] = $item;
            return $item;
        } else {
            return $GLOBALS[$cache_key];
        }
    }
}

function config_merge($config1, $config2)
{
    foreach ($config2 as $k => $v) {
        if (isset($config1[$k])) {
            if (is_array($config2[$k])) {
                $config1[$k] = config_merge($config1[$k], $config2[$k]);
            } else {
                $config1[$k] = $config2[$k];
            }
        } else {
            $config1[$k] = $config2[$k];
        }
    }
    return $config1;
}


#ifdef diff
function load_library($library)
{
    $library_file = $library. '.class.php';

    $top_library_file = GO_TOP_LIBRARY_DIR. DS. $library_file;
    $app_library_file = GO_LIBRARY_DIR. DS. $library_file;
    if (file_exists($top_library_file)) {
        go_require_once ($top_library_file);
    } elseif (file_exists($app_library_file)) {
        go_require_once ($app_library_file);
    }
}

function load_core($core_class)
{
    $library_file = $core_class. '.class.php';

    $library_file = GO_CORE_LIBRARY. DS. $library_file;
    go_require_once ($library_file);
}
/**
 * 记录日志
 * Enter description here ...
 * @param unknown_type $level
 * @param unknown_type $message
 */
function go_log($level, $message)
{
    if (in_array($level, (array)load_config('log_level'))) {
        $time = date('Y-m-d H:i:s', time());
        $day = date('Y-m-d', time());
        $logpath = load_config('log_path') ? load_config('log_path') : '/tmp';
        $prefix = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'GoPHP';
        $logfile = $logpath. DS. $day. DS. $prefix. '.'. strtolower($level). '.log';
        $logdir = dirname($logfile);
        if (!is_dir($logdir))
            __mkdir($logdir, 0755, true);

        if (!is_string($message)) {
            $message = var_export($message, true);
        }
        $pid = getmypid();
        $message = "{$time}: ({$pid}) {$message}\n";
        file_put_contents($logfile, $message, FILE_APPEND);
    }
}

function go_error($message, $header = '')
{
    echo $trace = go_trace();
    //file_put_contents('/tmp/php-error-trace.log', $message. $trace. "\n", FILE_APPEND);
    if (php_sapi_name() != 'cli' && !empty($header)) { header($header); }
    exit($message);
}

function onlineip()
{
    return $_SERVER['REMOTE_ADDR'];
}

/*
POST 请求封装
    $url: 请求地址
    $data: POST 参数
    $timeout: 超时时间
*/
function requestPost($url, $data = array(), $timeout = 4) {
    if (empty($data)) return false;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    if ($data) curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
    curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
    $result = curl_exec($ch);
    curl_close($ch);
    return $result;
}

function load_helper($helper)
{
    $helper_file = GO_TOP_HELPER_DIR. DS. $helper. '.helper.php';
    if (!file_exists($helper_file)) {
        $helper_file = GO_HELPER_DIR. DS. $helper. '.helper.php';
    }
    go_require_once ($helper_file);
}

function go_trace()
{
    $trace = '';
    $backtrace = debug_backtrace();

    foreach ($backtrace as $idx => $info) {
        if ( $info['function'] == 'go_trace') continue;
        $file = str_replace(GO_APP_ROOT. DS, '', $info['file']);
        $file = str_replace(GOPHP_ROOT. DS, '', $file);
        @$trace .= "#{$idx} {$info['class']}{$info['type']}{$info['function']}() called at {$file} {$info['line']}<br>\n";
        if ($idx >= 9) break;
    }
    return $trace;
}
if (!function_exists('__mkdir')):
/**
 * 循环创建目录
 * Enter description here ...
 * @param unknown_type $dir
 */
function __mkdir($path, $mod = 0775, $recursive = False)
{
    $dir_arr = array($path);
    while ($path !== '/') { $path = $dir_arr[] = dirname($path); }
    $dir_arr = array_reverse($dir_arr);
    foreach ($dir_arr as $dir) {
        if (substr_count($dir, '/') < 2) { continue; }
        if ( !is_dir($dir) ) {
            if ( !is_writable(dirname($dir)) ) { return False; }
            mkdir($dir, $mod);
        }
    }
}
endif;

/*
   if no daily dir created, log it to upper dir.
*/
function addlog($name, $txt, $print = false) {
    $txt = mb_substr($txt, 0, 2048);
    $host = array_key_exists('HTTP_HOST', $_SERVER) ? $_SERVER['HTTP_HOST'] : 'unknown';
    if (strstr($host, ":")) {
        $host = substr($host, 0, strrpos($host, ":"));
    }
    $path = P_LOG_DIR . "/" . $host . "/" . date('Y-m-d', time()) . "/";
    $client = 'user:null';
    if (isset($_SESSION)) {
        if (array_key_exists('USER_NAME', $_SESSION)) $client = json_encode($_SESSION);
    } else if ($GLOBALS["GO_ENV"] == GO_ENV_CLI) {
        $client = "daemon";
    }
    if (!file_exists($path)) {
        if (!mkdir($path, 0755, true)) {
            file_put_contents("/tmp/error.info", "make dir failed.");
            return false;
        }
    }
    if ($GLOBALS['GO_ENV'] == GO_ENV_CLI or $print) echo $txt;
    $backtrace = debug_backtrace();
    $trace = "";
    foreach ($backtrace as $idx => $info) {
        $trace .= str_replace(SERVER_ROOT,'',$info['file']) . ":" . $info['line'];
        if ($idx >= 9) break;
        $trace .= " - ";
    }
    $log = $_SERVER['REMOTE_ADDR'] . " - " . date("Y-m-d H:i:s") . " - $txt --- $trace - {$client}\n";
    file_put_contents($path . $name, $log, FILE_APPEND);
}

function microtime_float()
{
    /*
    list($usec, $sec) = explode(" ", microtime());
    return ((float)$usec + (float)$sec);
    */
    return gettimeofday(true);
}


function go_array_unique($array)
{
    $tmp = array();
    $new_array = array();
    foreach ($array as $item) {
        if (!isset($tmp[$item])) {
            $tmp[$item] = 1;
            $new_array[] = $item;
        }
    }

    return $new_array;
}


/**
  * 对CSV进行处理
  *
  * @param resource $ handle
  * @param int $ length
  * @param string $ delimiter
  * @param string $ enclosure
  * @return 文件内容或FALSE。
  */
 function mygetcsv(& $handle, $length = null, $d = ',', $e = '"'){
    $d = preg_quote($d);
    $e = preg_quote($e);
    $_line = "";
    $eof = false;
    while ($eof != true){
        $_line .= (empty ($length) ? fgets($handle) : fgets($handle, $length));
        $itemcnt = preg_match_all('/' . $e . '/', $_line, $dummy);
        if ($itemcnt % 2 == 0)
        $eof = true;
    }
    $_csv_line = preg_replace('/(?: |[ ])?$/', $d, trim($_line));
    $_csv_pattern = '/(' . $e . '[^' . $e . ']*(?:' . $e . $e . '[^' . $e . ']*)*' . $e . '|[^' . $d . ']*)' . $d . '/';
    preg_match_all($_csv_pattern, $_csv_line, $_csv_matches);
    $_csv_data = $_csv_matches[1];
    for ($_csv_i = 0; $_csv_i < count($_csv_data); $_csv_i++){
        $_csv_data[$_csv_i] = preg_replace('/^' . $e . '(.*)' . $e . '$/s', '$1', $_csv_data[$_csv_i]);
        $_csv_data[$_csv_i] = str_replace($e . $e, $e, $_csv_data[$_csv_i]);
    }
    return empty ($_line) ? false : $_csv_data;
 }

function anzhi_send_email($option = array()) {
    if (!isset($option['to']) ||
        !isset($option['subject']) ||
        !isset($option['content']))
        return false;
    if (!isset($option['server'])) {
        $option['server'] = 'mail.anzhi.com';
        $option['port'] = 25;
        $option['domain'] = 'anzhi.com';
    }
    if (!isset($option['user'])) {
        $option['user'] = 'support@anzhi.com';
        $option['password'] = 'goapk';
        $option['alias'] = 'support';
    }
    $rc = curl_init();
    curl_setopt($rc, CURLOPT_URL, "http://192.168.1.18:21178/mailer");
    curl_setopt($rc, CURLOPT_POST, true);
    curl_setopt($rc, CURLOPT_POSTFIELDS, $option);
    curl_exec($rc);
    $error = curl_errno($rc);
    curl_close($rc);
    return ($error == 0);
}

function sendNotification($data)
{
    $service_port = load_config('notification/port');
    $address = load_config('notification/host');

    $socket = @socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
    $result = @socket_connect($socket, $address, $service_port);
    if (!$result) { return false; }

    $put = json_encode($data). "\n";
    socket_write($socket, $put, strlen($put));
    socket_close($socket);
}
function go_require_once($file)
{
    static $require_file;
    if (isset($require_file[$file])) {
        return $require_file[$file];
    }
    if (!file_exists($file)) {
        return false;
    }
    $content = false;
    $content = require($file);
    $require_file[$file] = $content;
    return $content;
}

//android内部版本号 转换为可读形式
function firmware2os($firmware)
{
    if (empty($firmware)) return false;
    $map_arr = array(
        '3' => '1.5',
        '4' => '1.6',
        '5' => '2.0',
        '6' => '2.0',
        '7' => '2.1',
        '8' => '2.2',
        '9' => '2.3',
        '10' => '2.3',
        '11' => '3.0',
        '12' => '3.1',
        '13' => '3.2.x',
        '14' => '4.0',
        '15' => '4.0',
        '16' => '4.1',
        '17' => '4.2',
        '18' => '4.3',
        '19' => '4.4',
        '20' => '4.4',
        '21' => '5.0',
        '22' => '5.1',
        '23' => '6.0',
        '24' => '7.0',
    );
    ($os = $map_arr[$firmware])? True : $os =$map_arr[key($map_arr)];
    return $os;
}

//gpc递归过滤函数
function stripslashes_deep($value)
{
    if (empty($value)) {
       return $value;
    } else {
       if (is_array($value)) {
             return array_map('stripslashes_deep', $value);
         } else {
             if (get_magic_quotes_gpc()) {
                $value    =    stripslashes($value);
             }
             return trim($value);
       }
    }
}

//分页
function pagination_arr($page, $count, $pagesize = 10, $area = 10, $page_url_str = 'page', $request_uri = '')
{
    $page       = intval($page);
    $count      = intval($count);
    $pagesize   = intval($pagesize);
    $request_uri =  $request_uri? $request_uri : (IS_STATIC === 1?  ($_SERVER['SCRIPT_NAME'].'?'.$_SERVER['QUERY_STRING']) : $_SERVER['REQUEST_URI']);
    $url = preg_replace("#&". preg_quote($page_url_str). "=\d*#", "", $request_uri);

    if (!strstr($url, '?')){ $url .= "?"; }
    if ($area < 4) { $area = 4; }
    if ($page < 1) { $page = 1; }

    $maxpage    = ceil($count / $pagesize);

    if ($maxpage < 1) { $maxpage = 1; }
    if ($page > $maxpage) { $page = $maxpage; }

    $first_page = 1;
    $last_page  = $maxpage;
    $middle     = intval($area / 2);
    if ($maxpage > $area) {
        if (($page + $middle) > $maxpage) {
            $first_page     = $maxpage - $area + 1;
        } else {
            $first_page     = $page - $middle;
            $last_page      = $page + $middle - 1;
            if ($first_page < 1) {
                $first_page = 1;
                $last_page  = $area;
            }
        }
    }
    $thepage    = array();
    $first_url = $last_url = $pre_url = '';
    if ($page > 1) { $first_url = $url. "&" .$page_url_str."=1"; }
    if ($page < $maxpage) { $last_url = $url. "&" .$page_url_str. "=" .$maxpage; }
    if ( ($page - 1) > 0) { $pre_url = $url. "&" .$page_url_str. "=" .($page - 1); }
    if ( ($page + 1) <= $maxpage) { $next_url = $url. "&" .$page_url_str. "=" .($page + 1); }

    for ($i = $first_page; $i <= $last_page; $i++) {
        $thepage[$i]        =   $url."&".$page_url_str."=".$i;
    }
    $result['thepage']    = $thepage;
    $result['total']      = $count;
    $result['start']      = ($page - 1) * $pagesize;
    $result['page']       = $page;
    $result['maxpage']    = $maxpage;
    $result['first_url']    = $first_url;
    $result['last_url']    = $last_url;
    $result['pre_url'] = $pre_url;
    $result['next_url']   = $next_url;
    return $result;
}


function require_url($url, $data = array(), $timeout = 4){
    #if ( !is_array($data) || !$data) { return False; }
    if ($data) { $str = http_build_query($data); }
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

    if ($str) {
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $str);
    }
    curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
    curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
    $result = curl_exec($ch);
    curl_close($ch);
    return $result;
}

function getServerIp()
{
    if (isset($GLOBALS['go_server_ip'])) {
        $ip = $GLOBALS['go_server_ip'];
    } else {
        $ip = trim(@file_get_contents('/tmp/go_ip'));

        if (empty($ip)) {
            $prefix = load_config("ip_prefix");
            if (empty($prefix)) $prefix = '192.168.1.';
            $prefix = preg_quote($prefix);
            $cmd = "/sbin/ip addr | grep 'inet ' | grep '{$prefix}' | awk '{print $2}'| cut -d'/' -f1|head -n 1";
            $ip = trim(shell_exec($cmd));
            file_put_contents('/tmp/go_ip', $ip);
        }
        $GLOBALS['go_server_ip'] = trim($ip);
    }
    return $ip;
}

function is_static_file()
{
    return function_exists('url2static_url') && function_exists('url2static_file') && (IS_STATIC === 1) && defined('STATIC_DIR') && is_dir(STATIC_DIR) && is_writable(STATIC_DIR);
}

function checkApkType($apkType, $force_plain = false)
{
    $exclude_chl = array(
        '053f8ffca4bd46c8b9fa013d8ce45dec03467b39' => 1,
    );
    //天语渠道以及 4200以上版本不使用加密，直接使用明文，失败后使用https
    if (isset($exclude_chl[$_SESSION['CHANNEL_ID']]) || $_SESSION['VERSION_CODE']>=4200) {
        $force_plain = true;
    }

    if ($force_plain) {
        $apkType = substr($apkType, 0, 1);
    }

    return $apkType;
}

/**
 *	获取下载用的域名
 *  $file_info文件信息，$force_plain(兼容参数，对于确定使用加密路径的地方默认使用false)
 */
function getApkHost($file_info, $force_plain = true, $url = '')
{
	//如果有下载地址并且以http://开头的，返回空，为的是处理一些已经替换了下载地址情况还来调用本函数的特殊处理
	if ($url && stripos($url,'http://') === 0) {
		return $url;
	}
	if (isset($file_info['url']) && stripos($file_info['url'],'http://') === 0) {
		return '';
	}

    $r = mt_rand(1,100);
	$use_new = false;
	if ($file_info['fileid'] <= 2947480 || $file_info['has_letv'] == 1) {
		if ($r<=50) $use_new = true;
		if ($_SESSION['VERSION_CODE']>=6100) {
            //$use_new = true;
        }
        if (defined('APP_NAME') && (APP_NAME == 'www' || APP_NAME == 'wap')) {
            $use_new = true;
        }
        $imei_map = array(
            '358522088563680' => 1,
            '866479020036777' => 1,
            '862479033326417' => 1,
            '862479032875794' => 1,
            '869765029927199' => 1,
            '869765029927181' => 1,
            '358239054749066' => 1,
            '352105062130928' => 1,
            '867464023271273' => 1,
            '860916037288954' => 1,
            '860916037182488' => 1,
            '867092032211640' => 1,
            '867092032357245' => 1,            
        );
        if (isset($_SESSION['USER_IMEI']) && isset($imei_map[$_SESSION['USER_IMEI']])) {
            $use_new = true;
        }
	}

    if ($use_new) {
        //使用云端cdn
        // return 'http://yapk.cdn.anzhi.com'. $url;
    }

    if (isset($GLOBALS['use_tmp_host'])) {
        return 'http://118.26.224.18/test'. $url;
    }
    $cdn = load_config('cdn');
    $host = '';
    $app_name = defined('APP_NAME') ? APP_NAME : 'www';

    if ($use_new && isset($cdn['apk_host'][$app_name. '_yapk'])) {
        $app_name .= '_yapk';
    }

    $conf = $cdn['apk_host'][$app_name];
    if (!$conf) {
        $conf = $cdn['apk_host']['gomarket'];
    }

    $conf['apktype'] = checkApkType($conf['apktype'], $force_plain);
    switch($conf['apktype']) {
        case '1'://按照文件大小区分
            $filesize = '10485760'; // 10M

            if ($file_info['filesize'] > $filesize) {
                $host = $conf['large_apk_host'];
            } else {
                $host = $conf['small_apk_host'];
            }

        break;

        case '11'://按照文件大小区分（加密）
            $filesize = '10485760'; // 10M

            if ($file_info['filesize'] > $filesize) {
                $host = $conf['large_s_host'];
            } else {
                $host = $conf['small_s_host'];
            }

        break;

        case '2'://按照分类进行区分(游戏/应用)
            if ($file_info['parentid'] == 1) {
                $host = $conf['app_apk_host'];
            } elseif ($file_info['parentid'] == 2) {
                $host = $conf['game_apk_host'];
            } else {
                $host = $conf['logic_apk_host'];
            }
        break;

        case '22'://按照分类进行区分(游戏/应用)（加密）
            if ($file_info['parentid'] == 1) {
                $host = $conf['app_s_host'];
            } elseif ($file_info['parentid'] == 2) {
                $host = $conf['game_s_host'];
            } else {
                $host = $conf['logic_s_host'];
            }
        break;

        case '3'://按照业务进行划分
            $host = $conf['logic_apk_host'];
        break;

        case '33'://按照业务进行划分（加密）
            $host = $conf['logic_s_host'];
        break;

        default:
            $host = $conf['logic_apk_host'];
        break;
    }

    return $host.$url;
}

function getIconHost()
{
    return getImageHost();
}

function getThumbHost()
{
    return getImageHost();
}

function getImageHost()
{
    $cdn = load_config('cdn');
    $app_name = defined('APP_NAME') ? APP_NAME : 'www';
    $conf = $cdn['img_host'][$app_name];
    if (!$conf) {
        $conf = $cdn['img_host']['gomarket'];
    }
    if (is_array($conf)) {
        $k = array_rand($conf);
        $host = $conf[$k];
    } else {
        $host = $conf;
    }
    return $host;
}

interface GoPrivate {
    public function init($params);
    public function free();
}

function load_private_impl($name, $version, $params = null) {
    $inc_file = GOPHP_ROOT. '/inc/'. $name. '.php';
    if (!is_file($inc_file))
        return null;
    include_once($inc_file);
    if (!interface_exists($name))
        return null;
    # 注意命名
    $src_file = GOPHP_ROOT. '/src/'. "${name}_${version}.php";
    if (!is_file($src_file))
        return null;
    include_once($src_file);
    # 不好事先确定功能的话
    # 用接口比抽象类灵活一些
    $clz = "${name}_${version}";
    if (!class_exists($clz))
        return null;
    $obj = new $clz();
    $obj->init($params);
    return $obj;
}

function num_format($num, $type)
{
    if ($num === '') return $num;
    $num = intval($num);
    switch ($type) {
        case 1 :
            return $num;
        break;

        case 2:
            if ($num <= 100) {
                return '100+';
            } elseif ($num < 1000) {
                $n = floor($num / 100);
                return "{$n}00+";
            } elseif ($num < 10000) {
                $n = floor($num / 1000);
                return "{$n}000+";
            } elseif ($num < 100000) {
                $n = floor($num / 10000);
                return "{$n}万+";
            } elseif ($num < 1000000) {
                $n = floor($num / 100000);
                return "{$n}0万+";
            } elseif ($num < 10000000) {
                $n = floor($num / 1000000);
                return "{$n}00万+";
            } elseif ($num < 100000000) {
                $n = floor($num / 10000000);
                return "{$n}000万+";
            } elseif ($num < 1000000000) {
                $n = floor($num / 100000000);
                return "{$n}亿+";
            } else {
                return "10亿+";
            }
        break;

        default :
            return $num;
        break;
    }
}
//电子书下载量格式化
function number_format_download($num)
{
     if ($num <= 100) {
                return '100次';
            } elseif ($num < 1000) {
                $n = floor($num / 100);
                return "{$n}00次";
            } elseif ($num < 10000) {
                $n = floor($num / 1000);
                return "{$n}千次";
            } elseif ($num < 100000) {
                $n = floor($num / 10000);
                return "{$n}万次";
            } elseif ($num < 1000000) {
                $n = floor($num / 100000);
                return "{$n}0万次";
            } elseif ($num < 10000000) {
                $n = floor($num / 1000000);
                return "{$n}00万次";
            } else {
                return "1000万次";
            }
}
function getmm($file, $line)
{
    if (isset($_GET['DEBUG'])) {
        echo memory_get_usage()/1024/1024, " at {$file} {$line}\n";
    }
}

function go_dump()
{
    if (isset($_GET['DEBUG'])) {
        $arg_list = func_get_args();
        foreach($arg_list as $v) {
            print_r($v);
        }
    }
}

function go_useage()
{
	static $last_time;
	if (empty($last_time)) {
		$start = microtime_float();
	} else {
		$start = $last_time;
	}

	$end = microtime_float();
	$last_time = $end;
    if (isset($_GET['DEBUG'])) {
        $trace = debug_backtrace();
		$spend = round($end - $start, 4);
        echo "{$trace[0]['file']} {$trace[0]['line']} ", round(memory_get_usage()/1024/1024), "M {$spend}\n";
    }
}

function go_exit()
{
    if (isset($_GET['DEBUG'])) {
        exit;
    }
}

function des_encrypt($text, $key){
    $size = mcrypt_get_block_size('des','ecb');
    $text = pkcs5_pad($text, $size);
    $td = mcrypt_module_open('des', '', 'ecb', '');
    $keys = md5($key);
    $iv = mcrypt_create_iv (mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
    mcrypt_generic_init($td, $key, $iv);
    $data = mcrypt_generic($td, $text);
    mcrypt_generic_deinit($td);
    mcrypt_module_close($td);
    return $data;
}

function cdn_encode($raw)
{
    $base64 = base64_encode($raw);
    $result = str_replace('+', '-', $base64);
    $result = str_replace('/', '_', $result);
    $result = str_replace('=', '.', $result);
    return $result;
}

function cdn_decode($result)
{
    $result = str_replace('-', '+', $result);
    $result = str_replace('_', '/', $result);
    $result = str_replace('.', '=', $result);
    $raw = base64_decode($result);
    return $raw;
}

function pkcs5_pad ($text, $blocksize){
    $pad = $blocksize - (strlen($text) % $blocksize);
    /*
    $pad_num = $pad - 1;
    $text .= str_repeat("\0", $pad_num);
    $text .= chr($pad);
    return $text;
    */
    $text .= str_repeat("\0", $pad);
    return $text;
}

function encrypt_apk_url($url)
{
    $cdn = load_config('cdn');
    $app_name = defined('APP_NAME') ? APP_NAME : 'www';
    $conf = $cdn['apk_host'][$app_name];
    if (!$conf) {
        $conf = $cdn['apk_host']['gomarket'];
    }
    $type = 0;
    $conf['apktype'] = checkApkType($conf['apktype']);
    if ($conf['apktype'] == 11 || $conf['apktype'] == 22 || $conf['apktype'] == 33) {
        $type = 1;
    }
    if ($type == 1 && function_exists('mcrypt_module_open')) {
file_put_contents('/tmp/hq_apk.log', "aaaa\n", FILE_APPEND);
        $pathinfo = pathinfo($url);
        $encrypt = des_encrypt($pathinfo['basename'].time(), 'fwsecret');
        $result = cdn_encode($encrypt);
        return $pathinfo['dirname']. '/' . $result;
    } else {
        return $url;
    }
}

function des_decrypt($key, $encrypted){
    $td = mcrypt_module_open('des','','ecb','');
    $iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
    $ks = mcrypt_enc_get_key_size($td);
    mcrypt_generic_init($td, $key, $iv);
    $decrypted = mdecrypt_generic($td, $encrypted);
    mcrypt_generic_deinit($td);
    mcrypt_module_close($td);
    $plain_text = pkcs5_unpad($decrypted);
    return $plain_text;
}

function pkcs5_unpad($text){
    /*
   $pad = ord($text{strlen($text)-1});
   if ($pad > strlen($text)) {
       return false;
   }
   $check = str_repeat("\0", $pad - 1). chr($pad);
   if (substr($text, -1 * $pad) != $check) {
      return false;
   }
   return substr($text, 0, -1 * $pad);
   */
   return preg_replace('/\0+$/', '', $text);
}

function go_autoload($className){
    if (preg_match('/^Go.*/', $className)) {
        load_core($className);
    }
}

function strip_input($input)
{
    $input = strip_tags($input);
    return $input;
}

function go_str_cn($string, $length, $append = '...') {
    if ( $length <= 0 ) {
        return '';
    }

    $string = trim($string);
    $strlength = strlen($string);
    if ($length == 0 || $length >= $strlength) {
        return $string;
    } elseif ($length < 0) {
        $length = $strlength + $length;
        if ($length < 0) {
            $length = $strlength;
        }
    }
    $newstr = '';
	$j = 0;
    for ($i = 0; $i < $strlength; $i ++) {
		$ord = ord ($string[$i]);
		if ($ord<192) {
			$newstr .= $string[$i];
		} elseif ($ord <224) {
			$newstr .= $string[$i]. $string[++$i];
			$j++;
		} else {
			$newstr .= $string[$i]. $string[++$i]. $string[++$i];
			$j++;
		}
		$j++;
		if ($j>=$length) break;
    }
    if ($append && $newstr != $string) {
        $newstr .= $append;
    }
    return $newstr;
}

function getMarketIcon($vr=''){
	return '/img/market/logo72_v645.png';
}
