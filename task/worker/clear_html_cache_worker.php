<?php
require_once(dirname(__FILE__).'/../init.php');
//取得本机服务器IP
$p = popen('/sbin/ifconfig', 'r');
$s = fread($p, 4096);
preg_match('#inet addr:(192.168.[0-9\.]+?)\s#', $s, $ip);
$ip = $ip[1];

$task_name = 'clear_html_cache_'.$ip; //定义gearman的任务名

//将任务名set进cache里面，方便client做分发
$task_list = GoCache::get($gearman_worker_list_key);
$task_list[$task_name] = 1;
GoCache::set($gearman_worker_list_key, $task_list, 0, 'memcached');

$worker->addFunction($task_name, "clear_html_cache_func");



while ($worker->work());

function clear_html_cache_func($job)
{
    //global $html_cache_dir;

    if ( !($clear = unserialize( $job->workload())) ) { return False; }
 	//html cache的缓存目录
    if(isset($clear['dir_work'])){
      $html_cache_dir = realpath(dirname(__FILE__).'/../../'.$clear['dir_work'].'/html_cache/');
    }else{
      $html_cache_dir = realpath(dirname(__FILE__).'/../../newwebmarket-branch/html_cache/');
    }
    if (!$html_cache_dir) { return False; }
    if ($clear['all'] === 1) { //删除全部缓存
        $file_arr = glob($html_cache_dir.'/*');
        foreach ($file_arr as $file) {  rm_file($file); }
        return True;
    }

    if ($clear['softid'] && is_array($clear['softid'])) { //删除软件详细页的缓存
        foreach ($clear['softid'] as $softid) {
            preg_match('#([0-9]{0,3})[0-9]*#si', $softid, $prefix);
            if ( !($prefix = $prefix[1]) ) { continue ; }
            $suffix = '/soft/'.$prefix.'/'.$softid.'.html';
            $file = $html_cache_dir.$suffix;
            rm_file($file);
            rm_channel($suffix);
        }
    }

    if ($clear['sort'] && is_array($clear['sort'])) { //删除分类页的缓存
        foreach ($clear['sort'] as $sort) {
            $suffix =  '/sort/'.$sort.'/';
            $dir = $html_cache_dir.$suffix;
            rm_file($dir);
            rm_channel($suffix);
        }
    }

    if ($clear['index'] === 1) { //删除首页
        $suffix = '/index.html';
        $file = $html_cache_dir.$suffix;
        rm_file($file);
        rm_channel($suffix);
    }

    if ($clear['list'] && is_array($clear['list'])) { //删除各个type页
        foreach ($clear['list'] as $type) {
            $suffix = '/'.basename($type).'/';
            $dir = $html_cache_dir.$suffix;
            rm_file($dir);
            rm_channel($suffix);
        }
    }
}

//删除各个渠道对应的页面
function rm_channel($suffix)
{
    global $html_cache_dir;
    $channel_dir = glob($html_cache_dir.'/channel_*');
    foreach ($channel_dir as $dir) {
        $file = $dir.$suffix;
        rm_file($file);
    }
}

//删除文件
function rm_file($file)
{
    if ( !($file = format_file($file)) ) { return False; }
    echo date('Y-m-d H:i:s', time()).' '.$file."\n";
    if (is_file($file)) {
        unlink($file);
    } elseif (is_dir($file)) {
        system('rm -r -f '.escapeshellarg($file));
    }
}

//判断删除的文件是否合法
function format_file($file)
{
    $file = realpath($file);
    if ( !( is_file($file)) && !(is_dir($file) ) ) { return False; }
    if ( !( is_writable($file) ) || !( is_writable(dirname($file)) ) ) { return False; }
    if ( !( strstr($file, '/html_cache/') ) ) { return False; }
    return $file;
}
