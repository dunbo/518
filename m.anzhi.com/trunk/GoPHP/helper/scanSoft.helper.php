<?php
//安全扫描抽象类
abstract class scanSafeSoft{
	abstract protected static function requestScan($params); //请求扫描抽象函数
	abstract protected static function responseScan();	//安全厂商回调抽象函数
	/**
	 * POST 请求封装
	 * $url: 请求地址
	 * $str: POST 参数
	 */
	static public function requestPost($url, $str){
		if (empty($str)) return false;
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 1);
		if ($str) curl_setopt($ch, CURLOPT_POSTFIELDS, $str);
		$re = curl_exec($ch);
		$http_info = curl_getinfo($ch);
		curl_close($ch);
		return array("http_code"=>$http_info['http_code'],"re"=>$re);
	}
	/**
	 * GET 请求封装
	 * $url: 请求地址
	 * $data: GET 参数
	 * $timeout: 超时时间
	 */
	static public function requestGet($url, $data = array(), $timeout = 4){
		if (!is_array($data) or empty($data)) return false;
		$str = "";
		foreach ($data as $k => $v){
			$nv = rawurlencode($v);
			if ($nv === null){
				continue;
			}
			if (empty($str)) $str = $k . "=" . $nv;
			else $str .= "&" . $k . "=" . $nv;
		}
		if(strpos($url,'?')){
			$mark = '&';
		}else{
			$mark = '?';
		}
		if ($str) $url .= $mark . $str;
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		//curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
		curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
		$result = curl_exec($ch);
		$http_info = curl_getinfo($ch);
		curl_close($ch);
		return array("http_code"=>$http_info['http_code'],"re"=>$result,"url" => $url);
	}	
	/**
	 * 获取软件的hash
	 * 
	 * @param $soft_dir 软件的路径
	 * @param $hash hash的算法，如md5，sha1等
	 */
	static public function getSoftHash($soft_file,$hash="sha1"){
		if(!is_file($soft_file)) return false;	
		$re = hash_file($hash,$soft_file);
		return $re;
	}
	
	/**
	 * 将xml转化成数组
	 * 
	 * @param $xml xml数据
	 */
	static public function xml2Array($xml){
		//if(empty($xml)) return false;
		$response = ArrayXml::loadFromFile($xml);
		return $response;
	}
}



/**
*	安全厂商 腾讯
*	请求方式 POST method
*	
*/
class scanSafeSoftQQ extends scanSafeSoft{
	public static $id = 'anzhi';//安全厂商提供的合作id
	public static $request_url = 'http://open.scan.qq.com/api/scansoft?id=';
	public static $secure_key = 'uIZGaM65yjK7D5KD'; //密钥
	
	public static function requestScan($params){	
		$scan_parm = json_encode(array(
			'scanlist' => array(
				array(
					'sid' => (string) $params['sfid'],
					'url' => $params['download_url'],
					'md5' => $params['soft_hash'],
				)
			)
		));
		self::$request_url = self::$request_url.self::$request_url;
		//计算authkey
		$authkey = md5($scan_parm.self::$id.self::$secure_key);
		$post_str = "authkey=".$authkey."&request=".$scan_parm; 
		$re = self::requestPost(self::$request_url, $post_str);  
		
		if($re["http_code"]!=200){
			sleep(5);
			$re = self::requestPost(self::$request_url, $post_str);  
			if($re["http_code"]!=200){
				permanentlog("qq_scan_v127_err.log",$post_str." | ".date("Y-m-d H:i:s"));
				return false;
			}
		}
		
		permanentlog("qq_scan_request_v127.log",json_encode($re)."md5 :".$params['soft_hash']." | ".date("Y-m-d H:i:s"));
		
		return $re;
	}
	
