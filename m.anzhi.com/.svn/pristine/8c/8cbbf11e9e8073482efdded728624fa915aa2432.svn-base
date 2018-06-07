<?php
include_once (dirname(realpath(__FILE__)).'/init.php');

$from = trim($_POST['from']);
$ip = onlineip();
$type = trim($_POST['type']);
$data = array();
$time = time();

switch($type) {
	case "view":
		$softid = (int) $_POST['softid'];
		$data = array(
			'key' => $from."_view",
			'ip' => $ip,
			'softid' => $softid,
			'time' => $time,
		);
		break;

	case "":
		
		break;

}
permanentlog('other.log', json_encode($data));


echo 1;
return 1;
exit;
?>
