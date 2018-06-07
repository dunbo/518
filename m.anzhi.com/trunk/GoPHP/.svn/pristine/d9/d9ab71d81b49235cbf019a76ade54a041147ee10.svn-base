<?php
class GoRedisCacheAdapter 
{
    protected $readServerList = array();
    protected $writeServerList = array();
    protected static $serverList = array();
    protected $max_retry = 1;
    protected $connect_timeout = 3; //单位是秒，类型是浮点型
    protected $localReadServer = null;
    protected $localWriteServer = null;
    protected $current_server = null;
    protected static $ip;
    protected static $global_config;
    protected static $cron_server;
    
    public function __construct($config = '')
    {
        if (empty(self::$ip)) {
            self::$ip = getServerIp();
            self::$global_config = load_config('cache/redis');
            self::$cron_server = load_config('redis/setting/cron_server');
        }
        
        if (empty($config)) $config = self::$global_config;
        $ip = self::$ip;
        
        $cron_server = self::$cron_server;
        //单个配置不处理
        if (isset($config['host'])) {
            if (isset($config['alias'])) $config['host'] = $config['alias'];
            $key = strtolower("{$config['host']}:{$config['port']}");
            
            if (!isset($config['read']) || $config['read'] === true) {
                if (!isset(self::$serverList[$key])) {
                    self::$serverList[$key] = array('redis' => null, 'connected' => false, 'config' => $config);
                }
                $this->readServerList[] = $key;
            }
            
            if (!isset($config['write']) || $config['write'] === true) {
                if (!isset(self::$serverList[$key])) {
                    self::$serverList[$key] = array('redis' => null, 'connected' => false, 'config' => $config);
                }
                $this->writeServerList[] = $key;
            }
        } elseif (is_array($config)) {
            //多个配置进行处理
            unset($config['type']);
            foreach ($config as $item) {
                $local_host = $item['host'];
                if (isset($item['alias'])) $item['host'] = $item['alias'];
                $key = strtolower("{$item['host']}:{$item['port']}");
                
                if (isset($item['local']) && $item['local'] === true) {
                    if ($ip == $local_host) {
						//$cron_server不设置localWriteServer
                        if (($cron_server != $ip) && (!isset($item['write']) || $item['write'] === true)) {
                            if (!isset(self::$serverList[$key])) {
                                self::$serverList[$key] = array('redis' => null, 'connected' => false, 'config' => $item);
                            }
                            $this->localWriteServer = $key;
                        }

                        if (!isset($item['read']) || $item['read'] === true) {
                            if (!isset(self::$serverList[$key])) {
                                self::$serverList[$key] = array('redis' => null, 'connected' => false, 'config' => $item);
                            }
                            $this->localReadServer = $key;
                        }
                    }
					if (($cron_server != $ip)) {
						continue;
					}
                }
                
                if (!isset($item['write']) || $item['write'] === true) {
                    if (!isset(self::$serverList[$key])) {
                        self::$serverList[$key] = array('redis' => null, 'connected' => false, 'config' => $item);
                    }
                    $this->writeServerList[] = $key;
                }
                
                if (!isset($item['read']) || $item['read'] === true) {
                    if (!isset(self::$serverList[$key])) {
                        self::$serverList[$key] = array('redis' => null, 'connected' => false, 'config' => $item);
                    }
                    $this->readServerList[] = $key;
                }
        
            }
        }
    }
    //string 类型操作
    public function set($key, $value, $expired = 0)
    {
        $value = json_encode($value);
        if ($expired > 0) {
            $r = $this->setx('setex', $key, $expired, $value);
        } else {
            $r = $this->setx('set', $key, $value);
        }
        return $r;
    }
    //string 类型操作
    public function get($key, $callback = array(), $param_arr = array(), $expired = 0)
    {
        if($key == '') return false;
        $result = $this->getx('get', $key);
        if (!$result && !empty($callback)) {
            $result = call_user_func_array($callback, $param_arr);
            $this->set($key, $result, $expired);
        } else {
            $result = json_decode($result, true);
        }
        return $result;
    }
    
    public function sets_single($redis, $value, $expired = 0)
    {
        if (!$redis) return false;

        if($expired > 0) {
            $redis->multi(Redis::PIPELINE);     
            $r = $redis->mset($value);
            foreach($value as $k=>$v){
                $redis->setTimeout($k, $expired);
            }
            $redis->exec();
        } else {
            $r = $redis->mset($value);
        }
        return $r;
    }