	public static function responseScan(){
		$re = file_get_contents('php://input');
		if(empty($re)) return false;
		$authkey = $re['authkey']; 
		$response = $re['response'];
		//校验数据是否一致
		$auth_key_verify = md5($data['response'].self::$id.self::$secure_key);
		if($auth_key_verify == $authkey){
			$data = json_decode($response,true);
			return $data;
		}else{
			permanentlog('qq_scan_authkey_failure_v127.log','anzhi authverify key '.$auth_key_verify.' qq  authkey '.$authkey.' response data:'.$response.'|'.date('Y-m-d H:i:s')."\n");
			return false;
		}
	}
}
/**
*	安全管家 安全扫描*
***/
class softScanAQGJ extends scanSafeSoft{
	public static $response_url = "http://www.anzhi.com/aqgj_scan_response.php";
	public static $request_url = "http://sapi.aqgj.net/api1.php";
	
	/**
	 * 像安全管家发送安全扫描请求
	 * 
	 * @param array $params 参数
	 * 
	 */
	static public function requestScan($params){
		$post_str = array(
			"c"=>"anzhi",
			"u"=>"{$params['download_url']}",
			"ft"=>"apk",
			"rt"=>"ac",
			"h"=>self::$response_url,
			"rt"=>"ac",
			"sha1"=>$params['soft_hash'],
		);
		$re = self::requestPost(self::$request_url, $post_str);  
		
		if($re["http_code"]!=200){
			sleep(5);
			$re = self::requestPost(self::$request_url, $post_str);  
			if($re["http_code"]!=200){
				permanentlog("aqgj_scan_err.log",$post_str." | ".date("Y-m-d H:i:s"));
				return false;
			}
		}
		
		permanentlog("aqgj_scan_request.log",json_encode($re)." | ".date("Y-m-d H:i:s"));
		
		return $re;
	}
	/**
	 * 获取安全管家返回的扫描结果
	 * 
	 */
	static public function responseScan(){
		$re = file_get_contents('php://input');
		if(empty($re)) return false;
		$filename = "/tmp/".md5($re).time().".xml";
		file_put_contents($filename, trim(urldecode(substr($re,2))));
		$re_array = self::xml2Array($filename);
		unlink($filename);
		permanentlog("aqgj_scan_response.log", "xml is:".substr($re,2)."\r\narray is:".json_encode($re_array)." | ".date("Y-m-d H:i:s"));
		return $re_array;
	}
}
/*
* 网秦安全扫描
*
*
*/
class softScanWQ extends scanSafeSoft{
	public static $response_url = "http://www.anzhi.com/wq_scan_response.php?sha1=";	
	/**
	 *  网秦发送安全扫描请求
	 * 
	 * @param array $params 参数
	 * 
	 */
	public static function requestScan($params){
		$get_str = array(
			'url' => $params['download_url'],
			'key' => '20001008',
			'lang' => 'cn',
			'style' => 'xml',
			'back' =>  $params['download_url'],
			'callback' => self::$response_url.$params['soft_hash'], 
 		);
		$request_url = "http://scan.netqin.com/open/api";
		$re = self::requestGet($request_url, $get_str);  
		if($re["http_code"]!=200){
			sleep(5);
			$re = self::requestGet($request_url, $get_str);  
			if($re["http_code"]!=200){
				permanentlog("wq_scan_err.log",var_export($re,true)." | ".date("Y-m-d H:i:s"));
				return false;
			}
		}
		
		permanentlog("wq_scan_request.log",json_encode($re)." | ".date("Y-m-d H:i:s"));
		
		return $re;
	}
	/**
	 * 获取网秦返回的扫描结果
	 * 
	 */
	static public function responseScan(){
		$re = file_get_contents('php://input');		
		if(empty($re)) return false;
		$filename = "/tmp/".md5($re).time().".xml";		
		$re = trim(urldecode($re));
		$re = substr($re,9);
		file_put_contents($filename, $re);
		$re_array = self::xml2Array($filename);		
		unlink($filename);
		permanentlog("wq_scan_response.log", "xml is:".$re."\r\narray is:".json_encode($re_array)." | ".date("Y-m-d H:i:s"));
		return $re_array;
	}
}


