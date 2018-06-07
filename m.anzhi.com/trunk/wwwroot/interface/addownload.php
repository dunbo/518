<?php

class addownload
{
	public $params;
	public function __construct($param)
	{
		$this->params = $param;
	}
	public function getData()
	{	
		$package = $this->params['package'] ? trim($this->params['package']) : '';
		$channel = $this->params['channel'];
		$check_code = $this->params['checkcode'];
		$channel = $this->params['channel'];
		$ip = onlineip();
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
		
		$info = getAPI('soft.getdetail',array('package'=>$package));
		$info = json_decode($info, true);
		
		permanentlog('ad_download.log', json_encode(array(
			'softid' => $info["ID"],
			'package' => $package,
			'checkcode' => $check_code,
			'channel' => $channel,
			'submit_tm' => time(),
			'ip' => $ip,
			'key'=>'download'
		)));
		
		$filename = "$package.apk";
		$this->toLocation($info['DOWNLOAD_URL']);
	}
	function toLocation($url) {
        header("content-type:text/html; charset=utf-8");
        header("Location: {$url}");
        exit;
    }
}

?>