    public function sets($key_value_arr, $expired = 0)
    {
        $value = array();
        foreach($key_value_arr as $k=>$v){
            $value[$k] = json_encode($v);
        }

        if (!empty($this->localWriteServer)) {
            $redis = $this->getServerByKey($this->localWriteServer);
            $r = $this->sets_single($redis, $value, $expired);
        } else {
            foreach ($this->writeServerList as $k) {
                $redis = $this->getServerByKey($k);
                $r = $this->sets_single($redis, $value, $expired);
            }
        }
        return $r;
    }
    //string 类型操作
    public function gets($key, $callback = array(), $param_arr = array(), $expired = 0)
    {
        if($key == '') return false;
        $redis_result = $this->getx('mget', $key);
        if (!$redis_result && !empty($callback)) {
            $result = call_user_func_array($callback, $param_arr);
            $this->sets($result, $expired);
        } else {
            foreach($redis_result as $k=>$v){
                if ($v === false) continue;
                $result[$key[$k]] = json_decode($v, true);
            }
        }
        return $result;
    }
	//string 类型操作
    public function setnx($key, $value, $expired = 0)
    {
        $value = json_encode($value);
		$r = $this->setx('setnx', $key, $value);
		if ($expired > 0) {
			$this -> expire($key,$expired);
		}
        return $r;
    }

    /**
     * [redis hash设置操作函数，$hashname对应的缓存会被覆盖]
     * @param  [type]  $hashname  [缓存hashname]
     * @param  [type]  $value     [hash缓存的fieldkey到filedvalue的映射数组]
     * @param  integer $expired   [过期时间]
     * @param  [type]  $empty_del [$value为空时，是否删除$hashname 对应的缓存]
     * @return [type]             []
     */
    public function sethash_overwrite($hashname, $value, $expired = 0, $empty_del = false)
    {
        return $this->sethash($hashname, $value, $expired, $empty_del, true);
    }

    /**
     * [redis hash设置操作函数，内部函数不对外调用]
     * @param  [type]  $redis      [redis对象]
     * @param  [type]  $hashname   [缓存hashname]
     * @param  [type]  $value      [hash缓存的fieldkey到filedvalue的映射数组]
     * @param  integer $expired    [过期时间]
     * @param  boolean $empty_del  [$value为空时，是否删除$hashname 对应的缓存]
     * @param  boolean $over_write [是否覆盖$hashname对应的缓存]
     * @return [type]              [description]
     */
    protected function sethash_single($redis, $hashname, $value, $expired = 0, $empty_del = false, $over_write = false)
    {
        if (!$redis) return false;
        try {
            $redis->multi(Redis::PIPELINE);
            //设置空值时，是否删除$hashname对应的缓存
            if ($empty_del && empty($value)) {
                $redis->delete($hashname);
            }

            //是否进行覆盖
            if ($over_write) {
                $hashname_tmp = $hashname. ':gotmp';
                $redis->hmset($hashname_tmp, $value);
                $redis->rename($hashname_tmp, $hashname);
            } else {
                $redis->hmset($hashname, $value);
            }

            ($expired > 0) && $r = $redis->setTimeout($hashname, $expired);
            $r = $redis->exec();
        } catch (RedisException $e) {
            $msg = date('Y-m-d H:i:s');
            $day = date('Y-m-d');
            $msg .= ": set {$hashname} throw an execption {$this->current_server['host']}:{$this->current_server['port']}\n";
            file_put_contents("/tmp/redis_exception_{$day}.log", $msg, FILE_APPEND);
        }
        return $r;
    }

