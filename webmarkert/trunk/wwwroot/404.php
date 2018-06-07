<?php
include_once (dirname(realpath( __FILE__ )) . '/init.php');
$start_time = microtime_float();

$uri = $_SERVER['REQUEST_URI'];
$query = $_SERVER['QUERY_STRING'];
$query = preg_quote($query);
$uri = preg_replace('/^\/(game|app)\//', '/', $uri);
$uri = preg_replace('/^\/(game|app)$/', '/', $uri);
//$uri = preg_replace('/[^\/]+\//', '', $uri);
$uri = preg_replace('/\??'.$query.'/', '', $uri);

$channel = $_GET['channel'];
$parten_arr = array(
    array('/^\/$/', 'index.php', array(), 'index.html'),
    array('/^\/index\.html/', 'index.php'),
    array('/^\/sort_1_1.html/', 'applist.php?type=applist'),
    array('/^\/sort_2_1.html/', 'applist.php?type=gamelist'),
    array('/^\/soft_(\d+)\.html/', 'detail.php', array( 1 => 'id'), 'soft/%1$\'3.3s/%1$s.html'),
	array('/^\/pkg\/(.{2})(.{2})_([a-z0-9\._]+)\.html/i', 'package.php', array(1=>'dir',2=>'dir1', 3 => 'pkg'), 'pkg/%1$s/%2$s/%3$s.html'),	
    array('/^\/pkg\/([a-z0-9\._]+)\.html/i', 'package.php', array( 1 => 'pkg'), 'pkg/%1$\'3.6s/%1$s.html'),
    array('/^\/pkg\/([a-z0-9\._]+)/i', 'package.php', array( 1 => 'pkg'), 'pkg/%1$\'3.6s/%1$s.html'),
    array('/^\/recommend_(\d+)\.html/', 'good_recommend.php', array( 1 => 'page'), 'recommend_%1$s.html'),
	//评论详情页面
    array('/^\/post\/(.{2})(.{2})_([a-z0-9\._]+)\.html/i', 'comment.php?type1=post', array(1=>'dir',2=>'dir1', 3 => 'pkg'), 'post/%1$s/%2$s/%3$s.html'),			
	//软件详情评论页面
	array('/^\/comment\/(.{2})(.{2})_([a-z0-9\._]+)\.html/i', 'comment.php?type1=comment', array(1=>'dir',2=>'dir1', 3 => 'pkg'), 'comment/%1$s/%2$s/%3$s.html'),	
    array('/^\/applist\.html/', 'applist.php?type=applist'),
    array('/^\/gamelist\.html/', 'applist.php?type=gamelist'),
    array('/^\/subject\.html/', 'subject.php'),
    array('/^\/friendlink\.html/', 'join.php'),
    array('/^\/subjects_(\d+)\.html/', 'subject_list.php', array(1=>'page')),

	array('/^\/newsstand\/([a-z0-9\._]+)_([a-z0-9\._]+)_(\d+)\.html/', 'newsstand.php', array(1=>'ftype',2 => 'fftype',3=>'page')),
	array('/^\/newsstand\/([a-z0-9\._]+)_(\d+)\.html/', 'newsstand.php', array(1=>'ftype',2=>'page')),
	array('/^\/newsstand_(\d+)\.html/', 'newsstand.php',array(1=>'page')),
	array('/^\/newsstand\/(\d+)\.html/', 'newsstand.php?is_details=1',array(1=>'id'),'newsstand/%1$s.html'),	
    array(
        '/^\/sort_(\d+)_(\d+)_([a-z]+)\.html/', 
        'applist.php?type=appcat', 
        array(
            1 => 'sub_cat_id',
            2 => 'page',
            3 => array(
                'order',
                array( 'hot' => 1, 'new' => 0)
            ),
        ),
        'sort/%1$s/%2$s_%3$s.html'
    ),
    array(
        '/^\/tsort_(\d+)_(\d+)_(\d+)_([a-z]+)\.html/', 
        'applist.php?type=appctag', 
        array(
            1 => 'sub_tag_id',
            2 => 'sub_cat_id',
            3 => 'page',
            4 => array(
                'order',
                array( 'hot' => 1, 'new' => 0)
            ),
        ),
        'tsort/%1$s/%2$s/%3$s_%4$s.html'
    ),
    array(
        '/^\/list_(\d+)_(\d+)_([a-z]+)\.html/', 
        'list.php', 
        array(
            1 => 'parentid',
            2 => 'page',
            3 => array(
                'order',
                array( 'hot' => 1, 'new' => 0)
            ),
        ),
        'list/%1$s/%2$s_%3$s.html'
    ),
    array('/^\/subject_(\d+)\.html/', 'subject_detail.php', array( 1 => 'id'), 'subject/%1$\'3.3s/%1$s.html'),
    array('/^\/subject_(\d+)_(\d+)\.html/', 'subject_detail.php', array( 1 => 'id', 2 => 'page'), 'subject/%1$\'3.3s/%1$s_%2$s.html'),
    array('/^\/widgethotkey_(\d+)\.html/', 'widget_hotkey.php', array( 1=> 'theme')),
    array('/^\/widgetcat_(\d+)\.html/', 'widget_cat.php', array( 1 => 'parentid'), 'widgetcat/%1$\'3.3s/%1$s.html'),
    array('/^\/widgetcatetag_(\d+)\.html/', 'widget_catetag.php', array( 1 => 'parentid'), 'widgetcatetag/%1$\'3.3s/%1$s.html'),
    array('/^\/widgettop_(\d+)\.html/', 'widget_top.php', array( 1 => 'id'), 'widgettop/%1$\'3.3s/%1$s.html'),
	array('/^\/anzhi_qrimg\.html/', 'anzhi_qrimg.php' ,array(), 'anzhi_qrimg.html'),
    array(
        '/^\/widgetsort_(\d+)_(\d+)_(\d+)\.html/', 
        'widget_sort.php', 
        array(
            1 => 'id', 
            2 => 'order',
            3 => 'theme',
        ), 
        'widgetsort/%1$s/%2$s_%3$s.html'
    ),
    array(
        '/^\/widgettsort_(\d+)_(\d+)_(\d+)\.html/', 
        'widget_tsort.php', 
        array(
            1 => 'tag_id', 
            2 => 'cat_id',
            3 => 'order',
        ), 
        'widgettsort/%1$s/%2$s_%3$s.html'
    ),
    array(
        '/^\/widgetsubject_(\d+)_(\d+)_(\d+)\.html/', 
        'widget_subject.php', 
        array(
            1 => 'id', 
            2 => 'size',
            3 => 'theme',
        ), 
        'widgetsubject/%1$s/%2$s_%3$s.html'
    ),
    array('/^\/news\/content_([0-9]+)\.html/i', 'content_detail.php', array(1=>'content_id'), 'content/%1$s.html'),
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
        if (!empty($p)) $_GET = $p;
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

if ($has_rule && $file) {
    $cache_file = STATIC_DIR;
    if (!empty($channel) && preg_match('/^[a-z0-9_]+$/', $channel)) {
        $cache_file = $cache_file. DS. $channel. DS;
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
    if (preg_match('/\.html$/i', $cache_file)) {
        $info = get_static_info();
        $content .= "\n{$info}";
    }
    file_put_contents($cache_file, $content);
    exit($content);
}

function get_static_info()
{
    global $start_time;
    $end_time = microtime_float();
    $s = $end_time - $start_time;
    $t = date('Y-m-d H:i:s');
$script = <<<EOF
<script>
var s_generate_at='{$t}';
var s_spend ='{$s}';
</script>
EOF;
    return $script;
}

