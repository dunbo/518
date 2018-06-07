<?php
class download
{
	public $params;
	public function __construct($param)
	{
		$this->params = $param;
	}
	public function getData()
	{
		$softid = $this->params['softid'];
		//判断是否有个有效没有的话不让下
/*		load_helper('download');
		$can_download = is_can_donwload($softid);
		if (!$can_download) {
			header("HTTP/1.1 404 Not Found");
			            //var_dump($can_download,$softid);
			exit;
		}*/
        $download = array();
        $softObj = load_model('sjsoft');
        load_helper('utiltool');
        $option = array('softid' => $softid
                    ,'status'=>1,'hide'=>array(0,1));
		$app = $softObj->getDataList('sj_soft', array('where' => $option
				));
		$package = $app[0]['package'];
		if (empty($package))
		{
			header("HTTP/1.1 404 Not Found");
			exit;
		}
        $option = array('softid' => $softid
                    ,'package_status'=>1);
		$app = $softObj->getDataList('sj_soft_file', array('where' => $option
				));
		$package = $app[0]['apk_name'];
		if (empty($package))
		{
			header("HTTP/1.1 404 Not Found");
			exit;
		}
		// ip过滤
		$ip = onlineip();
		if (!$this->isIpBanned($ip, $softid))
		{
			$dltime = time();

			$h = date("H");

			permanentlog('parter_'.$h.'.json', json_encode(array('softid' => $app[0]["softid"],
						'package' => $app[0]["apk_name"],
						'channel' => $this->params['channel'],
						'ip' => $ip,
						'submit_tm' => time() ,
						)));

			permanentlog('install_log_'.$h.'.json', json_encode(array('softid' => $app[0]["softid"],
						'userid' => GO_UID_DEFAULT,
						'action' => ACTION_PARTER_DOWNLOAD,
						'channel' => $this->params['channel'],
						'submit_tm' => time() ,
						'package' => $app[0]["apk_name"],
						'ip' => $ip,
						)));
		}
		$filename = "$package.apk";
		$apk_host = getApkHost($app[0]);
		$this->toLocation($apk_host . $app[0]['url']);
		exit;
	}


	public function isIpBanned($ip, $softid)
	{
		// 单ip单软件每天最大下载量
		$threshold = 50;
		$redis =  GoCache::getCacheAdapter('redis');
		$key = $ip.":interface:".date("Ymd");
		$soft_num = $redis->gethash($key, $softid);
		if (!$soft_num)
		{
			$soft_num = 0;
		}

		if ($soft_num >= $threshold)
		{
			return True;;
		}
		$soft_num = $soft_num + 1;
		$r = $redis->sethash($key, array($softid=>$soft_num), 86400);

		return False;
	}
    function toLocation($url) {
        header("content-type:text/html; charset=utf-8");
        header("Location: {$url}");
        exit;
    }
}

?>
