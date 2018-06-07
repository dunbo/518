<?php
include_once 'init.php';

$file = $_GET['f'];
parse_str($_SERVER['QUERY_STRING'], $p);
unset($p['f']);
if (!empty($p)) {
    $uri = $file. '?'. http_build_query($p);
} else {
    $uri = $file;
}
$url = url2static_url($uri);
if($file == 'content_detail.php'){
	header("Location: /$url");
}else{
	header("Location: $url");
}
