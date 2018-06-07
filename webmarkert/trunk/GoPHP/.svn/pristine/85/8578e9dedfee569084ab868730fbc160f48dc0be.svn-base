<?php   
require_once 'xml/ArrayXml.class.php';
class scanSoft{
    static public function getSoftModel()
	{
		static $model;
		 
		if(!is_object($model))
         {		
		   $model = new GoModel();
		 }
		return $model;
	}
	 
	static public function goAction($params,$fileurl,$staticData='STATIC_DATA'){
		 $soft_hash_sha1=self::getSoftHash($staticData.$fileurl); //sha1
		 $soft_hash_md5=self::getSoftHash($staticData.$fileurl,'md5'); //md5 
		/*  if($selcetConn=='gophp')
		 {  
			if(!is_object($model))
			{
				$model = new GoModel();
			}
		 }  */
		 $re = self::requestPostQQ($params,$soft_hash_sha1);
		 $re = self::requestPostAQGJ($params,$soft_hash_sha1);
		 $re = self::requestGetWQ($params,$soft_hash_sha1);	
		 
		 $params['soft_hash'] = $soft_hash_md5;
		 $re = self::requestGetJS($params,$selcetConn);
	}
	
/* 	public function insertData($tablename,$scan_result ){
	  // global $softObj;
	   $softObj->insertData($safetable, $safelist);
	} */
	
	static public function requestPostQQ($params,$soft_hash,$modelobj){  
       permanentlog("qq_statr.log",var_export($params,true).$soft_hash." | ".date("Y-m-d H:i:s"));
		$scan_result = array(
			'sfid' => $params['sfid_type'],
			'hash' => $soft_hash,
			'provider' => 1,
			'time_req' => time(),
		);
      /*  if($selcetConn=='gophp')
	   {  
	       $data=$scan_result;
		   $model->insert($data);
	   }
	   else
	   { */
		   scanSoft::getSoftModel()->insert($scan_result);
	   //}
		$response_url = "http://www.anzhi.com/scan_soft_response.php?checkCompany=1";
		$post_str = "
			<?xml version='1.0' encoding='utf-8'?>
				<request>
					<service>CheckByUrl</service>
					<username>goapk</username>
					<password>pL5ceIVMu</password>
					<url>{$params['download_url']}</url>
					<response_url>{$response_url}</response_url>
					<hashcode>{$params['soft_hash']}</hashcode>
				</request>
		";
		
		$request_url = "http://open.scan.qq.com";//"http://market.goapk.com/scan_soft_response.php?checkCompany=1";
		$re = self::requestPost($request_url, $post_str);  
		
		if($re["http_code"]!=200){
			sleep(5);
			$re = self::requestPost($request_url, $post_str);  
			if($re["http_code"]!=200){
				permanentlog("qq_scan_err.log",$post_str." | ".date("Y-m-d H:i:s"));
				return false;
			}
		}
		
		permanentlog("qq_scan_request.log",json_encode($re)." | ".date("Y-m-d H:i:s"));
		
		return $re;
	}
	/**
	 * 获取qq返回的扫描结果
	 * 
	 */
	static public function getResponseQQ(){

		$re = file_get_contents('php://input');
		if(empty($re)) return false;
		$filename = "/tmp/".md5($re).time().".xml";
		file_put_contents($filename, trim($re));
		$re_array = self::xml2Array($filename);
		unlink($filename);
		permanentlog("qq_scan_response.log", "xml is:".$re."\r\narray is:".json_encode($re_array)." | ".date("Y-m-d H:i:s"));
		return $re_array;
	}
	