    /**
     * [redis hash设置操作函数]
     * @param  [type]  $hashname   [缓存hashname]
     * @param  [type]  $value      [hash缓存的fieldkey到filedvalue的映射数组]
     * @param  integer $expired    [过期时间]
     * @param  boolean $empty_del  [$value为空时，是否删除$hashname 对应的缓存]
     * @param  boolean $over_write [是否覆盖$hashname对应的缓存]
     * @return [type]              [description]
     */
    public function sethash($hashname, $value, $expired = 0, $empty_del = false, $over_write = false)
    {
        $new_value = array();
        foreach($value as $k=>$v){
            $new_value[$k] = json_encode($v);
        }
        if (!empty($this->localWriteServer)) {
            $redis = $this->getServerByKey($this->localWriteServer);
            $r = $this->sethash_single($redis, $hashname, $new_value, $expired, $empty_del, $over_write);
        } else {
            foreach ($this->writeServerList as $k) {
                $redis = $this->getServerByKey($k);
                $r = $this->sethash_single($redis, $hashname, $new_value, $expired, $empty_del, $over_write);
            }
        }
        return $r;
    }
    //hash 类型操作
    public function gethash($hashname, $key = '')
    {
        if($hashname == '') return false;
        if (empty($key)) {
			//$day = date('Y-m-d');
			//file_put_contents("/tmp/redis_hash_{$day}.log", $hashname."\n".go_trace()."\n", FILE_APPEND);
            //return false;
        }
        $func = '';
        if (is_array($key)) {
            $func = 'hmGet';
        } elseif (empty($key)) {
            $func = 'hGetAll';
        } else {
            $func = 'hGet';
        }
        if ($func == 'hGetAll') {
            $res = $this->getx($func, $hashname);
        } else {
            $res = $this->getx($func, $hashname, $key);
        }
        if ($res !== false) {
            if ($func == 'hGetAll' || $func == 'hmGet') {
                $result = array();
                foreach($res as $k => $v){
                    if ($v === false) continue;
                    $result[$k] = json_decode($v, true);
                }
            } else {
                $result = json_decode($res, true);
            }
            return $result;
        }
        return false;
    }

    public function setlist_single($redis, $key, $new_value, $expired = 0)
    {
        if (!$redis) return false;
        $r = false;
        $has_push = false;
        $pice = 1000;
        $i=0;
        foreach($new_value as $val){
            if (!$has_push) {
                $redis->multi(Redis::PIPELINE);
                $has_push = true;
            }
            $redis->rpush($key, $val);
            $i++;
            if ($i % $pice == 0){
                $r = $redis->exec();
                $has_push = false;
            }
        }
        if ($has_push){
            $r = $redis->exec();
        }
		($expired > 0) && $r = $redis->setTimeout($key, $expired);
        return $r;
    }

    //list 类型操作
    public function setlist($key, $value, $expired = 0)
    {
        $new_value = array();
         
        foreach($value as $k=>$v){
            $new_value[$k] = json_encode($v);
        }
        if (!empty($this->localWriteServer)) {
            $redis = $this->getServerByKey($this->localWriteServer);
            $r = $this->setlist_single($redis, $key, $new_value, $expired);
        } else {
            foreach ($this->writeServerList as $k) {
                $redis = $this->getServerByKey($k);
                $r = $this->setlist_single($redis, $key, $new_value, $expired);
            }
        }
        return $r;
    }
    
    public function setlist_sec($key, $value, $expired = 0)
    {
        $r = false;
        if ($value) {
			$new_value = array();
            foreach($value as $k=>$v){
                $new_value[$k] = addcslashes(json_encode($v), "'");
            }
            $params = implode("','", $new_value);
            unset($new_value);
            unset($value);
            $cmd = "\$redis->rpush('{$key}', '{$params}');";
            unset($params);
            if (!empty($this->localWriteServer)) {
                $redis = $this->getServerByKey($this->localWriteServer);
                if (!$redis) return false;
                $r = eval($cmd);
                ($expired > 0) && $redis->setTimeout($key, $expired);
            } else {
                foreach ($this->writeServerList as $k) {
                    $redis = $this->getServerByKey($k);
                    if (!$redis) continue;
                    $r = eval($cmd);
                    ($expired > 0) && $redis->setTimeout($key, $expired);		
                }
            }
        }
        return $r;
    }

    //list 类型操作
    public function getlist($key, $start = 0, $end = -1)
    {
        if($key == '') return false;
        return $this->getx('lRange', $key, $start, $end);
    }
    public function rpop($key)
    {
        if($key == '') return false;
        return $this->setx('rpop', $key);
    }

    public function setx_single($redis, $set_func, $set_params)
    {
        if (!$redis) return false;
        
        try {
            $r = call_user_func_array(array($redis, $set_func), $set_params);
        } catch (RedisException $e) {
            $msg = date('Y-m-d H:i:s');
            $day = date('Y-m-d');
            $msg .= ": setx {$set_params[0]} throw an execption {$this->current_server['host']}:{$this->current_server['port']}\n";
            file_put_contents("/tmp/redis_exception_{$day}.log", $msg, FILE_APPEND);
        }
        return $r;
    }

