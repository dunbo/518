<?php
$url_info = parse_url($_SERVER['REQUEST_URI']);
$uri = $url_info['path'];
$sub_dir = '';
if (preg_match('/^\/([a-zA-Z0-9\_]+)\//', $uri, $m)) {
	$string = $m[1];
	$sub_dir = $string;
	$tmp = explode('_', $string);
	if ($tmp[0] != 'concise') {
		$_GET['channel'] = $tmp[0];
	}
	
	if (stripos($string, 'concise') !== false) {
		$_GET['concise'] = 1;
	}
	
}

$uri = preg_replace('/(\/+)/', '/', $uri);
$uri = preg_replace('/([a-zA-Z0-9\_]+\/)?/', '', $uri);

//include_once (dirname(realpath( __FILE__ )) . '/init.php');		// 包含初使文件
//$start_time = microtime_float();			// 定义时间

$parten_arr = array(
	array('/^\/$/', 'index.php', array(), 'index.html'),
	array('/^\/index\.html/', 'index.php'),
    array('/^\/a_(\d+)\.html/', 'app.php?type=share&package=cn.goapk.market', array(1=>'aid')),
    array('/^\/f_(\d+)\.html/', 'app.php?type=share&package=cn.goapk.market', array(1=>'fid')),
#    array('/^\/battle\.html/', 'app.php?type=share&package=com.yuezhan'),
    array('/^\/battle\.html/', 'app.php?type=share&softid=2314272'),
	array('/^\/market\.html/', 'app.php?type=share&package=cn.goapk.market'),
    array('/^\/acts\.html/', 'app.php?type=acts&package=cn.goapk.market'),
	array('/^\/baidu\.html/', 'app.php?type=baidu&package=cn.goapk.market'),
	array('/^\/baidu_(\d+)\.html/', 'app.php?type=baidu&package=cn.goapk.market', array(1=>'aid')),
	array('/^\/index_([a-z]+)_(\d+)\.html/', 'index.php', array(1=>'type', 2=>'morelist'), 'index/%1$s_%2$d.html'),
	array('/^\/index_([a-z]+)\.html/', 'index.php', array(1=>'type'), 'index_%1$s.html'),
	array('/^\/index_(\d+)\.html/', 'index.php', array(1=>'morelist'), 'index/index_%1$d.html'),
	
	array('/^\/necessary_(\d+)\.html/', 'inapp.php', array(1=>'morelist'), 'necessary/necessary_%1$d.html'),
	array('/^\/necessary\.html/', 'inapp.php', array(), 'necessary.html'),
	
	array('/^\/classifyapp_(\d+)_(\d+)_(\d+)\.html/', 'app.php?type=classifyapp', array(1=>'sub_cat_id',2=>'order', 3=> 'morelist'), 'classifyapp/%1$d_%2$d_%3$d.html'),
	array('/^\/classifyapp_(\d+)_(\d+)\.html/', 'app.php?type=classifyapp', array(1=>'sub_cat_id',2=>'order'), 'classifyapp/%1$d_%2$d.html'),
	array('/^\/classifytag_(\d+)_(\d+)_(\d+)_(\d+)\.html/', 'app.php?type=classifytag', array(1=>'sub_tag_id',2=>'sub_cat_id',3=>'order', 4=> 'morelist'), 'classifytag/%1$d_%2$d_%3$d_%4$d.html'),
	array('/^\/classifytag_(\d+)_(\d+)_(\d+)\.html/', 'app.php?type=classifytag', array(1=>'sub_tag_id',2=>'sub_cat_id',3=>'order'), 'classifytag/%1$d_%2$d_%3$d.html'),
	/******************************/
	array('/^\/hanhua_(\d+)_(\d+)\.html/', 'app.php?type=hanhua', array(1 => 'hanhua_id', 2 => 'morelist'), 'hanhua/%1$d_%2$d.html'),
	array('/^\/hanhua_(\d+)\.html/', 'app.php?type=hanhua', array(1 => 'hanhua_id'), 'hanhua/%1$d.html'),
	/******************************/
    array('/^\/(history)_(\d+)_(\d+)\.html/', 'app.php', array(1=>'type', 2=>'softid', 3=>'morelist'), '%1$s/%2$d_%3$d.html'),
    array('/^\/(channel)_(\d+)_(\d+)\.html/', 'app.php', array(1=>'type', 2=>'softid', 3=>'chl_num'), '%1$s/%2$d_%3$d.html'),
    array('/^\/(chlpkg)_(\d+)_(\d+)\.html/', 'app.php', array(1=>'type', 2=>'softid', 3=>'chl_pkg_url_id'), '%1$s/%2$d_%3$d.html'),
	array('/^\/(info|comment|share|sem|history|mxzm|semgame)_(\d+)\.html/', 'app.php', array(1=>'type', 2=>'softid'), '%1$s/%2$\'3.3s/%2$d.html'),
	array('/^\/(post)_(.{2})(.{2})_([a-zA-Z0-9\_.]+)\.html/', 'comment_detail.php', array(1=>'type',2=>'dir',3=>'dir1', 4=>'package'), 'post/%2$s/%3$s/%4$s.html'),	
	array('/^\/(chlapp)_(.{8,12})_(.{2})(.{2})_([a-zA-Z0-9\_.]+)\.html/', 'app.php', array(1=>'type',2=>'chl_cid',3=>'dir',4=>'dir1', 5=>'package'), '%1$s/%2$s/%3$s/%4$s/%5$s.html'),	
	array('/^\/(app)_(.{2})(.{2})_([a-zA-Z0-9\_.]+)\.html/', 'app.php', array(1=>'type',2=>'dir',3=>'dir1', 4=>'package'), 'app/%2$s/%3$s/%4$s.html'),	
	array('/^\/(recommend|top|classify|classify)_(\d+)\.html/', 'app.php', array(1=>'type', 2=>'parent_cat_id'), '%1$s/%2$\'3.3s/%2$d.html'),

	array('/^\/subjectapp_(\d+)\.html/', 'subject.php?type=subject_app', array(1=>'subject_id'), 'subjectapp/%2$\'3.3s/%2$d.html'),
	array('/^\/subjectwifi_(\d+)\.html/', 'subject_wifi.php', array(1=>'subject_id'), 'subjectwifi/%2$\'3.3s/%2$d.html'),
	array('/^\/activaties_(\d+)\.html/','activaties.php?type=activaties',array(1=>'id'),'activaties/%1$\'3.3s/%1$d.html'),
	array('/^\/activity_(\d+)\.html/','activity.php?type=activity',array(1=>'id'),'activity/%1$\'3.3s/%1$d.html'),
	array('/^\/adactivity_(\d+)\.html/','adactivity.php?type=adactivity',array(1=>'id'),'adactivity/%1$\'3.3s/%1$d.html'),
	array('/^\/downloaded_(\d+)\.html/','downloaded.php?type=downloaded',array(1=>'id'),'downloaded/%1$\'3.3s/%1$d.html'),
	array('/^\/subject\.html/', 'subject.php', array(), 'subject.html'),
    array('/^\/gamenews_(\d+)\.html/', 'gamenews.php', array(1=>'id'), 'gamenews/%1$\'3.3s/%1$d.html'),
    array('/^\/sdkpushnotice_(\d+)\.html/', 'sdkpushnotice.php', array(1=>'id'), 'sdkpushnotice/%1$\'3.3s/%1$d.html'),
	array('/^\/anzhiapk_(\d+)\.html/', 'anzhiapk.php' ,array(1=>'type'), 'anzhiapk/%1$\'3.3s/%1$d.html'),
	array('/^\/anzhiapk\.html/', 'anzhiapk.php' ,array(), 'anzhiapk.html'),
	array('/^\/help_new\.html/', 'help.php?tpl_ver=v6' ,array(), 'help_new.html'),
	array('/^\/help\.html/', 'help.php' ,array(), 'help.html'),
	array('/^\/qinsmoon\.html/', 'qinsmoon.php' ,array(), 'qinsmoon.html'),
	array('/^\/lottery\.html/', 'lottery/lottery.php'),
	array('/^\/tianlong\.html/', 'lottery/tianlong.php' ,array(), 'tianlong.html'),
	array('/^\/pfcomjson_(\d+)_(\d+)\.html/', 'perfect.php?method=comment&ajax=1', array(1=>'id', 2=>'page'), 'pfcomjson/%1$d_%2$d.html'),
	array('/^\/pfhisjson_(\d+)\.html/', 'perfect.php?method=history&ajax=1', array(1=>'page'), 'pfhisjson/%1$d.html'),
	array('/^\/perfect_history\.html/', 'perfect.php?method=history', array(), 'perfect/history.html'),
	array('/^\/perfect_comment_(\d+)\.html/', 'perfect.php?method=comment', array(1=>'id'), 'perfect/comment_%1$d.html'),
	array('/^\/perfect_(\d+)\.html/', 'perfect.php', array(1=>'id'), 'perfect/%1$d.html'),
	
	array('/^\/([a-z]+)_(\d+)_(\d+)\.html/', 'app.php', array(1=>'type', 2=>'parent_cat_id', 3=>'morelist'), '%1$s/%2$d_%3$d.html'),
	array('/^\/promotion_(\d+)\.html/', 'lottery/promotion.php', array(1=>'id'), 'promotion/%1$d.html'),
	array('/^\/extend_(\d+)\.html/', 'lottery/coactivity_extend.php', array(1=>'aid'), 'extend/%1$d.html'),
	array('/^\/softnews_(\d+)\.html/', 'softnews.php', array(1=>'id'), 'softnews/%1$\'3.3s/%1$d.html'),
	array('/^\/softexpand_(\d+)\.html/', 'softexpand.php', array(1=>'softid'), 'softexpand/%1$\'3.3s/%1$d.html'),
	array('/^\/content_([0-9]+)\.html/', 'content_detail.php', array(1=>'content_id'), 'content/%1$s.html'),
	array('/^\/content_tf_([0-9]+)\.html/', 'content_tf_detail.php', array(1=>'content_id'), 'content_tf/%1$s.html'),
);

