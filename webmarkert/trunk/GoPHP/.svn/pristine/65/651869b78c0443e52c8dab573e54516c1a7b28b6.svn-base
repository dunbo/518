<?php
define("SOAP_LOCATION",'http://searchapi.goapk.com/api/webserver/http.php');
//define("SOAP_LOCATION",'http://172.16.1.69/api/webserver/http.php');
define("SOAP_URI",'dict/api/webserver/http.php');  
class GoSearchSecModel extends GoModel
{
	protected $soap_client;
	protected $filter_redis_read;
	protected $filter_redis_write;
	function __construct()
	{
		$filter_redis_write_config = load_config('filter/redis_write');
		$filter_redis_read_config = load_config('filter/redis_read');
		
		if($filter_redis_write_config){
			$this->filter_redis_write = new GoRedisCacheAdapter($filter_redis_write_config);		
		} else {
			$this->filter_redis_write = GoCache::getCacheAdapter('redis');
		}
		if($filter_redis_read_config){
			$this->filter_redis_read = new GoRedisCacheAdapter($filter_redis_read_config);	
		} else {
			$this->filter_redis_read = GoCache::getCacheAdapter('redis');
		}
		$this->soap_client= new SoapClient(null, array('location' => SOAP_LOCATION, 'uri' => SOAP_URI));
	}
	function get_search_result($search_key) 
	{
		$cache_key = 'SPHINX:CACHE:'. md5(strtolower($search_key));
		$result = $this->filter_redis_read->get($cache_key);
		if (!$result) {
		
			try {
				$matches = json_decode($this->soap_client->search($search_key),true);
				$result = array();
				if ($matches) {
					$result = $this->sort_search($search_key, $matches);
					$this->filter_redis_read->set($cache_key, $result, 600);
				}
			} catch (Exception $e) {
				$date = date('Y-m-d');
				$time = date('Y-m-d H:i:s');
				$error = $e->getMessage();
				$msg = "{$time}\t{$error}\n";
				file_put_contents("/tmp/search_error_{$date}.log", $msg, FILE_APPEND);
				return array(
					'softid_arr' => array(),
					'person_contrl' => array(),
				);
			}
		}
		return $result; 
	}
	
	function sort_search($search_key, $matches)
	{
		$softid_arr = array();
		$person_contrl = array();
		$runindex_arr = array();
		$download_arr = array();
		$cache = GoCache::getCacheAdapter('memcached');
		$white_softid_pkg = $cache -> get('safe_white_pkgs');
		$strlen = mb_strlen($search_key,'utf-8');
		foreach ($matches['data'] as $softid => $match) {
			$d = $match['total_downloaded'];
			$safe = $match['safe'];
			
			//人工干预属性
			$runindex = $match['runindex'];
			$is_intervres = $match['is_intervres'];
			
			if( !isset($white_softid_pkg[$softid]) && $safe >= 2){
				continue;
			}
			
			$softid_arr[$softid] = $softid;
			
			//query在纯中文字符2个及以上时做处理
			if($strlen >= 2 && $is_intervres > 0){
				$n++;
				$person_contrl[$softid] = $softid;
				$runindex_arr[$softid] = $runindex;
				$download_arr[$softid] = $d;
			}
		}
		
		if($person_contrl && $n>1){
			array_multisort($runindex_arr, SORT_DESC,
				$download_arr, SORT_DESC,
				$person_contrl
			);
			$person_contrl = array_flip($person_contrl);
		}
		return array(
			'softid_arr' => $softid_arr,
			'person_contrl' => $person_contrl,
		);
	}
	
	function user_recommend_search($search_key)
	{
		$imei = $_SESSION['USER_IMEI'];
		$imsi = $_SESSION['USER_IMSI'];
		try {
			$res = $this->soap_client->personaluser(array('query'=>$search_key,'imei'=>$imei,'imsi'=>$imsi));
			return json_decode($res,true);	
		} catch (Exception $e){
			return false;
		}
	}
	
}