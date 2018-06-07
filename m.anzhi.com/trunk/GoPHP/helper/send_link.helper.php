<?php
class send_link{
	//死链地址
	protected static $wdj_dead_link = "http://appapi.wandoujia.com/app/getBrokenLink.php?key=625f650b2ffc7e7ad508fa48b75380d3";
	protected static $under_key = "625f650b2ffc7e7ad508fa48b75380d3";
	/**
		发送死链请求
	*/
	public static function send($params){
		$softid_str = array();
		foreach($params as $key => $val){
			$underinfo['urls']['url'][] = array(
				'id' => $val['softid'],
				'link' => "http://wdj.anzhi.com/soft_" . $val['softid'] . ".html",
				'returnCode' => 403,
			);
			$softid_str[] = $val['softid'];
		}
		$underinfo['counts'] = count($underinfo['urls']['url']);
        $under_data['key'] = self::$under_key;
        $under_data['data'] = json_encode($underinfo);
        $http_result = self::requestPost(self::$wdj_dead_link, $under_data);
	
		$date=date("Ymd",time());
		$dir="/data/att/permanent_log/deadlink/{$date}/";
		if(!file_exists($dir)){
			mkdir($dir,0755,true);
		}
		//Success, Anzhi Broken Link Data Notification Received
		$softids = implode(',',$softid_str);
		if(strstr($http_result,'Success')){
			$time = date('Y-m-d H:i:s', time());
			$content = "{$time} : 成功发送死链：softids {$softids}\r\n";
			file_put_contents("{$dir}deadlink_success.log", $content,FILE_APPEND);
		}else{
			$time = date('Y-m-d H:i:s', time());
			$content = "{$time} : 发送死链失败：softids {$softids} \r\n";
			file_put_contents("{$dir}deadlink_failure.log", $content,FILE_APPEND);
		}
	}

	public static function requestPost($url,$vals){

	  	$res = curl_init();
 		curl_setopt($res, CURLOPT_URL, $url);
		//curl_setopt($res, CURLOPT_HTTPHEADER, array($host_dam));
		curl_setopt($res, CURLOPT_POST, true);
		curl_setopt($res, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($res, CURLOPT_POSTFIELDS, $data);
		$result = curl_exec($res);
		curl_close($res);
		return $result;

	}
}


