<?php 
require_once(dirname(realpath(__FILE__)) . '/init_page.php');
$sid = $_GET['sid'];
$tplObj -> out['sid'] = $sid;
$tplObj -> out['static_url'] = $configs['static_url'];
$tplObj -> out['aid'] = $aid;


$version_code = $_SESSION['VERSION_CODE'];
//小于6.3版本 升级
/*if($version_code <6300)
{
	$soft_model = load_model('softlist');
	$anzhilist = $soft_model->getPackageToSoftId("cn.goapk.market");
	$anzhiid = $anzhilist[0];
	$soft_info = $soft_model ->getsoftinfos($anzhiid, getFilterOption());
	$tplObj -> out['soft_info'] = $soft_info[$anzhiid];
	$tplObj -> display('lottery/comment_reply/upgrade.html');
}
else
{*/	
	if($_GET['telphone'])
	{
		$telphone = $_GET['telphone'];
		$aid = $_GET['aid'];
		$sid = $_GET['sid'];
		if(!preg_match("/^1[34578][0-9]{9}$/",$telphone) || strlen($telphone) != 11)
		{
			echo 500;
			return 500;
		}
		else
		{
			$log_data = array(
				'uid' => $_SESSION['USER_UID'],
				'users' =>$_SESSION['USER_NAME'],
				'imsi' => $_SESSION['USER_IMSI'],
				'device_id' => $_SESSION['DEVICEID'],
				'ip' => $_SERVER['REMOTE_ADDR'],
				'activity_id' => $aid,
				'sid' => $sid,
				'telphone' => $telphone,
				'time' => time(),
				'key'  => "user_info",
			);
			permanentlog('activity_'.$aid.'.log', json_encode($log_data));
			$data = array(200,$telphone,$aid,$sid,$key);
			echo json_encode($data);
			return json_encode($data);
		}
	}
	else
	{
		//打开页面日志
		$log_data = array(
			'uid' => $_SESSION['USER_UID'],
			'users' =>$_SESSION['USER_NAME'],
			'imsi' => $_SESSION['USER_IMSI'],
			'device_id' => $_SESSION['DEVICEID'],
			'ip' => $_SERVER['REMOTE_ADDR'],
			'activity_id' => $aid,
			'sid' => $sid,
			'time' => time(),
			'key' => 'show_homepage',
		);
		permanentlog('activity_'.$aid.'.log', json_encode($log_data));		
		$tplObj -> display('lottery/comment_reply/index.html');
	}
//}
?>