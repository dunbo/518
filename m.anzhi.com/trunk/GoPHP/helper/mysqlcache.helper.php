<?php
//在配置有redis的机器上开启redis缓存，替代mysql缓存
function getMysqlCache($key, $option = '', $server = 'mysql')
{
    $key = getMysqlCacheKey($key, $option);
    $var_key = "GO_CACHE_" . $key;
    if (!array_key_exists($var_key, $GLOBALS)) {
        $value = false;

		if (extension_loaded('redis')) {
			$value = GoCache::get($key, array(), array(), 0, 'redis');
//			$GLOBALS[$var_key] = $value;
			return $value;
		}
        $value = GoCache::get($key, array(), array(), 0, $server);
//        $GLOBALS[$var_key] = $value;
    } else {
		$value = $GLOBALS[$var_key];
	}
    return $value;
}

function getMysqlCacheKey($key, $option = '', $server = 'mysql')
{
    $option = str_replace(array("/", " "), "_", $option);
    return strtoupper($key) . $option;
}


function setMysqlCache($key, $value, $option = '', $server = 'mysql')
{
	$set = true;
	
	if (isset($GLOBALS['set_mysql'])) {
		$set = $GLOBALS['set_mysql'];
	}
$set = false;
    $key = getMysqlCacheKey($key, $option);
	if (extension_loaded('redis')) {
//		GoCache::delete($key, 0, 'redis');
		GoCache::set($key, $value, 0, 'redis');
		GoCache::set($key.'_time', time(), 0, 'redis');
	}
	if ($set) {
		$var_key = "GO_CACHE_" . $key;
//		$GLOBALS[$var_key] = $value;
		
		GoCache::delete($key, 0, $server);
		return GoCache::set($key, $value, 0, $server);
	}
}
