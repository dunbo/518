<?php
class GoCache
{
	public static  $cacheAdapters = array(); 

	/**
	 * 
	 * @param string $server
	 * @return GoCacheAdatper
	 */
	public static function getCacheAdapter($adapterName, $config='')
	{
		$adapterName = ucfirst($adapterName);
		$k = $adapterName. ':'. md5($config);
		if (isset(self::$cacheAdapters[$k])) {
			return self::$cacheAdapters[$k];
		} else {
			$adapterClassName = "Go{$adapterName}CacheAdapter";
		    $file_path_name = GO_CORE_LIBRARY.DS.$adapterClassName.".class.php";
			go_require_once($file_path_name);				
			$adapter = new $adapterClassName($config);
			
			self::$cacheAdapters[$k] = $adapter;
			return $adapter;
		}
	}

    public static function getCacheAdapterNew($config_name='')
    {
        if(empty($config_name)){
            $config_name = 'cache/memcached';
        }
        $config = load_config($config_name);
		if(empty($config)){
			$config = load_config('cache/memcached');
		}
        if(!isset($config['type'])){
            $config['type'] = 'Memcached';
        }
        $adapterName = ucfirst($config['type']);
        return self::getCacheAdapter($adapterName, $config);
    }
	
	public static function set($key, $value, $expired = 0, $adapterName = 'memcached', $config = '')
	{
		$adapter = self::getCacheAdapter($adapterName, $config);
		return $adapter->set($key, $value, $expired);
	}
	
	public static function get($key, $callback = array(), $param_arr = array(), $expired = 0, $adapterName = 'memcached', $config='')
	{
		$var_key = "GO_CACHE_{$adapterName}_{$key}" ;
		if (isset($GLOBALS[$var_key])) {
			return $GLOBALS[$var_key];
		} else {
			$adapter = self::getCacheAdapter($adapterName, $config);
			$value = $adapter->get($key, $callback, $param_arr, $expired);
			//$GLOBALS[$var_key] = $value;
			return $value;		
		}
	}
	
	public static function delete($key, $time = 0, $adapterName = 'memcached', $config='')
	{
		$adapter = self::getCacheAdapter($adapterName, $config);
		return $adapter->delete($key, $time);
	}
	
	public static function closeAll()
	{
		foreach(self::$cacheAdapters as $adapter) {
			$adapter->close();
		}
	}
}
