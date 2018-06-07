<?php
class GoCronRedisCacheAdapter	extends GoRedisCacheAdapter 
{
    protected $readServerList = array();
    protected $writeServerList = array();
    protected static $serverList = array();
    protected $max_retry = 1;
    protected $connect_timeout = 3; //单位是秒，类型是浮点型
    protected $localReadServer = null;
    protected $localWriteServer = null;
    protected static $ip;
    protected static $global_config;
    protected static $cron_server;
    
    public function __construct($config = '')
    {
	parent::__construct($config);	
    }
    
    public function sets_single($redis, $value, $expired = 0)
    {
        if (!$redis) return false;

        if($expired > 0) {
//            $redis->multi(Redis::PIPELINE);     
            $r = $redis->mset($value);
            foreach($value as $k=>$v){
                $redis->setTimeout($k, $expired);
            }
//            $redis->exec();
        } else {
            $r = $redis->mset($value);
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

    /**
     * 重写父类方法，去掉事务
     */
    protected function sethash_single($redis, $hashname, $value, $expired = 0, $empty_del = false, $over_write = false)
    {
        if (!$redis) return false;
        try {
//            $redis->multi(Redis::PIPELINE);
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
//            $r = $redis->exec();
        } catch (RedisException $e) {
            $msg = date('Y-m-d H:i:s');
            $day = date('Y-m-d');
            $msg .= ": set {$hashname} throw an execption\n";
            file_put_contents("/tmp/redis_exception_{$day}.log", $msg, FILE_APPEND);
        }
        return $r;
    }


    public function setlist_single($redis, $key, $new_value, $expired = 0)
    {
        if (!$redis) return false;
        $r = false;
        $has_push = false;
        ($expired > 0) && $r = $redis->setTimeout($key, $expired);
        $pice = 1000;
        $i=0;
        foreach($new_value as $val){
            if (!$has_push) {
//                $redis->multi(Redis::PIPELINE);
                $has_push = true;
            }
            $r = $redis->rpush($key, $val);
            $i++;
            if ($i % $pice == 0){
//                $r = $redis->exec();
                $has_push = false;
            }
        }
        if ($has_push){
            $r = $redis->exec();
        }
        return $r;
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
