<?php
  include_once('../../init.php');
  load_helper('utiltool');
  $device_id = $_GET['device_id'];
  $p = $_GET['p'];
  $ip = $_GET['ip'];
  $time = time();
  $data = array(
	'device_id' => $device_id,
	'part_section' => $p,
	'ip' =>$ip,
	'time' => $time,
	);
 permanentlog('dragonattack.log', json_encode($data));
?>