	/**
	 * 像安全管家发送安全扫描请求
	 * 
	 * @param array $params 参数
	 * 
	 */
	static public function requestPostAQGJ($params,$soft_hash ){
		permanentlog("aqgj_statr.log",var_export($params,true).$soft_hash." | ".date("Y-m-d H:i:s"));
		$scan_result = array(
			'sfid' => $params['sfid_type'],
			'hash' => $soft_hash,
			'provider' => 2,
			'time_req' => time(),
	    );
		
	   /* if($selcetConn=='gophp')
	   {  
	       $data=$scan_result;
		   $model->insert($data);
	   }
	   else
	   {
		   scanSoft::getSoftObj()->insertData('sj_soft_scan_result', $scan_result);
	   } */
	
	    scanSoft::getSoftModel()->insert($scan_result);
		$response_url = "http://www.anzhi.com/scan_soft_response.php?checkCompany=2";
		$post_str = array(
			"c"=>"anzhi",
			"u"=>"{$params['download_url']}",
			"ft"=>"apk",
			"rt"=>"ac",
			"h"=>$response_url,
			"rt"=>"ac",
			"sha1"=>$params['soft_hash'],
		);
		
		$request_url = "http://sapi.aqgj.net/api1.php";
		$re = self::requestPost($request_url, $post_str);  
		
		if($re["http_code"]!=200){
			sleep(5);
			$re = self::requestPost($request_url, $post_str);  
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
	static public function getResponseAQGJ(){
		$re = file_get_contents('php://input');
		if(empty($re)) return false;
		$filename = "/tmp/".md5($re).time().".xml";
		file_put_contents($filename, trim(urldecode(substr($re,2))));
		$re_array = self::xml2Array($filename);
		unlink($filename);
		permanentlog("aqgj_scan_response.log", "xml is:".substr($re,2)."\r\narray is:".json_encode($re_array)." | ".date("Y-m-d H:i:s"));
		return $re_array;
	}
	/**
	 *  网秦发送安全扫描请求
	 * 
	 * @param array $params 参数
	 * 
	 */
	static public function requestGetWQ($params,$soft_hash ){
	   permanentlog("wq_statr.log",var_export($params,true).$soft_hash." | ".date("Y-m-d H:i:s"));
	   $scan_result = array(
			'sfid' => $params['sfid_type'],
			'hash' => $soft_hash,
			'provider' => 3,
			'time_req' => time(),
	    );  
	    
		scanSoft::getSoftModel()->insert($scan_result);
	   
		$response_url = "http://www.anzhi.com/scan_soft_response.php?sha1=".$params['soft_hash']."&checkCompany=3";
		$get_str = array(
			'url' => $params['download_url'],
			'key' => '20001008',
			'lang' => 'cn',
			'style' => 'xml',
			'back' =>  $params['download_url'],
			'callback' => $response_url, 
 		);
		file_put_contents('/tmp/testanquan.log',var_export($get_str,true),FILE_APPEND);
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
	static public function getResponseWQ(){
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
	/**
	 * 向金山发送安全扫描请求
	 * 
	 * @param array $params 参数
	 * 
	 */
	static public function requestGetJS($params){
	    permanentlog("js_statr.log",var_export($params,true)." | ".date("Y-m-d H:i:s"));
		$scan_result = array(
			'sfid' => $params['sfid_type'],
			'hash' => $params['soft_hash'],
			'provider' => 4,
			'time_req' => time(),
	    );
		 
	    
		scanSoft::getSoftModel()->insert($scan_result);
	   
		$response_url = "http://www.anzhi.com/scan_soft_response.php?checkCompany=4";
		$post_str = array(
			"cid"=>"001",//合作方标识
			"u"=>urlencode($params['download_url']),
			"m"=>$params['soft_hash'],//包的md5值
			"t"=>"apk",
			"p"=>9,//扫描的优先级
			"um"=>md5($params['download_url']),//url的md5值
			"ver"=>1,//版本号
		);
		
		$request_url = "http://safescan.bd.m.ijinshan.com/__safescan.html";
		$re = self::requestGet($request_url, $post_str);  
		
		if($re["http_code"]!=200){
			sleep(5);
			$re = self::requestGet($request_url, $post_str);  
			if($re["http_code"]!=200){
				permanentlog("js_scan_err.log",$post_str." | ".date("Y-m-d H:i:s"));
				return false;
			}
		}
		
		permanentlog("js_scan_request.log",json_encode($re)." | ".date("Y-m-d H:i:s"));
		
		return $re;
	}
	/**
	 * 获取金山返回的扫描结果
	 * 
	 */
	static public function getResponseJS(){
		$re['cid']=$_GET['cid'];
		$re['m']=$_GET['m'];
		$re['t']=$_GET['t'];
		$re['um']=$_GET['um'];
		$re['c']=$_GET['c'];
		$re['ver']=$_GET['ver'];
	//	$re['cm']=$_GET['cm'];
		if(empty($re)) return false;
		permanentlog("js_scan_response.log", "narray is:".json_encode($re)." | ".date("Y-m-d H:i:s"));
		return $re;
	}
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
	static public function readdir(){
		print_r();
		
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

?>