<?php
ini_set("display_errors", 1);
//error_reporting(E_ALL);
class adinfo
{
	public $params;
	public function __construct($param)
	{
		$this->params = $param;
	}
	public function getData()
	{	
		global $channel_map;
		$ip_addr = array('192.168.1.165'=>1,'218.241.82.226'=>1,'120.55.247.10'=>1);
		$ip = onlineip();
		if (!isset($ip_addr[$ip])) {
			exit('ip is error');
		}
	
		$package = $this->params['package'] ? trim($this->params['package']) : '';
		$num = $this->params['num'] ? (int)$this->params['num'] : 1;
		if ($num > 100) $num = 100;
		$channel = $this->params['channel'];
		$channel_key = array_keys($channel_map,$channel);
		$channel_key = $channel_key[0];
		if (empty($channel_key)) exit('channel is error');
		if (empty($package)) exit('package is null');

		$model = new GoModel();	
		$option = array(
			'table' => 'sj_ad_library',
			'where' => array(
				'status' => 1,
				'package' => $package,
			),
			'field' => 'package',
			'cache_time' => 3600,
		);
		$tpcount = $model -> findOne($option);
		if (empty($tpcount['package'])) exit('package is error');
		
		//$info = getAPI('soft.getdetail',array('package'=>$package));
		$url = array();
		for ($i=0;$i<$num;$i++) {
			$str = md5(uniqid(md5(microtime(true)),true).mt_rand(1,10000));
			
			$url[] = "http://m.anzhi.com/interface/index.php?action=addownload&package=$package&channel=$channel_key&checkcode=$str";
			permanentlog('ad_download.log', json_encode(array(
				'package' => $package,
				'checkcode' => $str,
				'channel' => $channel,
				'submit_tm' => time(),
				'ip' => $ip,
				'key'=>'create_url'
			)));
		}
		
		$return  = array(
			'DATA' => $url,
			'KEY' => strtoupper($this -> params['action']),
		);
		return json_encode($return);
	}
}

?>
