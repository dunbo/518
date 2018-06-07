<?php
class GoMemcachedCacheAdapter
{
	protected $memcached;
	protected $readServerList = array();
	protected $writeServerList = array();
	protected static $serverList = array();
	protected $max_retry = 10;
	protected $connect_timeout = 200;
	protected $localReadServer = null;
	protected $localWriteServer = null;
	protected $code;
	protected $debug = false;
	protected static $ip;
	protected static $global_config;
	protected static $cron_server;
	
	public function __construct($config = '')
	{
		if (empty(self::$ip)) {
			self::$ip = getServerIp();
			self::$global_config = load_config('cache/memcached');
			self::$cron_server = load_config('memcached/setting/cron_server');
		}

		if (empty($config)) $config = self::$global_config;
		$ip = self::$ip;
		$cron_server = self::$cron_server;
		//单个配置不处理
		if (isset($config['host'])) {
			if (isset($config['alias'])) $config['host'] = $config['alias'];
			$key = strtolower("{$config['host']}:{$config['port']}");
			if (!isset(self::$serverList[$key])) {
				$memcache = new Memcached();
				if (isset($config['serializer'])) {
					$serializer = $this->getSerializerByKey($config['serializer']);
					if ($serializer !== false) {
						$memcache->setOption(Memcached::OPT_SERIALIZER, $serializer);
					}
				}
				$memcache->setOption(Memcached::OPT_CONNECT_TIMEOUT, $this->connect_timeout);
				$memcache->addServer($config['host'], $config['port']);
				self::$serverList[$key] = array('memcache' => $memcache, 'config' => $config);
			}
			
	        if (!isset($config['read']) || $config['read'] === true) {
		        $this->readServerList[] = self::$serverList[$key]['memcache'];
	        }
	        
	        if (!isset($config['write']) || $config['write'] === true) {
		        $this->writeServerList[] = self::$serverList[$key]['memcache'];
	        }
		} elseif (is_array($config)) {
			//多个配置进行处理
			unset($config['type']);
			foreach ($config as $item) {
				$localRead = false;
				$localWrite = false;
				$read = false;
				$write = false;
				
				$host = isset($item['alias']) ? $item['alias'] : $item['host'];
				$key = strtolower("{$item['host']}:{$item['port']}");
				$read_able = (!isset($item['read']) || $item['read'] === true);
				$write_able = (!isset($item['write']) || $item['write'] === true);

				if ((isset($item['local']) && $item['local'] === true)) {
					if ($ip == $item['host']) {
						if ($read_able) $localRead = true;
						if ($write_able) $localWrite = true;
					}

					if ($cron_server == $ip) {
						$localWrite = false;
						if ($write_able) $write = true;
						if ($read_able) $read = true;
					}
				} else {
					if ($write_able) $write = true;
					if ($read_able) $read = true;					
				}

				if ($localWrite || $localRead || $read || $write) {
					if (!isset(self::$serverList[$key])) {
						$memcache = new Memcached();
						if (isset($item['serializer'])) {
							$serializer = $this->getSerializerByKey($item['serializer']);
							if ($serializer !== false) {
								$memcache->setOption(Memcached::OPT_SERIALIZER, $serializer);
							}
						}
						$memcache->setOption(Memcached::OPT_CONNECT_TIMEOUT, $this->connect_timeout);
						$memcache->addServer($host, $item['port']);
						self::$serverList[$key] = array('memcache' => $memcache, 'config' => $item);
					}
					
					if ($localWrite) $this->localWriteServer = self::$serverList[$key]['memcache'];
					if ($localRead) $this->localReadServer = self::$serverList[$key]['memcache'];
					
					if ($read) $this->readServerList[] = self::$serverList[$key]['memcache'];
					if ($write) $this->writeServerList[] = self::$serverList[$key]['memcache'];
				}
			}
		}
	}
	
