<?php

error_reporting(E_ALL);

$a = new baidu();
$arr1 = $a->getList("哇哈哈",5);
$arr2 = $a->getSug("哈哈");
$arr3 = $a->getId(301627);
print '<pre>';
print_r($arr1);
print_r($arr2);
class baidu {

	private $url = '';				//发起请求的url
	private $from = '1000510b';		//合作方标记,百度提供
	private $token = 'anzhi';		//百度分配的唯一token
	public $type = 'app';			//type=app,对于android,type值为app
	public $index = 4;				//未知

	public $word;					//关键词
	public $rn = 10;				//每页显示记录数
	public $apilevel;				//android版本
	public $dpi;					//屏幕宽度,值如:320_480
	public $page = 1;				//页码
	public $class;					//检索资源类型,g只检索游戏,s只检索软件,默认为混合检索
	public $docid = 0;				//内容页docid
	
	private $pn = 0;				//偏移值,$pn=($page-1)*$rn;

	function __construct() {
		
	}

	function getList($word,$page) {	//获取检索列表
		//获取检索服务地址
		$url = "http://m.baidu.com/api?from={$this->from}&token={$this->token}&type={$this->type}&index=4";
		$ret = $this->req($url);
		if($ret['statuscode'] != 0) {
			return $ret;
		}
		$doc = (array)new SimpleXMLElement($ret['ret'],LIBXML_NOCDATA);
		$doc = $this->Obj_Arr($doc);
		if($doc['statuscode'] != 0) {
			return array(
				'ret' => $ret['ret'],
				'statuscode' => $doc['statuscode'],
				'statusmessage' => $doc['statusmessage'],
				'url' => $url,
			);
		}

		//检索服务地址 $doc['url'],开始检索结果列表
		if($word) $this->word = $word;
		if($page) $this->page = $page;
		$this->pn = ($this->page - 1) * $this->rn;
		$url = $doc['url']."&from={$this->from}&token={$this->token}&type={$this->type}&word={$this->word}&rn={$this->rn}&pn={$this->pn}";
		if($this->apilevel) $url .= "&apilevel={$this->apilevel}";
		if($this->dpi) $url .= "&dpi={$this->dpi}";

		$ret = $this->req($url);
		if($ret['statuscode'] != 0) {
			return $ret;
		}
		$doc = new SimpleXMLElement($ret['ret'],LIBXML_NOCDATA);
		$doc = $this->Obj_Arr($doc);
		if($doc['statuscode'] != 0) {
			return array(
				'ret' => $ret['ret'],
				'statuscode' => $doc['statuscode'],
				'statusmessage' => $doc['statusmessage'],
				'url' => $url,
			);
		}
		
		return $doc;
	}

	function getSug($word) {		//获取检索建议
		$url = "http://m.baidu.com/api?from={$this->from}&token={$this->token}&type={$this->type}&index=10";
		$ret = $this->req($url);
		if($ret['statuscode'] != 0) {
			return $ret;
		}
		$doc = (array)new SimpleXMLElement($ret['ret'],LIBXML_NOCDATA);	//解析xml,获得baidu sug地址
		$doc = $this->Obj_Arr($doc);	//$doc['url']为baidu sug地址
		if($doc['statuscode'] != 0) {
			return array(
				'ret' => $ret['ret'],
				'statuscode' => $doc['statuscode'],
				'statusmessage' => $doc['statusmessage'],
				'url' => $url,
			);
		}

		//$doc['url']为baidu sug地址,开始获取sug内容
		if($word) $this->word = $word;
		$url = $doc['url']."&from={$this->from}&token={$this->token}&type={$this->type}&word={$this->word}";
		$ret = $this->req($url);
		if($ret['statuscode'] != 0) {
			return $ret;
		}
		$doc = (array)new SimpleXMLElement($ret['ret'],LIBXML_NOCDATA);
		$doc = $this->Obj_Arr($doc);	//对从百度获取的xml内容解析
		if($doc['statuscode'] != 0) {
			return array(
				'ret' => $ret['ret'],
				'statuscode' => $doc['statuscode'],
				'statusmessage' => $doc['statusmessage'],
				'url' => $url,
			);
		}

		return $doc;
	}

	function getId($docid) {	//根据docid获取内容页
		if($docid) $this->docid = $docid;
		if(!$this->docid) {
			return array(
				'ret' => '',
				'statuscode' => -1,
				'statusmessage' => '请提供docid',
			);
		}

		$url = "http://m.baidu.com/api?from={$this->from}&token={$this->token}&type={$this->type}&docid={$this->docid}&action=search";
		$ret = $this->req($url);
		if($ret['statuscode'] != 0) {
			return $ret;
		}

		$doc = (array)new SimpleXMLElement($ret['ret'],LIBXML_NOCDATA);
		$doc = $this->Obj_Arr($doc);	//对内容页xml解析
		if($doc['statuscode'] != 0) {
			return array(
				'ret' => $ret['ret'],
				'statuscode' => $doc['statuscode'],
				'statusmessage' => $doc['statusmessage'],
				'url' => $url,
			);
		}

		return $doc;
	}

	//url发起请求
	protected function req($url, $timeout = 5) {
		if(function_exists('curl_init')) {
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			$ret = curl_exec($ch);
			$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			$errno = curl_errno($ch);
			$error = curl_error($ch);
			curl_close($ch);

			$arr = array(
				'ret' => $ret,
				'statuscode' => $errno,
				'statusmessage' => $error,
				'http' => $http_code,
				'url' => $url
			);
		} else {
			$opts = array(
				'http' => array(
					'method' => 'GET',
					'timeout'  => $timeout
				)
			);
			$context = stream_context_create($opts);
			$ret = file_get_contents($url, false, $context);
			
			$arr = array(
				'ret' => $ret,
				'statuscode' => ($ret===FALSE ? -1 : 0),	//0:成功
				'statusmessage' => 'file_get_content failure',
				'http' => $http_response_header,
				'url' => $url
			);
		}

		return $arr;
	}

	//对象转数组
	protected function Obj_Arr($obj) {
		$arr = array();
		$_arr = is_object($obj) ? get_object_vars($obj) : $obj;
		if($_arr) {
			foreach($_arr as $key => $val) {
				$val = (is_array($val) || is_object($val)) ? $this->Obj_Arr($val) : $val;
				$arr[$key] = $val;
			}
		}

		return $arr;
	}
}
