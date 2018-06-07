<?php
//include_once ("../../../stdafx.php");
//ini_set("display_errors", true);
//error_reporting(E_ALL);
include dirname(__FILE__).'/../../newgomarket.goapk.com/init.php';
include_once ("../../data/config_plus.php");
include_once ("./function.php");
include_once (dirname(__FILE__).'/../../www.anzhi.com/function.php');
//ini_set('display_errors', 1);
ini_set("display_errors", 0);
ini_set("memory_limit", "512M");
error_reporting(0);
$allow_ip = array(
	#渠道短名称 => 允许访问的ip数组
	'az_zh'=>array('127.0.0.1','192.168.1.135'),
	'az_ap'=>array('218.241.82.226','121.10.241.69','121.10.241.75'),
	'az_lyx'=>array('218.241.82.226','115.29.191.216'),
	'az_jl'=>array('218.241.82.226','113.90.81.50','113.110.183.29','121.201.0.194','121.201.0.195','121.201.0.196','121.201.0.197','121.201.0.198','121.201.0.199'),
);

/**
 * Dispatch class
 */
class Api{
	public $params = null;
	public $action_arr = array(
		'commonSoftList',
		'commonDownload'
		);
	public $channel_arr = array(
		'b3fb905eb79d7c16c167fb36350849d2',//众合游戏
		'a8b91a4b6834f7ca4bbc0108c07e021b',//爱拍
		'1074cf2d67a5f067ec7b2438fd68dc1b',//来游戏
		'ed951795401d6323af458634be668c31cbc8c8f8',//金立
	);
	public function getClassByAct(){
		$this->chlAdapter();
		if (in_array($this->params['action'], $this->action_arr)){
			if (is_file($this->params['action'] . ".php")){
				include("./".$this->params['action'] . ".php");
			}else{
				header("HTTP/1.1 404 Not Found");
				header("Status: 404 Not Found");
				exit;
			}
		}else{
			header("HTTP/1.1 417 Expectation Failed");
            exit;
		}
	}

	public function getData(){
        if(in_array($this -> params['channel'],$this -> channel_arr)){
			$class = $this->params['action'];
            $class = preg_replace('/\//', '_', $class);
            $dataMod = new $class($this->params);
            $data = $dataMod->getData();
        }else{
        	header("HTTP/1.1 403 Forbidden");
            exit;
        }
        if($data=="[]"){
        	header("HTTP/1.1 204 No Content");
            exit;
        }
		$ip = onlineip();
		permanentlog('third_partner_access.log',json_encode(array(
			'action' => $this -> params['action'],
			'channel' => $this -> params['channel'],
			'ip' => $ip,
			'url' => $_SERVER['QUERY_STRING'],
			'time' => date('Y-m-d H:i:s'),
		)));
		return $data;
	}

	public function chlAdapter(){
		$chl = $this -> params['channel'];
		if($chl=='b3fb905eb79d7c16c167fb36350849d2'||$chl=='a8b91a4b6834f7ca4bbc0108c07e021b'||$chl=='1074cf2d67a5f067ec7b2438fd68dc1b'){
			$this -> action_arr = array(
				'commonSoftList',
				'commonDownload'
			);
		}
	}
}
$channel_map = array(
	'az_zh'=>'b3fb905eb79d7c16c167fb36350849d2',
	'az_ap'=>'a8b91a4b6834f7ca4bbc0108c07e021b',
	'az_lyx'=>'1074cf2d67a5f067ec7b2438fd68dc1b',
	'az_jl'=>'ed951795401d6323af458634be668c31cbc8c8f8',
);
if (isset($_GET['channel']) && isset($channel_map[$_GET['channel']])) {
	$_GET['channel_old'] = $_GET['channel'];
    $_GET['channel'] = $channel_map[$_GET['channel']];
}
checkIP($allow_ip,$_GET['channel_old']);
$start_time = microtime_float();
$api = new Api();
$api->params = $_GET;
if(isset($api->params['limit'])&&$api->params['limit']>=30){
	$api->params['limit'] = 30;
}
$channel_info = getChannelInfo($_GET['channel']);
$_SESSION['API_CID'] =  $channel_info['id'] ? $channel_info['id']  : 0;
$api->getClassByAct();
echo $api->getData();
$end_time = microtime_float();
$spend = $end_time - $start_time;

if ($spend > 0.5) {
	$time = date('Y-m-d H:i:s', time());
	$date = date('Y-m-d');
	file_put_contents("/tmp/interface_slow_{$date}.log", "{$time} : {$_GET['action']}: {$spend}\n", FILE_APPEND);
}
function getChannelInfo($channel){
	$mod = new Gomodel();
	$option = array(
		'table' => 'sdk_channel',
		'field' => 'id,channel_name',
		'where' => array('channel_code' => $channel, 'status'=>1),
		'cache' => 86400
	);
	$res = $mod->findOne($option);
	if ($res)
		return $res;
	else
		return false;
}
function checkIp($allow_ip,$channel){
	if ($_GET['action']=='commonDownload') return true;
	$ip = onlineip();
	if(isset($allow_ip[$channel])){
		if(!in_array($ip,$allow_ip[$channel])){
			header("HTTP/1.1 403 Forbidden");
			exit;
		}
	}

}