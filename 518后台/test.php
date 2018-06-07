<?php
$index = <<<EOF
<frameset rows="50, *" border="0" framespacing="0" frameborder="1">
	<frame scrolling="no" marginheight="0" marginwidth="0" noresize="" frameborder="1" name="top" src="test.php?a=top"> </frame>
	<frame marginheight="0" marginwidth="0" noresize="" frameborder="1" name="main"></frame>
</frameset>
EOF;

$top = <<<EOF
<form action="test.php?a=main" method="post" target="main">
IP: <input name="ip" size="100"/><br>
URL: <input name="url" size="100"/>
<input type="submit"/>
</form>
EOF;

$action = isset($_GET['a']) ? $_GET['a'] : 'index';
$action = $action . 'Action';

$action();

function indexAction()
{
	global $index;
	exit($index);
}

function topAction()
{
	global $top;
	exit($top);
}

function mainAction()
{
	$url = $_POST['url'];
	$ip = $_POST['ip'];
	
	//http://admin.goapk.local/test.php
	$url = str_replace('\\', '/', $url);
	$domain = '';
	if (!preg_match('/^http:/', $url)) {
		$url = 'http://' . $url;

	}
	preg_match('/^http:\/\/([^\/]+)/', $url, $m);
	$domain = $m[1];
	
	$url = preg_replace('/^http:\/\/([^\/]+)\/(.*)/', 'http://' . $ip . '/\2', $url);
	
	$header = array('Host:' . $domain);
	$request = require_url($url, $header);
	exit($request['content']);
}

function require_url($url, $header, $timeout = 4){
    $ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
	curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
	$result = curl_exec($ch);
	
	
	$result =  array(
		'content' =>$result,
		'info' => curl_getinfo($ch)
	);
	curl_close($ch);
	
	return $result;
}


