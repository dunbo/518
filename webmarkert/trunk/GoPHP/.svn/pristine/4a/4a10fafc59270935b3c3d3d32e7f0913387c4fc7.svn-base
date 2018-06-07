<?php
/*
 * created by 黄文强
 * at 2013/02/19
 */
function ipToLocation($ip, $switch)
{
	if (!preg_match('/^(\d{1,3}.){3}\d{1,3}$/', $ip))
		return false;
	if ($switch) {
		static $model;
		$iplong = ip2long($ip);
		if (empty($model)) {
			$model = new GoModel();
		}
		$option = array(
			'table' => 'ip2location',
			'where' => "`end`>=$iplong",
			'cache_time' => 3600,
		);
		$info = $model->findOne($option, 'ip2location/ip');
		if (empty($info) || $info['start'] > $iplong)
			return false;
		else
			return $info;
	} else {
		require_once(dirname(__FILE__) . '/../data/IP.class.php');
		$ip_info = IP::find($ip);
		if (empty($ip_info)) {
			return false;
		} else {
			$info = array(
				'country' => $ip_info[0],
				'province' => $ip_info[1],
				'city' => $ip_info[2],
			);
			return $info;
		}
	}
}
?>
