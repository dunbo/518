<?php

$www = $_SERVER['HTTP_HOST']; //www.anzhi.com
$wap = 'http://'.str_replace('www', 'm', $www).'/';

$uri = $_GET['url'];
$uri = str_replace('http://', '', $uri);
$uri = str_replace($www, '', $uri);
$uri = preg_replace('/^\/(game|app)\//', '/', $uri);
$uri = preg_replace('/^\/(game|app)$/', '/', $uri);
//echo $uri;
$parten_arr = array(
    array('/^(\/)?$/', array(0=>1), 'index.html'),
    array('/^\/([a-z]{3,4})list\.html/', array(1=>array('app'=>1,'game'=>2)), 'recommend_%1$s.html'),
    array('/^\/subject\.html/', array(0=>1), 'subject.html'),
    /*array('/^\/pkg\/(.{4})_([a-z0-9\.]+)\.html/i', array(1=>1,2=>1), 'app_%1$s_%2$s.html'),
    array('/^\/pkg\/([a-z0-9\.]+)\.html/i', array(1=>1), 'app_rpkg_%1$s.html'),
    array('/^\/pkg\/([a-z0-9\.]+)/i', array(1=>1), 'app_rpkg_%1$s.html'),
    array('/^\/subject_(\d+)\.html/', array(1=>1), 'subjectapp_%1$s.html'),
    array('/^\/news\/content_(\d+)\.html/', array(1=>1), 'news/content_%1$s.html'),*/
);

$file_parten = false;
$params = array();

foreach ($parten_arr as $val) {
    if (preg_match($val[0], $uri, $m)) {
        if(isset($val[1]) && is_array($val[1])){
            foreach ($val[1] as $k => $v) {
                $var_value = $m[$k];
                if (is_array($v)) {
                    $var_value = $v[$var_value];
                }
                $params[] = $var_value;
            }
        }
        if(isset($val[2])){
            $file_parten = $val[2];
        }
        break;
    }
}

if ($file_parten && $params) {
    $new_url = vsprintf($file_parten, $params);
    echo $wap.$new_url;
}