$has_rule = false;
$file = false;
$file_parten = false;
$params = array();
foreach ($parten_arr as $val) {
    if (preg_match($val[0], $uri, $m)) {
        $file = $val[1];
        parse_str(preg_replace('#.+?\.php[\?]?#si', '', $file), $p);
        $file = preg_replace('#\?(.*)#si', '', $file);
        if (!empty($p)) {
			foreach($p as $k=>$v){
				$_GET[$k] = $v;
			}
		}
		
        if (isset($val[2])) {
            foreach ($val[2] as $k => $v) {
                $var_name = is_array($v) ? $v[0] : $v;
                $var_value = $m[$k];
                $params[] = $var_value;
                
                if (is_array($v)) {
                    $var_value = $v[1][$var_value];
                }
                $_GET[$var_name] = $var_value;
            }
        }
        if (isset($val[3])) {
            $file_parten = $val[3];
        }
        $has_rule = true;
        break;
    }
}
define('DS', DIRECTORY_SEPARATOR);
if ($has_rule && $file) {
    //$cache_file = STATIC_DIR;
	$cache_file = dirname(realpath(__FILE__)).'/html_cache/';
    if (!empty($sub_dir) && preg_match('/^[a-z0-9_]+$/', $sub_dir)) {
        $cache_file = $cache_file. DS. $sub_dir. DS;
    }
    if ($file_parten) {
        $cache_file .= vsprintf($file_parten, $params);
    } else {
        $cache_file .= $uri;
    }
    $_SERVER['SCRIPT_NAME'] = $file;
    $_SERVER['QUERY_STRING'] = http_build_query($_GET);
    mkdir(dirname($cache_file),0755,true);
   
    ob_start();
    include_once($file);
    $content = ob_get_clean();
    if (empty($content)) {
        exit;
    }
    file_put_contents($cache_file, $content);
    exit($content);
}
