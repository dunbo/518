<?php
function is_can_donwload($softid) {
    if (empty($softid)) return false;
    $memcached = GoCache::getCacheAdapter('memcached');
    $key = "ID2ALLENABLEDID_{$softid}";
    $has_realid = $memcached->get($key);
    if (!$has_realid) {
	    $redis = GoCache::getCacheAdapter('redis');
	    $realid = $redis->gethash('ALL:ID2ALLENABLEDID', $softid);
	    if (!empty($realid)) {
	        $memcached->set($key, 1, 300);
	        return true;   
	    } else {
//	        $memcached->set($key, 0, 300);
	        return false;   
	    }
    }
    return true;
}