	public function getSerializerByKey($key)
	{
		$serializer = false;
		switch ($key) {
			case 'php': 
				$serializer = Memcached::SERIALIZER_PHP;
			break;
			
			case 'json': 
				$serializer = Memcached::SERIALIZER_JSON ;
			break;
			
			case 'igbinary': 
				$serializer = Memcached::SERIALIZER_IGBINARY;
			break;
		}
		return $serializer;
	}
	
	public function set($key, $value, $expired = 0)
	{
		if(empty($key)){
//			file_put_contents('/tmp/debug.log', go_trace(), FILE_APPEND);
			return false;
		}
		$day = date('Y-m-d', time()) ;
		if ($this->debug) {
			$msg = date('Y-m-d H:i:s');
			$msg .= " {$key} {$_POST['KEY']}\n";
			file_put_contents("/tmp/memcache_cmd_{$day}.log", $msg, FILE_APPEND);
		}

		$start = microtime_float();
		if (!empty($this->localWriteServer)) {
			$r = $this->localWriteServer->set($key, $value, $expired);
			$this->code = $this->localWriteServer->getResultCode();
			if ($this->code != Memcached::RES_SUCCESS) {
				$msg = date('Y-m-d H:i:s');
				$msg .= ": set {$key} fail:". $this->localWriteServer->getResultMessage() . "\n";
				file_put_contents("/tmp/cache-error-{$day}.log", $msg. "\n", FILE_APPEND);
			}
			$end = microtime_float();
			$spend = $end - $start;
			if ($spend > 0.5){
				$msg = date('Y-m-d H:i:s');
				$sid = session_id();
				$msg .= ": {$sid} set {$key} spend {$spend}\n";
				file_put_contents("/tmp/cache_set_slow_{$day}.log", $msg, FILE_APPEND);
			}
		} else {
			foreach ($this->writeServerList as $server) {
				$r = $server->set($key, $value, $expired);
				$this->code = $server->getResultCode();
				if ($this->code != Memcached::RES_SUCCESS) {
					$msg = date('Y-m-d H:i:s');
					$msg .= ": set {$key} fail:". $server->getResultMessage() . "\n";
					file_put_contents("/tmp/cache-error-{$day}.log", $msg. "\n", FILE_APPEND);
				}
			}
		}
		return $r;
	}
	
	public function increment($key, $offset = 1)
	{
		$day = date('Y-m-d', time()) ;
		$start = microtime_float();
		if (!empty($this->localWriteServer)) {
			$r = $this->localWriteServer->increment($key, $offset);
			$this->code = $this->localWriteServer->getResultCode();
			if ($this->code != Memcached::RES_SUCCESS) {
				$msg = date('Y-m-d H:i:s');
				$msg .= ": increment {$key} fail:". $this->localWriteServer->getResultMessage() . "\n";
				file_put_contents("/tmp/cache-error-{$day}.log", $msg. "\n", FILE_APPEND);
			}
			$end = microtime_float();
			$spend = $end - $start;
			if ($spend > 0.5){
				$msg = date('Y-m-d H:i:s');
				$sid = session_id();
				$msg .= ": {$sid} increment {$key} spend {$spend}\n";
				file_put_contents("/tmp/cache_set_slow_{$day}.log", $msg, FILE_APPEND);
			}
		} else {
			foreach ($this->writeServerList as $server) {
				$r = $server->increment($key, $offset);
				$this->code = $server->getResultCode();
				if ($this->code != Memcached::RES_SUCCESS) {
					$msg = date('Y-m-d H:i:s');
					$msg .= ": increment {$key} fail:". $server->getResultMessage() . "\n";
					file_put_contents("/tmp/cache-error-{$day}.log", $msg. "\n", FILE_APPEND);
				}
			}
		}
		return $r;
	}

