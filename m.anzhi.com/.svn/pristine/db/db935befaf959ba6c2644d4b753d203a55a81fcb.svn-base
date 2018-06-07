<?php 
include_once (dirname(realpath(__FILE__)).'/../init.php');

if($_GET['sid'] && eregi('[0-9a-zA-z]', $_GET['sid']) && strlen($_GET['sid']) == 32)
{
	session_id($_GET['sid']);
}
if($_GET['telphone'])
{
	$telphone = $_GET['telphone'];
	$name = $_GET['name'];
	$address=$_GET['address'];
	$aid = $_GET['aid'];
	$sid = $_GET['sid'];
	$my_time = time();
	$key="award";
	session_start();
	if(!preg_match("/^1[34578][0-9]{9}$/",$telphone) || strlen($telphone) != 11)
	{
		echo 500;
		return 500;
	}
	else
	{
		$log_data = array(
			'activity_id' => $aid,
			'sid' => $sid,
			'telphone' => $telphone,
			'name' => $name,
			'ip' => $_SERVER['REMOTE_ADDR'],
			'device_id' => $_SESSION['DEVICEID'],
			'address'=>$address,
			'time' => time(),
			'key'  => $key,
		);
		permanentlog('activity_'.$aid.'.log', json_encode($log_data));
		$data = array(200,$telphone,$name,$address,$aid,$sid,$key);
		echo json_encode($data);
		return json_encode($data);
	}
}
else
{
	$tplObj -> out['sid'] = $_GET['sid'];
	$tplObj -> out['static_url'] = $configs['static_url'];
	$tplObj -> display('april_consum_index.html');
}
?>