    //通用类型操作
    public function setx()
    {
        $args = func_get_args();
        if (empty($args)) return false;
        $set_func = $args[0];
        $set_params = array_slice($args, 1);
        if (!empty($this->localWriteServer)) {
            $redis = $this->getServerByKey($this->localWriteServer);
            $r = $this->setx_single($redis, $set_func, $set_params);
        } else {
            foreach ($this->writeServerList as $k) {
                $redis = $this->getServerByKey($k);
                $r = $this->setx_single($redis, $set_func, $set_params);
            }
        }
        return $r;
    }
    //通用类型操作
    public function getx()
    {
        $args = func_get_args();
        if (empty($args)) return false;
        $get_func = $args[0];
        $get_params = array_slice($args, 1);
        
        $retry = 0;
        $skip_local = false;
        $start = microtime_float();
        while ($retry < $this->max_retry) {
            if ($skip_local === false && !empty($this->localReadServer)) {
                $redis = $this->getServerByKey($this->localReadServer);
            } else {
                $index = $this->getReadServer();
                if ($index === false || empty($this->readServerList[$index])) {
                    return false;
                } else {
                    $redis = $this->getServerByKey($this->readServerList[$index]);//modify by sunwenyang 
                }
            }
            $retry++;
            if (!$redis) continue;
			try {
				$result = call_user_func_array(array($redis, $get_func), $get_params);
			} catch (RedisException $e) {
				$msg = date('Y-m-d H:i:s');
				$day = date('Y-m-d');
				$k = $get_params[0];
				$msg .= ": getx {$k} throw an execption {$this->current_server['host']}:{$this->current_server['port']}\n";
				file_put_contents("/tmp/redis_exception_{$day}.log", $msg, FILE_APPEND);
				continue;
			}
            if ($result !== false) {
                $end = microtime_float();
                $spend = $end - $start;
                if ($spend > 0.5){
                    $msg = date('Y-m-d H:i:s');
                    $day = date('Y-m-d');
                    $sid = session_id();
                    $k = $get_params[0];
                    $msg .= ": {$sid} {$k} spend {$spend}\n";
                    file_put_contents("/tmp/redis_slow_{$day}.log", $msg, FILE_APPEND);
                }
                return $result;
            } else {
                $skip_local = true;
            }
        }
        return false;
    }
    
    
    public function delete($key, $time = 0)
    {   
        return $this->setx('delete', $key);
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
    
    public function getServerByKey($key)
    {
        return $this->checkConnect(self::$serverList[$key]);
    }
    
    //获取redis中设置生存周期中key的剩余周期时间，其中如果没有设置或者已经过期的为-1.
    public function getKeyTTL($key)
    {
        return $this->getx('ttl', $key);
    }
    //删除队列中的数据项
    public function lrem($key,$count,$val)
    {
        return $this->setx('lRem',$key,$count,$val);
        
    }
    //往set中增加数据元素
    public function  saddSet($key,$val)
    {
        return $this->setx("sAdd",$key,$val);
    }
    //查找set中是否有元素
    public function sIsMemberSet($key,$val)
    {
        return $this->getx("sIsMember",$key,$val);
    }
    //  比较key1和key2的不同，并将不同部分存在dest_key
    public function  sDiffStore($dest_key,$key1,$key2)
    {
        return $this->setx("sDiffStore",$dest_key,$key1,$key2);
    }
    public function expire($key,$cache_time)
    {
        return $this->setx("expire",$key,$cache_time);
    }
    public function expireAt($key,$expire)
    {
        return $this->setx("expireAt",$key,$expire);
    }
    public function  lSize($key)
    {
        return $this->getx("lSize",$key);
    }
    public function  lTrim($key,$start,$end)
    {
        return $this->setx("lTrim",$key,$start,$end);
    }
    public function lPush($key,$val,$expired =0)
    {
        $r = $this->setx("lPush",$key,$val);
		if ($expired > 0) {
			$this -> expire($key,$expired);
		}
		return $r;
    }
    public function exists($key)
    {
        return $this->getx("exists",$key);
    }
    public function hget($hashname,$key)
    {
        if($key == '') return false;
        return $this->getx('hGet',$hashname,$key);
    }
	public function hset($hashname,$key,$value)
    {
        if($key == '') return false;
        return $this->setx('hSet',$hashname,$key,$value);
    }
    public function hmget($hashname,$keys)
    {
        if(empty($keys)||empty($hashname)) return false;
        return $this->getx('hMGet',$hashname,$keys);
    }
    public function hmset($hashname,$key_value_paris)
    {
        if(empty($key_value_paris)||empty($hashname)) return false;
        return $this->setx('hMSet',$hashname,$key_value_paris);
    }
    public function hdel($hashname,$key)
    {
        if($key == '')return false;
        return $this->setx("hDel",$hashname,$key);
    }
    //连接
    public function connect($redis, $config)
    {
		$key = strtolower("{$config['host']}:{$config['port']}");
        try {
            if(empty($redis)) {
                $redis = new Redis();
            }
            $timeout = isset($config['timeout']) ? $config['timeout'] :$this->connect_timeout;
            if (empty($config['pconnect'])) {
                $res = $redis->connect($config['host'], $config['port'], $timeout);
            } else {
                $res = $redis->pconnect($config['host'], $config['port'], $timeout);
            }
            if (!$res) {
                self::$serverList[$key]['connected'] = false;
                $redis = false;
            }
        } catch (RedisException $e) {
			$msg = date('Y-m-d H:i:s');
			$day = date('Y-m-d');
			$msg .= ": connect {$config['host']}:{$config['port']} throw an execption\n";
			file_put_contents("/tmp/redis_exception_{$day}.log", $msg, FILE_APPEND);
					
            self::$serverList[$key]['connected'] = false;
            $redis = false;
        }
        return $redis;
    }
    
    public function checkConnect(& $config)
    {
        $redis = $config['redis'];
        if ($config['connected'] == false) {
            $start = microtime_float();
            
            $redis = $this->connect($redis, $config['config']);
            if (!$redis) {
                return false;
            }
            
            $config['connected'] = true;
            $config['redis'] = $redis;
            
            $end = microtime_float();
            $spend = $end - $start;
            if ($spend > 0.5){
                $msg = date('Y-m-d H:i:s');
                $day = date('Y-m-d');
                $sid = session_id();
                $msg .= ": {$sid} connect to redis {$config['config']['host']}:{$config['config']['port']} spend {$spend}\n";
                file_put_contents("/tmp/redis_connect_{$day}.log", $msg, FILE_APPEND);
            }
        }
		$this->current_server = $config['config'];
        return $redis;
    }
    
    public function pingConn()
    {
        foreach (self::$serverList as $k => $server) {
            if ($server['connected'] == true) {
                try {
                    $server['redis']->ping();
                } catch (RedisException $e) {
					$msg = date('Y-m-d H:i:s');
					$day = date('Y-m-d');
					$msg .= ": ping throw an execption {$server['config']['host']}:{$server['config']['port']}\n";
					file_put_contents("/tmp/redis_exception_{$day}.log", $msg, FILE_APPEND);
                    self::$serverList[$k]['connected'] = false;
                }
            }
        }
    }
    
    public function close()
    {
        foreach (self::$serverList as $k => $server) {
            if ($server['connected'] == true) {
                try {
                    $server['redis']->close();
                } catch (RedisException $e) {
					$msg = date('Y-m-d H:i:s');
					$day = date('Y-m-d');
					$msg .= ": close throw an execption {$server['config']['host']}:{$server['config']['port']}\n";
					file_put_contents("/tmp/redis_exception_{$day}.log", $msg, FILE_APPEND);
                }
                self::$serverList[$k]['connected'] = false;
            }
        }
    }
	
	public function multi_single($redis)
    {
        if (!$redis) return false;
        $r = $redis->multi(Redis::PIPELINE);
        return $r;
    }

	public function multi()
    {
        if (!empty($this->localWriteServer)) {
            $redis = $this->getServerByKey($this->localWriteServer);
            $r = $this->multi_single($redis);
        } else {
            foreach ($this->writeServerList as $k) {
                $redis = $this->getServerByKey($k);
                $r = $this->multi_single($redis);
            }
        }
        return $r;
    }

	public function exec_single($redis)
    {
        if (!$redis) return false;
        $r = $redis->exec();
        return $r;
    }

	public function exec()
    {
        if (!empty($this->localWriteServer)) {
            $redis = $this->getServerByKey($this->localWriteServer);
            $r = $this->exec_single($redis);
        } else {
            foreach ($this->writeServerList as $k) {
                $redis = $this->getServerByKey($k);
                $r = $this->exec_single($redis);
            }
        }
        return $r;
    }
}