    //批量获取 guokai add 2011-07-26
    public function gets($key_arr)
    {
		//$var_key = "GO_CACHE_memcached_" . md5(json_encode($key_arr)) ;
		//if (isset($GLOBALS[$var_key])) return $GLOBALS[$var_key];
		if ($this->debug) {
			$day = date('Y-m-d');
			$msg = date('Y-m-d H:i:s');
			$key = implode(',', $key_arr);
			$msg .= " {$key} {$_POST['KEY']}\n";
			file_put_contents("/tmp/memcache_cmd_{$day}.log", $msg, FILE_APPEND);
		}
		if($key_arr == '') return false;
		$retry = 0;
		$skip_local = false;
		$start = microtime_float();
		while ($retry < $this->max_retry) {
			if ($skip_local === false && !empty($this->localReadServer)) {
				$memcache = $this->localReadServer;
			} else {
				$index = $this->getReadServer();
				if ($index === false || empty($this->readServerList[$index])) {
					return false;
				} else {
					$memcache = $this->readServerList[$index];
				}
			}
			
			$result = $memcache->getMulti($key_arr);
			$this->code = $memcache->getResultCode();
			switch ($this->code) {
				case Memcached::RES_SUCCESS:
					$end = microtime_float();
					$spend = $end - $start;
					if ($spend > 0.5){
						$msg = date('Y-m-d H:i:s');
						$day = date('Y-m-d');
						$keys = implode(',', $key_arr);
                        $sid = session_id();
						$msg .= ": {$sid} get {$keys} retry {$retry} times spend $spend\n";
						file_put_contents("/tmp/cache_slow_{$day}.log", $msg, FILE_APPEND);
					}
					//$GLOBALS[$var_key] = $result;
					return $result;
				break;
				
				case Memcached::RES_TIMEOUT:
				case Memcached::RES_ERRNO:
					$skip_local = true;
					$day = date('Y-m-d');
					$keys = implode(',', $key_arr);
					$sid = session_id();
					$msg = date('Y-m-d H:i:s');
					$msg .= ": {$sid} get {$keys} fail: {$code} ". $memcache->getResultMessage() . " retry {$retry} times\n";
					file_put_contents("/tmp/cache-error-{$day}.log", $msg, FILE_APPEND);
					//$this->removeServer($index);
				break;
				
				case Memcached::RES_NOTFOUND:
					$msg = date('Y-m-d H:i:s');
					$keys = implode(',', $key_arr);
					$msg .= ": {$keys} not found\n";
					//file_put_contents('/tmp/cache-404.log', $msg, FILE_APPEND);
					if (!empty($callback)) {
						$res = call_user_func_array($callback, $param_arr);
						$this->set($key, $res, $expired);
						return $res;
					}
					//return false;
					$skip_local = false;
				break;
			}
			$retry++;
		}

		return false;
    }

    //批量set guokai add 2011-07-26
    public function sets($key_value_arr, $expired = 0)
    {
		if(!$key_value_arr) return False;
		if ($this->debug) {
			$day = date('Y-m-d');
			$msg = date('Y-m-d H:i:s');
			$key = implode(',', $key_value_arr);
			$msg .= " {$key} {$_POST['KEY']}\n";
			file_put_contents("/tmp/memcache_cmd_{$day}.log", $msg, FILE_APPEND);
		}
		if (!empty($this->localWriteServer)) {
			$r = $this->localWriteServer->setMulti($key_value_arr, $expired);
			$this->code = $this->localWriteServer->getResultCode();
		} else {
			foreach ($this->writeServerList as $server) {
				$r = $server->setMulti($key_value_arr, $expired);
				$this->code = $server->getResultCode();
			}
		}		
	    return $r;
    }

	public function get($key, $callback = array(), $param_arr = array(), $expired = 0)
	{
		//$var_key = "GO_CACHE_memcached_{$key}" ;
		//if (isset($GLOBALS[$var_key])) return $GLOBALS[$var_key];
	
		if($key == '') return false;
		if ($this->debug) {
			$day = date('Y-m-d');
			$msg = date('Y-m-d H:i:s');
			$msg .= " {$key} {$_POST['KEY']}\n";
			file_put_contents("/tmp/memcache_cmd_{$day}.log", $msg, FILE_APPEND);
		}		
		$retry = 0;
		$skip_local = false;
		$start = microtime_float();
		while ($retry < $this->max_retry) {
			if ($skip_local === false && !empty($this->localReadServer)) {
				$memcache = $this->localReadServer;
			} else {
				$index = $this->getReadServer();
				if ($index === false || empty($this->readServerList[$index])) {
					return false;
				} else {
					$memcache = $this->readServerList[$index];
				}
			}
			$result = $memcache->get($key);
			$this->code = $memcache->getResultCode();
			switch ($this->code) {
				case Memcached::RES_SUCCESS:
					$end = microtime_float();
					$spend = $end - $start;
					if ($spend > 0.5){
						$msg = date('Y-m-d H:i:s');
						$day = date('Y-m-d');
                        $sid = session_id();
						$msg .= ": {$sid} get {$key} retry {$retry} times spend $spend\n";
						file_put_contents("/tmp/cache_slow_{$day}.log", $msg, FILE_APPEND);
					}
					//$GLOBALS[$var_key] = $result;
					return $result;
				break;
				
				case Memcached::RES_TIMEOUT:
				case Memcached::RES_ERRNO:
					$skip_local = true;
					//$this->removeServer($index);
				break;
				
				case Memcached::RES_NOTFOUND:
					$msg = date('Y-m-d H:i:s');
					$msg .= ": {$key} not found\n";
					//file_put_contents('/tmp/cache-404.log', $msg, FILE_APPEND);
					if (!empty($callback)) {
						$res = call_user_func_array($callback, $param_arr);
						$this->set($key, $res, $expired);
						return $res;
					}
					return false;
				break;
				
			}
			$retry++;
		}
		return false;
	}
	
	public function delete($key, $time = 0)
	{
		if (!empty($this->localWriteServer)) {
			$r = $this->localWriteServer->delete($key, $time);
			$this->code = $this->localWriteServer->getResultCode();
		} else {
			foreach ($this->writeServerList as $server) {
				$r = $server->delete($key, $time);
				$this->code = $server->getResultCode();
			}
		}
		
		return $r;
	}
	
	public function getReadServer()
	{
		if (count($this->readServerList) > 1) {
			list($msec, $sec) = explode(" ", microtime());
			$msec = substr($msec, 2, -2);
			$index = $msec % (count($this->readServerList));
			return $index;
		} elseif (count($this->readServerList) == 1) {
			return 0;
		} else {
			return false;
		}
	}
	
	
	public function removeReadServer($index)
	{
		unset($this->readServerList[$index]);
		sort($this->readServerList);
	}
	
	public function getCode()
	{
		return $this->code;
	}

	public function decrement($key, $offset = 1)
	{
		$day = date('Y-m-d', time()) ;
		$start = microtime_float();
		if (!empty($this->localWriteServer)) {
			$r = $this->localWriteServer->decrement($key, $offset);
			$this->code = $this->localWriteServer->getResultCode();
			if ($this->code != Memcached::RES_SUCCESS) {
				$msg = date('Y-m-d H:i:s');
				$msg .= ": decrement {$key} fail:". $this->localWriteServer->getResultMessage() . "\n";
				file_put_contents("/tmp/cache-error-{$day}.log", $msg. "\n", FILE_APPEND);
			}
			$end = microtime_float();
			$spend = $end - $start;
			if ($spend > 0.5){
				$msg = date('Y-m-d H:i:s');
				$sid = session_id();
				$msg .= ": {$sid} decrement {$key} spend {$spend}\n";
				file_put_contents("/tmp/cache_set_slow_{$day}.log", $msg, FILE_APPEND);
			}
		} else {
			foreach ($this->writeServerList as $server) {
				$r = $server->decrement($key, $offset);
				$this->code = $server->getResultCode();
				if ($this->code != Memcached::RES_SUCCESS) {
					$msg = date('Y-m-d H:i:s');
					$msg .= ": decrement {$key} fail:". $server->getResultMessage() . "\n";
					file_put_contents("/tmp/cache-error-{$day}.log", $msg. "\n", FILE_APPEND);
				}
			}
		}
		return $r;
	}
}
